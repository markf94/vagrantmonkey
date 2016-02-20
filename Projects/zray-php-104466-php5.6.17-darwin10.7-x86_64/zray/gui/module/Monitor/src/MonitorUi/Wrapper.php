<?php
namespace MonitorUi;

use ZendServer\Exception,
	ZendServer\Log\Log;

class Wrapper {
	const DEFAULT_LIMIT = -1;
	const DEFAULT_OFFSET = 0;

	/**
	 * @param string $requestUid
	 * @param string $debug
	 * @param string $amf
	 * @return array
	 * @throws ZwasComponents_MonitorUi_Api_Exception
	 */
	public function getRequestSummary($requestUid, $debug = null, $amf = null) {
		try {
			return zend_monitor_ui_get_request_summary_2($requestUid);
		} catch (ZendMonitorUIException $e) {
			switch($e->getCode()) {
				case ZM_EXCEPTION_DB_ERROR:
					$message = _t('The Zend Monitor failed to access the issues database');
					break;
				default:
					$message = _t('The Zend Monitor failed to retrieve issues information');
			}
			throw new Exception($message, 0, $e);
		}
	}
	
	public function getIssueIdsByTraceFiles($traceFiles) {
		try {
			return zend_monitor_get_cluster_issue_ids_by_trace_files($traceFiles);
		} catch (ZendMonitorUIException $e) {
			switch($e->getCode()) {
				case ZM_EXCEPTION_DB_ERROR:
					$message = _t('The Zend Monitor failed to access the issues database');
					break;
				default:
					$message = _t('The Zend Monitor failed to retrieve issues information');
			}
			throw new Exception($message, 0, $e);
		}		
	}
	
	/**
	 * @param string $requestUid
	 * @throws ZendServer\Exception
	 */
	public function prepareRequestTrace($requestUid) {
		try {
			return zend_monitor_ui_prepare_request_trace($requestUid);
		} catch (\ZendMonitorUiException $e) {
			Log::err('Could not prepare request trace', $e);
			switch($e->getCode()) {
				case ZM_EXCEPTION_DB_ERROR:
					$message = _t('The Zend Monitor failed to access the issues database');
					throw new Exception($message, 0, $e);
					break;
				default:
					$message = _t('The Zend Monitor failed to retrieve issues information');
					throw new Exception($message, 0, $e);
			}
		}
	}
	
	/**
	 * @param array $where
	 * @return integer
	 * @throws ZendServer\Exception
	 */
	public function getIssuesCount(array $where) {
		try {
			return zend_monitor_get_issues_count($where);
		} catch (\ZendMonitorUiException $e) {
			Log::err('Failed to retrieve issues');
			Log::debug($e);
			throw new Exception('The \'monitor_ui\' extension failed to count issues', 0, $e);
		}
	}
	
	/**
	 * @param array $issuesIds
	 * @throws Exception
	 */
	public function deleteIssues(array $issuesIds) {
		try {
			$zDeleted=0;// value will be overwritten by zend_monitor_change_issues_status()
			Log::debug('calling zend_monitor_change_issues_status with DELETED, against ' . count($issuesIds) . ' issues');
			if (zend_monitor_change_issues_status($issuesIds, ZM_STATUS_DELETED, $zDeleted) === false) {
				throw new Exception("deleteIssues() has failed to execute");
			}
			
			return $zDeleted;
		} catch (\ZendMonitorUiException $e) {
			$msg = 'monitor_ui extension failed to delete issues using zend_monitor_change_issues_status()';
			Log::err($msg);
			Log::debug($e);
			throw new Exception($msg, 0, $e);
		}		
	}
	
	/**
	 * @param array $where
	 * @throws Exception
	 */
	public function deleteIssuesByFilter(array $where) {
		try {		
			$issuesIds = zend_monitor_get_issues_ids($where);
			return $this->deleteIssues($issuesIds);
		} catch (\ZendMonitorUiException $e) {
			$msg = 'monitor_ui extension failed to delete issues using zend_monitor_change_issues_status()';
			Log::err($msg);
			Log::debug($e);
			throw new Exception($msg, 0, $e);
		}
	}

	/**
	 * @param array $tracePaths
	 * @throws Exception
	 */
	public function deleteTraceData(array $tracePaths) {
		try {
			return zend_monitor_delete_tracing_data($tracePaths);
		} catch (\ZendMonitorUiException $e) {
			$msg = 'monitor_ui extension failed to delete trace data using zend_monitor_delete_tracing_data()';
			Log::err($msg);
			Log::debug($e);
			throw new Exception($msg, 0, $e);
		}
	}
			
	/**
	 * @param array $where
	 * @param array $limit
	 * @param array $sort
	 * @return array
	 * @throws ZendServer\Exception
	 */
	public function getIssues(array $where, array $limit=array(), array $sort=array()) {
		try {
			return zend_monitor_get_issues($where, $limit, $sort);
		} catch (\ZendMonitorUiException $e) {
			Log::err($e);
			throw new Exception('The \'monitor_ui\' extension failed to retrieve issues', 0, $e);
		}
	}
	
	/**
	 * @param array $issueIds
	 * @throws Exception
	 */
	public function getIssuesLastEventGroupData(array $issueIds = array()) {
		try {
			return zend_monitor_get_issues_last_event_group_data($issueIds);
		} catch (\ZendMonitorUiException $e) {
			Log::err($e);
			throw new Exception('The \'monitor_ui\' extension failed to retrieve issues last group data', 0, $e);
		}
	}
	
	/**
	 * @param int $issueId
	 * @return array
	 * @throws ZendServer\Exception
	 */
	public function getIssueData($issueId) {
		try {
			return zend_monitor_get_issue_data($issueId);
		} catch (\ZendMonitorUiException $e) {
			Log::err('Failed to retrieve issue data');
			Log::debug($e);
			throw new Exception('The \'monitor_ui\' extension failed to retrieve issue data', 0, $e);
		}
	}
	
	/**
	 * @param int $issueId
	 * @param int $limit
	 * @return array
	 * @throws ZendServer\Exception
	 */
	public function getIssueEventGroups($issueId, $limit = self::DEFAULT_LIMIT, $offset = self::DEFAULT_OFFSET) {
		try {
			return zend_monitor_get_issue_event_groups($issueId, $limit);
		} catch (\ZendMonitorUiException $e) {
			Log::err('Failed to retrieve event groups');
			Log::debug($e);
			throw new Exception('The \'monitor_ui\' extension failed to retrieve event groups', 0, $e);
		}
	}
	
	/**
	 * @param int $groupId
	 * @return array
	 * @throws ZendServer\Exception
	 */
	public function getEventGroupData($groupId) {
		try {
			return zend_monitor_get_event_group_data($groupId);
		} catch (\ZendMonitorUiException $e) {
			Log::err($e);
			Log::debug('Failed to retrieve event group data');
			throw new Exception('The \'monitor_ui\' extension failed to retrieve event group data', 0, $e);
		}
	}
}

