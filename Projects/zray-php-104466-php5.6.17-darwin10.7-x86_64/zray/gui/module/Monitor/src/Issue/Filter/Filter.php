<?php

namespace Issue\Filter;

use ZendServer\Filter\FilterInterface;

use Zend\Json\Json;

use ZendServer\Container\Structure;
use ZendServer\Filter\Filter as BaseFilter;

class Filter extends BaseFilter implements FilterInterface {
	
	/**
	 * @var array
	 */
	private $applicationIds = array();
	private $statuses = array();
	private $severities = array();
	private $eventTypes = array();
	private $ruleNames = array();
	private $issuesIds = array();
	private $freeText = "";
	private $from = '';
	private $to = '';
	private $aggKeys = array();
	private $fullUrl = '';
	protected $name = '';
	
	private $serializableProperties = array('statuses', 'severities', 'eventTypes');
	
	protected $type = \ZendServer\Filter\Filter::ISSUE_FILTER_TYPE;
    
	public function __construct($data) {
		if (isset($data['severities'])) {
			$this->severities = $data['severities'];
		}
		if (isset($data['statuses'])) {
			$this->statuses = $data['statuses'];
		}
		if (isset($data['eventTypes'])) {
			$this->eventTypes = $data['eventTypes'];
		}
		if (isset($data['ruleNames'])) {
			$this->ruleNames = $data['ruleNames'];
		}
		if (isset($data['applicationIds'])) {
			$this->applicationIds = $data['applicationIds'];
		}
		if (isset($data['issuesIds'])) {
			$this->issuesIds = $data['issuesIds'];
		}
		if (isset($data['freeText'])) {
			$this->freeText = $data['freeText'];
		}
		if (isset($data['from'])) {
    		$this->setFrom($data['from']);
		}
		if (isset($data['to'])) {
		    $this->setTo($data['to']);
		}
		if (isset($data['aggKeys'])) {
			$this->setAggKeys($data['aggKeys']);
		}
		if (isset($data['fullUrl'])) {
			$this->setFullUrl($data['fullUrl']);
		}
	}
	
	/**
	 * @param array $applicationIds
	 * @return \MonitorUi\Filter\Container
	 */
	public function getApplicationIds() {
		return $this->applicationIds;
	}

	public function getSeverities() {
		return $this->severities;
	}
	
	public function getEventTypes() {
		return $this->eventTypes;
	}
	
	public function getRuleNames() {
		return $this->ruleNames;
	}
	
	public function getStatuses() {
		return $this->statuses;
	}
	
	public function getIssuesIds() {
		return $this->issuesIds;
	}
	
	public function getFreeText() {
		return $this->freeText;
	}
	
	public function getFrom() {
	    return $this->from;
	}
	
	public function getTo() {
	    return $this->to;
	}
	
	public function getAggKeys() {
		return $this->aggKeys;
	}
	
	public function getFUllUrl() {
		return $this->fullUrl;
	}
	
	/**
	 * Get the $name
	 */
	public function getName() {
	    return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
	    $this->name = $name;
	}
	
	/**
     * @param array: $applicationIds
     */
    public function setApplicationIds($applicationIds) {
        $this->applicationIds = $applicationIds;
    }

	/**
     * @param multitype: $statuses
     */
    public function setStatuses($statuses) {
        $this->statuses = $statuses;
    }

	/**
     * @param multitype: $severities
     */
    public function setSeverities($severities) {
        $this->severities = $severities;
    }

	/**
     * @param multitype: $eventTypes
     */
    public function setEventTypes($eventTypes) {
        $this->eventTypes = $eventTypes;
    }
    
    /**
     * @param multitype: $ruleNames
     */
    public function setRuleNames($ruleNames) {
    	$this->ruleNames = $ruleNames;
    }
    
   public function setIssuesIds($issuesIds) {
   		$this->issuesIds = $issuesIds;
   }
   
   public function setFreeText($freeText) {
		$this->freeText = $freeText;
   }
   
   public function setFrom($from) {
       $this->from = $from;
   }
   
   public function setTo($to) {
       $this->to = $to;
   }
   
   public function setAggKeys($aggKeys) {
   		$this->aggKeys = $aggKeys;
   }
   
   public function setFullUrl($fullUrl) {
   	$this->fullUrl = $fullUrl;
   }

	public function serialize() {
	    $reflect = new \ReflectionClass($this);
	    $props = $reflect->getProperties();
	    $serializable = array();
	    foreach ($props as $prop) { /* @var $prop \ReflectionProperty */
	        if (in_array($prop->getName(), $this->serializableProperties)) {
	            $prop->setAccessible(true);
	            $serializable[$prop->getName()] = $prop->getValue($this);
	        }
	    }
	    return Json::encode($serializable);
	}
}