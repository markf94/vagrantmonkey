<?php
namespace Issue;
use ZendServer\Log\Log;
class Container {
	
	/**
	 * @var array
	 */
	protected $issueData;
	
	/**
	 * @param array $issue
	 */
	public function __construct(array $issue) {
		if (isset($issue[ZM_DATA_REQUEST_COMPONENTS]) && isset($issue[ZM_DATA_REQUEST_COMPONENTS]['type'])) {
			unset($issue[ZM_DATA_REQUEST_COMPONENTS]['type']);
		}
		$this->issueData = $issue;
	}
	
	public function toArray() {
		return $this->getIssueData();
	}
	
	/**
	 * @return array
	 */
	public function getIssueData() {
		return $this->issueData;
	}
	
	public function getMvcData() {	    
	    if (isset($this->issueData['comp_value']) && ! empty($this->issueData['comp_value'])) {
	        $names = explode(',', $this->issueData['comp_name']);
	        $values = explode(',', $this->issueData['comp_value']);
	        
	        $uniqueValues = count(array_unique($names));
	        
	        $result = array();
	        for ($i = 0; $i < $uniqueValues; $i++) {
	            $result[$names[$i]] = $values[$i]; 
	        }
	        
	        // ordering by the module->controller->action bug #ZSRV-15177
	        $orderBy = array('module', 'controller', 'action');
	        $orderedByResult = array();
	        foreach ($orderBy as $key) {
	            if (array_key_exists($key, $result)) {
	                $orderedByResult[$key] = $result[$key];
	            }
	        }
	        if (empty($orderedByResult)) {
	            $orderedByResult = $result;
	        }
	        
	        return $orderedByResult;
	    }
	    
	    return array();
	}
	
	public function hasMvcData() {
		return isset($this->issueData['comp_name']);
	}
	
	public function setMvcData($mvcData) {
	    $this->issueData['comp_name'] =  implode(',', array_keys($mvcData));
	    $this->issueData['comp_value'] =  implode(',', array_values($mvcData));
	}
	
	/**
	 * @return integer
	 */
	public function getId() {
		return isset($this->issueData['cluster_issue_id']) ? $this->issueData['cluster_issue_id'] : null;
	}
	
	/**
	 * @return string
	 */
	public function getRuleName() {
		return $this->issueData['rule_name'];
	}
	
	/**
	 * @return integer
	 */
	public function getCount() {
		return $this->issueData['repeats'];
	}
	/**
	 * Returns the trace file flag for the element
	 * @return boolean
	 */
	public function hasTrace() {
		return $this->issueData['tracer_dump_file']?true:false;
	}
	
	/**
	 * @return integer
	 */
	public function getFirstOccurance() {
		return $this->issueData['first_timestamp'];
	}
	
	/**
	 * @return integer
	 */
	public function getLastOccurance() {
		return $this->issueData['last_timestamp'];
	}
	
	/**
	 * @return integer
	 */
	public function getEventType() {
		return $this->issueData['event_type'];
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->issueData['full_url'];
	}
	
	/**
	 * @return string
	 */
	public function getBaseUrl() {
		return '';
	}
	
	/**
	 * @return string
	 */
	public function getFilename() {
		return $this->issueData['file_name'];
	}
	
	/**
	 * @return string
	 */
	public function getFunction() {
		return $this->issueData['function_name'];
	}
	
	/*
	 * @return string
	 */
	public function getCustomEventClass() {
	    return (string) $this->getAggregationValue(ZM_DATA_ATTR_CLASS);
	}
	
	/**
	 * @return integer
	 */
	public function getLine() {
		return $this->issueData['line'];
	}
	
	/**
	 * @return integer
	 */
	public function getSeverity() {
		return $this->issueData['severity'];
	}
	
	/**
	 * @return integer
	 */
	public function getStatus() {
		return $this->issueData['status'];
	}
	
	/**
	 * @return string
	 */
	public function getAggregationHint() {
		return $this->issueData['agg_hint'];
	}
	
	/**
	 * @return string
	 */
	public function getErrorString() {
		return isset($this->issueData['error_string'])?$this->issueData['error_string']:"";
	}
	
	public function setErrorString($str) {
		$this->issueData['error_string'] = $str;
	}
	
	
	/**
	 * @return integer
	 */
	public function getPHPErrorType() {
		return (integer)$this->getAggregationValue(ZM_DATA_ATTR_ERROR_TYPE);
	}
	
	/**
	 * @return array
	 */
	public function getApplicationId() {
		return $this->issueData['app_id'];
	}
	
	public function getLastAppId() {
		$lastAppId = null;
		$appIds = array($this->getApplicationId());
		if (!(is_array($appIds) && $appIds)) {
			return $lastAppId;
		}
				
		foreach($appIds as $appId) {
			if ($appId != -1) {
				$lastAppId = $appId;
			}
		}
		
		return $lastAppId;
	}
	
	/**
	 * @param string $key
	 * @return array
	 */
	private function getAggregationValue($key) {
		if (isset($this->issueData[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES])
				&& array_key_exists($key, $this->issueData[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES])) {
				
			return $this->issueData[ZM_DATA_ISSUE_AGG_KEY_ATTRIBUTES][$key];
		}
		return null;
	}	

	public function setMaxEventGroup(\EventsGroup\Container $eventGroup) {
		$this->issueData['maxEventGroup'] = $eventGroup;
	}
	
	/**
	 * @return \EventsGroup\Container
	 */
	public function getMaxEventGroup() {
		return isset($this->issueData['maxEventGroup']) ? $this->issueData['maxEventGroup'] : new \EventsGroup\Container(array());
	}

	public function setCodeTracingEventGroupId($eventGroupId) {
		$this->issueData['codeTracingEventGroupId'] = $eventGroupId;
	}
	
	/**
	 * @return integer
	 */
	public function getCodeTracingEventGroupId() {
		return isset($this->issueData['codeTracingEventGroupId']) ? $this->issueData['codeTracingEventGroupId'] : '';
	}	
	
	/**
	 * @return integer
	 */
	public function getRuleId() {
		return isset($this->issueData['rule_id']) ? $this->issueData['rule_id'] : '';
	}
}

