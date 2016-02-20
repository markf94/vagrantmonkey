<?php
namespace JobQueue\Model;

use JobQueue\Filter\Dictionary;
use ZendServer\Exception;
use ZendJobQueue, ZendJobQueueException;
use ZendServer\Log\Log;
use Deployment\IdentityFilterInterface;
use Deployment\IdentityApplicationsAwareInterface;

class Mapper implements IdentityApplicationsAwareInterface {

	const OPTION_VALIDATE_SSL_INHERIT = 'VALIDATE_SSL_INHERIT';
	const OPTION_VALIDATE_SSL_STRICT = 'VALIDATE_SSL_STRICT';
	const OPTION_VALIDATE_SSL_ACCEPT = 'VALIDATE_SSL_ACCEPT';
	
	/**
	 * @var ZendJobQueue
	 */
	protected $jobqueue;

	/**
	 * @var Dictionary
	 */
	protected $dictionary;	
	
	/**
	 * @var IdentityFilterInterface
	 */
	protected $identityFilter;
	
	/**
	 * @var \JobQueue\Db\Mapper
	 */
	protected $queuesMapper;
	
	/**
	 * @brief set queues mapper
	 * @param \JobQueue\Db\Mapper $queuesMapper 
	 * @return  
	 */
	public function setQueuesMapper(\JobQueue\Db\Mapper $queuesMapper) {
		$this->queuesMapper = $queuesMapper;
	}
	
	/**
	 * @brief
	 * @return \JobQueue\Db\Mapper
	 */
	public function getQueuesMapper() {
		/* @var \JobQueue\Db\Mapper */
		return $this->queuesMapper;
	}
	
	/**
	 * @var array
	 */
	protected $validateSslValues = array(
		self::OPTION_VALIDATE_SSL_INHERIT,
		self::OPTION_VALIDATE_SSL_STRICT,
		self::OPTION_VALIDATE_SSL_ACCEPT,
	);
	
	/**
	 * @return array
	 */
	public function getStatistics() {
		return $this->getJobqueue()->getStatistics();
	}
	
	/**
	 * @brief add queue_Status to the job info
	 * @param <unknown> $jobInfo 
	 * @return  
	 */
	protected function updateJobRecordWithQueueData(&$jobInfo) {
		if (!$jobInfo || !$jobInfo['queue_id']) {
			return;
		}
		
		// add queue status to the result
		/* @var \JobQueue\Db\Mapper */
		$queueData = $this->getQueuesMapper()->getQueue($jobInfo['queue_id']);
		$jobInfo['queue_status'] = $queueData ? $queueData['status'] : null;
	}
	
	/**
	 * 
	 * @param array $filter
	 * @param integer $limit
	 * @param integer $offset
	 * @param string $sortBy
	 * @param string $direction
	 * @return \JobQueue\Model\JobsSet
	 */
	public function getJobsList($filter = array(), $limit = 0, $offset = 0, $sortBy = 'creationTime', $direction = 'DESC', $singleJob = null) {
		$total=0;//total would be set by getJobsList()
		$applications = array();
		if (isset($filter['app_ids'])) {
			$applications = $filter['app_ids'];
			$this->getIdentityFilter()->setAddGlobalAppId(false);
		} else {
			$this->getIdentityFilter()->setAddGlobalAppId(true);
		}
		$params = array(
			'sort_by' => $this->getDictionary()->getSortConstant($sortBy),
			'sort_direction' => $this->getDictionary()->getSortDirectionConstant($direction),
			'count' => (int) $limit,
			'start' => (int) $offset,
			'app_ids' => $this->getIdentityFilter()->filterAppIds($applications,true)
		);
		
		if ($filter) {
			$params += $filter;
		}
		
		try {
			Log::debug("Calling getJobsList() with the following params: " . var_export($params, true));
			if($singleJob){
				$resultItem = $this->getJobqueue()->getJobInfo($singleJob);
				$this->updateJobRecordWithQueueData($resultItem);
				
				$result = array();
				$result[] = $resultItem;
			} else {
				$result = $this->getJobqueue()->getJobsList($params, $total);				
			}
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to  retrieve job list: %s', array($e->getMessage())), null, $e);
		}		
		
		$count = sizeof($result);
		Log::debug("getJobsList() returned total of {$total} jobs");
		$resultSet = new JobsSet($result);
		$resultSet->setTotal($total);
		return $resultSet;
	}
	
	/**
	 * @param string $url
	 * @param array $vars
	 * @param array $options
	 */
	public function createRule($url, array $vars, array $options) {
		try {
			$result = $this->getJobqueue()->createHttpJob($url, $vars, $options);
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to create job rule: %s', array($e->getMessage())), null, $e);
		}
		
		return $result;
	}
	
	/**
	 * @param string $id
	 * @param string $vars
	 * @param string $options
	 */
	public function updateRule($id, array $vars, array $options, $url = null) {
		try {
			
			$rule = $this->getSchedulingRule($id);
			if (is_null($url)) {
				$url = $rule['script'];
			}
						
			$result = $this->getJobqueue()->updateSchedulingRule($id, $url, $vars, $options);
						
			$rule = $this->getSchedulingRule($id);
			
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to update job rule: %s', array($e->getMessage())), null, $e);
		}
	
		return $rule;
	}
	
	/**
	 * @param string $id
	 * @return array
	 */
	public function runNowRule($id) {
		try {
				
			$this->getJobqueue()->runNowSchedulingRule($id);
			$latestJob = $this->getJobsList(array(Dictionary::FILTER_COLUMN_RULE_IDS => array($id)), 1)->current();
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to execute new rule: %s', array($e->getMessage())), null, $e);
		}
	
		return $latestJob;
	}
	
	public function getJob($jobId) {
		try {
			$result = $this->getJobqueue()->getJobInfo($jobId);
			$this->updateJobRecordWithQueueData($result);

			$this->getIdentityFilter()->setAddGlobalAppId(true);
			if (! count($this->getIdentityFilter()->filterAppIds(array($result['app_id'])))) {
				throw new Exception(_t('Access to job denied.  You have no access to the application associated with the job.'));
			}
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to retrieve job list: %s', array($e->getMessage())), null, $e);
		}
	
		return $result;
	}
	
	/**
	 * @param integer $jobId
	 */
	public function deleteJob($jobId) {
		if ($this->getJobqueue()->getJobStatus($jobId) === false) {
			throw new Exception(_t('Failed to retrieve job'));
		}
		
		try {
			$this->getJobqueue()->removeJob($jobId);
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to delete job: %s', array($e->getMessage())), null, $e);
		}
	}
	
	public function deleteJobsByFilter(array $params, $total) {
		$jobs = $this->getJobsList($params, $total);
		foreach ($jobs as $job) {
			$this->deleteJob($job['id']);
		}
	}
	
	/**
	 * @param integer $jobId
	 */
	public function requeueJob($jobId) {
		if ($this->getJobqueue()->getJobStatus($jobId) === false) {
			throw new Exception(_t('Failed to retrieve job'));
		}
		
		try {
			$this->getJobqueue()->restartJob($jobId);
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to requeue job: %s', array($e->getMessage())), null, $e);
		}
	}
	
	public function createSchedulingRule(\JobQueue\Model\RecurringJob $rule) {
	    $id		= $rule->getId();
	    $script = $rule->getScript();
	    $vars	= $rule->getVars();
	    if (is_null($vars)) {
	        $vars = array();
	    }
	    $options = array();
	    $val = $rule->getName();
	    if (!empty($val)) {
	        $options['name'] = $val;
	    }
	    $val = $rule->getSchedule();
	    if (!empty($val)) {
	        $options['schedule'] = $val;
	    }
	    $q = new ZendJobQueue();
	    try {
	        $ret = $q->createHttpJob($script, $vars, $options);
	    } catch (ZendJobQueueException $e) {
	        throw new Exception($e->getMessage());
	    }
	
	    if ($ret === false) {
	        throw new Exception(_t('Failed to create a new recurring job. Make sure the Job Queue component is running and that you have the proper permissions'));
	    }
	    return true;
	}
	

	/**
	 * @param integer $ruleId
	 */
	public function resumeRule($ruleId) {
		$this->getSchedulingRule($ruleId); // validating first		
	
		try {
			$this->getJobqueue()->resumeSchedulingRule($ruleId);
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to resume rule: %s', array($e->getMessage())), null, $e);
		}
	}	
	
	/**
	 * @param integer $ruleId
	 */
	public function disableRule($ruleId) {
		$this->getSchedulingRule($ruleId); // validating first
	
		try {
			$this->getJobqueue()->suspendSchedulingRule($ruleId);
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to disable rule: %s', array($e->getMessage())), null, $e);
		}
	}
	
	/**
	 * @param integer $ruleId
	 */
	public function deleteRule($ruleId) {
		$this->getSchedulingRule($ruleId); // validating first
	
		try {
			if (!$this->getJobqueue()->deleteSchedulingRule($ruleId)) {
				throw new Exception(_t('Failed to delete rule: %s', array($ruleId)));
			}
		} catch (\ZendJobQueueException $e) {
			throw new Exception(_t('Failed to delete rule: %s', array($e->getMessage())), null, $e);
		}
	}

	/**
	 * @param integer $ruleId
	 */
	public function getSchedulingRule($ruleId) {			
		if (($rule = $this->getJobqueue()->getSchedulingRule($ruleId)) === false) {
			throw new Exception(_t('Failed to retrieve rule'));
		}
	
		$rule['priority'] = \JobQueue\JobQueueInterface::PRIORITY_URGENT - $rule['priority'];
		$validateSsl = $this->getValidateSslValues();
		if (! isset($rule['validate_ssl'])) {
			$rule['validate_ssl'] = key($validateSsl);
		}
		$rule['validate_ssl'] = $validateSsl[$rule['validate_ssl']];
		return $rule;
	}		

	/**
	 * 
	 */
	public function getSchedulingRules() {			
		if (($rules = $this->getJobqueue()->getSchedulingRules()) === false) {
			throw new Exception(_t('Failed to retrieve rules'));
		}
	
		return $rules;
	}

	/**
	 * @return Boolean
	 */
	public function isJobQueueDaemonRunning() {
		return ZendJobQueue::isJobQueueDaemonRunning();
	}


	/**
	 * @return ZendJobQueue
	 * @throws Exception
	 */
	protected function getJobqueue() {
		if ($this->jobqueue) {
			return $this->jobqueue;
		}
	
		if (class_exists("ZendJobQueue")) {
			return $this->jobqueue = new ZendJobQueue();
		} else {
			throw new Exception(_t('Job Queue extension must be loaded'));
		}
		
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
		$this->identityFilter->setAddGlobalAppId();
		return $this->identityFilter;
	}

	/**
	 * @return array
	 */
	public function getValidateSslValues() {
		return $this->validateSslValues;
	}

	/* (non-PHPdoc)
	 * @see \Deployment\IdentityApplicationsAwareInterface::setIdentityFilter()
	 */
	public function setIdentityFilter(IdentityFilterInterface $filter) {
		$this->identityFilter = $filter;
	}
	
}