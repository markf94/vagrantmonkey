<?php

/**
 * @return string
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_ui_get_gui_host_url() {}
/**
 * @param string $requestUid
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_ui_get_request_summary_2($requestUid) {}
/**
 * @param string $requestUid
 * @param string $debug
 * @param string $amf
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_ui_get_request_summary($requestUid, $debug = null, $amf = null) {}

/**
 * @param string $requestUid
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_ui_prepare_request_trace($requestUid){}
/**
 * @param array $where
 * @param array $limit
 * @param array $sort
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_get_issues($where, $limit, $sort){}
function zend_monitor_get_issues_last_event_group_data($ids) {}
/**
 * @param array $where
 * @return integer
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_get_issues_count($where){}
/**
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_get_rules_names(){}
/**
 * @param array $ids
 * @param int $status
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_change_issues_status($ids, $status){}
/**
 * @param int $issueId
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_get_issue_data($issueId){}
/**
 * @param int $issueId
 * @param int $limit
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_get_issue_event_groups($issueId, $limit){}
/**
 * @param int $groupId
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_get_event_group_data($groupId){}
/**
 * @param integer $globalThreshold
 * @param integer $unopenThreshold
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_delete_old_issues($globalThreshold, $unopenThreshold){}
/**
 * @param integer $eventType
 * @param string $data
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_ui_report_event($eventType, $data){}
/**
 * @param array $nodes
 * @return array
 * @throws ZendMonitorUIException::ZM_EXCEPTION_DB_ERROR for db error
 * @throws ZendMonitorUIException for other errors
 */
function zend_monitor_delete_obsolete_issues($nodes){}

class ZendMonitorUIException extends Exception {
	const ZM_EXCEPTION_DB_ERROR = 1;
}
