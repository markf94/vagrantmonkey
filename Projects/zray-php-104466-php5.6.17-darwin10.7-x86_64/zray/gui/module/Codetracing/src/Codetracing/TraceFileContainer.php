<?php
namespace Codetracing;
use ZendServer\Exception as ZSException;

class TraceFileContainer {
	
    const ZCT_REASON_CODE_REQUEST = 0;
    const ZCT_REASON_MONITOR_EVENT = 1;
    const ZCT_REASON_MANUAL_REQUEST = 2;
    const ZCT_REASON_SEQFAULT = 3;
    
    
    const DUMP_REASON_CODE_REQUEST = 'CodeRequest';
    const DUMP_REASON_MONITOR_EVENT = 'MonitorEvent';
    const DUMP_REASON_MANUAL_REQUEST = 'ManualRequest';
    const DUMP_REASON_SEQFAULT = 'Segfault';
	/**
	 * @var array
	 */
	protected $trace;
	
	/**
	 * @param array $trace
	 */
	public function __construct(array $trace) {
		$this->trace = $trace;
	}
	
	public function toArray() {
		return $this->trace;
	}
	
	/**
	 * @return string
	 */
	public function getHost() {
		return isset($this->trace['host']) ? $this->trace['host'] : '';
	}
	
	/**
	 * @return int
	 */
	public function getRowId() {
		return isset($this->trace['id']) ? $this->trace['id'] : '';
	}
	
	/**
	 * @return int
	 */
	public function getId() {
		return isset($this->trace['trace_id']) ? $this->trace['trace_id'] : '';
	}

	public function setId($trace_id) {
		$this->trace['trace_id'] = $trace_id;
	}
		
	/**
	 * @return int
	 */
	public function getDate() {
		return isset($this->trace['trace_time']) ? $this->trace['trace_time'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return isset($this->trace['originating_url']) ? $this->trace['originating_url'] : '';
	}
	
	/**
	 * @return int
	 */
	public function getApplicationId() {
		return isset($this->trace['app_id']) ? $this->trace['app_id'] : '';
	}
	
	public function getRouteDetails() {
		return isset($this->trace['routeDetails']) ? $this->trace['routeDetails'] : array();
	}
	
	/**
	 * @return int
	 */
	public function getReason() {
		return isset($this->trace['reason']) ? self::getReasonMap($this->trace['reason']) : '';
	}

	/**
	 * @return Boolean
	 */
	public function isMonitorTrace() {
		return $this->getReason() === self::DUMP_REASON_MONITOR_EVENT;
	}
		
	/**
	 * @return int
	 */
	public function getNodeId() {
		return isset($this->trace['node_id']) ? $this->trace['node_id'] : '';
	}
	
	/**
	 * @rturn int
	 */
	public function getTraceSize() {
		return isset($this->trace['trace_size']) ? $this->trace['trace_size'] : '';
	}
	
	/**
	 * @rturn string
	 */
	public function getFilePath() {
		return isset($this->trace['filepath']) ? $this->trace['filepath'] : '';
	}
	
	protected function getReasonMap($reasonId) {
	    switch ($reasonId) {
	        case self::ZCT_REASON_CODE_REQUEST: 	return self::DUMP_REASON_CODE_REQUEST;
	        case self::ZCT_REASON_MONITOR_EVENT: 	return self::DUMP_REASON_MONITOR_EVENT;
	        case self::ZCT_REASON_MANUAL_REQUEST: 	return self::DUMP_REASON_MANUAL_REQUEST;
	        case self::ZCT_REASON_SEQFAULT: 		return self::DUMP_REASON_SEQFAULT;
	    }
	}
}