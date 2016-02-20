<?php

namespace JobQueue\Db;

use Zend\Db\Sql\Expression;

use Zend\Json\Json;

use Audit\AuditTypeInterface;

use JobQueue\Model\JobsSet,
	JobQueue\Filter\Dictionary;

use Application\Module;

use ZendServer\Log\Log,
ZendServer\Set,
Zend\Db\TableGateway\TableGateway,
\Configuration\MapperAbstract,
Zend\Db\Sql\Select,
Zend\Db\Sql\Predicate\PredicateSet;
use ZendServer\Exception;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\NotIn;
use Deployment\IdentityApplicationsAwareInterface;
use Deployment\IdentityFilterInterface;
use Zend\Db\Adapter\Adapter;
use JobQueue\JobQueueInterface;
class Mapper extends MapperAbstract implements IdentityApplicationsAwareInterface {
	
	const DEFAULT_QUEUE_ID = 1;
	
	/**
	 * @var IdentityFilterInterface
	 */
	private $identityFilter;
	private $_paramFieldToDbField = array( 
			
			"creationTime" => "creation_time",
			"startTime" => "start_time",
			"priority" => "priority",
			"status" => "status",
			"id" => "id",
			"name" => "name",
			"script" => "script",
			"application" => "app_id",
			
			
			);
	
	/**
	 * @var Dictionary
	 */
	protected $dictionary;
	
	/**
	 * @var \Servers\Db\Mapper
	 */
	protected $serversMapper = null;
	
	/**
	 * @brief 
	 * @param \Servers\Db\Mapper $serverMapper 
	 * @return  
	 */
	public function setServersMapper($serverMapper) {
		$this->serversMapper = $serverMapper;
	}
	
	
	/**
	 * @param integer $recurringJobId
	 * @throws Exception
	 * @return Set
	 */
	public function getSchedulingRule($recurringJobId) {
		
		try {
				
			$select = $this->getSchedulingRuleSelect();
			$select->order("jobqueue_job.schedule_time DESC");
			
			$where = new Where();
			$where->equalTo('jobqueue_schedule.id', $recurringJobId);
			$select->where($where);
			$select->limit(1);
			
			$result = $this->selectWith($select);
			$result = $this->translateSchedulingRuleValues($result);
			reset($result);
			Log::debug("JQ rules found: " . var_export($result, true));
			return current($result);
			
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to retrieve rules list: %s', array($e->getMessage())), null, $e);
		}
	}

	/**
	 * @param array $filters
	 * @return Where
	 */
	private function filters(array $filters = array()) {
		$where = new Where();
		
		$where->notEqualTo('schedule_id', '-1');
		$where->notEqualTo('jobqueue_schedule.status', \JobQueue\JobQueueInterface::STATUS_REMOVED);
		
		if (isset($filters['status'])) {
			$where->in('jobqueue_schedule.status', $filters['status']);	
		}
		
		if (isset($filters['applicationIds'])) {
			$where->in('jobqueue_schedule.application_id', $filters['applicationIds']);
		}
		
		if (isset($filters['rules'])) {
			$where->in('jobqueue_schedule.id', $filters['rules']);
		}
		
		if (isset($filters['queue_ids'])) {
			$where->in('jobqueue_schedule.queue_id', $filters['queue_ids']);
		}
		
		if (isset($filters['freeText']) && $filters['freeText']) {
			$freeText = $filters['freeText'];
			$predicate = new Predicate(null, Predicate::OP_OR);
			$predicate->like('jobqueue_schedule.name', "%$freeText%");
			$predicate->like('jobqueue_schedule.script', "%$freeText%");
			$predicate->equalTo('jobqueue_schedule.id', $freeText);

			$where->addPredicate($predicate);
		}
		
		return $where;
	}
	
	/**
	 * @param $limit
	 * @param $offset
	 * @throws Exception
	 * @return Set
	 */
	public function getSchedulingRules($limit = null, $offset = null, $filters = array(), $sortBy = '', $direction = 'DESC') {
		try {
			$select = $this->getSchedulingRuleSelect();
			$where = $this->filters($filters);
			
			$select->where($where);
			
			
			if (! is_null($limit) && $limit > 0) {
				$select->limit(intval($limit));
			}
			if (! is_null($offset) && $offset > 0) {
				$select->offset(intval($offset));
			}
			if (! empty($sortBy) && ! empty($direction)) {
				$select->order(array($sortBy => $direction));
			} else {
				$select->order("jobqueue_job.schedule_time DESC");
			}
			
			$result = $this->selectWith($select);
			
			$result = $this->translateSchedulingRuleValues($result);
			
			Log::debug("JQ rules found: " . var_export($result, true));
			
			return $result;
			
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to retrieve rules list: %s', array($e->getMessage())), null, $e);
		}
	}
	

	public function countSchedulingRules($filters = array()) {
	
		try {
	
			$select = new \Zend\Db\Sql\Select();
			$select->from($this->getTableGateway()->getTable());
			$select->columns(array(new \Zend\Db\Sql\Expression("COUNT(*)")));
			$select->join("jobqueue_schedule", "jobqueue_schedule.id = jobqueue_job.schedule_id", array("id" => "id", "status"));
			$select->join("jobqueue_queue", "jobqueue_queue.id = jobqueue_job.queue_id", array("queue_name" => "name"));
			
			$where = $this->filters($filters);
			$select->where($where);
				
			$select->group("jobqueue_schedule.id");
							
			$result = $this->selectWith($select);
			
			$count = sizeof($result);
			return $count;
				
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to retrieve rules list: %s', array($e->getMessage())), null, $e);
		}
	}
	
	public function deleteRulesByAppId($appId) {
		
		$dbConnection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
		$dbConnection->beginTransaction();
		try {
			$dbConnection->execute("DELETE FROM jobqueue_schedule WHERE application_id = " . (int) $appId);
			$dbConnection->execute("UPDATE jobqueue_job set status = " . \JobQueue\JobQueueInterface::STATUS_REMOVED . " WHERE application_id = " . (int) $appId);
			$dbConnection->commit();
		} catch (\Exception $ex) {
			$dbConnection->rollback();
			Log::err($ex->getMessage());
			return false;
		}
		return true;
		
	}
	
	public function deleteRule($ruleId) {
	
		Log::debug("Deleting JQ rule $ruleId");
		
		$dbConnection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
		$dbConnection->beginTransaction();
		$deleted = 0;
		try {
			
			$delete = new \Zend\Db\Sql\Delete();
			$delete->from('jobqueue_schedule');
			$delete->where(array('id' => (int) $ruleId));
			
			$adapter = $this->getTableGateway()->getAdapter();
			$deleted = $adapter->query($delete->getSqlString($adapter->getPlatform()), Adapter::QUERY_MODE_EXECUTE);
			
			$update = new \Zend\Db\Sql\Update();
			$update->table($this->getTableGateway()->getTable());
			$update->set(array('status' => \JobQueue\JobQueueInterface::STATUS_REMOVED));
			$update->where(array('schedule_id' => (int) $ruleId, 'status' => \JobQueue\JobQueueInterface::STATUS_SCHEDULED));
				
			$this->getTableGateway()->updateWith($update);
			
			$dbConnection->commit();
		} catch (\Exception $ex) {
			$dbConnection->rollback();
			Log::err($ex->getMessage());
		}
		return $deleted;
	
	}
	
	/**
	 * @param integer $jobId
	 * @return array
	 */
	public function getJob($jobId) {
		$result = $this->getJobsList(array(),1,0,'creationTime','DESC',$jobId, $bringAlsoTextFields = true)->current();
		return $result;
	}
	
	/**
	 *
	 * @param array $filter
	 * @param integer $limit
	 * @param integer $offset
	 * @param string $sortBy
	 * @param string $direction
	 * @param integer $singleJob - the ID of a single job
	 * @param bool $allData - get also the text fields: `vars`, `headers`, `errors`, `output`
	 * @return \JobQueue\Model\JobsSet
	 */
	public function getJobsList($filter = array(), $limit = 0, $offset = 0, $sortBy = '', $direction = 'DESC', $singleJob = null, $allData = false) {		
		$total=0;
		
		try {
			// set read_uncommitted mode for sqlite
			$this->setReadUncommittedMode();
			
			$select = $this->createSelectFromParams($filter, $limit, $offset, $sortBy, $direction, $allData);
			
			$totalSelect = new Select();
			$totalSelect->from($this->getTableGateway()->getTable());
			$totalSelect->columns(array("count" => new \Zend\Db\Sql\Expression("COUNT(*)")));
			$totalSelect->where($select->getRawState('where'));
			$totalResult = $this->selectWith($totalSelect);
			$total = $totalResult[0]['count'];
			
			if($singleJob){				
				$singleSelect = clone $select;
				$singleSelect->from($this->getTableGateway()->getTable());
				$where = new Where();
				$where->equalTo('jobqueue_job.id', $singleJob);
				$singleSelect->where($where);
				$result = $this->selectWith($singleSelect);
				$total = 1;
			} else {
				$result = $this->selectWith($select);
			}
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to  retrieve job list: %s', array($e->getMessage())), null, $e);
		}

		$count = count($result);
		
		// add "node_name" field to the result
		/* @var \Servers\Db\Mapper */
		$servers = array();
		if ($this->serversMapper) {
			$serversList = $this->serversMapper->findAllServers();
			
			foreach ($serversList as $serverData) {
				$servers[$serverData->getNodeId()] = $serverData->getNodeName();
			}
		}
		
		foreach ($result as $i => $rec) {
			$result[$i]['node_name'] = $rec['node_id'] && isset($servers[$rec['node_id']]) ? $servers[$rec['node_id']] : $rec['node_id'];
		}
		
		Log::debug("getJobsList() returned total of {$total} jobs");
		$resultSet = new JobsSet($result);
		$resultSet->setTotal($total);
		return $resultSet;
	}
	
	public function deleteJobsByFilter(array $filter) {
		
		Log::debug("deleteJobsByFilter: " . var_export($filter, true));
		
		$where = $this->parseFilters($filter);
		/// do not remove scheduled tasks
		$scheduledStatus = $this->getDictionary()->getStatusConstant('Scheduled');
		
		/// allow deleting a scheduled job that has no rule 
		$scheduledAllow = new Predicate();
		$scheduledAllow->equalTo('jobqueue_job.status', $scheduledStatus);
		$scheduledAllow->equalTo('jobqueue_job.schedule_id', 0);

		/// do not remove a scheduled job if it has a rule (i.e, not schedule_id = 0)
		$scheduledRuleNotAllowed = new Predicate();
		$scheduledRuleNotAllowed->notEqualTo('jobqueue_job.status', $scheduledStatus);
		$scheduledRuleNotAllowed->orPredicate($scheduledAllow);
		
		$where->addPredicate($scheduledRuleNotAllowed);
		
		$deleted = $this->delete($where);
		
		Log::debug("deleteJobsByFilter: $deleted jobs deleted");
		
		return $deleted;
		
	}
	
	/**
	 * @brief 
	 * @param <unknown> $filter 
	 * @param <unknown> $limit 
	 * @param <unknown> $offset 
	 * @param <unknown> $sortBy 
	 * @param <unknown> $direction 
	 * @param bool $allData - get also the text fields: `vars`, `headers`, `errors`, `output`
	 * @return \Zend\Db\Sql\Select
	 */
	private function createSelectFromParams($filter = array(), $limit = 0, $offset = 0, $sortBy = '', $direction = 'DESC', $allData = false) {
		
		// sanitize direction parameter
		if (!in_array(strtolower($direction), array('asc', 'desc'))) {
			$direction = 'ASC';
		}
		
		Log::debug("received the following filter: " . var_export($filter, true));
		
		$select = new Select();
		$select->join("jobqueue_schedule", "jobqueue_schedule.id = jobqueue_job.schedule_id", array ("schedule", 'schedule_rule_id' => 'id'), Select::JOIN_LEFT);
		$select->join("jobqueue_queue", "jobqueue_queue.id = jobqueue_job.queue_id", array ('queue_name' => 'name', 'queue_status' => 'status'), Select::JOIN_LEFT);
		
		$columns = array(
			"id" => "id",
			"app_id" => "application_id",
			"type",
			"name",
			"node_id",
			"status",
			"priority",
			"persistent",
			"script",
			"creation_time",
			"end_time",
			"start_time",
			"predecessor",
			"schedule_id",
			"schedule_time",
			"timeout",
			"queue_id",
			"start_or_schedule_time" => new Expression('ifnull(jobqueue_job.start_time, jobqueue_job.schedule_time)'),
		);
		
		// get also the text fields
		if ($allData) {
			$columns = array_merge($columns, array(
				 "variables" => 'vars', 
				 "http_headers", 
				 "output", 
				 "error", 
			));
		}
		
		$select->columns($columns);
		$select->from($this->getTableGateway()->getTable());
		
		if (!is_null($sortBy) && !empty($sortBy)) {
			$sortBy = $this->_paramFieldToDbField[$sortBy];
		}
		
		if ($sortBy == "priority"){
			if ($direction == "DESC") { // the priorities constants in the db are order in reverse priority
				$direction = "ASC";
			} else {
				$direction = "DESC";
			}
		} else if ($sortBy == 'start_time') {
			$sortBy = "start_or_schedule_time";
		}
		
		if ($sortBy == "creation_time") {
			$sortBy = "id";
		}
		
		$where = $this->parseFilters($filter);
		$select->where($where);
		
		if (!is_null($sortBy) && !empty($sortBy)) {
			$select->order(array ($sortBy => $direction));
		}
		
		$select->limit(intval($limit));
		$select->offset(intval($offset));
		
		return $select;
	}
	
	/**
	 * @brief build SQL from filters. 
	 * note!
	 * 	status and application ID are added to `where` anyway
	 * @param array $filter
	 * @param Where $where
	 * @return Where
	 */
	private function parseFilters(array $filter, Where $where = null) {
		if (is_null($where)) {
			$where = new Where();
		}
		
		if (isset($filter['status']) && $filter['status']) {
			$where->in('jobqueue_job.status', array_values($filter['status']));
		} else {
			$statuses = $this->getDictionary()->getStatusColumnsDictionary();
			$where->notEqualTo('jobqueue_job.status', $statuses[Dictionary::STATUS_REMOVED]);
		}
		
		// @TODO - check for allowed apps
		if (isset($filter['app_ids']) && !empty($filter['app_ids'])) {
			$where->in('jobqueue_job.application_id', $filter['app_ids']);
		}
		
		/*
		if (isset($filter['app_ids']) && $filter['app_ids']) {
			/// if specified application ids, do not add global app entries
			$this->getIdentityFilter()->setAddGlobalAppId(false);
			$applications = $filter['app_ids'];
		} else {
			$this->getIdentityFilter()->setAddGlobalAppId(true);
		}
		$where->in('jobqueue_job.application_id', array_values($this->getIdentityFilter()->filterAppIds($applications, true)));
		 */

		if (isset($filter['priority']) && $filter['priority']) {
			$where->in('jobqueue_job.priority', array_values($filter['priority']));
		}
		
		if (isset($filter['rule_ids']) && $filter['rule_ids']) {
			$where->in('jobqueue_job.schedule_id', array_values($filter['rule_ids']));
		}
		
		if (isset($filter['queue_ids']) && $filter['queue_ids']) {
			$where->in('jobqueue_job.queue_id', array_values($filter['queue_ids']));
		}
		
		if (isset($filter['executed_before']) && $filter['executed_before']) {
			$executeBefore = $filter['executed_before'];
			$where->lessThanOrEqualTo("jobqueue_job.start_time", $executeBefore);
		}
		
		if (isset($filter['executed_after']) && $filter['executed_after']) {
			$executeAfter = $filter['executed_after'];
			$where->greaterThanOrEqualTo("jobqueue_job.start_time", $executeAfter);
		}
		
		if (isset($filter['freeText']) && $filter['freeText']) {
			$freeText = $filter['freeText'];
			$predicate = new Predicate(null, Predicate::OP_OR);
			$predicate->like('jobqueue_job.script', "%$freeText%");
			$predicate->like('jobqueue_job.name', "%$freeText%");
			$where->addPredicate($predicate);
		}
		
		return $where;
	}
	
	protected function getDictionary() {
		if ($this->dictionary) {
			return $this->dictionary;
		}
	
		return $this->dictionary = new Dictionary();
	}
	/**
	 * @return IdentityFilterInterface
	 */
	public function getIdentityFilter() {
		return $this->identityFilter;
	}

	/**
	 * @param \JobQueue\Filter\Dictionary $dictionary
	 */
	public function setDictionary($dictionary) {
		$this->dictionary = $dictionary;
	}
	/* (non-PHPdoc)
	 * @see \Deployment\IdentityApplicationsAwareInterface::setIdentityFilter()
	 */
	public function setIdentityFilter(\Deployment\IdentityFilterInterface $filter) {
		$this->identityFilter = $filter;
	}


	/**
	 * @param array $result
	 * @return array
	 */
	private function translateSchedulingRuleValues(array $result) {
		$validateSsl = $this->getDictionary()->getValidateSslValues();
			
		// now fix the priorities
		foreach ($result as $key => &$val) {
			$prt = $this->getDictionary()->dbPriorityToActualPriority((int) $val['priority']);
			$val['priority'] = $prt;
	
			if (! isset($val['validate_ssl'])) {
				$val['validate_ssl'] = key($validateSsl);
			}
			$val['validate_ssl'] = $validateSsl[$val['validate_ssl']];
		}
		return $result;
	}
	
	/**
	 * @return \Zend\Db\Sql\Select
	 */
	private function getSchedulingRuleSelect() {
		$select = new \Zend\Db\Sql\Select();
		$select->from($this->getTableGateway()->getTable());
		$select->join("jobqueue_schedule", "jobqueue_schedule.id = jobqueue_job.schedule_id", array(
			'id', 'name', 'status', 'schedule', 
			'queue_id', 'priority', 'app_id' => 'application_id',
			'persistent', 'script', 'type', 'vars',
		));
		$select->join("jobqueue_queue", "jobqueue_queue.id = jobqueue_schedule.queue_id", array("queue_name" => "name", 'queue_status' => 'status'));
	
		$select->group("jobqueue_schedule.id");
		$select->columns(array(
				'last_run' => new \Zend\Db\Sql\Expression('MAX(start_time)'),
				"next_run" => new \Zend\Db\Sql\Expression('MAX(schedule_time)'),
		));
		
		return $select;
	}
	
	/**
	 * Get list of fields in queues table
	 */
	public function getQueueFields() {
		return array('id', 'name', 'priority', 'max_http_jobs', 'max_wait_time', 
			'status', 'http_connection_timeout', 'http_job_timeout', 'http_job_retry_count', 'http_job_retry_timeout',
			'running_jobs_count', 'pending_jobs_count'
		);
	}

	/**
	 * return queue data
	 * @param integer $queueId
	 * @return Ambigous <\ZendServer\Set, multitype:>|boolean
	 */
	public function getQueue($queueId) {
		foreach ($this->getQueues(array('show_deleted' => true)) as $queue) {
			if ($queue['id'] == $queueId) return $queue;
		}
		return false;
	}
	
	/**
	 * Remap result set
	 * when one field should be received under other name. 
	 * e.g. when renaming field name
	 */
	protected $resultSetMapping = array(
		'jobqueue_queue' => array(
			'queue_priority' => 'priority',
		),
	);
	
	/**
	 * @brief Remap result set, when one field should be received under other name. e.g. when renaming field name
	 * @param \ZendServer\Set|array $resultSet 
	 * @param bool $getAsArray 
	 * @return \ZendServer\Set|array
	 */
	protected function getRemappedResultSet($resultSet, $reverseMapping = false) {
		foreach ($this->resultSetMapping as $tableName => $tableMap) {
			if (strcasecmp($this->getTableGateway()->getTable(), $tableName) == 0) {
				
				$newResultSet = array();
				foreach ($resultSet as $rec) {
					// duplicate the record
					$theRec = $rec;
					
					// remap its fields
					foreach ($tableMap as $originalFieldName => $newFieldName) {
						if ($reverseMapping) {
							if (isset($theRec[$newFieldName])) {
								$theRec[$originalFieldName] = $theRec[$newFieldName];
								unset($theRec[$newFieldName]);
							}
						} else {
							if (isset($theRec[$originalFieldName])) {
								$theRec[$newFieldName] = $theRec[$originalFieldName];
								unset($theRec[$originalFieldName]);
							}
						}
					}
					
					// add record to new list
					$newResultSet[] = $theRec;
				}
				
				return is_array($resultSet) ? $newResultSet : (new \ZendServer\Set($newResultSet));
			}
		}
	}
	
	/**
	 * 
	 * [order, direction (asc/desc), show_deleted bool (default: false)]
	 */
	 
	 /**
	  * @brief Get list of queues
	  * @param array $params 
	  * @param bool $remapResultSet - pass thru fields remapping (field `priority` is defined as `queue_priority` in the DB)
	  * @return array|Set 
	  */
	public function getQueues($params = array(), $remapResultSet = true) {
		// set read_uncommitted mode for sqlite
		try {
			$this->setReadUncommittedMode();
		} catch (\Exception $e) {}
		
		$select = new \Zend\Db\Sql\Select();
		$select->from($this->getTableGateway()->getTable());
	    
		if (!isset($params['show_deleted']) || $params['show_deleted'] === false) {
			$where = new Where();
			$where->notEqualTo('STATUS', JobQueueInterface::QUEUE_DELETED);
			$select->where($where);
		}
		
		if (!empty($params['order']) && !empty($params['direction'])) {
			$select->order(strtoupper($params['order'] . ' ' . $params['direction']));
		}
		
		$result = $this->selectWith($select);
		
		// get queue statistics and add to the result
		$stats = $this->getQueueStats($queueId = null, $getAsObject = true);
		foreach ($result as &$rec) {
			$rec['running_jobs_count'] = isset($stats[$rec['id']]) && isset($stats[$rec['id']]['running_jobs_count']) ? $stats[$rec['id']]['running_jobs_count'] : 0;
			$rec['pending_jobs_count'] = isset($stats[$rec['id']]) && isset($stats[$rec['id']]['pending_jobs_count']) ? $stats[$rec['id']]['pending_jobs_count'] : 0;
		}
		
		if ($remapResultSet) {
			$result = $this->getRemappedResultSet($result);
		}
		return $result;
	}
		
	/**
	 * @param array $params - must contain `name`, `priority`, `status`
	 */
	public function addQueue($params) {
		$params = $this->getRemappedResultSet(array($params), 'reverse mapping = true')[0];
		
		$result = $this->getTableGateway()->insert($this->getArrayWithUppercasedKeys($params));
		if ($result) {
			// return the ID on success
			return $this->getTableGateway()->getLastInsertValue();
		}
		
		return false;
	}
	
	/**
	 * @param array $params - must contain `name`, `priority`, `status`
	 */
	public function updateQueue($id, $params) {
		$params = $this->getRemappedResultSet(array($params), 'reverse mapping = true')[0];
		
		// prepare condition
		$where = new Where();
		$where->equalTo('ID', $id);
		
		// update the db
		$this->getTableGateway()->update($this->getArrayWithUppercasedKeys($params), $where);
		
		// returning `true` - in case of an error, an exception will be thrown
		return true;
	}
	
	/**
	 * @param array $params - must contain `name`, `priority`, `status`
	 */
	public function suspendQueue($id) {
		// prepare condition
		$where = new Where();
		$where->equalTo('ID', $id);
		
		// suspend queue that is only in status active
		$where->equalTo('STATUS', JobQueueInterface::QUEUE_ACTIVE);
		
		// update the db
		return $this->getTableGateway()->update(array('STATUS' => JobQueueInterface::QUEUE_SUSPENDED), $where);
	}
	
	/**
	 * @param array $params - must contain `name`, `priority`, `status`
	 */
	public function activateQueue($id) {
		// prepare condition
		$where = new \Zend\Db\Sql\Where();
		$where->equalTo('ID', $id);
		
		// activate queue that is only in status suspended
		$where->equalTo('STATUS', JobQueueInterface::QUEUE_SUSPENDED);
		
		// update the db
		return $this->getTableGateway()->update(array('STATUS' => JobQueueInterface::QUEUE_ACTIVE), $where);
	}
	
	/**
	 * @param integer $id
	 * @param bool $forceDelete - delete the queue from the DB without checking
	 * @return boolean
	 */
	public function deleteQueue($id, $forceDelete = false) {
		/* @var \Zend\Db\Sql\Predicate\Predicate */
		$where = new Where();
		$where->equalTo('ID', $id);
		
		// delete directly from the database without checking
		if ($forceDelete) {
			return $this->getTableGateway()->delete($where);
		}
		 
		// get the name of the queue
		$queueRec = $this->getTableGateway()->select($where);
		$queueRec = $queueRec->count() > 0 ? $queueRec->current() : null;
		if (is_null($queueRec)) {
			return false;
		}
		$currentName = null;
		foreach ($queueRec as $key => $val) {
			if (strcasecmp($key, 'name') == 0) {
				$currentName = $val;
				break;
			}
		}
		if (is_null($currentName)) {
			return false;
		}
		
		$newName = $currentName.' (deleted)';
		
		// check if queue with same name was deleted before
		while (is_numeric($this->getQueueIdByName($newName, 'search in deleted also!'))) {
			if (!isset($nameCounter)) {
				$nameCounter = 2;
			} else {
				$nameCounter++;
			}
			
			$newName = $currentName.' (deleted #'.$nameCounter.')';
		}
		
		// update the status to deleted
		return !!$this->getTableGateway()->update(array(
			'NAME' => $newName,
			'STATUS' => JobQueueInterface::QUEUE_DELETED,
		), array(
			'ID' => $id,
		));
	}

	/**
	 * @return boolean
	 */
	public function deleteAllQueues() {
		$where = new Where();
		$where->notEqualTo('id', self::DEFAULT_QUEUE_ID);
		
		return $this->getTableGateway()->delete($where);
	}


	/**
	 * Get queue id by name
	 * @param string $queueName
	 * @return null|integer
	 */
	public function getQueueIdByName($queueName, $searchInDeletedAlso = false) {
		$queueId = null;
		$params = array();
		if ($searchInDeletedAlso) {
			$params['show_deleted'] = true;
		}
		
		foreach ($this->getQueues($params) as $queue) {
			if (strcasecmp($queue['name'], $queueName) == 0) {
				$queueId = $queue['id'];
			}
		}
		
		return $queueId;
	}
	
	/**
	 * @brief Get list of columns of the queues table
	 * @return  
	 */
	protected function getQueuesTableColumns() {
		$gateway = $this->getTableGateway();
		$metadata = new \Zend\Db\Metadata\Metadata($gateway->getAdapter());
		return $metadata->getColumnNames($gateway->getTable());
	}
	
	/**
	 * @brief generate export SQL query (string)
	 * @return string
	 */
	public function getQueuesExportJson() {
		
		// using reverse mapping, to 
		$allQueues = $this->getQueues(array(), $applyFieldsRemapping = false);
		
		foreach ($allQueues as $i => $queue) {
			if (array_key_exists('running_jobs_count', $allQueues[$i])) {
				unset($allQueues[$i]['running_jobs_count']);
			}
			if (array_key_exists('pending_jobs_count', $allQueues[$i])) {
				unset($allQueues[$i]['pending_jobs_count']);
			}
		}
		
		return json_encode($allQueues);
	}	
	
	
	protected function getReplaceIntoStatementSql($data, $useInsert = false) {
		
		$replinsert = $useInsert ? 'INSERT' : 'REPLACE';
		
		return $replinsert.' INTO `'.$this->getTableGateway()->getTable().'` '.
			'(`'.implode('`,`', array_keys($data)).'`)'.
			' VALUES '.
			"('".implode("', '", array_values($data))."')";
	}
	/**
	 * @brief generate export SQL query (string)
	 * @return string
	 */
	public function getQueueImportSql($queueData) {
		
		
		// for default queue use `replace into` with ID
		if ($queueData['id'] == self::DEFAULT_QUEUE_ID) {
			return $this->getReplaceIntoStatementSql($queueData);
		} 
		
		// for existing queues use `repalce into` with the existing `id`
		/* @var \ZendServer\Set */
		$currentQueue = $this->getTableGateway()->select(array('name' => $queueData['name']));
		if ($currentQueue->count() > 0) {
			$rec = $currentQueue->current();
			$queueData['id'] = $rec['id'];
		} else {
			unset($queueData['id']);
		}
		
		return $this->getReplaceIntoStatementSql($queueData, 'use insert instead of replace');
	}
	
	public function importQueues($queuesToImport) {
		// get current queues
		$currentQueues = array();
		foreach ($this->getQueues() as $queue) {
			$currentQueues[$queue['id']] = $queue['name'];
		}
		
		// gather queues for "Replace" and for "Insert"
		$overrideSqls = array();
		$insertSqls = array();
		foreach ($queuesToImport as $queue) {
			if (in_array($queue['name'], array_values($currentQueues))) {
				
				// update the ID for the 'replace into' statement
				$existingQueueId = array_search($queue['name'], $currentQueues);
				$queue['id'] = $existingQueueId;
				
				$overrideSqls[] = $this->getReplaceIntoStatementSql($queue);
			} else {
				
				// remove the ID for the `insert into` statement
				unset($queue['id']);
				$insertSqls[] = $this->getReplaceIntoStatementSql($queue, 'use `insert into`');
			}
		}
		
		$sqlErrors = true;
		
		// execute `replace` sqls first
		foreach ($overrideSqls as $sql) {
			try {
				$this->getTableGateway()->getAdapter()->query($sql)->execute();
			} catch (\Exception $e) {
				Log::alert($e->getMessage());
				$sqlErrors = false;
			}
		}
		
		// execute `insert` sqls afterwards
		foreach ($insertSqls as $sql) {
			try {
				$this->getTableGateway()->getAdapter()->query($sql)->execute();
			} catch (\Exception $e) {
				Log::alert($e->getMessage());
				$sqlErrors = false;
			}
		}
		
		return $sqlErrors;
	}
	
	/**
	 * Change the keys to uppercase
	 * @param \Traversable $arr
	 * @return array
	 */
	protected function getArrayWithUppercasedKeys(\Traversable $arr) {
		$newArr = array();
		if (!empty($arr)) {
			foreach ($arr as $k => $v) {
				$newArr[strtoupper($k)] = $v;
			}
		}
		
		return $newArr;
	}
	
	/**
	 * @brief get number of runnning and pending jobs per each queue
	 * @param integer $queueId 
	 * @param bool $asObject - if true, return array with queue_id as a key, otherwise as regular array
	 * @return array
	 */
	public function getQueueStats($queueId = null, $asObject = false) {
		
		$tableGateway = new TableGateway('jobqueue_job', $this->getTableGateway()->getAdapter());
		$select = new Select('jobqueue_job');
		$select->columns(array(
			'queue_id',
			'status',
			'total_jobs' => new Expression('count(id)'),
		));
		$select->group(array('queue_id', 'status'));
		$result = $tableGateway->selectWith($select);
		
		$resultArray = array();
		foreach ($result as $rec) {
			
			// if requested specific queue id - skip all the rest
			if (is_numeric($queueId) && $queueId > 0 && $rec['queue_id'] != $queueId) continue;
			
			if (!isset($resultArray[$rec['queue_id']])) {
				$resultArray[$rec['queue_id']] = array(
					'queue_id' => $rec['queue_id'],
					'running_jobs_count' => 0,
					'pending_jobs_count' => 0,
				);
			}
			
			// all the statuses of future jobs
			$futureJobsStatuses = array(
				\JobQueue\JobQueueInterface::STATUS_PENDING,
				\JobQueue\JobQueueInterface::STATUS_SCHEDULED,
				\JobQueue\JobQueueInterface::STATUS_WAITING_PREDECESSOR,
			);
			
			if (in_array($rec['status'], $futureJobsStatuses)) {
				$resultArray[$rec['queue_id']]['pending_jobs_count'] = $rec['total_jobs'];
			}
			
			if ($rec['status'] == \JobQueue\JobQueueInterface::STATUS_RUNNING) {
				$resultArray[$rec['queue_id']]['running_jobs_count'] = $rec['total_jobs'];
			}
		}
		
		if ($asObject) {
			return $resultArray;
		} else {
			return new Set(array_values($resultArray), '\JobQueue\Model\QueueStats');
		}
	}
	
	/**
	 * @param array $params - must contain `name`, `priority`, `status`
	 */
	public function insertNewQueueStats($queueId) {
		$result = $this->getTableGateway()->insert(array('queue_id' => $queueId));
		if ($result) {
			// return the ID on success
			return $this->getTableGateway()->getLastInsertValue();
		}
		 
		return false;
	}

	/**
	 * @brief set read_uncommitted mode for sqlite
	 * @return 
	 */
	protected function setReadUncommittedMode() {
		if ($this->isSqlite()) {
			$dbConnection = $this->getTableGateway()->getAdapter()->getDriver()->getConnection(); /* @var $dbConnection \Zend\Db\Adapter\Driver\ConnectionInterface */
			$dbConnection->execute("PRAGMA read_uncommitted = 1");
		}
	}
}
