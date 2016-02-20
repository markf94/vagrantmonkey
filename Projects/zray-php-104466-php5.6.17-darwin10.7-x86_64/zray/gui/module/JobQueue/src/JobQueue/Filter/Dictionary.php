<?php

namespace JobQueue\Filter;

use ZendJobQueue;
use JobQueue\JobQueueInterface;

use \ZendServer\Exception;

class Dictionary {	

	// Sort
	const COLUMN_ID = 'id';
	const COLUMN_TYPE = 'type';
	const COLUMN_SCRIPT = 'script';
	const COLUMN_APP = 'application';
	const COLUMN_NAME = 'name';
	const COLUMN_PRIORITY = 'priority';
	const COLUMN_STATUS = 'status';
	const COLUMN_MAX_HTTP_JOBS = 'max_http_jobs';
	const COLUMN_MAX_WAIT_TIME = 'max_wait_time';
	const COLUMN_PREDECESSOR = 'predecessor';
	const COLUMN_PERSISTENCE = 'persistence';
	const COLUMN_CREATIONTIME = 'creationTime';
	const COLUMN_SCHEDULETIME = 'scheduleTime';
	const COLUMN_STARTTIME = 'startTime';
	const COLUMN_ENDTIME = 'endTime';
	const COLUMN_NEXT_RUN = 'next_run';
	const COLUMN_LAST_RUN = 'last_run';
	const COLUMN_APP_ID	  = 'app_id';
	const COLUMN_RUNNING_JOBS_COUNT	  = 'running_jobs_count';
	const COLUMN_PENDING_JOBS_COUNT	  = 'pending_jobs_count';
	
	//Filter only
	const FILTER_COLUMN_APP_ID = 'app_id';
	const FILTER_COLUMN_APP_IDS = 'app_ids';
	const FILTER_COLUMN_RULE_ID = 'rule_id';
	const FILTER_COLUMN_RULE_IDS = 'rule_ids';
	const FILTER_COLUMN_QUEUE_IDS = 'queue_ids';
	const FILTER_COLUMN_SCHEDULED_BEFORE = 'scheduled_before';
	const FILTER_COLUMN_SCHEDULED_AFTER = 'scheduled_after';
	const FILTER_COLUMN_EXECUTED_BEFORE = 'executed_before';
	const FILTER_COLUMN_EXECUTED_AFTER = 'executed_after';
	const FILTER_COLUMN_FREE_TEXT = 'freeText';
	
	
	const STATUS_PENDING 				= 'pending';
	const STATUS_WAITING_PREDECESSOR	= 'waiting_predecessor';	
	const STATUS_RUNNING 				= 'running';
	const STATUS_COMPLETED 				= 'completed';
	const STATUS_OK 					= 'ok';
	const STATUS_FAILED 				= 'failed';
	const STATUS_LOGICALLY_FAILED		= 'logically_failed';
	const STATUS_TIMEOUT 				= 'timeout';
	const STATUS_REMOVED 				= 'removed';
	const STATUS_SCHEDULED 				= 'scheduled';
	const STATUS_SUSPENDED 				= 'suspended';
		
	const PRIORITY_LOW		= 'low';
	const PRIORITY_NORMAL	= 'normal';
	const PRIORITY_HIGH		= 'high';
	const PRIORITY_URGENT	= 'urgent';
	
	const SORT_DESC	= 'DESC';
	const SORT_ASC	= 'ASC';	
	
	const OPTION_VALIDATE_SSL_INHERIT = 'VALIDATE_SSL_INHERIT';
	const OPTION_VALIDATE_SSL_STRICT = 'VALIDATE_SSL_STRICT';
	const OPTION_VALIDATE_SSL_ACCEPT = 'VALIDATE_SSL_ACCEPT';
	
	/**
	 * @return array
	 */
	public function getValidateSslValues() {
		return array(
			self::OPTION_VALIDATE_SSL_INHERIT,
			self::OPTION_VALIDATE_SSL_STRICT,
			self::OPTION_VALIDATE_SSL_ACCEPT,
		);
	}
	
	/**
	 * @return array
	 */
	public function getSortColumnsDictionary() {
		return array(
				self::COLUMN_ID				=> JobQueueInterface::SORT_BY_ID,
				self::COLUMN_TYPE			=> JobQueueInterface::SORT_BY_TYPE,
				self::COLUMN_SCRIPT			=> JobQueueInterface::SORT_BY_SCRIPT,
				self::COLUMN_APP			=> JobQueueInterface::SORT_BY_APPLICATION,
				self::COLUMN_NAME			=> JobQueueInterface::SORT_BY_NAME,
				self::COLUMN_PRIORITY		=> JobQueueInterface::SORT_BY_PRIORITY,
				self::COLUMN_STATUS			=> JobQueueInterface::SORT_BY_STATUS,
				self::COLUMN_PREDECESSOR	=> JobQueueInterface::SORT_BY_PREDECESSOR,
				self::COLUMN_PERSISTENCE	=> JobQueueInterface::SORT_BY_PERSISTENCE,
				self::COLUMN_SCHEDULETIME	=> JobQueueInterface::SORT_BY_SCHEDULE_TIME,
				self::COLUMN_STARTTIME		=> JobQueueInterface::SORT_BY_START_TIME,
				self::COLUMN_ENDTIME		=> JobQueueInterface::SORT_BY_END_TIME,
				self::COLUMN_CREATIONTIME	=> JobQueueInterface::SORT_BY_CREATION_TIME,
				self::COLUMN_RUNNING_JOBS_COUNT	=> JobQueueInterface::SORT_BY_RUNNING_JOBS_COUNT,
				self::COLUMN_PENDING_JOBS_COUNT	=> JobQueueInterface::SORT_BY_PENDING_JOBS_COUNT,
		    
				self::COLUMN_LAST_RUN		=> '',
				self::COLUMN_NEXT_RUN		=> '',
				self::COLUMN_APP_ID			=> '',
		);
	}


	/**
	 * @return array
	 */
	public function getFilterColumns() {
		return array(
				self::COLUMN_TYPE			=> self::COLUMN_TYPE,
				self::COLUMN_SCRIPT			=> self::COLUMN_SCRIPT,
				self::COLUMN_NAME			=> self::COLUMN_NAME,
				self::COLUMN_PRIORITY		=> self::COLUMN_PRIORITY,
				self::COLUMN_STATUS			=> self::COLUMN_STATUS,
				
				self::FILTER_COLUMN_APP_IDS				=> self::FILTER_COLUMN_APP_IDS,				
				self::FILTER_COLUMN_RULE_IDS			=> self::FILTER_COLUMN_RULE_IDS,				
				self::FILTER_COLUMN_QUEUE_IDS			=> self::FILTER_COLUMN_QUEUE_IDS,				
				self::FILTER_COLUMN_SCHEDULED_BEFORE	=> self::FILTER_COLUMN_SCHEDULED_BEFORE,				
				self::FILTER_COLUMN_SCHEDULED_AFTER		=> self::FILTER_COLUMN_SCHEDULED_AFTER,				
				self::FILTER_COLUMN_EXECUTED_BEFORE		=> self::FILTER_COLUMN_EXECUTED_BEFORE,				
				self::FILTER_COLUMN_EXECUTED_AFTER		=> self::FILTER_COLUMN_EXECUTED_AFTER,
				self::FILTER_COLUMN_FREE_TEXT			=> self::FILTER_COLUMN_FREE_TEXT,
		);
	}
	


	/**
	 * @return array
	 */
	public function getStatusColumnsDictionary() {
		return array(
				self::STATUS_PENDING				=> JobQueueInterface::STATUS_PENDING,
				self::STATUS_WAITING_PREDECESSOR	=> JobQueueInterface::STATUS_WAITING_PREDECESSOR,
				self::STATUS_RUNNING				=> JobQueueInterface::STATUS_RUNNING,
				self::STATUS_COMPLETED				=> JobQueueInterface::STATUS_COMPLETED,
				self::STATUS_OK						=> JobQueueInterface::STATUS_OK,
				self::STATUS_FAILED					=> JobQueueInterface::STATUS_FAILED,
				self::STATUS_LOGICALLY_FAILED		=> JobQueueInterface::STATUS_LOGICALLY_FAILED,
				self::STATUS_TIMEOUT				=> JobQueueInterface::STATUS_TIMEOUT,
				self::STATUS_REMOVED				=> JobQueueInterface::STATUS_REMOVED,
				self::STATUS_SCHEDULED				=> JobQueueInterface::STATUS_SCHEDULED,
				self::STATUS_SUSPENDED				=> JobQueueInterface::STATUS_SUSPENDED,
		);
	}		

	private $priorityToDbPriority = array ( // sadly, the priorities kept in the DB are reversed that actual values!
			JobQueueInterface::PRIORITY_LOW => JobQueueInterface::PRIORITY_URGENT,
			JobQueueInterface::PRIORITY_NORMAL => JobQueueInterface::PRIORITY_HIGH,
			JobQueueInterface::PRIORITY_HIGH => JobQueueInterface::PRIORITY_NORMAL,
			JobQueueInterface::PRIORITY_URGENT => JobQueueInterface::PRIORITY_LOW,
	);

	public function getStatuses() {
		$arrayStatus = array();
		foreach (array_keys($this->getStatusColumnsDictionary()) as $key) {
			$arrayStatus[$key] = $key;
		}
		
		return $arrayStatus;	
	}
	
	public function getStatusConstant($status) {
		if (!$this->isKnownStatus($status)) {
			throw new Exception(_t("status '%s' is not a known status: '%s'", array($status, implode(',', array_keys($this->getStatusDictionaryReversed())))));
		}
	
		$statuses = $this->getStatusDictionaryReversed();
		return $statuses[$status];
	}
	
	public function isKnownStatus($status) {
		return array_key_exists($status, $this->getStatusDictionaryReversed());
	}
	
	public function statusesToBit(array $statuses) {
		$statusBit = 0;
		foreach ($statuses as $status) {
			$statusBit |= $this->getStatusConstant($status);
		}
	
		return $statusBit;
	}
	
	public function statusesToDbValues(array $statuses) {
		$res = array();
		foreach ($statuses as $status) {
			$constant = $this->getStatusConstant($status);
			if (!is_array($constant)) {
				$constant = array($constant);
			}
			
			foreach ($constant as $singleConstant) {
				$res[] = $singleConstant;
			}			
		}
	
		return $res;
	}

		
	/**
	 * @return array
	 */
	public function getSortDirectionColumnsDictionary() {
		return array(
			self::SORT_DESC => JobQueueInterface::SORT_DESC,
			self::SORT_ASC  => JobQueueInterface::SORT_ASC,
		);
	}
	
	/**
	 * @return array
	 */
	public function getStatusDictionary() {
		return array(
			0 => _t('Waiting'), // Freezed: 'Waiting'(JQ_STATUS_PENDING) becasue of bug #ZSRV-8801, temprorally to show Active in case of Waiting
			1 => _t('Waiting'), // JQ_STATUS_WAITING_PREDECESSOR
			2 => _t('Running'),
			3 => _t('Completed'),
			4 => _t('OK'),
			5 => _t('Failed'),
			6 => _t('Failed'),
			7 => _t('Timeout'),
			8 => _t('Removed'),
			9 => _t('Scheduled'),
			10 => _t('Suspended'),
		);
	}

	/**
	 * @return array
	 */
	public function getRuleStatusDictionary() {
		return array(
				0 => _t('Active'),
				1 => _t('Waiting'),
				2 => _t('Running'),
				3 => _t('Completed'),
				4 => _t('OK'),
				5 => _t('Failed'),
				6 => _t('Failed'),
				7 => _t('Timeout'),
				8 => _t('Removed'),
				9 => _t('Scheduled'),
				10 => _t('Suspended'),
		);
	}
	
	
	/**
	 * @return array
	 */
	public function getStatusDictionaryReversed() {
		return array(
				'Waiting' => array(JobQueueInterface::STATUS_PENDING, JobQueueInterface::STATUS_WAITING_PREDECESSOR),
				'Running' => JobQueueInterface::STATUS_RUNNING,
				'Completed' => array(JobQueueInterface::STATUS_COMPLETED),
		        'OK'      => JobQueueInterface::STATUS_OK,
				'Failed' => array(JobQueueInterface::STATUS_FAILED, JobQueueInterface::STATUS_LOGICALLY_FAILED),
				'Timeout' => JobQueueInterface::STATUS_TIMEOUT,
				'Removed' => JobQueueInterface::STATUS_REMOVED,
				'Scheduled' => JobQueueInterface::STATUS_SCHEDULED,
				'Suspended' => JobQueueInterface::STATUS_SUSPENDED, /// kept for backwards compatibility in webapi usage
				'Retrying' => JobQueueInterface::STATUS_SUSPENDED, /// added for different name of suspended state
		);
	}
	
	/**
	 * @return array
	 */
	public function getRuleDictionaryReversed() {
		return array(
			'Active' 	=> 0,
			'Suspended' => 10,
		);
	}
	
	/**
	 * @return array
	 */
	public function getStatusDictionaryForFiltering() {
		$arrayForFiltering = array();
		foreach (array_keys($this->getStatusDictionaryReversed()) as $key) {
			$arrayForFiltering[$key] = $key; // identical key,value
		}

		return $arrayForFiltering;
	}
	
	/**
	 * @return array
	 */
	public function getRuleDictionaryForFiltering() {
		$arrayForFiltering = array();
		foreach (array_keys($this->getRuleDictionaryReversed()) as $key) {
			$arrayForFiltering[$key] = $key; // identical key,value
		}
	
		return $arrayForFiltering;
	}
			
	/**
	 * @return array
	 */
	public function getPriorityColumnsDictionary() { // filtering uses 'JobQueueInterface::JOB_PRIORITY_LOW' and friends
		return array(
				self::PRIORITY_LOW		=> JobQueueInterface::PRIORITY_LOW,
				self::PRIORITY_NORMAL	=> JobQueueInterface::PRIORITY_NORMAL,
				self::PRIORITY_HIGH		=> JobQueueInterface::PRIORITY_HIGH,
				self::PRIORITY_URGENT	=> JobQueueInterface::PRIORITY_URGENT,
		);
	}
	
	/**
	 * @return array
	 */
	public function getPriorities() {
		$arrayPriorities = array();
		foreach (array_keys($this->getPriorityColumnsDictionary()) as $key) {
			$arrayPriorities[$key] = $key; // identical key,value
		}
		
		return $arrayPriorities;
	}

	public function getPriorityConstant($priority) {
		if (!$this->isKnownPriority($priority)) {
			throw new Exception(_t("priority '%s' is not a known priority: '%s'", array($priority, implode(',', array_keys($this->getPriorityColumnsDictionary())))));
		}
	
		$priorities = $this->getPriorityColumnsDictionary();
		return $priorities[$priority];
	}
	
	public function isKnownPriority($priority) {
		return array_key_exists($priority, $this->getPriorityColumnsDictionary());
	}
	
	public function prioritiesToDbValues(array $priorities) {
		$res = array();
		foreach ($priorities as $priority) {
			$res[] = $this->priorityToDbPriority[$this->getPriorityConstant($priority)];
		}
	
		return $res;
	}
	
	public function dbPriorityToActualPriority($priority) {
		switch ($priority) {
			case JobQueueInterface::PRIORITY_LOW:
				return JobQueueInterface::PRIORITY_URGENT;
			case JobQueueInterface::PRIORITY_NORMAL:
				return JobQueueInterface::PRIORITY_HIGH;
			case JobQueueInterface::PRIORITY_HIGH:
				return JobQueueInterface::PRIORITY_NORMAL;
			case JobQueueInterface::PRIORITY_URGENT:
				return JobQueueInterface::PRIORITY_LOW;
				
		}
	}
	
	/**
	 * @return array
	 */
	public function getPriorityDictionary() { // result returned from the db uses 'JobQueueInterface::PRIORITY_LOW' and friends
		return array(
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_LOW]	=> _t(self::PRIORITY_LOW),
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_NORMAL]	=> _t(self::PRIORITY_NORMAL),
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_HIGH]	=> _t(self::PRIORITY_HIGH),
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_URGENT]	=> _t(self::PRIORITY_URGENT),
		);
	}
	
	/**
	 * @return array
	 */
	public function getDbPriorityDictionary() { // result returned from the db uses 'JobQueueInterface::PRIORITY_LOW' and friends
		// in the database the order is opposite.
		// in the PHP API 3 is urgent, in the DB 0 - is urgent
		return array(
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_URGENT]		=> _t(self::PRIORITY_LOW),
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_HIGH]	=> _t(self::PRIORITY_NORMAL),
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_NORMAL]	=> _t(self::PRIORITY_HIGH),
				$this->priorityToDbPriority[JobQueueInterface::PRIORITY_LOW]	=> _t(self::PRIORITY_URGENT),
		);
	}
	
	public function getSortConstant($sortName) {
		$sortColumns = $this->getSortColumnsDictionary();
		if (!isset($sortColumns[$sortName])) {
			throw new Exception(_t("sort column '%s' not found", array($sortName)));
		}
	
		return $sortColumns[$sortName];
	}
	
	public function getSortDirectionConstant($sortDirection) {
		$sortDirectionColumns = $this->getSortDirectionColumnsDictionary();
		if (!isset($sortDirectionColumns[$sortDirection])) {
			throw new Exception(_t("sort Direction '%s' not found", array($sortDirection)));
		}
	
		return $sortDirectionColumns[$sortDirection];
	}

	public function getJQTimeRange() {
		return array (
				'all' => _t ( 'All' ),
				'day' => _t ( '24 Hours' ),
				'week' => _t ( 'Week' ),
				'month' => _t ( 'Month' ),
		);
	}
	
	public function getTimeRanges() {
		$timeRangesArray = array('all' => array());
		$timeRangesArray['month'] = array(date('m/d/Y H:i', strtotime('-1 month')), date('m/d/Y H:i'), strtotime('-1 month'), time());
		$timeRangesArray['week'] = array(date('m/d/Y H:i', time() - 7*24*60*60) , date('m/d/Y H:i'), time() - 7*24*60*60, time());
		$timeRangesArray['day'] = array(date('m/d/Y H:i', time() - 24*60*60), date('m/d/Y H:i'), time() - 24*60*60, time());
		
		return $timeRangesArray;
	}
	
}