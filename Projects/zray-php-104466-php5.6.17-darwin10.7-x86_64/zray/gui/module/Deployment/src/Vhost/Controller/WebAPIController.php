<?php
namespace Vhost\Controller;

use ZendServer\Mvc\Controller\WebAPIActionController,
	WebAPI,
	Audit\Db\Mapper as auditMapper,
	ZendServer\Log\Log,
	Audit\Db\ProgressMapper,
	Zend\View\Model\ViewModel,
	Zend\Uri\UriFactory,
	Application\Module,
	Vhost\VhostNodeContainer,
	Vhost\Reply\Exception;
use Vhost\Mapper\AddVhost;
use Vhost\Mapper\AbstractVhostAction;
use Vhost\Mapper\EditVhost;
use Vhost\Entity\Vhost;
use Vhost\Form\VhostActionHydrator;
use Zend\Form\Form;
use Vhost\Reply\VhostOperationContainer;
use ZendServer\Configuration\Manager;

use Vhost\Filter\Filter,
	Vhost\Filter\Translator,
	Vhost\Filter\Dictionary;

class WebAPIController extends WebAPIActionController
{
	/**
	 * @var \Vhost\Filter\Dictionary
	 */
	private $dictionary;
	
	public function vhostValidateSslAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('sslCertificateChainPath' => '', 'sslCertificatePath' => '', 'sslCertificateKeyPath' => '', 'sslAppName' => ''));
		
		$sslCertificatePath = $this->validateString($params['sslCertificatePath'], 'sslCertificatePath');
		$sslCertificateKeyPath = $this->validateString($params['sslCertificateKeyPath'], 'sslCertificateKeyPath');
		$sslCertificateChainPath = $this->validateString($params['sslCertificateChainPath'], 'sslCertificateChainPath');
		$sslAppName = $this->validateString($params['sslAppName'], 'sslAppName');
		
		$tasks = $this->getLocator()->get('Vhost\Mapper\Tasks'); /* @var $tasks \Vhost\Mapper\Tasks */
		$taskId = $tasks->validateSslFiles($sslCertificatePath, $sslCertificateKeyPath, $sslCertificateChainPath, $sslAppName);
		
		$reply = $this->getRepliesMapper()->waitAndExtractReply($taskId);
		$viewModel = new ViewModel(array('reply' => $reply));
		$viewModel->setTemplate('vhost/web-api/vhost-validate-template');
		return $viewModel;
	}
	
	public function vhostValidateTemplateAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('sslEnabled' => 'FALSE', 'sslCertificatePath' => '', 'sslCertificateKeyPath' => '', 'sslCertificateChainPath' => '', 'sslAppName' => ''));
		$this->validateMandatoryParameters($params, array('name', 'port', 'template'));
		$name = $this->validateStringNonEmpty($params['name'], 'name');
		$port = $this->validatePositiveInteger($params['port'], 'port');
		$template = $this->validateStringNonEmpty($params['template'], 'template');
		
		$sslEnabled = $this->validateBoolean($params['sslEnabled'], 'sslEnabled');
		$sslCertificatePath = $this->validateString($params['sslCertificatePath'], 'sslCertificatePath');
		$sslCertificateKeyPath = $this->validateString($params['sslCertificateKeyPath'], 'sslCertificateKeyPath');
		$sslCertificateChainPath = $this->validateString($params['sslCertificateChainPath'], 'sslCertificateChainPath');
		$sslAppName = $this->validateString($params['sslAppName'], 'sslAppName');
		
		$tasks = $this->getLocator()->get('Vhost\Mapper\Tasks'); /* @var $tasks \Vhost\Mapper\Tasks */
		$taskId = $tasks->validateTemplate($name, $port, $template, $sslEnabled, $sslCertificatePath, $sslCertificateKeyPath, $sslCertificateChainPath, $sslAppName);
		
		$reply = $this->getRepliesMapper()->waitAndExtractReply($taskId);
		return array('reply' => $reply, 'success' => $reply->getSuccessCode(), 'message' => $reply->getMessage());
	}
	
	public function vhostDisableDeploymentAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('vhost'));
		$vhostId = $this->validateInteger($params['vhost'], "vhost");
		
		$vhostMapper = $this->getVhostMapper();
		$currentVhost = $this->validateVhostExists($params['vhost']);
		
		try {
			
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_VHOST_DISABLE_DEPLOYMENT,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Vhost: %s', array($currentVhost->getName() . ':' . $currentVhost->getPort())))));
		
			$status = $vhostMapper->vhostStatus($currentVhost);
			if (! in_array($status, array(Vhost::STATUS_OK, Vhost::STATUS_PENDING_RESTART))) {
				throw new \ZendServer\Exception(_t('Deployment can only be disabled on valid virtual hosts')); 
			}
			
			if ($currentVhost->isZendDefined()) {
				throw new WebAPI\Exception(_t("Vhost %s deployment cannot be disabled on Zend Server vhosts", array($vhostId)), WebAPI\Exception::VIRTUAL_HOST_INVALID);
			}
			
			if (! $currentVhost->isManagedByZend()) {
				throw new WebAPI\Exception(_t("Vhost %s deployment already disabled", array($vhostId)), WebAPI\Exception::VIRTUAL_HOST_INVALID);
			}
			
			$vhostMapper->unmanageVhost($vhostId);
		} catch (\Exception $e) {
			Log::err("Failed to redeploy vhost:" . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			if ($e instanceof \ZendServer\Exception) {
				throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
			throw $e; 
		}
		
		$vhostsNodes = $this->getVhostNodes(array($vhostId));
		
		return array('vhosts' => array($currentVhost), 'vhostsNodes' => $vhostsNodes);
	}
	
	public function vhostEnableDeploymentAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array('applyImmediately' => 'TRUE'));
		$this->validateMandatoryParameters($params, array('vhost'));
		$this->validatePositiveInteger($params['vhost'], 'vhost');
		$applyImmediately = $this->validateBoolean($params['applyImmediately'], 'applyImmediately');
		
		$currentVhost = $this->validateVhostExists($params['vhost']);
		
		try {
			$vhostMapper = $this->getVhostMapper();
			
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_VHOST_ENABLE_DEPLOYMENT,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Vhost: %s', array($currentVhost->getName() . ':' . $currentVhost->getPort())))));
		
			$status = $vhostMapper->vhostStatus($currentVhost);
			if (! in_array($status, array(Vhost::STATUS_OK, Vhost::STATUS_PENDING_RESTART))) {
				throw new \ZendServer\Exception(_t('Deployment can only be disabled on valid virtual hosts'));
			}
			
			if (! $currentVhost->isManagedByZend()) {
				$vhostMapper->manageVhost($params['vhost'], $applyImmediately);
			}
			
		} catch (\Exception $e) {
			Log::err("Failed to enable deployment on vhost:" . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			throw new WebAPI\Exception(_t('Failed to enable deployment on vhost. %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$installDir = get_cfg_var ( 'zend.install_dir' );
		$webserverType = $this->getDirectivesMapper()->selectSpecificDirectives(array('zend.webserver_type'))->current()->getFileValue();
		if ($webserverType == 'nginx') {
			$addLine = 'include "' . $installDir . '/etc/sites.d/zend_managed_vhost_' . $currentVhost->getId() . '.conf";';
		} else {
			$addLine = 'Include "' . $installDir . '/etc/sites.d/zend_managed_vhost_' . $currentVhost->getId() . '.conf"';
		}
		
		$addLine = str_replace('/', DIRECTORY_SEPARATOR, $addLine);
		
		$toPath = $currentVhost->getConfigFile();
		
		return array('addLine' => $addLine, 'toPath' => $toPath);
	}
	
	public function vhostGetStatusAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(
			array('vhosts' => array(), 'limit' => Module::config('list', 'resultsPerPage'), 'offset' => 0, 'order' => 'name', 'direction' => 'ASC', 'filters' => array(), 'filterName' => '')
		);
		
		$vhostsIds = $this->validateArray($params['vhosts'], 'vhosts');
		foreach ($vhostsIds as $idx => $vhost) {
			$this->validateInteger($vhost, "vhosts[{$idx}]");
		}
		
		$this->validateLimit($params['limit']);
		$this->validateOffset($params['offset']);
		$this->validateOrder($params['order']);
		$this->validateDirection($params['direction']);
		$filterData = $this->validateArray($params['filters'], 'filters');
		$filterData = $this->renameFilterKeys($filterData);
		$filterName = $this->validateString($params['filterName'], 'filterName');
		$dictionary = $this->getDictionary();
		foreach (array_keys($filterData) as $filterKey) {
			$this->validateAllowedValues($filterKey, "filter[{$filterKey}]", $dictionary->getFilterColumns());
		}
			
		$filter = $this->getFilterObj($filterName, $filterData);
		$translator = new Translator($filter);
		
		$vhostMapper = $this->getVhostMapper();
		
		$requestedSpecificVshosts = false;
		if (count($vhostsIds) > 0) {
			$requestedSpecificVshosts = true;
		}
		
		$vhostsResult = $vhostMapper->getVhosts($vhostsIds, $translator->translate(), $params['limit'], $params['offset'], $params['order'], $params['direction']);
		$vhosts = array();
		$vhostsIds = array();
		foreach ($vhostsResult as $vhost) {
			$vhosts[] = $vhost;
			$vhostsIds[] = $vhost->getId();
		}

		$vhostsNodes = $this->getVhostNodes($vhostsIds);
		
		if ($requestedSpecificVshosts) {
			$total = count($vhosts);
		} else {
			$total = $vhostMapper->countVhosts($translator->translate());
		}
		
		return array('vhosts' => $vhosts, 'vhostsNodes' => $vhostsNodes, 'total' => $total);
	}
	
	public function vhostRedeployAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('vhost'));
		$vhostId = $this->validateInteger($params['vhost'], "vhost");
	
		$vhostMapper = $this->getVhostMapper();
			
		$currentVhost = $this->validateVhostExists($params['vhost']);
		$vhostValidator = $this->getLocator()->get('Vhost\Validator\VhostValidForRedeploy'); /* @var $vhostValidator \Vhost\Validator\VhostValidForDeploy */
		if (! $vhostValidator->isValid("{$currentVhost->getName()}:{$currentVhost->getPort()}")) {
			throw new WebAPI\Exception(current($vhostValidator->getMessages()), WebAPI\Exception::VIRTUAL_HOST_IS_NOT_MANAGED);
		}
		
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_VHOST_REDEPLOY, ProgressMapper::AUDIT_NO_PROGRESS,
					array(array(_t('Vhost: %s', array($currentVhost->getName() . ':' . $currentVhost->getPort())))));
				
			$vhostMapper->redeployVhost($params['vhost']);
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_REQUESTED);
		} catch (\Exception $e) {
			Log::err("Failed to redeploy vhost:" . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			throw new WebAPI\Exception(_t('Failed to redeploy vhost %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$vhostsNodes = $this->getVhostNodes(array($vhostId));
		
		return array('vhosts' => array($currentVhost), 'vhostsNodes' => $vhostsNodes);
	}
	
	public function vhostRemoveAction() {
		$this->isMethodPost();
				
		$params = $this->getParameters(
			array('vhosts' => array(), 'removeApplications' => 'FALSE')
		);
		
		$this->validateMandatoryParameters($params, array('vhosts'));
		
		$vhostsIds = $this->validateArrayNonEmpty($params['vhosts'], 'vhosts');
		$removeApplications = $this->validateBoolean($params['removeApplications'], 'removeApplications');
		
		foreach ($vhostsIds as $idx => $vhost) {
			$this->validateInteger($vhost, "vhosts[{$idx}]");
			$this->validateVhostExists($vhost);
		}
		
		$vhostMapper = $this->getVhostMapper();
		
		$vhostsResult = $vhostMapper->getVhosts($vhostsIds);
		$vhostIds = array();
		$vhostNames = array();
		foreach ($vhostsResult as $vhostResult) { /* @var $vhostResult \Vhost\Entity\Vhost */
			if (! $vhostResult->isZendDefined()) {
				throw new WebAPI\Exception(_t('Cannot remove system vhost %s:%s', array($vhostResult->getName(), $vhostResult->getPort())), WebAPI\Exception::VIRTUAL_HOST_IS_NOT_MANAGED); 
			}
			$vhostIds[] = (int) $vhostResult->getId();
			$vhostNames[] = "{$vhostResult->getName()}:{$vhostResult->getPort()}";
		}
		
		if (! $removeApplications) {
			foreach ($vhostsIds as $idx => $vhost) {
				$vhostApps = $this->vhoseGetApplications($vhost);
				$vhost = $this->getVhostMapper()->getVhostById($vhost);	
				if (count($vhostApps) > 0) {
					$vhostApp = current($vhostApps);
					throw new WebAPI\Exception(_t('There is an application relying on \'%s\'. To remove the virtual host, first remove the application \'%s:%s\'.', array($vhost->getName(), $vhost->getPort(), $vhostApp['name'])), WebAPI\Exception::VIRTUAL_HOST_HAS_DEPENDENTS);
				}
			}
		}
		
		try {
			$appsToRemove = array();
			
			if ($removeApplications) {
				foreach ($vhostIds as $vhostId) {
					$vhostApps = $this->vhoseGetApplications($vhostId);
					foreach ($vhostApps as $appId => $appData) {
						$appsToRemove[$appId] = $appData['userName'];
					}
				}
			}
			
			$auditExtraData = array(_t('Vhosts') => implode(', ', $vhostNames));
			if (count($appsToRemove) > 0) {
				$auditExtraData[_t('Removed Applications')] = array_values($appsToRemove); 
			}	
			
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_VHOST_REMOVE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($auditExtraData));
			
			$vhostMapper->removeVhosts($vhostIds);
			
			foreach ($appsToRemove as $appId => $appName) {
				$this->getRequest()->getPost()->set('ignoreFailures', 'TRUE');
				$this->getRequest()->getPost()->set('removeApplicationData', 'TRUE');
				$this->getRequest()->getPost()->set('appId', $appId);
				
				$this->forward()->dispatch('DeploymentWebAPI-1_2', array('action' => 'applicationRemove'));
			}
			
		} catch (\Exception $e) {
			Log::err("Failed to remove vhosts:" . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			throw new WebAPI\Exception(_t('Failed to remove vhosts %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		Log::info("Vhosts: " . implode(', ', $params['vhosts']) . ' have been removed');
		
		$vhostsResult = $vhostMapper->getVhosts($params['vhosts']);
		$vhosts = array();
		$vhostsIds = array();
		foreach ($vhostsResult as $vhost) {
			$vhosts[] = $vhost;
			$vhostsIds[] = $vhost->getId();
		}
		$vhostsNodes = $this->getVhostNodes($vhostsIds);
		
		$viewModel = new ViewModel(array('vhosts' => $vhosts, 'vhostsNodes' => $vhostsNodes));
		$viewModel->setTemplate('vhost/web-api/1x6/vhost-get-status');
		return $viewModel;
		
	}
	
	public function vhostAddSecureIbmiAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('forceCreate' => 'FALSE', 'sslAppName' => ''));
		$this->validateMandatoryParameters($params, array('name', 'sslAppName'));
		$params = $this->validateVhostParameters($params);
		$params['sslEnabled'] = true;
	
		return $this->addVhost($params);
	}
	
	public function vhostAddSecureAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('forceCreate' => 'FALSE', 'sslCertificateChainPath' => ''));
		$this->validateMandatoryParameters($params, array('name', 'sslCertificatePath', 'sslCertificateKeyPath'));
		$params = $this->validateVhostParameters($params);
		$params['sslEnabled'] = true;
		
		return $this->addVhost($params);
	}
	
	public function vhostAddAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('forceCreate' => 'FALSE'));
		$this->validateMandatoryParameters($params, array('name'));
		$params = $this->validateVhostParameters($params);
		$params['sslEnabled'] = false;
		return $this->addVhost($params);
	}
	
	public function vhostEditAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('forceCreate' => 'FALSE', 'sslCertificatePath' => '', 'sslCertificateKeyPath' => '', 'sslCertificateChainPath' => '', 'sslAppName' => ''));
		$this->validateMandatoryParameters($params, array('template', 'vhostId'));
		
		$vhostEditMapper = $this->getLocator()->get('Vhost\Mapper\EditVhost');
		$params = $this->validateVhostParameters($params);
		
		$vhostMapper = $this->getVhostMapper();
		
		$vhostEditMapper->setForceCreate($params['forceCreate']);
		$vhostEditMapper->setVhostId($params['vhostId']);
		$vhostEditMapper->setTemplate($params['template']);
		
		$vhost = $this->validateVhostExists($vhostEditMapper->getVhostId());
		
		// fill with default values
		$vhostEditMapper->setSslCertificatePath($vhost->getCertificatePath());
		$vhostEditMapper->setSslCertificateKeyPath($vhost->getCertificateKeyPath());
		$vhostEditMapper->setSslCertificateChainPath($vhost->getCertificateChainPath());
		$vhostEditMapper->setSslAppName($vhost->getAppName());
		

		if (! $vhost->isZendDefined()) {
			throw new \WebAPI\Exception("Only Zend-Defined virtual hosts can be modified", \WebAPI\Exception::WEBSERVER_CONFIGURATION_ERROR); 
		}
		
		if (! $vhost->isSsl()
			/// check that all ssl parameters are empty if this is not an SSL vhost
			&& count(array_filter(
				array($params['sslCertificateChainPath'], $params['sslCertificatePath'], $params['sslCertificateKeyPath'], $params['sslAppName']),
				function($item) {return (strlen($item) > 0);}
		))) {
			
			throw new \WebAPI\Exception("Not-secured vhost '{$vhost->getName()}:{$vhost->getPort()}' may not accept SSL parameters", \WebAPI\Exception::INVALID_PARAMETER); 
		}
		
		// on ibmi use only sslAppName parameter
		$manager = new Manager();
		if ($manager->getOsType() == \ZendServer\Configuration\Manager::OS_TYPE_IBMI) {
			$this->validateString($params['sslAppName'], 'sslAppName');
			if (! empty($params['sslAppName'])) {
				$vhostEditMapper->setSslAppName($params['sslAppName']);
			}
		} else { // on other os use certificate, key and chain
			$this->validateString($params['sslCertificatePath'], 'sslCertificatePath');
			$this->validateString($params['sslCertificateKeyPath'], 'sslCertificateKeyPath');
			$this->validateString($params['sslCertificateChainPath'], 'sslCertificateChainPath');
			
			if (! empty($params['sslCertificatePath'])) {
				$vhostEditMapper->setSslCertificatePath($params['sslCertificatePath']);
			}
			if (! empty($params['sslCertificateKeyPath'])) {
				$vhostEditMapper->setSslCertificateKeyPath($params['sslCertificateKeyPath']);
			}
			if (! empty($params['sslCertificateChainPath'])) {
				$vhostEditMapper->setSslCertificateChainPath($params['sslCertificateChainPath']);
			}
		}
		
		$vhostEditMapper->setVhostName($vhost->getName());
		$vhostEditMapper->setPort($vhost->getPort());
		
		try {
				
			$auditType = auditMapper::AUDIT_VHOST_EDIT;
			$auditData = array(
				_t('Vhost Id') => $vhostEditMapper->getVhostId(),
				_t('Vhost Name') => "{$vhostEditMapper->getVhostName()}:{$vhostEditMapper->getPort()}",
				_t('Vhost Template') => $vhostEditMapper->getTemplate()
			);
				
			$auditMessage = $this->auditMessage($auditType, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($auditData));
			
			$vhost = $vhostEditMapper->setVhost();
		} catch (\Exception $e) {
			$code = WebAPI\Exception::INTERNAL_SERVER_ERROR;
			if ($e instanceof \Vhost\Mapper\Exception && $e->getCode() == \Vhost\Mapper\Exception::APACHE_CONFIGURATION_INVALID) {
				$code = WebAPI\Exception::WEBSERVER_CONFIGURATION_ERROR;
			}
			Log::err("Failed to edit vhost: " . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			throw new WebAPI\Exception(_t("Failed to edit vhost: %s", array($e->getMessage())), $code, $e);
		}
	
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		Log::info("Vhost has been editted");
		
		$vhosts = array($vhost);
		$vhostsIds = array($vhost->getId());
		
		$vhostsNodes = $this->getVhostNodes($vhostsIds);
		
		$viewModel = new ViewModel(array('vhosts' => $vhosts, 'vhostsNodes' => $vhostsNodes));
		$viewModel->setTemplate('vhost/web-api/1x6/vhost-get-status');
		return $viewModel;
		
	}
	
	public function vhostGetDetailsAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('vhost'));
		$vhostId = $this->validatePositiveInteger($params['vhost'], 'vhost');

		$vhost = $this->validateVhostExists($vhostId);
		$vhostsNodes = $this->getVhostNodes(array($vhostId));
		
		return array('vhosts' => array($vhost), 'vhostsNodes' => $vhostsNodes);
	}
	
	
	private function validateVhostNames($names, $parameterName = 'names') {
		$this->validateArray($names, 'names');
			
		foreach ($names as $name) {
			$this->validateVhostName($name);
		}
	}

	private function validateVhostName($name, $parameterName = 'name') {
		if (preg_match('#^[[:alnum:]\\-_\.]+:[[:digit:]]+$#', $name) > 0) {
			$uri = UriFactory::factory('http://' . $name);
			if (! $uri->isValid()) {
				throw new WebAPI\Exception(_t("Parameter %s contains invalid vhost name %s", array($parameterName, $name)), WebAPI\Exception::INVALID_PARAMETER);
			}	
		} else {
			throw new WebAPI\Exception(_t("Parameter %s contains is invalid %s", array($parameterName, $name)), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	private function getVhostNodes($vhostsIds) {
		return $this->getVhostMapper()->getFullVhostNodes($vhostsIds, $this->getServersMapper());
	}
	
	private function vhoseGetApplications($vhostId) {
		$vhostApps = $this->getDeploymentMapper()->getApplicationsByVhostIds(array($vhostId));
		$vhostAppsToReturn = array();
		foreach ($vhostApps as $apps) {
			$appIds = array_keys($apps);
			foreach ($appIds as $appId) {
				$app = $this->getDeploymentMapper()->getApplicationById($appId);
				$vhostAppsToReturn[$appId] = array ('id' => $appId, 'name' => $app->getApplicationName(), 'userName' => $app->getUserApplicationName());
			}
		}
	
		return $vhostAppsToReturn;
	}
	
	/**
	 * @param array $vhostParams
	 * @return AddVhost
	 * @throws WebAPI\Exception
	 */
	private function validateVhostParameters($vhostParams) {
		if (isset($vhostParams['name'])) {
			$vhostParams['name'] = $this->validateHost(strtolower($vhostParams['name']), 'name');
		}
	
		if (isset($vhostParams['port'])) {
			$this->validateMaxInteger($vhostParams['port'], 65535 , 'port');
			$vhostParams['port'] = $this->validatePositiveInteger($vhostParams['port'], 'port');
		
		}
		
		if (isset($vhostParams['template'])) {
			$vhostParams['template'] = $this->validateString($vhostParams['template'], 'template');
		}
		
		if (isset($vhostParams['vhostId']) && $vhostParams['vhostId']) {
			$vhostParams['vhostId'] = $this->validatePositiveInteger($vhostParams['vhostId'], 'vhostId');
		}
		
		if (isset($vhostParams['forceCreate'])) {
			$vhostParams['forceCreate'] = $this->validateBoolean($vhostParams['forceCreate'], 'forceCreate');
		}
		
		return $vhostParams;
	}
	
	/**
	 * @return array
	 */
	private function getServersIds() {
		$servers = $this->getServersMapper()->findAllServers();
		return array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
	}

	/**
	 *
	 * @param integer $vhostId
	 * @throws WebAPI\Exception
	 * @return \Vhost\Entity\Vhost
	 */
	private function validateVhostExists($vhostId) {
		$currentVhost = $this->getVhostMapper()->getVhostById($vhostId);
		if (is_null($currentVhost->getId())) {
			throw new WebAPI\Exception(_t("Vhost %s does not exist", array($vhostId)), WebAPI\Exception::NO_SUCH_VHOST); 
		}
		return $currentVhost;
	}
	

	/**
	 * @param array|Parameters $params
	 * @throws \WebAPI\Exception
	 * @return \Zend\View\Model\ViewModel
	 */
	private function addVhost($params) {
		$form = $this->getLocator()->get('Vhost\Form\Vhost');
		$form->prepareElements();
		$form->setHydrator(new VhostActionHydrator());
		$form->setObject($this->getLocator()->get('Vhost\Mapper\AddVhost'));
		$form->setData($params);
		
		if (! $form->isValid()) {
			throw new \WebAPI\Exception(sprintf('Invalid parameter \'%s\': %s', key($form->getMessages()), current(current($form->getMessages()))), \WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$vhostAddMapper = $form->getData(); /* @var $vhostAddMapper \Vhost\Mapper\AddVhost */
		$vhostMapper = $this->getVhostMapper();
		
		$vhostsResult = $vhostMapper->getVhostByName("{$vhostAddMapper->getVhostName()}:{$vhostAddMapper->getPort()}");
		if (! is_null($vhostsResult)) {
			throw new WebAPI\Exception(_t("Vhost %s:%s already exists", array($vhostAddMapper->getVhostName(), $vhostAddMapper->getPort())), WebAPI\Exception::VIRTUAL_HOST_ALREADY_EXISTS);
		}
	
		try {
	
			$auditType = auditMapper::AUDIT_VHOST_ADD;
			$auditData = array(
					_t('Vhost Name') => "{$vhostAddMapper->getVhostName()}:{$vhostAddMapper->getPort()}",
					_t('Vhost Template') => $vhostAddMapper->getTemplate()
			);
	
			$auditMessage = $this->auditMessage($auditType, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($auditData));
	
			$vhostsResult = $vhostAddMapper->setVhost();
	
		} catch (\Exception $e) {
			$code = WebAPI\Exception::INTERNAL_SERVER_ERROR;
			if ($e instanceof \Vhost\Mapper\Exception && $e->getCode() == \Vhost\Mapper\Exception::APACHE_CONFIGURATION_INVALID) {
				$code = WebAPI\Exception::WEBSERVER_CONFIGURATION_ERROR;
			}
			Log::err("Failed to add vhost: " . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array("message" => $e->getMessage()));
			throw new WebAPI\Exception(_t("Failed to add vhost: %s", array($e->getMessage())), $code, $e);
		}
	
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		Log::info("Vhost has been added.");
	
		$vhosts = array($vhostsResult);
		$vhostsIds = array($vhostsResult->getId());
	
		$vhostsNodes = $this->getVhostNodes($vhostsIds);
	
		$viewModel = new ViewModel(array('vhosts' => $vhosts, 'vhostsNodes' => $vhostsNodes));
		$viewModel->setTemplate('vhost/web-api/1x6/vhost-get-status');
		return $viewModel;
	}
	
	/**
	 * @param integer $order
	 * @throws WebAPI\Exception
	 */
	protected function validateOrder($order) {
		$order = strtolower($order);
		$sortColumns = $this->getVhostMapper()->getSortColumnsDictionary();
		if (! in_array($order, $sortColumns)) {
			throw new WebAPI\Exception(
					_t('Parameter \'order\' must be one of %s',
							array(implode(', ', $this->getVhostMapper()->getSortColumnsDictionary()))),
					WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 *
	 * @param string $filterName
	 * @return \Vhost\Filter\Filter
	 */
	private function getFilterObj($filterName, $filterData) {
		if (!$filterName) return new Filter($filterData);
	
		$filterList = $this->getFilterMapper()->getByTypeAndName(Filter::VHOST_FILTER_TYPE, $filterName); /* @var $filterList \ZendServer\Filter\FilterList */
		if (! count($filterList)) {
			throw new WebAPI\Exception(_t("Cannot find Vhost filter '%s' in '%s' table",array($filterName, $this->getFilterMapper()->getTableName())), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return new Filter($filterData + $filterList->current()->getData()); // actual data will take precedence over dbData
	}
	
	private function getDictionary() {
		if ($this->dictionary) {
			return $this->dictionary;
		}
	
		return $this->dictionary = $this->getLocator()->get('Vhost\Filter\Dictionary');
	}
	
	private function renameFilterKeys($filterData) { // adjusting keys from the global filter widget conventions
		return $filterData;
	}
}
