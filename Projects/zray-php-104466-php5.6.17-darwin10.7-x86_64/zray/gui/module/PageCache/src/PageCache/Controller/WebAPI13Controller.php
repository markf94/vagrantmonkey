<?php

namespace PageCache\Controller;

use Zend\View\Model\ViewModel;

use Audit\AuditTypeInterface;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use ZendServer\Mvc\Controller\WebAPIActionController;

use	WebAPI,
	ZendServer\Log\Log,
	ZendServer;
use Deployment\Model;

class WebAPI13Controller extends WebAPIActionController
{
	public function pagecacheExportRulesAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('applicationId' => -1));
		$resolver = $this->getLocator('ViewTemplatePathStackWebAPI'); /* @var $resolver \WebAPI\View\Resolver\TemplatePathStack */
		$resolver->setWebapiVersion('1.3'); // otherwise viewscripts will be looked using current webapi version (1.4 at the moment)
		
		try {
			$mapper = $this->getLocator('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
			$rules = $mapper->getRules(array(), array($params['applicationId']));
			
			$deploymentMapper = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentMapper \Deployment\Model */
			$apps = $deploymentMapper->getApplicationsInfo(array($params['applicationId']));
			$apps->setHydrateClass('\Deployment\Application\InfoContainer');
			
			foreach ($rules as $key => $rule) { /* @var $rule \PageCache\Rule */
				$appId = $rule->getAppId();
				if ($appId != -1) {
					foreach ($apps as $app) { /* @var $app \Deployment\Application\InfoContainer */
						if ($app->getApplicationId() == $appId) {
							$rulePath = $rule->getUrlPath();
							$appPath = parse_url($app->getBaseUrl(), PHP_URL_PATH );
							$relRulePath = substr($rulePath, strlen($appPath));
							$rule->setUrlPath($relRulePath);
							$rule->setUrlHost("");
							
							$rules[$key] = $rule;
							break;
						}
					}
				}
			}	
			
			
			
		} catch (\Exception $e) {
			Log::err($e);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		// prepare the environment for a file download
		$this->setHttpResponseCode('200', 'OK');
		$response = $this->getResponse(); /* @var $response \Zend\Http\PhpEnvironment\Response */
		$response->getHeaders()->addHeaders(array(
				'Content-Type' => 'application/vnd.zend.pagecache.rules+xml',
				'Content-Disposition' => 'attachment; filename="pagecache_rules.xml"'
		));
		
		// @todo remove temporary fix for view model variables' propagation when setTerminal is true
		$this->layout('layout/nothing');
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setVariable('rules', $rules);
		return $viewModel;
	}
	
	public function pagecacheImportRulesAction() {
		$params = $this->getParameters();
		$paceCacheRules = $params['paceCacheRules'];
		$pageCacheRulesXml = new \SimpleXMLElement($paceCacheRules);

		try {
			$mapper = $this->getLocator('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
			
			foreach ($pageCacheRulesXml->cache->url as $ruleXml){ /* @var $ruleXml \SimpleXMLElement */
				$rule = new \PageCache\Rule();
				$rule->loadXml($ruleXml, -1);
				$mapper->saveRule($rule);
			}
		} catch (\Exception $e) {
			Log::err($e);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		$this->rulesChanged($this->getServersMapper()->findRespondingServersIds());
		
		return array('pagecacheIsImported' => true);
	}	
	
	public function pagecacheClearCacheByRuleNameAction() {		
		try {
			$this->isMethodPost();
		} catch (\Exception $e) {
			$this->handleException($e, 'The method used should be POST');
		}
		
		$params = $this->getParameters(array(
				'ruleName' => "",			
				'uri' => "",
		));
		
		$this->validateStringNonEmpty($params['ruleName'], 'ruleName');
		if ($params['uri']) {
			$this->validateUri($params['uri'], 'uri');
		}
		
		$audit = $this->auditMessage(AuditTypeInterface::AUDIT_CLEAR_PAGE_CACHE_CACHE, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $audit \Audit\Container */
		
		$tasksMapper = $this->getLocator()->get('Zsd\Db\TasksMapper'); /* @var $tasksMapper \Zsd\Db\TasksMapper */
		$this->getLocator()->get('PageCache\Model\Tasks')->clearCache($params);
		
		$viewModel = new ViewModel();
		$viewModel->setTemplate('page-cache/web-api/pagecache-cache-clear');
		return $viewModel;
	}
	
	public function pagecacheSaveApplicationRuleAction() {
		$params = $this->getParameters(array(
				'ruleId' => "-1",
				'urlScheme' => 'http',
				'urlPath' => '',
				'matchType' => 'exactMatch',
				'lifetime' => "",
				'compress' => "TRUE",
				'name' => '',
				'applicationId' => "-1",
				'conditionsType' => 'and',
				'conditions' => array(),
				'splitBy' => array(),
		));
		
		$this->validatePositiveInteger($params['applicationId'], "applicationId");
		
		$app = $this->getApp($params['applicationId']);
				
		if ($app) {
			$baseUrl = $app->getBaseUrl();
			
			$port = parse_url($baseUrl, PHP_URL_PORT);	
			if ($port) {
				$params['urlHost'] = parse_url($baseUrl, PHP_URL_HOST) . ":$port";
			} else {
				$params['urlHost'] = parse_url($baseUrl, PHP_URL_HOST);
			}
			$params['urlPath'] = parse_url($baseUrl, PHP_URL_PATH) . "/" . $params['urlPath'];
		} else {
			throw new WebAPI\Exception(_t("Invalid application ID '%s'",array($params['applicationId'])), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $this->doSaveRule($params);
			
	}
	
	private function doSaveRule($params) {
		$params = $params->toArray();
		try {
			
			$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
			
			$this->isMethodPost();
			
			$audit = $this->auditMessage(Mapper::AUDIT_PAGE_CACHE_SAVE_RULE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(
					'name' => $params['name']
					, 'ruleId' => $params['ruleId']
			))); /* @var $audit \Audit\Container */
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, array(array(
					'name' => $params['name']
					, 'ruleId' => $params['ruleId'])));
			
			try {
								
				if (isset($params['ruleId'])) {
					$this->validateInteger($params['ruleId'], "ruleId");
				}
				$this->validateInteger($params['applicationId'], "applicationId");
				if (intval($params['applicationId']) != -1) {
					$this->validateExistingAppId($params['applicationId'], "applicationId");
				}
				
				$this->validateStringNonEmpty($params['name'], "name");
				$this->validatePositiveInteger($params['lifetime'], "lifetime");
				$this->validateAllowedValues($params['matchType'], 'matchType', $this->getAllowedMatchTypes());
				
				// handling for edge case
				$validatehost = strstr($params['urlHost'], Model::DEFAULT_SERVER) ? 'default-server' : $params['urlHost'];
				
				if ($params['matchType'] != "exactMatch") {
					$this->validateValidRegex($validatehost, 'urlHost');
					$this->validateValidRegex($params['urlPath'], 'urlPath');
				} else {
					$this->validateHostWithPort($validatehost, 'urlHost');
					if ($params['urlPath']) {
						$this->validateUri("http://{$validatehost}/{$params['urlPath']}", 'urlPath');
					}
				}
				
				$this->validateAllowedValues($params['compress'], 'compress', $this->getAllowedCompressValues());
				$this->validateAllowedValues($params['urlScheme'], 'urlScheme', array("", "http","https", "https?"));
				$this->validateAllowedValues($params['conditionsType'], 'conditionsType', array("and","or"));
				
				$this->validateArray($params['conditions'], 'conditions');
				foreach($params['conditions'] as $id => $condition) {
					$this->validateAllowedValues($condition['global'], 'global', $mapper->getSuperGlobalsDictionary());					

					$this->validateArrayKey($condition['element'], 'condition');
				}
				
				$this->validateStringNonEmpty($params['name'], 'name');
				
				$this->validateArray($params['splitBy'], 'splitBy');
				foreach($params['splitBy'] as $key => $splitBy) {
					
					if ($splitBy['global'] == 'entire') {
						$params['splitBy'][$key]['global']  = '_SERVER';
						$params['splitBy'][$key]['element'] = 'QUERY_STRING';
						
						$splitBy['global'] = '_SERVER';
						$splitBy['element'] = 'QUERY_STRING';
					}
					
					if ($splitBy['global'] == 'uri') {
						$params['splitBy'][$key]['global']  = '_SERVER';
						$params['splitBy'][$key]['element'] = 'REQUEST_URI';
					
						$splitBy['global'] = '_SERVER';
						$splitBy['element'] = 'REQUEST_URI';
					}
							
					$this->validateAllowedValues($splitBy['global'], 'splitBy[global]', $mapper->getSplitSuperGlobalsDictionary());
				
					$splitByElement = trim($splitBy['element']);
					if (empty($splitByElement)) {
						throw new WebAPI\Exception(_t("Missing value for selected super global"), WebAPI\Exception::INVALID_PARAMETER);
					}
					
					if ($splitBy['element']) {
						$this->validateArrayKey($splitBy['element'], 'splitBy');
					}					
				}
				
				$rule = new \PageCache\Rule;
				$rule->loadArray($params, $params['applicationId']);
	
				// new rule cannot contain the existing url or name
				if ($params['ruleId'] == "-1" && ($mapper->ruleUrlExists($rule->getUrl()) || ! $this->validateUniqueRuleName($params['name']))) {			
					throw new WebAPI\Exception(_t("A rule with the url '%s' already exists",array($rule->getUrl())), WebAPI\Exception::INVALID_PARAMETER);
				}
			} catch (WebAPI\Exception $e) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
						$e->getMessage())));
				
				throw $e;
			}
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$ruleId = $mapper->saveRule($rule);
		
		if ($ruleId == -1) {
			throw new WebAPI\Exception(_t("Rule '%s' does not exist",array($params['ruleId'])), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		Log::debug("Updated Page Cache rule " . $ruleId);
		
		if (isset($params['notifySelfOnly']) && $params['notifySelfOnly'] == '1') {
			$edition = new ZendServer\Edition();
			$servers = array($edition->getServerId());
		} else {
			$servers = $this->getServersMapper()->findRespondingServersIds();			
		}
		
		if (!isset($params['notifyChange']) || $params['notifyChange'] == '1') {
			$this->rulesChanged($servers);
		} else {
			$this->rulesChanged(array($edition->getServerId()));
		}
		
		
		$rule = $mapper->getRules(array($ruleId));
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array(
				'name' => $params['name']
				, 'ruleId' => $params['ruleId'])));
		
		$viewModel = new ViewModel(array('rule' => array_pop($rule)));
		$viewModel->setTemplate('page-cache/web-api/pagecache-rule-info');
		return $viewModel;
	}
	
	public function pagecacheSaveRuleAction() {
		$params = $this->getParameters(array(
				'ruleId' => "-1",
				'urlScheme' => 'http',
				'urlHost' => '',
				'urlPath' => '',
				'matchType' => 'exactMatch',
				'lifetime' => "",
				'compress' => "TRUE",
				'name' => '',
				'applicationId' => "-1",
				'conditionsType' => 'and',
				'conditions' => array(),
				'splitBy' => array(),
		));
		
		return $this->doSaveRule($params);			
	}
	
	/**
	 * 
	 * @param integer $appId
	 * @return \Deployment\Application\InfoContainer|NULL
	 */
	protected function getApp($appId) {
		
		$deploymentMapper = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentMapper \Deployment\Model */
		$apps = $deploymentMapper->getApplicationsInfo(array($appId));
		$apps->setHydrateClass('\Deployment\Application\InfoContainer');
				
		foreach ($apps as $app) {
			/* @var $app \Deployment\Application\InfoContainer */
			if ($app->getApplicationId() == $appId) {
				return $app;
			}
		}
	
		return NULL;
	}
	
	
	public function pagecacheDeleteRulesByApplicationIdAction() {
		
		$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
		
		try {
			$params = $this->getParameters();
			$this->isMethodPost();
			$this->validateMandatoryParameters($params, array('applicationId'));
			$this->validatePositiveInteger($params['applicationId'], 'applicationId');
			
			$auditData = $params['applicationId'];
			$deploymentModel = $this->getLocator()->get('Deployment\Model');
			$appContainer = $deploymentModel->getApplicationById($auditData);
			
			$audit = $this->auditMessage(Mapper::AUDIT_PAGE_CACHE_DELETE_RULES, ProgressMapper::AUDIT_PROGRESS_STARTED, array(array(
					'applicationId' => $auditData,
					'applicationName' => $appContainer->getApplicationName()
			))); /* @var $audit \Audit\Container */
				
		} catch (ZendServer\Exception $e) {
				
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
					$e->getMessage())));
				
			$this->handleException($e, 'Input validation failed');
		}
		
		Log::debug("Deleting rules of application " . $params['applicationId']);
		
		$rulesDeleted = $mapper->getRules(array(), array($params['applicationId']));
		
		$mapper->deleteRulesByApplicationId($params['applicationId']);
		
		if (isset($params['notifySelfOnly']) && $params['notifySelfOnly'] == '1') {
			$edition = new ZendServer\Edition();
			$servers = array($edition->getServerId());
		} else {
			$servers = $this->getServersMapper()->findRespondingServersIds();
		}
		
		$this->rulesChanged($servers);
		
		$auditData = array();
		foreach ($rulesDeleted as $deleted) {
			$auditData[] = $deleted->getName();
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array(
				'applicationId' => $auditData,
				'applicationName' => $appContainer->getApplicationName())));
		
		$viewModel = new ViewModel(array('rules' => $rulesDeleted));
		$viewModel->setTemplate('page-cache/web-api/pagecache-rules-list');
		return $viewModel;
	}
	
	public function pagecacheDeleteRulesAction() {
	
		$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
		
		try {
			$params = $this->getParameters();
			
			$auditData = $params['rules'];
			
			$audit = $this->auditMessage(Mapper::AUDIT_PAGE_CACHE_DELETE_RULES, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(
					'rules' => $auditData
			))); /* @var $audit \Audit\Container */
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, array(array(
					'rules' => $auditData)));
			
			
			$this->isMethodPost();
			$this->validateMandatoryParameters($params, array('rules'));
			$this->validateArray($params['rules'], 'rules');
			foreach ($params['rules'] as $key => $ruleId) {
				$this->validatePositiveInteger($ruleId, "rules[$key]");
			}
		} catch (ZendServer\Exception $e) {
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
				$e->getMessage())));			
			
			$this->handleException($e, 'Input validation failed');
		}
		
		Log::debug("Deleting rules " . var_export($params['rules'], true));
		
		$rulesDeleted = $mapper->getRules($params['rules']);
		
		$mapper->deleteRules($params['rules']);			

		if (isset($params['notifySelfOnly']) && $params['notifySelfOnly'] == '1') {
			$edition = new ZendServer\Edition();
			$servers = array($edition->getServerId());
		} else {
			$servers = $this->getServersMapper()->findRespondingServersIds();
		}
		
		$this->rulesChanged($servers);
		
		$auditData = array();
		foreach ($rulesDeleted as $deleted) {
			$auditData[] = $deleted->getName();
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array(
					'rules' => $auditData)));
		$viewModel = new ViewModel(array('rules' => $rulesDeleted));
		$viewModel->setTemplate('page-cache/web-api/pagecache-rules-list');
		return $viewModel;
	}
	
	public function pagecacheRulesListAction() {
		$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
		try {
			$this->isMethodGet();
			
			$params = $this->getParameters(array(
					'applicationIds' => array()
					, 'freeText' => ""
					
					));

			$this->validateArray($params['applicationIds'], "applicationIds");
			$this->validateString($params['freeText'], "freeText");
			
			foreach ($params['applicationIds'] as $appId) {
				$appId = intval($appId);
				if ($appId != -1) {
					$this->validateExistingAppId($appId, $appId);
				}
			}
			
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$rules = $mapper->getRules(array(), $params['applicationIds'], $params['freeText']);
		
		Log::debug("Received " . count($rules) . " from page cache mapper");
		
		foreach ($rules as $key => $rule) {
			$appId = $rule->getAppId();
			$app = $this->getApp($appId);
			if ($app) {
				$rules[$key]->setAppName($app->getApplicationName());
			}
		}
		
		return array('rules' => $rules);
	}
	
	public function pagecacheRuleInfoAction() {
		$mapper = $this->getLocator()->get('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
					
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array(
					'id' => ''));
			$this->validateMandatoryParameters($params, array('id'));
			$this->validateInteger($params['id'], 'id');
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$rules = $mapper->getRules(array($params['id']));
		if (!$rules) {
			throw new WebAPI\Exception(_t("Cannot find a rule with the provided ID '%s'",array($params['id'])), WebAPI\Exception::INVALID_PARAMETER);
		}
		$rule = array_pop($rules);
		
		$appId = $rule->getAppId();
		$app = $this->getApp($appId);
		if ($app) {
			$rule->setAppName($app->getApplicationName());
		}
		
		return array('rule' => $rule);
	}
	

	
	protected function getAppName($appId) {
	
		$deploymentMapper = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentMapper \Deployment\Model */
		$apps = $deploymentMapper->getApplicationsInfo();
		$apps->setHydrateClass('\Deployment\Application\InfoContainer');
	
		foreach ($apps as $app) {
	
			/* @var $app \Deployment\Application\InfoContainer */
			if ($app->getApplicationId() == $appId) {
				return $app->getUserApplicationName();
			}
		}
	
		return "";
	}
		
	/**
	 * @param integer $order
	 * @throws WebAPI\Exception
	 */
	protected function validateOrder($order, $allowedColumns) {
		$order = strtolower($order);
		$allowedColumns = array_change_key_case($allowedColumns);
		if (! isset($allowedColumns[$order])) {
			throw new WebAPI\Exception(
					_t('Parameter \'order\' must be one of %s',
							array(implode(', ', array_keys($allowedColumns)))),
					WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	
	protected function getAllowedMatchTypes() {
		return array (
				"exactMatch",
				"regexMatch",
				"regexIMatch",				
				); 
		
	}
	
	protected function getAllowedCompressValues() {
		return array (
				"TRUE",
				"FALSE",			
		);
	
	}
	
	private function rulesChanged($serversIds) {			
		$this->getLocator()->get('PageCache\Model\Tasks')->syncPageCacheRulesChanges($serversIds); // whenever we change the rules data, we should notify ZSD to sync changes to all responding servers
	} 
	
	private function validateArrayKey($key, $name){
		
		$key = trim($key);
		
		$ok = ($key !== "");
		if (!$ok) {
			throw new WebAPI\Exception(_t("Failed to add $name due to missing key element for selected super global"), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		if (strpos($key, "[") !== false || strpos($key, "]") !== false) {
			if (!preg_match('#^(\[[^\]\[]+\]){1,}$#', $key)) {
				$ok = false;
			}
		}
		
		if (!$ok) {
			throw new WebAPI\Exception(_t("Invalid element '%s'. A valid super global element should be provided in '$name'",array($key)), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	private function validateUniqueRuleName($name) {
		$mapper = $this->getLocator('PageCache\Model\Mapper'); /* @var $mapper \PageCache\Model\Mapper */
		$rules = $mapper->getRules(array(), array(), '', $name);
		if (count($rules) > 0) {
			throw new WebAPI\Exception(_t('A rule with the name \'%s\' already exists', array($name)), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $name;
	}
}