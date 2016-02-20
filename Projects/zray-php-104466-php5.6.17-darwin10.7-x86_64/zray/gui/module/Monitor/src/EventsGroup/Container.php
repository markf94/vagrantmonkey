<?php
namespace EventsGroup;

class Container {
	/**
	 * @var array
	 */
	protected $eventsGroup;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $eventsGroup, $key=null) {
		$this->eventsGroup = $eventsGroup;
	}
	
	public function toArray() {
		return $this->eventsGroup;
	}
	
	/**
	 * @return boolean
	 */
	public function hasCodetracing() {
		if (!isset($this->eventsGroup['tracer_dump_file'])) {
			return false;
		}
		return $this->eventsGroup['tracer_dump_file']?true:false;
	}
	
	public function getIssueId() {
		return $this->eventsGroup['cluster_issue_id'];
	}
	
	/**
	 * @return integer
	 */
	public function getLoad() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_LOAD]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_LOAD] : 0;
	}

	/**
	 * @return integer
	 */
	public function getAvgOutputSize() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_OUT_SIZE]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_OUT_SIZE] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getOutputSize() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_OUT_SIZE]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_OUT_SIZE] : 0;
	}

	/**
	 * @return integer
	 */
	public function getRelOutputSize() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_OUT_SIZE_CHANGE_PERCENT]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_OUT_SIZE_CHANGE_PERCENT] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getAvgMemUsage() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_MEM_USAGE]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_MEM_USAGE] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getMemUsage() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE] : 0;
	}	

	/**
	 * @return integer
	 */
	public function getRelMemUsage() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE_CHANGE_PERCENT])
			? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_MEM_USAGE_CHANGE_PERCENT] : 0;
	}
		
	/**
	 * @return integer
	 */
	public function getAvgExecTime() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_EXEC_TIME]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_AVG_EXEC_TIME] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getExecTime() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME] : 0;
	}

	/**
	 * @return integer
	 */
	public function getRelExecTime() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME_CHANGE_PERCENT])
			? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_EXEC_TIME_CHANGE_PERCENT] : 0;
	}
		
	/**
	 * @return string
	 */
	public function getJavaBacktrace() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA]) && $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA]
			? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA] : '';
	}
	
	/**
	 * @return string
	 */
	public function getUserData() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA]) && $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA]
			? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_USER_DATA] : '';
	}
	
	/**
	 * @return string
	 */
	public function getClass() {
		return isset($this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_CLASS]) ? $this->eventsGroup[ZM_DATA_ATTR][ZM_DATA_ATTR_CLASS] : '';
	}
	
	/**
	 * @return integer
	 */
	public function getServerId() {
		return isset($this->eventsGroup['node_id']) ? $this->eventsGroup['node_id'] : '';
	}
	
	/**
	 * @return integer
	 */
	public function getstartTime() {
		return isset($this->eventsGroup['first_timestamp']) ? $this->eventsGroup['first_timestamp'] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getEventsCount() {
		return isset($this->eventsGroup['repeats']) ? $this->eventsGroup['repeats'] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getEventsGroupId() {
		return isset($this->eventsGroup['event_id']) ? $this->eventsGroup['event_id'] : '';
	}	
	
	/**
	 * @return integer
	 */
	public function getEmailAction() {
		return isset($this->eventsGroup['email_action']) ? $this->eventsGroup['email_action'] : '';
	}
	
	/**
	 * @return integer
	 */
	public function getUrlAction() {
		return isset($this->eventsGroup['url_action']) ? $this->eventsGroup['url_action'] : '';
	}
	
	public function getEventType() {
		return (int) $this->eventsGroup['event_type'];
	}
	

}