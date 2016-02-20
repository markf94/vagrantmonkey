<?php

namespace JobQueue\Filter;
use ZendServer\Exception;


use ZendServer\Filter\FilterInterface;

use Zend\Json\Json;

use ZendServer\Container\Structure;
use ZendServer\Filter\Filter as BaseFilter;

class Filter extends BaseFilter implements FilterInterface {
	
	
	const ISSUE_FILTER_TYPE = 'job';

	/**
	 * @var Dictionary
	 */
	protected $dictionary;
		
	protected $applicationIds = array();
	protected $statuses = array();
	protected $script;
	protected $priority;
	protected $ruleId;
	protected $ruleIds;
	protected $queueId;
	protected $queueIds;
	
	protected $scheduledBefore;
	protected $scheduledAfter;
	protected $executedBefore;
	protected $executedAfter;
	protected $freeText;
	
	
	protected $serializableProperties = array('statuses', 'script', 'priority', 'ruleIds');
	
	function getType() {
        return self::ISSUE_FILTER_TYPE;
    }
    
	public function __construct($data) {	
		// though API allows filtering according to type, this field is of no interest (JOB_TYPE_HTTP Vs Obsolete JOB_TYPE_SHELL), and hence will be ignored
		// @todo - app_id
		
		if (isset($data['name'])) {
			$this->setName($data['name']);
		}
		
		if (isset($data['script'])) {
			$this->setScript($data['script']);
		}		

		if (isset($data['priority'])) {
			$this->setPriority($data['priority']);
		}		
		
		if (isset($data['status'])) {
			$this->setStatuses($data['status']);
		}

		if (isset($data['rule_ids'])) {
			$this->setRuleIds($data['rule_ids']);
		}		

		if (isset($data['queue_ids'])) {
			$this->setQueueIds($data['queue_ids']);
		}		

		if (isset($data['scheduled_before'])) {
			$this->setScheduledBefore($data['scheduled_before']);
		}

		if (isset($data['scheduled_after'])) {
			$this->setScheduledAfter($data['scheduled_after']);
		}

		if (isset($data['executed_before'])) {
			$this->setExecutedBefore($data['executed_before']);
		}

		if (isset($data['executed_after'])) {
			$this->setExecutedAfter($data['executed_after']);
		}	

		if (isset($data['freeText'])) {
			$this->setFreeText($data['freeText']);
		}

		if (isset($data['app_ids'])) {
			$this->setApplicationIds($data['app_ids']);
		}
	}
    
    /**
     * @return array $app_id
     */
    public function getApplicationIds() {
    	return $this->applicationIds;
    }
    
	public function getScript() {
		return $this->script;
	}
	
	/**
	 * @param string $script
	 */
	public function setScript($script) {
		$this->script = $script;
	}

	/**
	 * @return array
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @param mixed $priority
	 */
	public function setPriority($priority) {
		if (is_string($priority)) {
			$priority = array($priority);
		}
		
		$this->priority = $priority;
	}
	
	/**
	 * @return array
	 */
	public function getStatuses() {
		return $this->statuses;
	}
	
	/**
	 * @param mixed $statuses
	 */
	public function setStatuses($statuses) {
		if (is_string($statuses)) {
			$statuses = array($statuses);
		}
		
		$this->statuses = $statuses;
	}
	
	public function getRuleId() {	
		return $this->ruleId;
	}


	public function getRuleIds() {
		return $this->ruleIds;
	}
	
	public function getQueueId() {	
		return $this->queueId;
	}


	public function getQueueIds() {
		return $this->queueIds;
	}
	
	
	/**
	 * @param integer $ruleId
	 */
	public function setRuleIds($ruleIds) {
		
		$this->ruleIds = $ruleIds;
	}
	
	/**
	 * @param integer $ruleId
	 */
	public function setRuleId($ruleId) {
		if (is_array($ruleId)) {
			$ruleId = current($ruleId);// widget filter might pass array instead of integer
		}
		
		$this->validate_numeric($ruleId, 'ruleId');		
		$this->ruleId = $ruleId;
	}

	/**
	 * @param array $queueIds
	 */
	public function setQueueIds($queueIds) {
		
		$this->queueIds = $queueIds;
	}
	
	/**
	 * @param integer $queueId
	 */
	public function setQueueId($queueId) {
		if (is_array($queueId)) {
			$queueId = current($queueId);// widget filter might pass array instead of integer
		}
		
		$this->validate_numeric($queueId, 'queueId');		
		$this->queueId = $queueId;
	}

	private function validate_numeric($value, $name) {
		if (! is_numeric($value)) {
			throw new Exception(_t("%s filter must be numeric, '%s' passed", array($name, $value)));
		}		
	}
	
	public function getScheduledBefore() {
		return $this->scheduledBefore;
	}

	public function setScheduledBefore($scheduledBefore) {
		$this->validate_numeric($scheduledBefore, 'scheduledBefore');
		$this->scheduledBefore = $scheduledBefore;
	}

	public function getScheduledAfter() {
		return $this->scheduledAfter;
	}

	public function setScheduledAfter($scheduledAfter) {
		$this->validate_numeric($scheduledAfter, 'scheduledAfter');
		$this->scheduledAfter = $scheduledAfter;
	}

	public function getExecutedBefore() {
		return $this->executedBefore;
	}

	public function setExecutedBefore($executedBefore) {
		$this->validate_numeric($executedBefore, 'executedBefore');
		$this->executedBefore = $executedBefore;
	}

	public function getExecutedAfter() {
		return $this->executedAfter;
	}

	public function setExecutedAfter($executedAfter) {
		$this->validate_numeric($executedAfter, 'executedAfter');
		$this->executedAfter = $executedAfter;
	}
	
	public function setFreeText($freeText) {
		$this->freeText = $freeText;
	}
	
	public function getFreeText() {
		return $this->freeText;
	}

	/**
     * @param array: $applicationIds
     */
    public function setApplicationIds($applicationIds) {
    	// convert to array of integers and not strings, for call API that supports only on array of ints
    	if (is_array($applicationIds)) {
    		$applicationIds = array_map("intval", $applicationIds);
    	}
    	$this->applicationIds = $applicationIds;
    }    
    
	public function serialize() {
	    $reflect = new \ReflectionClass($this);
	    $props = $reflect->getProperties();
	    $serializable = array();
	    foreach ($props as $prop) { /* @var $prop \ReflectionProperty */
	        if (!in_array($prop->getName(), $this->serializableProperties)) {
	            $prop->setAccessible(true);
	            $serializable[$prop->getName()] = $prop->getValue($this);
	        }
	    }
	    return Json::encode($serializable);
	}
	

	public function getDictionary() {
		if ($this->dictionary) {
			return $this->dictionary;
		}
	
		return $this->dictionary = new Dictionary();
	}
}