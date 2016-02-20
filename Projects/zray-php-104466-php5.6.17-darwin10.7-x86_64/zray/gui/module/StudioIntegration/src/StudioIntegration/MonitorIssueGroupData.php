<?php
namespace StudioIntegration;


class MonitorIssueGroupData {

	/**
	 * @var Issue\Container
	 */
	private $issueData;
	
	/**
	 * @var \EventsGroup\DataContainer
	 */
	private $eventGroupData;
	
	/**
	 * @var array
	 */
	private $eventGroupSuperGlobals = null;
	
	public function __construct($issueId, $eventGroupId, $monitorUiModel) {
		$this->eventGroupData = $monitorUiModel->getEventGroupData($eventGroupId);		
		$this->issueData = $monitorUiModel->getIssue($issueId);
		$this->eventGroupSuperGlobals = $this->eventGroupData->getSuperGlobalsData();// as the super globals data is supposed to be accessed many times, save future function calls	
		
	}
	
	/**
	 * @return string
	 */
	public function getFullUrl() {
		return $this->issueData->getUrl(); 
	}
	
	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->issueData->getFileName();
	}
	
	/**
	 * @return integer
	 */
	public function getLine() {
		return $this->issueData->getLine();
	}
	
	/**
	 * @return array
	 */
	public function getGet() {
		$result = $this->eventGroupSuperGlobals[ZM_DATA_VAR_GET];
		if (is_null($result)) {
			$result = array();
		}
		return $result;
	}
	
	/**
	 * @return array
	 */
	public function getPost() {
		$result = $this->eventGroupSuperGlobals[ZM_DATA_VAR_POST];
		if (is_null($result)) {
			$result = array();
		}
		return $result;
	}
	
	/**
	 * @return array
	 */
	public function getCookies() {
		$result = $this->eventGroupSuperGlobals[ZM_DATA_VAR_COOCKIE];
		if (is_null($result)) {
			$result = array();
		}
		return $result;
	}
	
	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->eventGroupSuperGlobals[ZM_DATA_VAR_SERVER];
	}
	
	/**
	 * @return string
	 */
	public function getRawPostData() {
		return (string)$this->eventGroupSuperGlobals[ZM_DATA_VAR_RAW_POST_DATA];
	}
	
	/**
	 * @return array
	 */
	public function getBacktraceData() {
		$backtrace = $this->eventGroupData->getBacktrace();
		if (! is_array($backtrace)) {
			return array();
		}
		return $backtrace;
	}
	
	/**
	 * @return integer
	 */
	public function getEventGroupNodeId() {
		return $this->eventGroupData->getServerId();
	}	
}