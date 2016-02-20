<?php
namespace MonitorRules\Controller;
use Zend\View\Model\ViewModel;

use Audit\Db\ProgressMapper;

use WebAPI\Exception;


use ZendServer\Mvc\Controller\WebAPIActionController,
MonitorRules\Rule,
ZendServer\Set,
MonitorRules\Condition,
MonitorRules\Action,
ZendServer\Log\Log,
MonitorRules\Model\Mapper,
WebAPI
;

class WebAPIController extends WebAPIActionController {
	
	protected $allowedFilterKeys = array('freetext', 'applications', 'rulesIds');
	
	protected $mandatoryRuleProperties = array('rule_type_id', 'rule_parent_id', 'app_id', 'name', 'enabled');
	protected $nonMandatoryRuleProperties = array('creator', 'description');
	
	protected $mandatoryConditionProperties = array('attribute', 'operand');
	protected $nonMandatoryConditionProperties = array('condition_id', 'operation');
	
	protected $mandatoryTriggerProperties = array('trigger_id', 'severity');
	
	protected $mandatoryActionProperties = array('action_type');
	protected $nonMandatoryActionProperties = array('action_id', 'action_url', 'send_to', 'tracing_duration');
	
	protected $allowedSeverityValues = array(-1,0,1);
	protected $allowedActionsTypeValues = array(-1,0,1,2,3);
	protected $allowedCreatorValues = array(1,2);
	
	public function monitorExportRulesAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('applicationId' => -1, 'retrieveGlobal' => 'TRUE'));
		
		$resolver = $this->getLocator('ViewTemplatePathStackWebAPI'); /* @var $resolver \WebAPI\View\Resolver\TemplatePathStack */
		$resolver->setWebapiVersion('1.3'); // otherwise viewscripts will be looked using current webapi version (1.4 at the moment)
		
		$retrieveGlobal = $this->validateBoolean($params['retrieveGlobal'], 'retrieveGlobal');
		
		try {
			$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
			$applications = array($params['applicationId']);
			
			$rules = $mapper->findMonitorRules(array('applications' => $applications));
		} catch (\Exception $e) {
			Log::err($e);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		// prepare the environment for a file download
		$this->setHttpResponseCode('200', 'OK');
		$response = $this->getResponse(); /* @var $response \Zend\Http\PhpEnvironment\Response */
		$response->getHeaders()->addHeaders(array(
			'Content-Type' => 'application/vnd.zend.monitor.rules+xml',
			'Content-Disposition' => 'attachment; filename="monitor_rules.xml"'
		));
		
		// @todo remove temporary fix for view model variables' propagation when setTerminal is true
		//$this->layout('layout/nothing');
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setVariable('rules', $rules);
		return $viewModel;
	}
	
	public function monitorImportRulesAction() {
		$params = $this->getParameters();
		$monitorRules = $params['monitorRules'];		
		$monitorRulesXml = new \SimpleXMLElement($monitorRules);
		$mapper = $this->getLocator('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
		
		//get all global monitoring rules from db
		$globalRulesArray = $mapper->getGlobalRules();
		
		foreach($monitorRulesXml->rule as $rule){
			$ruleName = (string) $rule->ruleProperties->name;
			$ruleId = null;
			foreach($globalRulesArray as $existingRule){
				//search if rule exists and needs updating
				if($existingRule['NAME'] == (string) $ruleName){
					$ruleId = $existingRule['RULE_ID'];
				}
			}
			//else create new rule
			$mapper->createRuleFromXml($rule, $ruleId);
		}

		//$this->layout('layout/nothing');
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		return array('monitorRulesImported' => true);
	}
	
	public function monitorGetRulesListAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('filters' => array()));
		$this->validateArray($params['filters'], 'filters');
		$this->validateFilters($params['filters']);
		try {
			$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
		    $rules = $mapper->findMonitorRules($params['filters']);
		} catch (\Exception $e) {
			Log::err($e);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		$acl = $this->getLocator()->get('ZendServerAcl');
		// disable the custom and job queue rules in free edition
		if (! $acl->isAllowed('data:useMonitorProRuleTypes')) {
			$updatedRules = $rules->toArray();
			foreach ($updatedRules as $key => $rule) {
				if (in_array($rule['RULE_TYPE_ID'], array( 'jq-job-exec-error', 'jq-job-logical-failure', 'jq-job-exec-delay', 'custom'))) {
					$updatedRules[$key]['ENABLED'] = 0;
				}
			}
			$rules = new Set($updatedRules, 'MonitorRules\Rule');
		}
		
		return array('rules' => $rules);
	}
	
	public function monitorEnableRulesAction() {
		$rulesIdsPassed = $this->preProcessDisableEnableActions();

		
		try {
			$this->disableEnableRules(true, $rulesIdsPassed);
		} catch (Exception $e) {
			$this->auditMessage(\Audit\Db\Mapper::AUDIT_MONITOR_RULES_ENABLE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					array($rulesIdsPassed),
					array('errorMessage' => $e->getMessage())
			));
			throw $e;
		}
		
		$this->auditMessage(\Audit\Db\Mapper::AUDIT_MONITOR_RULES_ENABLE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(
				array($rulesIdsPassed),
		));
		return $this->postProcessRuleChanges($rulesIdsPassed, $this->getServersMapper()->findRespondingServersIds());
	}	

	public function monitorDisableRulesAction() {
		$rulesIdsPassed = $this->preProcessDisableEnableActions();

		try {
			$this->disableEnableRules(false, $rulesIdsPassed);
		} catch (Exception $e) {
			$this->auditMessage(\Audit\Db\Mapper::AUDIT_MONITOR_RULES_DISABLE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					array($rulesIdsPassed),
					array('errorMessage' => $e->getMessage())
					));
			throw $e;
		}
		
		
		$this->auditMessage(\Audit\Db\Mapper::AUDIT_MONITOR_RULES_DISABLE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(
				array($rulesIdsPassed),
		));
		return $this->postProcessRuleChanges($rulesIdsPassed, $this->getServersMapper()->findRespondingServersIds());
	}

	public function monitorSetRuleAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('ruleId' => -1, 'ruleConditions' => array(), 'ruleTriggers'  => array(), 'acceptDuplicate' => 'FALSE'));
			$this->validateMandatoryParameters($params, array('ruleProperties'));
			$ruleId = $this->validateInteger($params['ruleId'], 'ruleId');
			$acceptDuplicate = $this->validateBoolean($params['acceptDuplicate'], 'acceptDuplicate');
			
			$ruleProperties = $this->validateArray($params['ruleProperties'], 'ruleProperties');
			$ruleName = '';
			if (isset($params['ruleProperties']['name'])) {
				$ruleName = $params['ruleProperties']['name'];
			}
			
			$auditType = $ruleId == -1 ? \Audit\Db\Mapper::AUDIT_MONITOR_RULES_ADD : \Audit\Db\Mapper::AUDIT_MONITOR_RULES_SAVE;
			$ruleConditions = $this->validateArray($params['ruleConditions'], 'ruleConditions');
			$ruleTriggers = $this->validateArray($params['ruleTriggers'], 'ruleTriggers');
			
			$this->validateSetRuleData($ruleProperties, $ruleConditions, $ruleTriggers); // in-depth validation of mandatory and optional parameters
			
		} catch (\Exception $e) {
			if (isset($auditType)) {
				$this->auditMessage($auditType, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
						array('Monitor rule name: '.$ruleName, 'errorMessage' => $e->getMessage(), 'ruleId' => $ruleId)
				));
			}
			Log::err("{$this->getCmdName()} - input validation failed");
			Log::debug($e);
			throw $e;
		}	
		
		try {
		    $mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
		    $ruleIdModified = $mapper->setRule($ruleId, $ruleProperties, $ruleConditions, $ruleTriggers, $acceptDuplicate);
		} catch (\Exception $e) {
			$this->auditMessage($auditType, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
					array('Monitor rule name: '.$ruleName,'errorMessage' => $e->getMessage(), 'ruleId' => $ruleId)
			));
			throw new WebAPI\Exception(_t("%s failed - %s",array($this->getCmdName(), $e->getMessage())), WebAPI\Exception::NO_SUCH_MONITOR_RULE); // @todo - add constant DB_ERROR
		}
		
		$this->auditMessage($auditType, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(
				array('Monitor rule name: '.$ruleName)
		));
		
		if (isset($params['notifySelfOnly']) && $params['notifySelfOnly'] == '1') {
			$edition = new \ZendServer\Edition();
			$serversToNotify = array($edition->getServerId());
		} else {
			$serversToNotify = $this->getServersMapper()->findRespondingServersIds();
		}
		
		if (!isset($params['notifyChange']) || $params['notifyChange'] == '1') {
			return $this->postProcessRuleChanges(array($ruleIdModified), $serversToNotify, true);
		} else {
			return $this->postProcessRuleChanges(array($ruleIdModified), $serversToNotify, false);
		}
	}
	
	public function monitorSetRuleUpdatedAction() {
		$this->isMethodPost();
		$this->syncMonitorRulesChanges($this->getServersMapper()->findRespondingServersIds());
	
		$viewModel = new ViewModel(array('rules' => array()));
		$viewModel->setTemplate('monitor-rules/web-api/monitor-get-rules-list');
		return $viewModel;
	}
		
	public function monitorRemoveRulesAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters();		
			$rulesIds = $this->validateArray($params['rulesIds'], 'rulesIds');
		} catch (\Exception $e) {
			Log::err("{$this->getCmdName()} - input validation failed");
			Log::debug($e);
			throw $e;
		}

		$this->validateRulesIds($rulesIds);
		
		try {
		    $mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
		    $ruleNames = $this->getMonitorRulesMapper()->findMonitorRulesNames($rulesIds);
		    $rulesRemoved = $mapper->removeRules($rulesIds);
		} catch (\Exception $e) {
						
			$this->auditMessage(\Audit\Db\Mapper::AUDIT_MONITOR_RULES_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
				array('Monitor rule names: ' . implode(', ', $ruleNames), 'errorMessage' => $e->getMessage(), 'ruleIds' => $rulesIds)
			));
			throw new WebAPI\Exception(_t("%s failed - %s",array($this->getCmdName(),$e->getMessage())), WebAPI\Exception::NO_SUCH_MONITOR_RULE);
		}

		$this->syncMonitorRulesChanges($this->getServersMapper()->findRespondingServersIds());
		$this->auditMessage(\Audit\Db\Mapper::AUDIT_MONITOR_RULES_REMOVE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(
				array('Monitor rule names: ' . implode(', ', $ruleNames), 'ruleIds' => $rulesIds)
		));
		return array('rulesRemoved' => $rulesRemoved);
	}	

	/**
	 * @param array $ruleProperties
	 * @param array $ruleConditions
	 * @param array $ruleTriggers
	 */
	protected function validateSetRuleData($ruleProperties, $ruleConditions, $ruleTriggers) {
		$this->validateRuleProperties($ruleProperties);
		$this->validateRuleConditions($ruleConditions);
		$this->validateRuleTriggers($ruleTriggers);
	}
	
	/**
	 * @param array $ruleProperties
	 * @throws WebAPI\Exception
	 */	
	protected function validateRuleProperties($ruleProperties) {
		foreach ($this->mandatoryRuleProperties as $key) {
			if (!isset($ruleProperties[$key])) {
				throw new WebAPI\Exception(_t("%s validation failed - missing mandatory ruleProperties field: '%s'",array($this->getCmdName(),$key)), WebAPI\Exception::MISSING_PARAMETER);
			}
				
			$value = $ruleProperties[$key];
			if ($key === 'rule_type_id') {
				$this->validateString($value, $key) && $this->validateExistingRuleType($value, $key);
			}
			elseif ($key === 'rule_parent_id' && $value) $this->validateInteger($value, $key);
			elseif ($key === 'app_id' && $value) $this->validateInteger($value, $key);
			elseif ($key === 'name') $this->validateStringNonEmpty($value, $key);
			elseif ($key === 'enabled') $this->validateInteger($value, $key);
			elseif ($key === 'description' && $value) $this->validateString($value, $key);
			elseif ($key === 'url' && $value) $this->validateString($value, $key);
		}		

		foreach ($this->nonMandatoryRuleProperties as $key) {
			if (!isset($ruleProperties[$key])) {
				continue;
			}
		
			$value = $ruleProperties[$key];
			if ($key === 'creator') $this->validateInteger($value, $key) && $this->validateAllowedValues($value, $key, $this->allowedCreatorValues);
		}		
	}
	
	/**
	 * @param array $ruleConditions
	 * @throws WebAPI\Exception
	 */	
	protected function validateRuleConditions($ruleConditions) {
		if (!$ruleConditions) {
			return; // no conditions passed
		}
	
		foreach($ruleConditions as $ruleCondition) {
			$this->validateCondition($ruleCondition);
		}
	}
	
	/**
	 * @param array $ruleTriggers
	 * @throws WebAPI\Exception
	 */	
	protected function validateRuleTriggers($ruleTriggers) {
		foreach ($ruleTriggers as $ruleTrigger) {
			$this->validateTriggerProperties($ruleTrigger);
			$this->validateTriggerConditions($ruleTrigger);
			$this->validateTriggerActions($ruleTrigger);
		}
	}
	
	/**
	 * @param array $ruleTrigger
	 * @throws WebAPI\Exception
	 */	
	protected function validateTriggerProperties($ruleTrigger) {
		foreach ($this->mandatoryTriggerProperties as $key) {
			if (!isset($ruleTrigger['triggerProperties'][$key])) {
				throw new WebAPI\Exception(_t("%s validation failed - missing mandatory triggerProperties field: '%s'",array($this->getCmdName(),$key)), WebAPI\Exception::MISSING_PARAMETER);
			}
	
			$value = $ruleTrigger['triggerProperties'][$key];
			if ($key === 'trigger_id') $this->validateInteger($value, $key);
			elseif ($key === 'severity') $this->validateInteger($value, $key) && $this->validateAllowedValues($value, $key, $this->allowedSeverityValues);
		}
	}

	/**
	 * @param array $ruleTrigger
	 * @throws WebAPI\Exception
	 */
	protected function validateTriggerConditions($ruleTrigger) {
		if (!isset($ruleTrigger['triggerConditions'])) {
			return; // trigger does not neccessarily have conditions
		}
	
		foreach ($ruleTrigger['triggerConditions'] as $condition) {
			$this->validateCondition($condition);
		}
	}
	
	/**
	 * @param array $condition
	 * @throws WebAPI\Exception
	 */
	protected function validateCondition($condition) {
		foreach ($this->mandatoryConditionProperties as $key) {
			if (!isset($condition[$key])) {
				throw new WebAPI\Exception(_t("%s validation failed - missing mandatory Condition field: '%s'",array($this->getCmdName(),$key)), WebAPI\Exception::MISSING_PARAMETER);
			}
	
			$value = $condition[$key];
			if ($key === 'attribute') $this->validateString($value, $key) && $this->validateExistingConditionAttribute($value, $key);
			elseif ($key === 'operand' && $value) $this->validateOperandUnits($value, $condition['attribute'], $key, 'attribute');
		}
	
		foreach ($this->nonMandatoryConditionProperties as $key) {
			if (!isset($condition[$key])) {
				continue;
			}
	
			$value = $condition[$key];
			if ($key === 'condition_id' && $value) $this->validateInteger($value, $key);
			elseif ($key === 'operand' && $value) {
				try { // either string or integer
					$this->validateString($value, $key);
				} catch (\Exception $e) {
					$this->validateInteger($value, $key);
				}
			}
		}
	}
	
	/**
	 * @param array $ruleTrigger
	 * @throws WebAPI\Exception
	 */
	protected function validateTriggerActions($ruleTrigger) {
		if (!isset($ruleTrigger['triggerActions'])) {
			return; // trigger does not neccessarily have actions
		}
	
		foreach ($ruleTrigger['triggerActions'] as $action) {
			$this->validateAction($action);
		}
	}
	
	/**
	 * @param array $action
	 * @throws WebAPI\Exception
	 */	
	protected function validateAction($action) {
		foreach ($this->mandatoryActionProperties as $key) {
			if (!isset($action[$key])) {
				throw new WebAPI\Exception(_t("%s validation failed - missing mandatory Action field: '%s'",array($this->getCmdName(),$key)), WebAPI\Exception::MISSING_PARAMETER);
			}
	
			$value = $action[$key];
			if ($key === 'action_type') $this->validateInteger($value, $key) && $this->validateAllowedValues($value, $key, $this->allowedActionsTypeValues);
		}
	
		foreach ($this->nonMandatoryActionProperties as $key) {
			if (!isset($action[$key])) {
				continue;
			}
	
			$value = $action[$key];
			if ($key === 'action_id' && $value) $this->validateInteger($value, $key);
			elseif ($key === 'action_url' && $value) $this->validateString($value, $key) && $this->validateUri($value, $key);
			elseif ($key === 'send_to' && $value) $this->validateString($value, _t('Email')) && $this->validateEmailAddress($value, _t('Email'));
			if ($key === 'tracing_duration' && $value) $this->validateInteger($value, $key);
		}
	}
	
	/**
	 * @param string $ruleTypeId
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 * @return string
	 */
	protected function validateExistingRuleType($ruleTypeId, $parameterName) {
		if (! in_array($ruleTypeId, array_keys($this->getMonitorRulesMapper()->getAllowedRuleTypes()))) {
			throw new WebAPI\Exception(_t("Rule type '%s' is not allowed",array($ruleTypeId)), WebAPI\Exception::INVALID_PARAMETER);
		}
		if (! in_array($ruleTypeId, array_keys($this->getMonitorRulesMapper()->getDictionaryRuleTypeToAttribute()))) {
			throw new WebAPI\Exception(_t("Unknown rule_type_id passed '%s'",array($ruleTypeId)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $ruleTypeId;
	}
	
	/**
	 * @param string $attribute
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 * @return string
	 */
	protected function validateExistingConditionAttribute($attribute, $parameterName) {
		if (! in_array($attribute, $this->getMonitorRulesMapper()->getAllAttributeNames())) {
			throw new WebAPI\Exception(_t("Unknown condition attribute passed '%s'",array($attribute)), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return $attribute;
	}
	
	/**
	 * @param string $attribute
	 * @param string $attribute
	 * @param string $parameterName
	 * @throws WebAPI\Exception
	 * @return string
	 */
	protected function validateOperandUnits($operand, $attribute, $parameterName, $attributeParameterName) {
		$this->validateExistingConditionAttribute($attribute, $attributeParameterName);
		
		if ($attribute === 'function-name') return $this->validateString($operand, "{$parameterName} of type {$attribute}");
		if ($attribute === 'exec-time') return $this->validatePositiveInteger($operand, "{$parameterName} of type {$attribute}");
		if ($attribute === 'exec-time-percent-change') return $this->validatePercent($operand, "{$parameterName} of type {$attribute}");
		if ($attribute === 'mem-usage-percent-change') return $this->validatePercent($operand, "{$parameterName} of type {$attribute}");
		if ($attribute === 'out-size-percent-change') return $this->validatePositiveInteger($operand, "{$parameterName} of type {$attribute}"); // Inconsistent Output Size can contains more than 100%. So validatePercent is not appropriate 
		if ($attribute === 'error-type') return $this->validateInteger($operand, "{$parameterName} of type {$attribute}"); // technically, one can use negative bit-masks
	
		return $attribute;
	}
		
	private function preProcessDisableEnableActions() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$rulesIds = $this->validateArray($params['rulesIds'], 'rulesIds');
		} catch (\Exception $e) {
			Log::err("{$this->getCmdName()} - input validation failed");
			Log::debug($e);
			throw $e;
		}		

		$this->validateRulesIds($rulesIds);
		
		return $rulesIds;
	}
	
	private function validateRulesIds($rulesIdsPassed) {
		$rulesIds = $this->getMonitorRulesMapper()->findMonitorRulesIds($rulesIdsPassed);
		$unknownRules = implode(',', array_diff($rulesIdsPassed, $rulesIds));	

		if ($unknownRules) {
			Log::err("{$this->getCmdName()} failed - the following unknown ruleId(s) were passed: {{$unknownRules}}");
			throw new WebAPI\Exception(_t("%s failed - the following unknown rule(s) were passed: %s",array($this->getCmdName(),$unknownRules)), WebAPI\Exception::NO_SUCH_MONITOR_RULE);
		}
	}
	
	private function disableEnableRules($toEnable, $rulesIdsPassed) {
		try {
			if (!($res = $this->getMonitorRulesMapper()->disableEnableRules($toEnable, $rulesIdsPassed))) { // most probably never reach here, as we validated that rules passed are valid
				throw new \Exception(_t("SQL Update statement failed to affect any rows"));
			}
		} catch (\Exception $e) {
			Log::err("{$this->getCmdName()}  failed: " . $e->getMessage());
			throw new WebAPI\Exception(_t("{$this->getCmdName()} failed: %s", array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}		
	}
	
	private function postProcessRuleChanges($rulesIdsPassed, $serversToNotify, $notifyChange = true) {
		if ($notifyChange) {
			$this->syncMonitorRulesChanges($serversToNotify);
		}
		
		$rules = $this->getMonitorRulesMapper()->findMonitorRulesByRuleId($rulesIdsPassed); /* @var $rules \ZendServer\Set */		
		$viewModel = new ViewModel(array('rules' => $rules));
		$viewModel->setTemplate('monitor-rules/web-api/monitor-get-rules-list');
		return $viewModel;
	}
	
	private function syncMonitorRulesChanges($serverIds) {
		$this->getLocator()->get('MonitorRules\Model\Tasks')->syncMonitorRulesChanges($serverIds); // whenever we change the rules data, we should notify ZSD to sync changes to all responding servers
	}
	
	private function validateFilters(array $filters) {
		$diff = array_diff(array_keys($filters), $this->allowedFilterKeys);
		if (count($diff) > 0) {
			$allowedFilterKeys = implode(',', $this->allowedFilterKeys);
			throw new WebAPI\Exception(_t("Parameter 'filters' can contain '%s' keys only", array($allowedFilterKeys)), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
}