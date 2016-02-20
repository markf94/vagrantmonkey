<?php
namespace Issue\Filter;
use ZendServer\Log\log;

class Translator {
    
    /**
     * \Issue\Filter\Filter
     * @var $filter
     */
    protected $filter;
    
    protected $monitorRulesMapper;
    
    /**
     * \Issue\Filter\Dictionary
     * @var $issueDictionary
     */
    protected $issueDictionary;
    
    public function __construct(\Issue\Filter\Filter $filter = null) {
        if ($filter) {
            $this->filter = $filter;
        }
    }
    
    public function setMonitorRuleMapper(\MonitorRules\Model\Mapper $mapper) {
        $this->monitorRulesMapper = $mapper;
    }
    
    public function getMonitorRuleMapper() {
        return $this->monitorRulesMapper;
    }
    
    public function setFilter(\Issue\Filter\Filter $filter) {
        $this->filter = $filter;
    }
    
    /**
     * @return \Issue\Filter\Filter
     */
    public function getFilter() {
        return $this->filter;
    }
    
    public function translate() {
        $translation = array();
        if (is_array($this->getFilter()->getApplicationIds()) && count($this->getFilter()->getApplicationIds())) {
            $translation[ZM_FILTER_APP_ID] = $this->getFilter()->getApplicationIds();
            $translation['applicationIds'] = $this->getFilter()->getApplicationIds();
        }
		if (is_array($this->getFilter()->getRuleNames()) && count($this->getFilter()->getRuleNames()) > 0) {
		    
			$rules = $this->getMonitorRuleMapper()->getRuleIdsFromNames($this->getFilter()->getRuleNames());
			$ruleIdsArray = array();
			foreach($rules as $rule) {
				$ruleIdsArray[] = $rule['RULE_ID'];
			}
			
			if (!empty($ruleIdsArray)) {
    			$translation['ruleNames'] = $ruleIdsArray;
            	$translation[ZM_FILTER_RULE_NAME] = $ruleIdsArray;
			}
        }
        
        if (is_array($this->getFilter()->getEventTypes()) && count($this->getFilter()->getEventTypes()) > 0) {
            $translation[ZM_FILTER_EVENT_TYPE] = $this->getEventConstants($this->getFilter()->getEventTypes());
            $translation['eventTypes'] = $this->getEventConstants($this->getFilter()->getEventTypes());
        }
    
        if (is_array($this->getFilter()->getSeverities()) && count($this->getFilter()->getSeverities()) > 0) {
            // Currently engine doesn't support array
            $severities = $this->getIssueDictionary()->severitiesToConstants($this->getFilter()->getSeverities());
            $translation[ZM_FILTER_SEVERITY] = $severities[0];
            $translation['severities'] = array($severities[0]);
        }
        
        if (is_array($this->getFilter()->getIssuesIds()) && count($this->getFilter()->getIssuesIds()) > 0) {
        	$translation[ZM_FILTER_ISSUES_IDS] = $this->getFilter()->getIssuesIds();
        	$translation['issueIds'] = $this->getFilter()->getIssuesIds();
        }
        
        if ($this->getFilter()->getFreeText()) {
        	$translation[ZM_FILTER_FREE_TEXT] = $this->getFilter()->getFreeText();
        	$translation['freeText'] = $this->getFilter()->getFreeText();
        }
        
        if ($this->getFilter()->getFrom()) {
            $translation[ZM_FILTER_AFTER_TIMESTAMP] = $this->getFilter()->getFrom();
            $translation['from'] = $this->getFilter()->getFrom();
        }
        
        if ($this->getFilter()->getTo()) {
            $translation[ZM_FILTER_BEFORE_TIMESTAMP] = $this->getFilter()->getTo();
            $translation['to'] = $this->getFilter()->getTo();
        }
        
        if ($this->getFilter()->getAggKeys()) {
        	$translation['aggKeys'] = $this->getFilter()->getAggKeys();
        }
        
        if ($this->getFilter()->getFUllUrl()) {
        	$translation['fullUrl'] = $this->getFilter()->getFUllUrl();
        }
        
        return $translation;
    }
    

    /**
     * @return \Issue\Filter\Dictionary
     */
    public function getIssueDictionary() {
    	if ($this->issueDictionary) return $this->issueDictionary;    	

    	return $this->issueDictionary = new \Issue\Filter\Dictionary();
    }
    
    private function getEventConstants($eventStrings) {
    	$eventsConstants = array();
    	$stringtoConstant = $this->getIssueDictionary()->getIssueEventTypesConstants();
    	foreach($eventStrings as $eventString) {
    		$eventsConstants[] = $stringtoConstant[$eventString];
    	}
    	
    	return $eventsConstants;
    }
   
}