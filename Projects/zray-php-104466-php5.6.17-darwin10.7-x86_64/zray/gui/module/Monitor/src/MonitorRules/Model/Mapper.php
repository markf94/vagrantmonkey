<?php

namespace MonitorRules\Model;

use Deployment\IdentityApplicationsAwareInterface;
use Deployment\IdentityFilterException;
use Deployment\IdentityFilterInterface;
use ZendServer\Permissions\AclQuery;

use ZendServer\Permissions\AclQuerierInterface;

use ZendServer\Log\Log;

use ZendServer\Set;

use Configuration\MapperAbstract;

use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Db\Sql\Where;

class Mapper extends MapperAbstract implements AclQuerierInterface, EventsCapableInterface, IdentityApplicationsAwareInterface {
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $rulesTable;
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $actionsTable;
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $conditionsTable;
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $triggersTable;
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $ruleTypesTable;

    /**
     * @var IdentityFilterInterface
     */
    private $identityFilter;

	/**
	 * @var AclQuery
	 */
	private $acl;
	
	private $AttributeToOperation = array(
			'function-name'				=>	'string-in-list',
			'exec-time'					=>	'number-greater-than',
			'exec-time-percent-change'	=>	'number-greater-than',
			'mem-usage-percent-change'	=>	'number-greater-than',
			'mem-usage'					=>	'number-greater-than',
			'out-size-percent-change'	=>	'number-greater-than',
			'error-type'				=>	'bitwise-and',
		);
	/**
	 * @var EventManagerInterface
	 */
	private $events;

	/**
	 * @param EventManagerInterface $eventManager
	 */
	public function setEventManager(EventManagerInterface $eventManager) {
		$this->events = $eventManager;
	}

	/*
	 * (non-PHPdoc)
	 * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
	 */
	public function getEventManager() {
		return $this->events;
	}

	/**
	 * @param array $filter
	 * @return \ZendServer\Set['\MonitorRules\Rule']
	 */
	public function findMonitorRulesByRuleId(array $rulesIds = array(), $appIds = array()) {
        return $this->processRules($this->getRulesByIds($rulesIds, $appIds), array('rulesIds' => $rulesIds, 'applications' => $appIds));
	}
	
	/**
	 * @param array $filter
	 * @return \ZendServer\Set
	 */
	public function findMonitorRules(array $filter = array()) {
		$where = null; // for some reason (zf2 b3?), passing an empty string fails
        $applications = isset($filter['applications']) && is_array($filter['applications']) ? $filter['applications'] : array();

        try {
            $applications = $this->identityFilter->filterAppIds($applications, true);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return $this->processRules(array(), $filter);
            }
        }

        if ($applications) {
			$where .= ' AND ' . $this->getSqlInStatement('APP_ID', $applications);
		}
		
		$freetext = isset($filter['freetext']) && $filter['freetext'] ? $filter['freetext'] : '';
		if ($freetext) {
		    $where .= " AND (DESCRIPTION LIKE '%$freetext%' OR NAME LIKE '%$freetext%')";
		}

		$rulesIds = isset($filter['rulesIds']) && $filter['rulesIds'] ? $filter['rulesIds'] : array();
		if ($rulesIds) {
			$where .= ' AND ' . $this->getSqlInStatement('RULE_ID', $rulesIds);
		}		
		
		if ($where) {
			$where = preg_replace('/^ AND /', '', $where);
		}
		
		$rules = $this->getRulesTable()->select($where)->toArray();
		
		return $this->processRules($rules, $filter);
	}
	
	/**
	 * @param array $filter
	 * @return \ZendServer\Set
	 */
	public function findAllMonitorRules(array $filter = array()) {
	    $where = null; // for some reason (zf2 b3?), passing an empty string fails
		$applications = isset($filter['applications']) && is_array($filter['applications']) ? $filter['applications'] : array();

        try {
            $applications = $this->identityFilter->filterAppIds($applications, true);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return $this->processRules(array(), $filter);
            }
        }

		if ($applications) {
			$where .= ' AND ' . $this->getSqlInStatement('APP_ID', $applications);
		}
	
		$freetext = isset($filter['freetext']) && $filter['freetext'] ? $filter['freetext'] : '';
		if ($freetext) {
			$where .= " AND (DESCRIPTION LIKE '%$freetext%' OR NAME LIKE '%$freetext%')";
		}
	
		$rulesIds = isset($filter['rulesIds']) && $filter['rulesIds'] ? $filter['rulesIds'] : array();
		if ($rulesIds) {
			$where .= ' AND ' . $this->getSqlInStatement('RULE_ID', $rulesIds);
		}
	
		if ($where) {
			$where = preg_replace('/^ AND /', '', $where);
		}
	
		$rules = $this->getRulesTable()->select($where)->toArray();
	
		Log::debug('Retrieved '.count($rules).' rules by filter ' . print_r($filter, true));
		return new Set($rules, 'MonitorRules\Rule');
	}
	
	/**
	 * @param array $filter
	 * @return Array
	 */
	public function findMonitorRulesNames(array $rulesIds = array()) {
		$rules = $this->findMonitorRulesByRuleId($rulesIds);
		return array_map(function($rule) {return $rule['NAME'];}, $rules->toArray());
	}
	
	/**
	 * @param array $filter
	 * @return Array
	 */
	public function findMonitorRulesIds(array $rulesIds = array()) {
		$rules = $this->findMonitorRulesByRuleId($rulesIds);
		return array_map(function($rule) {return $rule['RULE_ID'];}, $rules->toArray());
	}	

	/**
	 * 
	 * @param integer $ruleId
	 * @param array $ruleProperties
	 * @param array $ruleConditions
	 * @param array $ruleTriggers
	 */
	public function setRule($ruleId, $ruleProperties, $ruleConditions, $ruleTriggers, $acceptDuplicate = false) {
	    // set the parent id if it's a new rule or parent_id is missed
	    if (! isset($ruleProperties['rule_parent_id']) || !$ruleProperties['rule_parent_id']) {
	        $ruleProperties['rule_parent_id'] = '-1';
	    }
	    if ($ruleId == -1) {
	    	// not allow to give a new rule name when the name already exists for this application
			foreach ($this->getRuleNames() as $rule) {
				if ($rule['NAME'] == $ruleProperties['name'] && $rule['APP_ID'] == $ruleProperties['app_id']) {
					if ($acceptDuplicate) {
						return $rule['RULE_ID'];
					} else {
						throw new \ZendServer\Exception(_t("Rule name '%s' already exists for this application",array($ruleProperties['name'])));
					}
				}
			}
	    	return $this->setNewRule($ruleProperties, $ruleConditions, $ruleTriggers);
		}
		
		return $this->updateRule($ruleId, $ruleProperties, $ruleConditions, $ruleTriggers);
	}
	
	/**
	 * @param array $rulesIds
	 * @param array $appIds
	 * 
	 * @throws \ZendServer\Exception
	 * @return boolean
	 */	
	public function removeRules(array $rulesIds = array(), $appIds = array()) {
		// first validating
		$rules = $this->findMonitorRulesByRuleId($rulesIds, $appIds);	
		
		foreach ($this->getRulesByIds($rulesIds) as $rule) {
			if (!$this->isAppRule($rule) && !$this->createdByUser($rule)) {
				throw new \ZendServer\Exception(_t("Rule %s is not an application rule or user-created, thus it cannot be deleted",array($rule['RULE_ID'])));
			}			
		}
		
		$rules = $this->findMonitorRulesByRuleId($rulesIds, $appIds);
		foreach ($rules as $rule) {
			$this->removeRule($rule);
		}

		return true;
	}
	
	public function removeApplicationRules($appId) {
		$dbConnection = $this->getRulesTable()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
		
		$rules = $this->getRulesByAppId($appId);
		$ids = array();
		foreach ($rules as $rule) {
			$ids[] = $rule['RULE_ID'];
		}
		
		Log::debug("Removing monitor rules " . implode("," , $ids));
		$this->removeRules($ids);
	}
	
	public function createdByUser($rule) {
		if (isset($rule['CREATOR']) && $rule['CREATOR'] == 1) {
			return true;
		}
		
		return false;
	}
		
	public function isAppRule($rule) {
		if (isset($rule['APP_ID']) && is_numeric($rule['APP_ID']) && $rule['APP_ID'] > 0) {
			return true;
		}
		
		return false;
	}

	/**
	 * @param boolean $toEnable - true to enable, false to disable
	 * @param array $rulesIds
	 * @return integer affectedRowsCount
	 */
	public function disableEnableRules($toEnable, array $rulesIds) {		
		$where = $this->getSqlInStatement('RULE_ID', $rulesIds);
		return $this->setTableGateway($this->getRulesTable())->update(array('ENABLED' => intval($toEnable)), $where);
	}
		
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $ruleTypesTable
	 */
	public function getRuleTypesTable() {
		return $this->ruleTypesTable;
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $rulesTable
	 */
	public function getRulesTable() {
		return $this->rulesTable;
	}

	/**
	 * @return \Zend\Db\TableGateway\TableGateway $actionsTable
	 */
	public function getActionsTable() {
		return $this->actionsTable;
	}

	/**
	 * @return \Zend\Db\TableGateway\TableGateway $conditionsTable
	 */
	public function getConditionsTable() {
		return $this->conditionsTable;
	}

	/**
	 * @return \Zend\Db\TableGateway\TableGateway $triggersTable
	 */
	public function getTriggersTable() {
		return $this->triggersTable;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $triggersTable
	 * @return \MonitorRules\Model\Mapper
	 */
	public function setTriggersTable($triggersTable) {
		$this->triggersTable = $triggersTable;
		return $this;
	}
	
	/**
	 * @param \Zend\Db\TableGateway\TableGateway $triggersTable
	 * @return \MonitorRules\Model\Mapper
	 */
	public function setRuleTypesTable($ruleTypesTable) {
		$this->ruleTypesTable = $ruleTypesTable;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $actionsTable
	 * @return \MonitorRules\Model\Mapper
	 */
	public function setActionsTable($actionsTable) {
		$this->actionsTable = $actionsTable;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $conditionsTable
	 * @return \MonitorRules\Model\Mapper
	 */
	public function setConditionsTable($conditionsTable) {
		$this->conditionsTable = $conditionsTable;
		return $this;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $rulesTable
	 * @return \MonitorRules\Model\Mapper
	 */
	public function setRulesTable($rulesTable) {
		$this->rulesTable = $rulesTable;
		return $this;
	}
	
	public function getRuleTypes() {
		$ruleTypes = $this->getRuleTypesTable()->select()->toArray();
		
		$ruleTypesHash = array();
		$removeTypes = array();
		if (! $this->acl->isAllowed('data:useMonitorProRuleTypes')) {
			$removeTypes = array(
				'jq-job-exec-error',
				'jq-job-logical-failure',
				'jq-job-exec-delay',
				'custom'
			);
		}
		
		foreach ($ruleTypes as $ruleType) {
			if (in_array($ruleType['TYPE_ID'], $removeTypes)) {
				$ruleType['TYPE_ENABLED'] = false;
			} else {
				$ruleType['TYPE_ENABLED'] = true;
			}
			$ruleTypesHash[$ruleType['TYPE_ID']] = $ruleType;
		}
		
		return new Set($ruleTypesHash, 'MonitorRules\Type');
	}
	
	public function getRuleTypesForNewGlobalRule() {
		$ruleTypes = $this->getRuleTypes();
		
		$toFilterArray = array ('request-relative-large-out-size',
								'java-exception',
								'jq-job-exec-error',
								'jq-job-logical-failure',
								'jq-job-exec-delay',
								'tracer-write-file-fail');
		$filteredRuleTypes = array_diff_key($ruleTypes->toArray(), array_flip($toFilterArray));
		return new Set($filteredRuleTypes, 'MonitorRules\Type');
	}

	/**
	 * @return array
	 */
	public function getAllowedRuleTypes() {
		$ruleTypesHash = $this->getDictionaryRuleTypeToAttribute();
		if (! $this->acl->isAllowed('data:useMonitorProRuleTypes')) {
			$removeTypes = array(
					'jq-job-exec-error',
					'jq-job-logical-failure',
					'jq-job-exec-delay',
					'custom'
			);
				
			$ruleTypesHash = array_diff_key($ruleTypesHash, array_flip($removeTypes));
		}
		return $ruleTypesHash;
	}
	
	/**
	 * Returns array of dictionary that translates from rule type to attributes list
	 */
	public function getDictionaryRuleTypeToAttribute() {
		return array(	'function-error' 					=> array('function-name'),
      					'function-slow-exec' 				=> array('exec-time'),
      					'request-slow-exec' 				=> array('exec-time', 'exec-time-percent-change'),
				       	'request-large-mem-usage' 			=> array('mem-usage', 'mem-usage-percent-change'),
				      	'request-relative-large-out-size' 	=> array('out-size-percent-change'),
				      	'java-exception'					=> array(),
						'custom'							=> array(),
				      	'zend-error' 						=> array('error-type'),
				      	'jq-job-exec-error' 				=> array(),
				      	'jq-job-logical-failure' 			=> array(),
				      	'jq-job-exec-delay' 				=> array(),
				      	'jq-daemon-high-concurrency'		=> array(),
				      	'tracer-write-file-fail' 			=> array());
     
  	}
  	public function getAttributeNames() {
  		return array(	'exec-time'					=> _t('Execute Time'),
  						'exec-time-percent-change' 	=> _t('Execute Time &#37;'),
  						'mem-usage' 				=> _t('Memory Usage'),
  						'mem-usage-percent-change' 	=> _t('Memory Usage &#37;'),
  						'out-size-percent-change' 	=> _t('Outsize &#37;'),
  						'error-type' 				=> _t('PHP Error Type'));
  	}
  	
  	public function getAllAttributeNames() {
  		return array_keys($this->AttributeToOperation);
  	}
  	
  	/**
  	 * returns all global rules
  	 */
  	public function getRuleNames() {
  		$select = new \Zend\Db\Sql\Select();
  		$select->from($this->getRulesTable()->getTable());
  		$select->columns(array('RULE_ID','NAME','APP_ID'));
  		return $this->getRulesTable()->selectWith($select)->toArray();
  	}
  	
  	/**
  	 * returns all global rules
  	 */
  	public function getRuleIdsFromNames($names) {
  		$select = new \Zend\Db\Sql\Select();
  		$select->from($this->getRulesTable()->getTable());
  		$where = new Where();
  		$where->in('NAME', $names);
  		$select->where($where);
  		$select->columns(array('RULE_ID', 'NAME'));
  		return $this->getRulesTable()->selectWith($select)->toArray();
  	}
  	
  	/**
  	 * returns all global rules
  	 */
  	public function getGlobalRules() {
  		$select = new \Zend\Db\Sql\Select();
  		$select->from($this->getRulesTable()->getTable());
  		$select->where($this->getSqlInStatement('APP_ID', array('-1')));
  		$select->columns(array('RULE_ID', 'NAME'));
  		return $this->getRulesTable()->selectWith($select)->toArray();
  	}
  	
  	public function createRuleFromXml($xml, $ruledId = null){/** @var $xml \SimpleXMLElement */
  		//change properties of rule from xml to php array
  		$ruleProperties['rule_type_id'] = (string)$xml->ruleProperties->rule_type_id;
  		$ruleProperties['name'] = (string)$xml->ruleProperties->name;
  		$ruleProperties['description'] = (string)$xml->ruleProperties->description;
  		$ruleProperties['enabled'] = (string)$xml->ruleProperties->enabled;
  		$ruleProperties['url'] = (string)$xml->ruleProperties->url;

  		$ruleProperties['enabled'] = ($ruleProperties['enabled'] == '1' ? 1 : 0);
  		//change conditions of rule from xml to php array
  		$i = 0; 		
  		if(isset($xml->ruleConditions) && count((array)$xml->ruleConditions->condition) != 0){
  			foreach((array)$xml->ruleConditions as $condition){
  				if(!isset($condition->operation) && !isset($condition->attribute) && !isset($condition->operand)){
  					//bug in xml that gives a string with spaces instead of empty array
  					$ruleConditions[$i] = array();
  					break;
  				}
  				if(isset($condition->operation)) $ruleConditions[$i]['operation'] = trim((string)$condition->operation);
  				if(isset($condition->attribute)) $ruleConditions[$i]['attribute'] = trim((string)$condition->attribute);
  				if(isset($condition->operand)) $ruleConditions[$i]['operand'] = trim((string)$condition->operand);
  				$i++;
  			} 			
  		} else {
  			$ruleConditions = array();
  		}

  		//change triggers of rule from xml to php array
  		$i = 0;
  		if(isset($xml->ruleTriggers) && count((array)$xml->ruleTriggers->trigger) != 0){
  			foreach($xml->ruleTriggers->trigger as $trigger){
  				//*** tringer properties ***
  				$ruleTriggers[$i]['triggerProperties']['severity'] = (string)$trigger->triggerProperties->severity;
  				
  				//*** trigger conditions ***
  				$j = 0;
				foreach ( $trigger->triggerConditions->condition as $condition ) {
					if (! isset ( $condition->operation ) && ! isset ( $condition->attribute ) && ! isset ( $condition->operand )) {
						// bug in xml that gives a string with spaces
						$ruleTriggers [$i] ['triggerConditions'] = array ();
						break;
					}
					if (isset ( $condition->operation )) $ruleTriggers [$i] ['triggerConditions'] [$j] ['operation'] = ( string ) $condition->operation;
					if (isset ( $condition->attribute )) $ruleTriggers [$i] ['triggerConditions'] [$j] ['attribute'] = ( string ) $condition->attribute;
					if (isset ( $condition->operand )) $ruleTriggers [$i] ['triggerConditions'] [$j] ['operand'] = ( string ) $condition->operand;
					$j ++;
				}
				
				if((! isset($ruleTriggers[$i]['triggerConditions'])) || (! $ruleTriggers[$i]['triggerConditions'])){
					$ruleTriggers[$i]['triggerConditions'] = array();
  				}
  				
  				//*** trigger actions ***
  				$j = 0;			
				foreach ( $trigger->triggerActions->action as $action ) {
					if (! isset ( $action->action_type ) && ! isset ( $action->action_url ) && ! isset ( $action->tracing_duration )) {
						// bug in xml that gives a string with spaces instead of
						$ruleTriggers [$i] ['triggerActions'] = array ();
						break;
					}
					if (isset ( $action->action_type )) $ruleTriggers [$i] ['triggerActions'] [$j] ['action_type'] = ( string ) $action->action_type;
					if (isset ( $action->action_url )) $ruleTriggers [$i] ['triggerActions'] [$j] ['action_url'] = ( string ) $action->action_url;
					if (isset ( $action->tracing_duration )) $ruleTriggers [$i] ['triggerActions'][$j]['tracing_duration'] = (string)$action->tracing_duration;
  					$j++;
  				}
  				
  				if((! isset($ruleTriggers[$i]['triggerActions'])) || (!$ruleTriggers[$i]['triggerActions'])){
  					$ruleTriggers[$i]['triggerActions'] = array();
  				}  				
			
  				$i++;
  			}
  		} else {
  			$ruleTriggers = array();
  		}

  		if($ruledId == null){
  			$this->setNewRule($ruleProperties, $ruleConditions, $ruleTriggers);
  		} else {
  			$this->updateRule($ruledId, $ruleProperties, $ruleConditions, $ruleTriggers);
  		}
  		
  	}


	// PRIVATE FUNCTIONS FROM HERE
	private function removeRule(\MonitorRules\Rule $rule) {
		$dbConnection = $this->getRulesTable()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
		$ruleId = $rule->getId();
		
		try {
			$dbConnection->beginTransaction();
			
			$this->getRulesTable()->delete(array('RULE_ID'=>$ruleId));
			$this->getTriggersTable()->delete($this->getSqlInStatement('TRIGGER_ID', array_keys($rule->getTriggers())));	
			
			if ($this->getTriggerConditions($rule)) {
				$this->getConditionsTable()->delete("RULE_ID = {$ruleId} OR " . $this->getSqlInStatement('CONDITION_ID', $this->getTriggerConditions($rule))); // @todo - not using arrays as of ZF2 B3 bug
			}
			if ($this->getTriggerActions($rule)) {			
				$this->getActionsTable()->delete($this->getSqlInStatement('ACTION_ID', $this->getTriggerActions($rule)));
			}
			
			$dbConnection->commit();
		} catch (\Exception $e) {
			Log::err("Failed to remove rule {$ruleId}: " . $e->getMessage());
			$dbConnection->rollback();
			throw $e;
		}
	
		Log::debug("Rule {$ruleId} removed ok");
		return true;
	}
	
	private function getTriggerConditions(\MonitorRules\Rule $rule) {
		$conditions = array();
		foreach ($rule->getTriggers() as $trigger) { /* @var $trigger \MonitorRules\Trigger */
			$conditions = array_merge($conditions, array_keys($trigger->getConditions()));
		}
	
		return $conditions;
	}
	
	private function getTriggerActions(\MonitorRules\Rule $rule) {
		$actions = array();
		foreach ($rule->getTriggers() as $trigger) { /* @var $trigger \MonitorRules\Trigger */
			$actions = array_merge($actions, array_keys($trigger->getActions()));
		}
	
		return $actions;
	}	
	
	private function updateRule($ruleId, $ruleProperties, $ruleConditions, $ruleTriggers) {
		// @todo - validate $ruleProperties against db columns ,and strip tags
		$dbConnection = $this->getRulesTable()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
	
		try {
			$dbConnection->beginTransaction();
	
			if (!current($this->getRulesTable()->select(array('RULE_ID'=>$ruleId))->toArray())) {
				throw new \Exception("rule id passed '{$ruleId}' does not seem to exist - to set a new rule, pass '-1' as rule id");
			}
			
			$this->getRulesTable()->update($ruleProperties, array('RULE_ID'=>$ruleId));

			$newConditions = $updateConditions = array();
			foreach ($ruleConditions as $ruleCondition) { // first divide rules passed to existing/new
				$ruleCondition['rule_id'] = $ruleId;
				$ruleCondition['trigger_id'] = 0;
				if (isset($ruleCondition['condition_id']) && $ruleCondition['condition_id'] > 0) {
					$updateConditions[$ruleCondition['condition_id']] = $ruleCondition;
				}
				else {
					$newConditions[] = $ruleCondition;
				}
			}
	
			// we first iterate over the existing rules, and either update them or delete them. then we go over the new rules passed and add them
			$existingConditions = $this->findRuleConditions($ruleId);
			foreach ($existingConditions as $existingCondition) {
				$conditionId = $existingCondition['CONDITION_ID'];
				
				if (isset($updateConditions[$conditionId])) {
					if (isset($updateConditions[$conditionId]['operation']) && !trim($updateConditions[$conditionId]['operation'])) { // client does not pass operation properly
						unset($updateConditions[$conditionId]['operation']);
					}
					$this->getConditionsTable()->update($updateConditions[$conditionId], array('CONDITION_ID'=>$conditionId));					
				}else {
					$this->getConditionsTable()->delete(array('CONDITION_ID'=>$conditionId));
				}
			}

			foreach ($newConditions as $newCondition) {
				$this->setNewRuleCondition($ruleCondition, $ruleId);
			}
			
			$newTriggers = $updateTriggers = array();
			$ruleTriggers = $this->cleanUpdatedTriggers($ruleTriggers);		
			foreach ($ruleTriggers as $ruleTrigger) {
				$ruleTrigger['triggerProperties']['rule_id'] = $ruleId;
				$triggerProperties = $ruleTrigger['triggerProperties'];
				if (isset($triggerProperties['trigger_id']) && $triggerProperties['trigger_id'] > 0) {
					$updateTriggers[$triggerProperties['trigger_id']] = $ruleTrigger;//['triggerProperties']
				}
				else {
					$newTriggers[] = $ruleTrigger;
				}
			}
			
			$existingTriggers = $this->findRuleTriggers($ruleId);
			foreach ($existingTriggers as $existingTrigger) {
				$triggerId = $existingTrigger['TRIGGER_ID'];
				if (isset($updateTriggers[$triggerId])) {
					$this->updateTrigger($updateTriggers[$triggerId], $triggerId);
				}else {
					$this->deleteTriggerData($triggerId);
				}
			}
			
			foreach ($newTriggers as $newTrigger) {
				$this->setNewTrigger($newTrigger, $ruleId);
			}
	
			$dbConnection->commit();
		} catch (\Exception $e) {
			Log::err("Failed to set new rule: " . $e->getMessage());
			$dbConnection->rollback();
			throw $e;
		}
	
		Log::debug("Update of rule {$ruleId} was executed ok");
		return $ruleId;
	}
	
	private function deleteTriggerData($triggerId) {
		$this->getTriggersTable()->delete(array('TRIGGER_ID'=>$triggerId));
		$this->getActionsTable()->delete(array('TRIGGER_ID'=>$triggerId));
		$this->getConditionsTable()->delete(array('TRIGGER_ID'=>$triggerId));
	}
	
	/**
	 * GUI client passes trigger_id_X => cond_id_1 + trigger_id_X => cond_id_2, rather than trigger_id_X => cond_id_1 + cond_id_2. 
	 * We also group the triggers according to the serverity, as gui may pass -1 to an exisiting trigger (for instance when adding relative value to an already set absolute trigger value in slow request trigger)
	 * 
	 * @param array $ruleTriggers
	 */
	private function cleanUpdatedTriggers($ruleTriggers) {
		$cleanedTriggers = array();
		foreach ($ruleTriggers as $ruleTrigger) {
			$idx = $ruleTrigger['triggerProperties']['severity'];			
			if (isset($ruleTrigger['triggerProperties']['trigger_id']) && $ruleTrigger['triggerProperties']['trigger_id'] > 0) {
				$triggerId = $ruleTrigger['triggerProperties']['trigger_id'];
			} else {
				$triggerId = -1;
			}

			if (isset($cleanedTriggers[$idx])) {
				if ($triggerId > 0) {
					$cleanedTriggers[$idx]['triggerProperties']['trigger_id'] = $triggerId;
				}
				
				if (isset($ruleTrigger['triggerConditions'])) {
					foreach($ruleTrigger['triggerConditions'] as $condition) {
						$cleanedTriggers[$idx]['triggerConditions'][] = $condition;
					}
				}
				if (isset($ruleTrigger['triggerActions'])) {
					foreach($ruleTrigger['triggerActions'] as $action) {
						$cleanedTriggers[$idx]['triggerActions'][] = $action;
					}
				}
			} else {
				$cleanedTriggers[$idx] = $ruleTrigger;
			}
		}
	
		return $cleanedTriggers;
	}
	
	private function setNewRule($ruleProperties, $ruleConditions, $ruleTriggers) {
		// @todo - validate $ruleProperties against db columns ,and strip tags
	
		$dbConnection = $this->getRulesTable()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
	
		try {
			$dbConnection->beginTransaction();
	
			if (!isset($ruleProperties['creator'])) {
				$ruleProperties['creator'] = 1; // default value
			}
				
			$res = $this->getRulesTable()->insert($ruleProperties);
			$ruleId = $this->getRulesTable()->getLastInsertValue();
			
			if (!$ruleId) {
				throw new \ZendServer\Exception(_t("Failed to insert to the rules table"));
			}
				
			foreach ($ruleConditions as $ruleCondition) {
				$this->setNewRuleCondition($ruleCondition, $ruleId);
			}
				
			foreach ($ruleTriggers as $ruleTrigger) {
				$this->setNewTrigger($ruleTrigger, $ruleId);
			}
	
			$dbConnection->commit();
		} catch (\Exception $e) {
			Log::err("Failed to set new rule: " . $e->getMessage());
			Log::debug($e);
			$dbConnection->rollback();
			throw $e;
		}
	
		Log::debug("Newly created rule {$ruleId} was set ok");
		return $ruleId;
	}
	
	private function updateTrigger($trigger, $triggerId) {
		$this->getTriggersTable()->update($trigger['triggerProperties'], array('TRIGGER_ID'=>$triggerId));
	
		// TRIGGER CONDITIONS HANDLING
		$newTriggerConditions = $updateTriggerConditions = array();
		if (isset($trigger['triggerConditions'])) {			
			foreach ($trigger['triggerConditions'] as $triggerCondition) {
				$triggerCondition['rule_id'] = 0;
				$triggerCondition['trigger_id'] = $triggerId;
				if (isset($triggerCondition['condition_id']) && $triggerCondition['condition_id'] > 0) {
					$updateTriggerConditions[$triggerCondition['condition_id']] = $triggerCondition;
				}
				else {
					$newTriggerConditions[] = $triggerCondition;
				}
			}
		}
			
		$existingConditions = $this->findTriggerConditions($triggerId);
		foreach ($existingConditions as $existingCondition) {
			$conditionId = $existingCondition['CONDITION_ID'];
			if (isset($updateTriggerConditions[$conditionId])) {
				if (isset($updateTriggerConditions[$conditionId]['operation']) && !trim($updateTriggerConditions[$conditionId]['operation'])) { // client does not pass operation properly
					unset($updateTriggerConditions[$conditionId]['operation']);
				}					
				$this->getConditionsTable()->update($updateTriggerConditions[$conditionId], array('CONDITION_ID'=>$conditionId));
			}else {
				$this->getConditionsTable()->delete(array('CONDITION_ID'=>$conditionId));
			}
		}
		
		foreach ($newTriggerConditions as $newTriggerCondition) {
			$this->setNewTriggerCondition($newTriggerCondition, $triggerId);
		}

		// TRIGGER ACTIONS HANDLING
		$newtriggerActions = $updatetriggerActions = array();
		if (isset($trigger['triggerActions'])) {			
			foreach ($trigger['triggerActions'] as $triggerAction) {
				$triggerAction['trigger_id'] = $triggerId;
				if (isset($triggerAction['action_id']) && $triggerAction['action_id'] > 0) {
					$updatetriggerActions[$triggerAction['action_id']] = $triggerAction;
				}
				else {
					$newtriggerActions[] = $triggerAction;
				}
			}
		}
			
		$existingActions = $this->findtriggerActions($triggerId);
		foreach ($existingActions as $existingAction) {
			$actionId = $existingAction['ACTION_ID'];
			if (isset($updatetriggerActions[$actionId])) {
				$this->getActionsTable()->update($updatetriggerActions[$actionId], array('ACTION_ID'=>$actionId));
				$this->getEventManager()->trigger('update-monitorrule-trigger-action', $triggerId, $updatetriggerActions[$actionId]);
			}else {
				$this->getActionsTable()->delete(array('ACTION_ID'=>$actionId));
			}
		}
		
		foreach ($newtriggerActions as $newtriggerAction) {
			$this->setNewtriggerAction($newtriggerAction, $triggerId);
		}

	}
	
	private function setNewTrigger($trigger, $ruleId) {
		$triggerProperties = $trigger['triggerProperties'];
		unset($triggerProperties['trigger_id']);
		$triggerProperties['rule_id'] = $ruleId;
		
		$triggers = $this->getTriggersTable()->select($triggerProperties);
		if (0 == $triggers->count()) {
			$this->getTriggersTable()->insert($triggerProperties);	//@todo - validate values
			$triggerId = $this->getTriggersTable()->getLastInsertValue();
		} else {
			$triggerRow = $triggers->current();
			$triggerId = $triggerRow['TRIGGER_ID'];
		}
		
		if (isset($trigger['triggerConditions'])) {
			foreach ($trigger['triggerConditions'] as $triggerCondition) {
				$this->setNewTriggerCondition($triggerCondition, $triggerId);
			}
		}
	
		if (isset($trigger['triggerActions'])) {
			foreach ($trigger['triggerActions'] as $triggerAction) {
				$this->setNewTriggerAction($triggerAction, $triggerId);
			}
		}
	}

	private function setNewRuleCondition($ruleCondition, $ruleId) {
		unset($ruleCondition['condition_id']);
		$ruleCondition['rule_id'] = $ruleId;
		$ruleCondition['trigger_id'] = 0;
		if (!isset($ruleCondition['operation']) || !trim($ruleCondition['operation'])) { // client code does not display operation, hence it's value might not be passed
			$ruleCondition['operation'] = $this->getruleOperationFromAttribute($ruleCondition['attribute']);
		}

		$this->getConditionsTable()->insert($ruleCondition);
	}	
	
	private function getruleOperationFromAttribute($attribute) {
		if (!isset($this->AttributeToOperation[$attribute])) {
			throw new \ZendServer\Exception(_t("Unknown condition attribute value %s",array($attribute)));
		}
		
		return $this->AttributeToOperation[$attribute];
	}
	
	private function setNewTriggerCondition($triggerCondition, $triggerId) {
		unset($triggerCondition['condition_id']);
		$triggerCondition['rule_id'] = 0;
		$triggerCondition['trigger_id'] = $triggerId;
		if (!isset($triggerCondition['operation']) || !trim($triggerCondition['operation'])) { // client code does not display operation, hence it's value might not be passed
			$triggerCondition['operation'] = $this->getruleOperationFromAttribute($triggerCondition['attribute']);
		}
		
		$this->getConditionsTable()->insert($triggerCondition);
	}
	
	private function setNewTriggerAction($triggerAction, $triggerId) {
		unset($triggerAction['action_id']);
		$triggerAction['trigger_id'] = $triggerId;
		
		$currTriggers = $this->getActionsTable()->select(array('TRIGGER_ID'=>$triggerId, 'ACTION_TYPE'=>$triggerAction['action_type']))->toArray();
		if (count($currTriggers) == 0) {
			$this->getActionsTable()->insert($triggerAction);
			$this->getEventManager()->trigger('update-monitorrule-trigger-action', $triggerId, $triggerAction);
		}
	}	
	
	private function findRuleConditions($ruleId) {
		return $this->getConditionsTable()->select(array('RULE_ID'=>$ruleId))->toArray();
	}
	
	private function findRuleTriggers($ruleId) {
		return $this->getTriggersTable()->select(array('RULE_ID'=>$ruleId))->toArray();
	}

	private function findTriggerConditions($triggerId) {
		return $this->getConditionsTable()->select(array('TRIGGER_ID'=>$triggerId))->toArray();
	}

	private function findtriggerActions($triggerId) {
		return $this->getActionsTable()->select(array('TRIGGER_ID'=>$triggerId))->toArray();
	}	
	
	private function getRulesByIds(array $rulesIds, array $appIds=array()) {

        try {
            $appIds = $this->identityFilter->filterAppIds($appIds, true);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return array();
            }
        }

		$select = new \Zend\Db\Sql\Select();
		$select->from($this->getRulesTable()->getTable());
		$select->where($this->getSqlInStatement('RULE_ID', $rulesIds));
		if (count($appIds)) {
		    $select->where($this->getSqlInStatement('APP_ID', $appIds));
		}
		return $this->getRulesTable()->selectWith($select)->toArray();
	}
	
	private function getRulesByAppId($appId) {
		$select = new \Zend\Db\Sql\Select();
		$select->from($this->getRulesTable()->getTable());
		$select->where("APP_ID = $appId");
		return $this->getRulesTable()->selectWith($select)->toArray();
	}
	
	private function processRules(array $rules, $filter) {
		$rules = $this->addRulesPeripherals($rules);
		
		foreach ($rules as $ruleId => $rule) {
			if (! empty($rule['RULE_PARENT_ID']) && isset($rules[$rule['RULE_PARENT_ID']])) {
				unset($rules[$rule['RULE_PARENT_ID']]);
			}
		}
	
		Log::debug('Retrieved '.count($rules).' rules by filter ' . print_r($filter, true));
		return new Set($rules, 'MonitorRules\Rule');
	}
	
	private function sortRulesByName(array $rules) {
		$rulesByName = $rulesById = array();		
		foreach($rules as $rule) {
			$rulesByName[$rule['NAME']] = $rule;
		}
		
		uksort($rulesByName, 'strcasecmp'); // non case sensitive
		foreach($rulesByName as $rule) {
			$rulesById[$rule['RULE_ID']] = $rule;
		}
		
		return $rulesById;
	}
	
	/**
	 * Add actions and conditions to the rules map
	 * @param array $rulesMap
	 * @return array
	 */
	private function addRulesPeripherals(array $rulesMap = array()) {
		$ruleTypeIds = array_map(function($item){return $item['RULE_TYPE_ID'];}, $rulesMap);
		$ruleTypes = $this->getRuleTypesTable()->select($this->getSqlInStatement('TYPE_ID', $ruleTypeIds))->toArray();
		$associatedTypes = array();
		foreach ($ruleTypes as $ruleType) {
			$associatedTypes[$ruleType['TYPE_ID']] = $ruleType;
		}
		
		if (!$rulesMap) { // empty
			return $rulesMap;
		}
		
		if (!$rulesMap) { // empty
			return $rulesMap;
		}
		
		$rulesIds = array_map(function($item){return $item['RULE_ID'];}, $rulesMap);
		$ruleConditions = $this->getConditionsTable()->select($this->getSqlInStatement('RULE_ID', $rulesIds))->toArray();
		
		$triggers = $this->getTriggersTable()->select($this->getSqlInStatement('RULE_ID', $rulesIds))->toArray();
		$triggerIds = array_map(function($item) {return $item['TRIGGER_ID'];}, $triggers);
	
		$actions = $this->getActionsTable()->select($this->getSqlInStatement('TRIGGER_ID', $triggerIds))->toArray();
		
		$conditions = $this->getConditionsTable()->select($this->getSqlInStatement('TRIGGER_ID', $triggerIds))->toArray();

		$rules = array();
		foreach ($rulesMap as $rule) {
			$ruleId = $rule['RULE_ID'];
			
			$ruleConditionsArray = array();
			foreach ($ruleConditions as $ruleCondition) {
				if ($ruleId == $ruleCondition['RULE_ID']) {
					$ruleConditionsArray[$ruleCondition['CONDITION_ID']] = $ruleCondition;
				}
			}
			
			$triggersArray = array();
			foreach ($triggers as $trigger) {
				if ($ruleId == $trigger['RULE_ID']) {
					$triggerId = $trigger['TRIGGER_ID'];
					$triggersArray[$triggerId] = $trigger;
					$triggersArray[$triggerId]['actions'] = array();
					$triggersArray[$triggerId]['conditions'] = array();
	
					foreach ($actions as $action) {
						$actionTriggerId = $action['TRIGGER_ID'];
						if ($actionTriggerId == $triggerId) {
							$triggersArray[$triggerId]['actions'][$action['ACTION_ID']] = $action;
						}
					}
					
					foreach ($conditions as $condition) {
						$conditionTriggerId = $condition['TRIGGER_ID'];
						if ($conditionTriggerId == $triggerId) {
							$triggersArray[$triggerId]['conditions'][$condition['CONDITION_ID']] = $condition;
						}
					}
				}
			}
			
			$rules[$ruleId] = $rule;
			$rules[$ruleId]['conditions'] = $ruleConditionsArray;
			$rules[$ruleId]['triggers'] = $triggersArray;			
		}
	
		return $this->sortRulesByName($rules);
	}
	
	/**
	 * @return array sql queries
	 */
	public function getExportData() {
	
		$data = array();
	
		$table = $this->getTableGateway()->getTable();
		$select = new \Zend\Db\Sql\Select ( $table );
	
		$r = $this->getTableGateway()->getResultSetPrototype();
		$res = $this->selectWith($select)->toArray();
	
		$intVals =  array("IS_INSTALLED", "IS_LOADED", "IS_ZEND_COMPONENT");
	
		$blackList = array(
	
		);
	
	
		foreach ($res as $row) {
				
			if (in_array($row['NAME'], $blackList)) {
				continue;
			}
				
			foreach ($row as $key => $val) {
				if (!in_array($key, $intVals)) {
					$row[$key] = "'" . $row[$key] . "'";
				}
			}
			$line = "INSERT INTO " . (string) $this->getTableGateway()->getTable() . " (" . implode(",", array_keys($row)) . ') VALUES (' . implode(",", array_values($row)) . ")";
			$data[] = $line;
		}
	
		return $data;
	}
	/**
	 * @param AclQuery $acl
	 * @return Mapper
	 */
	public function setAcl(AclQuery $acl) {
		$this->acl = $acl;
		return $this;
	}

    /* (non-PHPdoc)
     * @see \Deployment\IdentityApplicationsAwareInterface::setIdentityFilter()
     */
    public function setIdentityFilter(IdentityFilterInterface $filter) {
        $filter->setAddGlobalAppId(true);
        $this->identityFilter = $filter;
        return $this;
    }
    
    /**
     * @param boolean $flag
     */
    public function setAddGlobalAppId($flag = true) {
    	$this->identityFilter->setAddGlobalAppId($flag);
    }

}
