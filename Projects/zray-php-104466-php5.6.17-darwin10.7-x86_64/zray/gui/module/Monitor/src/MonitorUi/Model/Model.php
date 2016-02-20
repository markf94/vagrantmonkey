<?php
namespace MonitorUi\Model;

use EventsGroup\DataContainer;

use ZendServer\Set, Issue, Event, ZendServer\Log\Log, ZendServer\Exception,
MonitorUi\Filter, MonitorUi\Wrapper;

use \Issue\Filter\Translator;
use MonitorRules\Model\Mapper;

class Model {

	const ASC = 'ASC';
	const DESC = 'DESC';
	
	/**
	 * @var MonitorUi\Wrapper
	 */
	private $wrapper = null;
	
	/**
	 * 
	 * @var \ZendServer\Filter\Mapper
	 */
	private $filterMapper = null;
	
	/**
	 * 
	 * @var \Issue\Filter\Translator
	 */
	private $filterTranslator = null;
	
	/**
	 * @var \Issue\Db\Mapper
	 */
	private $issueMapper;
	/**
	 * @var \EventsGroup\Db\Mapper
	 */
	private $eventsGroupMapper;
	
	/**
	 * @param string $requestUid
	 * @throws ZendServer\Exception
	 */
	public function prepareRequestTrace($requestUid) {
		return $this->getWrapper()->prepareRequestTrace($requestUid);
	}
	
	/**
	 * @param string $requestUid
	 * @param string $debug
	 * @param string $amf
	 * @return array
	 * @throws ZwasComponents_MonitorUi_Api_Exception
	 */
	public function getRequestSummary($requestUid, $debug = null, $amf = null) {
		return $this->getWrapper()->getRequestSummary($requestUid, $debug, $amf);
	}
	
	
	/**
	 * @param array $issueIds
	 */
	public function getIssuesLastEventGroupData(array $issueIds = array()) {
		return $this->getWrapper()->getIssuesLastEventGroupData($issueIds);
	}
	
	/**
	 * @param array $params
	 * @param integer $limit
	 * @param integer $offset
	 * @param string $orderby
	 * @param string $direction
	 * @return \Issue\Set
	 */
	public function getIssues(array $params, $limit, $offset, $orderby, $direction) {
		$translation = $this->translateParams($params);
		$issues = $this->getIssueMapper()->getIssues($translation, $limit, $offset, $orderby, $direction);
		Log::info('Retrieved ' . count($issues) . ' issues');
		Log::debug('Filters used: '.print_r($translation, true));
		$issues->setHydrateClass('\Issue\Container');
		return $issues;
	}
	
	public function getIssueIdsByTraceFiles($traceFiles) {
		return $this->getWrapper()->getIssueIdsByTraceFiles($traceFiles);
	}
	
	public function deleteIssues(array $issuesIds) {
		return $this->getWrapper()->deleteIssues($issuesIds);
	}

	public function deleteIssuesByFilter(array $params) {
		return $this->getWrapper()->deleteIssuesByFilter($this->translateParams($params));
	}	

	public function deleteTraceData(array $tracePaths) {
		return $this->getWrapper()->deleteTraceData($tracePaths);
	}	
	
	/**
	 * @param array $params
	 * @return integer
	 */
	public function getIssuesCount(array $params) {
	    $filter = $this->getFilter($params);
		$filterTranslator = $this->getFilterTranslator(); /* @var $filterTranslator \Issue\Filter\Translator */ 
		$filterTranslator->setFilter($filter);
		$issuesCount = $this->getIssueMapper()->getIssuesCount($filterTranslator->translate());
		Log::info('Issues count: ' . $issuesCount);
		return $issuesCount;
	}
	

	/**
	 * @param integer $issueId
	 * @return \Issue\Container
	 */
	public function getIssue($issueId) {
		$issue = $this->getIssueMapper()->getIssue($issueId);
		
		Log::info("Retrieved issues details ($issueId)");
		return $issue;
	}
	
	/**
	 * @param integer $issueId
	 * @param integer $limit
	 * @return \ZendServer\Set
	 */
	public function getEventsGroup($groupId) {
		$eventGroup = $this->getEventsGroupMapper()->getEventsGroup($groupId);
		return $eventGroup;
	}
	
	/**
	 * @param integer $issueId
	 * @param integer $limit
	 * @return \ZendServer\Set
	 */
	public function getEventsGroups($issueId, $limit = Wrapper::DEFAULT_LIMIT, $offset = Wrapper::DEFAULT_OFFSET) {
		$eventGroups = $this->getEventsGroupMapper()->getEventsGroups($issueId, $limit, $offset);
		
		Log::info('Retrieved ' . $eventGroups->count() . ' events groups, limit is '. $limit);
		return $eventGroups;
	}
	
	/**
	 * @param integer $groupId
	 * @return \EventsGroup\DataContainer
	 */
	public function getEventGroupData($groupId) {
		$eventsGroup = $this->getEventsGroupMapper()->getEventGroupData($groupId);
		Log::info('Retrieved events group data ('. $groupId .')');
		return $eventsGroup;
	}
	
	/**
	 * @return array
	 */
	public function getSortColumnsDictionary() {
		if (!defined('ZM_DATA_ISSUE_ID') && !defined('ZM_DATA_RULE_NAME')) {
			throw new \Exception(_t('Failed to run monitor module. The Monitor UI component is not loaded.'));
		}
		return array(
				'id' => ZM_DATA_ISSUE_ID,
				'name' => ZM_DATA_RULE_NAME,
				'repeats' => ZM_DATA_REPEATS,
				'date' => ZM_DATA_LAST_TIMESTAMP,
				'eventType' => ZM_DATA_EVENT_TYPE,
				'fullUrl' => ZM_DATA_FULL_URL,
				'severity' => ZM_DATA_SEVERITY,
				'status' => ZM_DATA_STATUS,
		);
	}
	
	/**
	 * @param MonitorUi\Wrapper $wrapper
	 * @return \MonitorUi\Model\Model
	 */
	public function setWrapper($wrapper) {
		$this->wrapper = $wrapper;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public static function getCustomEventTypes() {
	    return array (
	            ZM_TYPE_CUSTOM,
	    );
	}
	
	/**
	 * @return array
	 */
	public static function getPerformanceEventTypes() {
	    return array (
	            ZM_TYPE_REQUEST_SLOW_EXEC,
	            ZM_TYPE_REQUEST_RELATIVE_SLOW_EXEC,
	            ZM_TYPE_REQUEST_LARGE_MEM_USAGE,
	            ZM_TYPE_REQUEST_RELATIVE_LARGE_MEM_USAGE,
	            ZM_TYPE_REQUEST_RELATIVE_LARGE_OUT_SIZE,
	    );
	}
	
	/**
	 * @return array
	 */
	public static function getFunctionEventTypes() {
	    return array (
	            ZM_TYPE_FUNCTION_ERROR,
	            ZM_TYPE_FUNCTION_SLOW_EXEC,
	    );
	}
	
	/**
	 * @return array
	 */
	public static function getPhpErrorsEventTypes() {
	    return array (
	            ZM_TYPE_ZEND_ERROR,
	    );
	}
	
	/**
	 * @return array
	 */
	public static function getJavaExceptionsEventTypes() {
	    return array (
	            ZM_TYPE_JAVA_EXCEPTION,
	    );
	}
	
	/**
	 * @return array
	 */
	public static function getJobQueueEventTypes() {
	    return array (
            ZM_TYPE_JQ_JOB_EXEC_ERROR,
            ZM_TYPE_JQ_JOB_LOGICAL_FAILURE,
            ZM_TYPE_JQ_JOB_EXEC_DELAY,
            ZM_TYPE_JQ_DAEMON_HIGH_CONCURRENCY_LEVEL,
        );
	}
	
	/**
     * @return \Issue\Filter\Translator
     */
    public function getFilterTranslator() {
        if (is_null($this->filterTranslator)) {
            $this->filterTranslator = new \Issue\Filter\Translator();
        }
        return $this->filterTranslator;
    }

	/**
     * @param $filterTranslator
     */
    public function setFilterTranslator($filterTranslator) {
        $this->filterTranslator = $filterTranslator;
    }
    
    /**
     * @return \ZendServer\Filter\Mapper
     */
    public function getFilterMapper() {
        if (is_null($this->filterMapper)) {
            $this->filterMapper = new \ZendServer\Filter\Mapper();
        }
        return $this->filterMapper;
    }
    
    /**
     * @param \ZendServer\Filter\Mapper $filterMapper
     */
    public function setFilterMapper($filterMapper) {
        $this->filterMapper = $filterMapper;
    }

    private function translateParams($params) {
    	$filter = $this->getFilter($params);
    	$filterTranslator = $this->getFilterTranslator(); /* @var $filterTranslator \Issue\Filter\Translator */
    	$filterTranslator->setFilter($filter);
    	return $filterTranslator->translate();
    }
    
	/**
	 * @return \MonitorUi\Wrapper
	 */
	private function getWrapper() {
		if (is_null($this->wrapper)) {
			$this->wrapper = new Wrapper();
		}
	
		return $this->wrapper;
	}
	
	/**
	 * @param array $params
	 * @return \Issue\Filter\Filter
	 */
	private function getFilter(array $params) {
		if (isset($params['filterId']) && $params['filterId']) {
		    $filterMapper = $this->getFilterMapper();
		    $filter = $filterMapper->getByTypeAndName(\ZendServer\Filter\Filter::ISSUE_FILTER_TYPE, $params['filterId']);
		    
		    if (! count($filter)) {
		        $filter = new \Issue\Filter\Filter(array());
		    } else {
		        $factory = new \ZendServer\Filter\Factory();
		        $filter = $factory->getContainer($filter->current());
		    }
		} else {
		    $filter = new \Issue\Filter\Filter(array());
		}
		
		/* @var $filter \Issue\Filter\Filter */
		
		if (isset($params['applicationIds']) && is_array($params['applicationIds'])
				&& isset($params['applicationIds'][0]) && $params['applicationIds'][0] != 0) {
			$filter->setApplicationIds($params['applicationIds']);
		}
		
		if (isset($params['severities']) && is_array($params['severities'])) {
		    $filter->setSeverities($params['severities']);
		}
		
		if (isset($params['statuses']) && is_array($params['statuses'])) {
		    $filter->setStatuses($params['statuses']);
		}
		
		if (isset($params['eventTypes']) && is_array($params['eventTypes'])) {
		    $filter->setEventTypes($params['eventTypes']);
		}
		
		if (isset($params['issuesIds']) && is_array($params['issuesIds'])) {
			$filter->setIssuesIds($params['issuesIds']);
		}
		
		if (isset($params['freeText']) && $params['freeText']) {
			$filter->setFreeText($params['freeText']);
		}
		
		if (isset($params['from'])) {
		    $filter->setFrom($params['from']);
		}
		
		if (isset($params['to'])) {
		    $filter->setTo($params['to']);
		}
		
		if (isset($params['aggKeys'])) {
			$filter->setAggKeys($params['aggKeys']);
		}

		if (isset($params['ruleNames']) && !empty($params['ruleNames'])) {
			$filter->setRuleNames($params['ruleNames']);
		}
		
		if (isset($params['fullUrl'])) {
			$filter->setFullUrl($params['fullUrl']);
		}
				
		return $filter;
	}
	
	/**
	 * @param string $column
	 * @return array
	 */
	private function getSortColumn($column) {
		$columns = $this->getSortColumnsDictionary();
		return $columns[strtolower($column)];
	}
	
	/**
	 * @return \Issue\Db\Mapper $issueMapper
	 */
	public function getIssueMapper() {
		return $this->issueMapper;
	}

	/**
	 * @return \EventsGroup\Db\Mapper $eventsGroupMapper
	 */
	public function getEventsGroupMapper() {
		return $this->eventsGroupMapper;
	}

	/**
	 * @param \Issue\Db\Mapper $issueMapper
	 * @return Model
	 */
	public function setIssueMapper($issueMapper) {
		$this->issueMapper = $issueMapper;
		return $this;
	}

	/**
	 * @param \EventsGroup\Db\Mapper $eventsGroupMapper
	 * @return Model
	 */
	public function setEventsGroupMapper($eventsGroupMapper) {
		$this->eventsGroupMapper = $eventsGroupMapper;
		return $this;
	}

}

