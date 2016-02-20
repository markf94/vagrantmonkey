<?php

namespace JobQueue\Controller;
use Acl\License\Exception as LicenseException;
use Audit\Db\Mapper as auditMapper;

use JobQueue\Model\JobsSet;
use JobQueue\Model\RulesSet;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use ZendServer\Mvc\Controller\WebAPIActionController;
// ZendServer\Exception;

use Zend\Mvc\Controller\ActionController,
	Application\Module,
	WebAPI,
	Zend\Stdlib,
	ZendServer\Log\Log,
	Zend\Validator,
	ZendServer,
	ZendServer\Set;
use JobQueue;
use Configuration\DdMapper,
	Zend\Json\Json;

use JobQueue\Filter\Filter,
	JobQueue\Filter\Translator,
	JobQueue\Filter\Dictionary,
	JobQueue\JobQueueInterface;
	
use ZendServer\FS\FS;


use Zend\View\Model\ViewModel;

class WebAPIController extends WebAPIActionController
{
	
	const MAX_QUEUE_PRIORITY = 4; // priority might be 0 to 4;
	const MAX_QUEUE_NAME_LENGTH = 32; // priority might be 0 to 4;
	const MIN_QUEUE_NAME_LENGTH = 2; // priority might be 0 to 4;

	const QUEUES_JSON_FILE_NAME = 'queues.json';
	
	/**
	 * @var Dictionary
	 */
	protected $dictionary;
	
	/**
	 * @var boolean
	 */
	private $jobqueueLoaded = null;
	
	public function jobqueueStatisticsAction() {
		$this->isMethodGet();
		$statistics = $this->getJobqueueMapper()->getStatistics();
		return array('statistics' => $statistics);
	}
	
	public function jobqueueRequeueJobsAction() {		
		try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$messageParams = array(array('jobs' => $params['jobs']));
			
			$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_REQUEUE_JOBS, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $messageParams); /* @var $audit \Audit\Container */
				
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, $messageParams);
			
			$this->validateMandatoryParameters($params, array('jobs'));
			$this->validateArray($params['jobs'], 'jobs');
			foreach ($params['jobs'] as $key => $jobId) {
				$this->validateInteger($jobId, "jobs[$key]");
			}
		} catch (Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			$this->handleException($e, 'Input validation failed');
		}
	
		$requeued = array();
		$failed = array();		
		foreach ($params['jobs'] as $jobId) {
			try {
				$this->getJobqueueMapper()->requeueJob($jobId);
				$job = $this->getJobqueueMapper()->getJob($jobId);
				$requeued[] = $job;
			} catch (\Exception $e) {
				$failed[] = $jobId;
				Log::warn('Could not requeue job '. $jobId);
				Log::debug($e);
			}
		}		

		if ($failed) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($failed));
		}else {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $messageParams);
		}
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		return array('jobs' => new JobsSet($requeued), 'statuses' => $this->getDictionary()->getStatusDictionary(), 'priorities' => $this->getDictionary()->getPriorityDictionary(), 'applications' => $applicationsSet);
	}
	
	public function jobqueueJobInfoAction() {			
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array(
					'id' => ''));
			$this->validateMandatoryParameters($params, array('id'));
			$this->validateInteger($params['id'], 'id');
		} catch (Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$job = $this->getJobsMapper()->getJob($params['id']);
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');

		return array('job' => $job, 'applications' => $applicationsSet, 'statuses' => $this->getDictionary()->getStatusDictionary());
	}
	
	public function jobqueueSaveRuleAction() {
		try {
			$this->isMethodPost();
			
			$params = $this->getParameters(array(
				'url' => '',
				'ruleId' => '-1', 
				'vars' => array(), 
				'options' => array()
			));
			
			$auditExtraData = array();
			
			if (!empty($params['options'])) {
				$auditExtraData = $auditExtraData + $params['options'];
			}
			
			$url = trim($params['url']);
			if (!empty($url)) {
				$auditExtraData['url'] = $url;
			}
			
			if (is_numeric($params['ruleId']) && $params['ruleId'] != -1) {
				$auditExtraData['ruleId'] = $params['ruleId'];// adding a ruleId in extra data only if it's not creating new rule
			}
			
			/* @var $audit \Audit\Container */
			$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_SAVE_RULE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($auditExtraData)); 
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, array($auditExtraData));
			try {
				
				$createNewRule = true;
				if ($params['ruleId'] == '-1' && empty($params['url'])) {
					throw new WebAPI\Exception(_t("URL or ruleId must be supplied"), WebAPI\Exception::INVALID_PARAMETER);
				} elseif ($params['ruleId'] == '-1') {
					// "add" action: URL must be supplied
					$this->validateMandatoryParameters($params, array('url'));
					
					// check that the options parameter isn't empty, and contains required params
					if (empty($params['options'])) {
						throw new WebAPI\Exception(_t('Rule options must be supplied'), WebAPI\Exception::INVALID_PARAMETER);
					}
					
					if (!array_key_exists('interval', $params['options']) && !array_key_exists('schedule', $params['options'])) {
						throw new WebAPI\Exception(_t('Interval or schedule must be supplied in rule options'), WebAPI\Exception::INVALID_PARAMETER);
					}
					
				} else {
					// "update" action: ruleId must be supplied
					$this->validateMandatoryParameters($params, array('ruleId'));
					$this->validatePositiveInteger($params['ruleId'], 'ruleId');
					
					$createNewRule = false;
				}
				if ($url) {
					if (!filter_var($url, FILTER_VALIDATE_URL)) {
						throw new WebAPI\Exception(_t("Parameter '%s' must be a valid URL",array('url')), WebAPI\Exception::INVALID_PARAMETER);				
					}
				}
				
				$this->validateArray($params['vars'], 'vars');
				$options = $this->validateJobOptions($params['options']);
			} catch (Exception $e) {
				$this->handleException($e, 'Input validation failed');
			}
		} catch (\Exception $ex) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($ex->getMessage())));
			throw $ex;
		}
		
		if ($createNewRule) {
			$jobId = $this->getJobqueueMapper()->createRule($url, $params['vars'], $options);
			if (!$jobId) {
				throw new WebAPI\Exception(_t("Failed to create rule for '%s'",array('url')), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
			
			$jobInfo = $this->getJobqueueMapper()->getJob($jobId);
			$rule = $this->getJobqueueMapper()->getSchedulingRule($jobInfo['schedule_id']);
		} else {
			// if URL wasn't supplied, take it from the DB, because `updateSchedulingRule` 
			// (which is called from queues mapper's `updateRule` method) requires URL.
			if (empty($url)) {
				$currentRuleData = $this->getJobqueueMapper()->getSchedulingRule($params['ruleId']);
				$url = $currentRuleData['script'];
			}
			
			$rule = $this->getJobqueueMapper()->updateRule($params['ruleId'], $params['vars'], $options, $url);
		}		
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($auditExtraData));
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$viewModel = new ViewModel(array('rule' => $rule, 'statuses' => $this->getDictionary()->getRuleStatusDictionary(), 'priorities' => $this->getDictionary()->getPriorityDictionary(), 'applications' => $applicationsSet));
		$viewModel->setTemplate('job-queue/web-api/jobqueue-rule-info');
		
		return $viewModel;		
	}
	
		
	public function jobqueueRunNowRuleAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array(
					'ruleId' => ''
					));
			
			
			$auditDetails = $this->auditJobRulesDetails(array($params['ruleId']));
			
			$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_RUN_NOW_RULE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $auditDetails); /* @var $audit \Audit\Container */
				
			$this->validateMandatoryParameters($params, array('ruleId'));
			$this->validateInteger($params['ruleId'], 'ruleId');						
		} catch (Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$job = $this->getJobqueueMapper()->runNowRule($params['ruleId']);
		
		$rule = $this->getJobqueueMapper()->getSchedulingRule($params['ruleId']);
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array(
				'ruleId' => $params['ruleId'],
		)));
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$viewModel = new ViewModel(array('rule' => $rule, 'statuses' => $this->getDictionary()->getRuleStatusDictionary(), 'priorities' => $this->getDictionary()->getPriorityDictionary(), 'applications' => $applicationsSet));
		$viewModel->setTemplate('job-queue/web-api/jobqueue-rule-info');
		return $viewModel;
	}
	
	public function jobqueueDeleteJobsByPredefinedFilterAction() {
		try {
			$this->isMethodPost();
			$dictionary = $this->getDictionary();
			$params = $this->getParameters(array(
					'filter' => array(),
					'filterName' => '',
			));
			$filterData = $this->validateArray($params['filter'], 'filter');
			$filterData = $this->renameFilterKeys($filterData);
			$filterName = $this->validateString($params['filterName'], 'filterName');
			
			foreach (array_keys($filterData) as $filterKey) {
				$this->validateAllowedValues($filterKey, "filter[{$filterKey}]", $dictionary->getFilterColumns());
			}
			
			$filter = $this->getFilterObj($filterName, $filterData);
			$translator = new Translator($filter);
			
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_DELETE_JOBS, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($params)); /* @var $audit \Audit\Container */	
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED);
		
		$numberOfJobs = $this->getJobsMapper()->deleteJobsByFilter($translator->translate());
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('Count' => $numberOfJobs));
		
		return array();
	}
	
	public function jobqueueAddJobAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array(
				'vars' => array(),
				'options' => array()
			)
		);
		
		$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_ADD_JOB, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $params->toArray()); /* @var $audit \Audit\Container */

		$this->validateMandatoryParameters($params, array('url'));
		$url = $this->validateUri($params['url'], 'url');
		
		$options = $this->validateJobOptions($params['options']);
		
		try {
			$jobId = $this->getJobqueueMapper()->createRule($url, $params['vars'], $options);
		} catch (Exception $e) {
			$this->auditMessageProgress($audit->getAuditId(), ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			throw new WebAPI\Exception(_t("Failed to create rule for '%s'",array('url')), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		$job = $this->getJobqueueMapper()->getJob($jobId);
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');

		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $params->toArray());

		return array('job' => $job, 'applications' => $applicationsSet, 'statuses' => $this->getDictionary()->getStatusDictionary());
	}
	
	public function jobqueueDeleteJobsAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('jobs'));
			
			$messageParams = array(array('jobs' => $params['jobs']));
			$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_DELETE_JOBS, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $messageParams); /* @var $audit \Audit\Container */
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, $messageParams);
			
			$this->validateMandatoryParameters($params, array('jobs'));
			$this->validateArray($params['jobs'], 'jobs');
			foreach ($params['jobs'] as $key => $jobId) {
				$this->validateInteger($jobId, "jobs[$key]");
			}
		} catch (Exception $e) {
			$this->throwWebApiException($e, 'Input validation failed', WebAPI\Exception::INVALID_PARAMETER);
		}
	
		$failed = array();		
		foreach ($params['jobs'] as $jobId) {
			try {
				$job = $this->getJobqueueMapper()->getJob($jobId);
				$this->getJobqueueMapper()->deleteJob($jobId);
				$deleted[] = $job;
			} catch (\Exception $e) {
				$failed[] = $jobId;
				Log::warn(_t('Could not delete job '. $jobId));
				Log::debug($e);
			}
		}		
		
		if ($failed) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($failed));
		}else {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $messageParams);			
		}
		
		/// clean up job status before display
		foreach ($deleted as &$job) {
			$job['status'] = \JobQueue\JobQueueInterface::STATUS_REMOVED;
		}

		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		return array('jobs' => new JobsSet($deleted), 'failed' => $failed, 'statuses' => $this->getDictionary()->getStatusDictionary(), 'priorities' => $this->getDictionary()->getPriorityDictionary(), 'applications' => $applicationsSet);
	}
	
	public function jobqueueJobsListAction() {
		$dictionary = $this->getDictionary();
		
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array(
					'limit' => Module::config('list', 'resultsPerPage'),
					'offset' => 0,
					'order' => 'creationTime', 
					'direction' => 'DESC',
					'filter' => array(),
					'filterName' => '',
					));	
			if(isset($params['jobId']) && is_numeric($params['jobId'])){
				$jobId = $this->validateLimit($params['jobId']);
			} else {
				$jobId = null;
			}
			
			$limit = $this->validateLimit($params['limit']);
			$offset = $this->validateOffset($params['offset']);
			if ($this->isJobQueueLoaded()) {
				$order = $this->validateOrder($params['order'], $dictionary->getSortColumnsDictionary());
			}
			$direction = $this->validateDirection($params['direction']);
			$filterData = $this->validateArray($params['filter'], 'filter');
			$filterData = $this->renameFilterKeys($filterData);
			$filterName = $this->validateString($params['filterName'], 'filterName');
			foreach (array_keys($filterData) as $filterKey) {
				$this->validateAllowedValues($filterKey, "filter[{$filterKey}]", $dictionary->getFilterColumns());
			}
			
			$filter = $this->getFilterObj($filterName, $filterData);
			$translator = new Translator($filter);

			$licenseMapper = $this->getLocator()->get('Acl\License\Mapper'); /* @var $licenseMapper \Acl\License\Mapper */
			if (isset($params['filter']['from']) && $params['filter']['from'] > 0) {
				if (!$licenseMapper->isValid($params['filter']['from']) ||  $params['filter']['from'] > $params['filter']['to']) {
					throw new LicenseException('Not valid time range');
				}
			}
			
		} catch (Exception $e) {
			$this->throwWebApiException($e, 'Input validation failed', WebAPI\Exception::INVALID_PARAMETER);
		}
		
		$jobsMapper = $this->getJobsMapper();
		$jobs = $jobsMapper->getJobsList($translator->translate(), $limit, $offset, $order, $direction, $jobId);
		
		$jobsArray = $jobs->toArray();
		$total = $jobs->getTotal();
		
		$jobs = new JobsSet($jobsArray);
		$jobs->setTotal($total);
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$priorityDictionary = $this->getDictionary()->getPriorityDictionary();
		$statusDictionary = $this->getDictionary()->getStatusDictionary();
		
		return array('jobs' => $jobs, 'statuses' => $statusDictionary, 'priorities' => $priorityDictionary, 'applications' => $applicationsSet);
	}

	
	public function jobqueueResumeRulesAction() {
		return $this->jobqueuePerformRulesAction("resumeRule");
	}
	
	public function jobqueueDisableRulesAction() {
		return $this->jobqueuePerformRulesAction("disableRule");
	}
	
	/**
	 * @param array $ruleIds
	 * @return array
	 */
	private function auditJobRulesDetails($ruleIds) {
		$rules = $this->getJobsMapper()->getSchedulingRules(null, null, array('rules' => $ruleIds));
		$ruleNamesAndIds = array_map(function($job){
			return "{$job['id']}: ({$job['name']}, {$job['script']})";
		}, $rules);
			
		return array(array('rules' => implode(', ', $ruleNamesAndIds)));
	}
	
	public function jobqueueDeleteRulesAction() {
		try {
			$params = $this->getParameters();
	
			$this->isMethodPost();
			$this->validateMandatoryParameters($params, array('rules'));
			$this->validateArray($params['rules'], 'rules');
			foreach ($params['rules'] as $key => $ruleId) {
				$this->validatePositiveInteger($ruleId, "rules[$key]");
			}
		} catch (Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
			
		$auditDetails = $this->auditJobRulesDetails($params['rules']);
		$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_DELETE_RULES,
				ProgressMapper::AUDIT_PROGRESS_REQUESTED,
				$auditDetails); /* @var $audit \Audit\Container */
			
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, $auditDetails);
	
		$rulesChanged = array();
		Log::debug("Deleting rules " . var_export($params['rules'], true));
		foreach ($params['rules'] as $key => $ruleId) {
			$ruleChanged = $this->getJobqueueMapper()->getSchedulingRule($ruleId);
			$this->getJobsMapper()->deleteRule($ruleId);
			$rulesChanged[] = $ruleChanged;
		}
	
		foreach($rulesChanged as $id => $rule) {
			$rulesChanged[$id]['status'] = \JobQueue\JobQueueInterface::STATUS_REMOVED;
		}
	
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $auditDetails);
	
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
	
		$viewModel = new ViewModel(array('rules' => new RulesSet($rulesChanged), 'statuses' => $this->getDictionary()->getRuleStatusDictionary(), 'priorities' => $this->getDictionary()->getPriorityDictionary(), 'applications' => $applicationsSet));
		$viewModel->setTemplate('job-queue/web-api/jobqueue-rules-list');
		return $viewModel;
	}
	
	public function jobqueueRulesListAction() {
		$this->isMethodGet();		
		
		$params = $this->getParameters(array(
			'limit' => 0,
			'offset' => 0,
			'order' => 'next_run',
			'direction' => 'DESC',
			'filters' => array(),
		));
		
		$this->validateFilters($params['filters']);
		$this->validateOffset($params['offset']);
		$this->validateLimit($params['limit']);
		$this->validateDirection($params['direction']);
		$filters = $params['filters'];
		if (isset($filters['status'])) {
			$statusDictionary = $this->getDictionary()->getRuleDictionaryReversed();
			foreach ($filters['status'] as $key => $status) {
				if (isset($statusDictionary[$status])) {
					$filters['status'][$key] = $statusDictionary[$status];  
				}
			}
		}
		
		$dictionary = $this->getDictionary();
		if ($this->isJobQueueLoaded()) {
			$order = $this->validateOrder($params['order'], $dictionary->getSortColumnsDictionary());
		}
		
		$jobsMapper = $this->getJobsMapper();
		$rules = $jobsMapper->getSchedulingRules($params['limit'], $params['offset'], $filters, $order, $params['direction']);
		$totalRules = $jobsMapper->countSchedulingRules($filters);
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$ruleStatusDictionary = $this->getDictionary()->getRuleStatusDictionary();
		$priorityDictionary = $this->getDictionary()->getPriorityDictionary();
		
		return array('rules' => new RulesSet($rules), 'total' => $totalRules, 'statuses' => $ruleStatusDictionary, 'priorities' => $priorityDictionary, 'applications' => $applicationsSet);
	}

	public function jobqueueRuleInfoAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array(
					'id' => ''));
			$this->validateMandatoryParameters($params, array('id'));
			$this->validateInteger($params['id'], 'id');
		} catch (Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$rule = $this->getJobsMapper()->getSchedulingRule($params['id']);
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$queueData = $this->getQueuesMapper()->getQueue($rule['queue_id']);
		$rule['queue_status'] = $queueData['status'];
		
		return array('rule' => $rule, 'statuses' => $this->getDictionary()->getRuleStatusDictionary(), 'priorities' => $this->getDictionary()->getDbPriorityDictionary(), 'applications' => $applicationsSet);
	}

	public function getDictionary() { // @todo - DI
		if ($this->dictionary) {
			return $this->dictionary;
		}
	
		return $this->dictionary = $this->getLocator()->get('JobQueue\Filter\Dictionary');
	}
	
	
	// ///////////////////////////////////// Queues ///////////////////////////////////////// //

	/**
	 * @return \JobQueue\Db\Mapper
	 */
	protected function getQueuesMapper() {
		return $this->getServiceLocator()->get('JobQueue\Queues\Mapper');
	}
	
	/**
	 * Get list of queues GET
	 */
	public function jobqueueGetQueuesAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array(
			'order' => 'id',
			'direction' => 'ASC',
		));
		
		$mapper = $this->getQueuesMapper();
		$this->validateAllowedValues(strtolower($params['order']), 'order', $mapper->getQueueFields());
		$this->validateAllowedValues(strtolower($params['direction']), 'direction', array('asc', 'desc'));
		
		$queues = $mapper->getQueues($params);
		
		return array(
			'queues' => $queues,
		);
	}
	
	protected function checkMaxConcurrentJobPerQueue($max_http_jobs) {
		// check max_http_jobs agains global limit
		$globalMaxHttpJobs = intval($this->getDirectivesMapper()->getDirectiveValue('zend_jobqueue.max_http_jobs'));
		if ($globalMaxHttpJobs > 0 && $max_http_jobs > $globalMaxHttpJobs) {
			throw new WebAPI\Exception('Max concurrent jobs can be up to '.$globalMaxHttpJobs, WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * Create queue POST
	 * parameters: name*[, status[, priority]]
	 */
	public function jobqueueCreateQueueAction() {
		$this->isMethodPost();
		
		$defaults = array(
			'name' => null,
			'status' => JobQueueInterface::QUEUE_ACTIVE,
			'priority' => '2', // normal
			'max_http_jobs' => '5',
			'max_wait_time' => '5',
			'http_connection_timeout' => '30',
			'http_job_timeout' => '120',
			'http_job_retry_count' => '10',
			'http_job_retry_timeout' => '1',
		);
		
		// get params, assign defaults
		$params = $this->getParameters($defaults);
		$params['name'] = trim($params['name']);
		
		try {
			$this->validateMandatoryParameters($params, array('name'));
			
			$this->validateString($params['name'], 'name');
			$this->validateStringLength($params['name'], 2, self::MAX_QUEUE_NAME_LENGTH, 'name');
			$this->validateName($params['name'], 'name');
			
			$this->validateAllowedValues($params['status'], 'status', array(JobQueueInterface::QUEUE_ACTIVE, JobQueueInterface::QUEUE_SUSPENDED, JobQueueInterface::QUEUE_DELETED));
			
			$this->validateInteger($params['priority'], 'priority');
			$this->validateMaxInteger($params['priority'], self::MAX_QUEUE_PRIORITY, 'priority');
			
			$this->validatePositiveInteger($params['max_http_jobs'], 'max_http_jobs');
			// $this->checkMaxConcurrentJobPerQueue($params['max_http_jobs']);
			
			$this->validatePositiveNumber($params['max_wait_time'], 'max_wait_time');
			
			$this->validatePositiveInteger($params['http_connection_timeout'], 'http_connection_timeout');
			$this->validatePositiveInteger($params['http_job_timeout'], 'http_job_timeout');
			$this->validatePositiveInteger($params['http_job_retry_count'], 'http_job_retry_count');
			$this->validatePositiveInteger($params['http_job_retry_timeout'], 'http_job_retry_timeout');
			
			$this->validateNonRelevantParameters($params, array_keys($defaults));
			
		} catch (Exception $e) {
			$this->handleException($e, 'validation error');
		}
		
		$mapper = $this->getQueuesMapper();
		
		// check if queue with the same name already exists
		$allQueues = $mapper->getQueues();
		if (!empty($allQueues)) foreach ($allQueues as $q) {
			if (strcasecmp(trim($q['name']), $params['name']) == 0) {
				$msg = $params['message'] = 'queue with same name already exists';
				$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_ADD_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params));
				$this->handleException(new WebAPI\Exception($msg, WebAPI\Exception::DUPLICATE_RECORD), $msg);
			}
		}
		
		// add the queue to the database
		$newId = $mapper->addQueue($params);
		if ($newId !== false) {
			$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_ADD_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($params));			
			
			// also add a jobqueue queue stats entry
			/* @var JobQueue\Db\Mapper */
			$queueStatsMapper = $this->getLocator()->get('JobQueue\QueuesStats\Mapper');
			$result = $queueStatsMapper->insertNewQueueStats($newId);
			if (false === $result) {
				// remove the new created queue
				$mapper->deleteQueue($newId, $forceDelete = true);
				
				$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_ADD_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params));
				$this->handleException(new WebAPI\Exception(_t('Error adding the queue statistics record'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Error adding the queue statistics record');
			}
			
			return array('result' => 'success', 'id' => $newId);
		}
		
		$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_ADD_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params));
		$this->handleException(new WebAPI\Exception(_t('Error adding the queue'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Error adding the queue');
	}
	
	/**
	 * Update queue POST
	 * id*[, name [, priority[, status]]]
	 */
	public function jobqueueUpdateQueueAction() {
		$this->isMethodPost();
		
		$defaults = array(
			'id' => null,
			'name' => null,
			'status' => null,
			'priority' => null,
			'max_http_jobs' => null,
			'max_wait_time' => null,
			'http_connection_timeout' => null,
			'http_job_timeout' => null,
			'http_job_retry_count' => null,
			'http_job_retry_timeout' => null,
		);
		
		// get params, assign defaults
		$params = $this->getParameters($defaults);
		
		/* @var \JobQueue\Db\Mapper */
		$mapper = $this->getQueuesMapper();
		
		try {
			
			$this->validateMandatoryParameters($params, array('id'));
			$this->validatePositiveInteger($params['id'], 'id');
			
			
			// check that the queue with the required ID exists
			if (false === $mapper->getQueue($params['id'])) {
				$this->handleException(new WebAPI\Exception(_t('Queue does not exist'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Queue does not exist');
			}
			
			if (!is_null($params['name'])) {    
				$this->validateString($params['name'], 'name');
				$this->validateStringLength($params['name'], self::MIN_QUEUE_NAME_LENGTH, self::MAX_QUEUE_NAME_LENGTH, 'name');
				$this->validateName($params['name'], 'name');
				
				if ($params['id'] == \JobQueue\Db\Mapper::DEFAULT_QUEUE_ID && strcasecmp($params['name'], 'default') != 0) {
					$this->handleException(
						new WebAPI\Exception(_t('Changing the name of the default queue is not allowed'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 
						'Changing the name of the default queue is not allowed'
					);
				}
			} else {
				unset($params['name']);
			}
			
			if (!is_null($params['status'])) {    
			   $this->validateAllowedValues($params['status'], 'status', array(JobQueueInterface::QUEUE_ACTIVE, JobQueueInterface::QUEUE_SUSPENDED, JobQueueInterface::QUEUE_DELETED));
			} else {
				unset($params['status']);
			}
			
			if (!is_null($params['priority'])) {
				$this->validateInteger($params['priority'], 'priority');
				$this->validateMaxInteger($params['priority'], self::MAX_QUEUE_PRIORITY, 'priority');
			} else {
				unset($params['priority']);
			}
		
			if (!is_null($params['max_http_jobs'])) {
				$this->validatePositiveInteger($params['max_http_jobs'], 'max_http_jobs');
				// $this->checkMaxConcurrentJobPerQueue($params['max_http_jobs']);
			} else {
				unset($params['max_http_jobs']);
			}
		
			if (!is_null($params['max_wait_time'])) {
				$this->validatePositiveNumber($params['max_wait_time'], 'max_wait_time');
			} else {
				unset($params['max_wait_time']);
			}
			
			if (!is_null($params['http_connection_timeout'])) {
				$this->validatePositiveInteger($params['http_connection_timeout'], 'http_connection_timeout');
			} else {
				unset($params['http_connection_timeout']);
			}
		
			if (!is_null($params['http_job_timeout'])) {
				$this->validatePositiveInteger($params['http_job_timeout'], 'http_job_timeout');
			} else {
				unset($params['http_job_timeout']);
			}
		
			if (!is_null($params['http_job_retry_count'])) {
				$this->validatePositiveInteger($params['http_job_retry_count'], 'http_job_retry_count');
			} else {
				unset($params['http_job_retry_count']);
			}
		
			if (!is_null($params['http_job_retry_timeout'])) {
				$this->validatePositiveInteger($params['http_job_retry_timeout'], 'http_job_retry_timeout');
			} else {
				unset($params['http_job_retry_timeout']);
			}
		
			$this->validateNonRelevantParameters($params, array_keys($defaults));
			
		} catch (Exception $e) {
			$this->handleException($e, 'validation error');
		}
		
		// check if queue with the same name already exists
		if (isset($params['name'])) {
			$allQueues = $mapper->getQueues();
			if (!empty($allQueues)) foreach ($allQueues as $q) {
				if ($q['name'] == $params['name'] && $q['id'] != $params['id']) {
					$msg = $params['message'] = 'queue with same name already exists';
					$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_UPDATE_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params));
					$this->handleException(new WebAPI\Exception($msg, WebAPI\Exception::DUPLICATE_RECORD), $msg);
				}
			}
		}
		
		// add the queue to the database
		$result = $mapper->updateQueue($params['id'], $params);
		if ($result) {
			$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_UPDATE_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($params));
			return array('result' => 'success');
		}
		
		$this->auditMessage(Mapper::AUDIT_JOB_QUEUE_UPDATE_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params));
		$this->handleException(new WebAPI\Exception(_t('Error updating the queue'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Error updating the queue');
	}
	
	/**
	 * @TODO check if the queue has jobs or rules assigned to it
	 * Delete queue POST
	 * id*
	 */
	public function jobqueueDeleteQueueAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array(
			'id' => null,
			'delete_related_stuff' => 0,
		));

		try {
			$this->validateMandatoryParameters($params, array('id'));
			$this->validatePositiveInteger($params['id'], 'id');
			
			// check the default queue
			if (intval($params['id']) == 1) {
				throw new \Exception('The default queue cannot be deleted');
			}
		} catch (Exception $e) {
			$this->handleException($e, 'validation error');
		}
		
		$queueId = $params['id'];
		$mapper = $this->getQueuesMapper();
		
		// get queue name (and check that the queue exists)
		$queues = $mapper->getQueues();
		$queueName = null;
		if (!empty($queues)) {
			foreach ($queues as $q) {
				if ($q['id'] == $queueId) {
					$params['name'] = $queueName = $q['name'];
					break;
				}
			}
		}
		
		if (is_null($queueName)) {
			$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_DELETE_QUEUE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params));
			$this->handleException(new WebAPI\Exception(_t('Queue not found'), WebAPI\Exception::MISSING_RECORD), 'Queue not found');
		}
		
		// check if the queue has recurring jobs assigned on it
		$jobsMapper = $this->getJobsMapper();
		$rules = $jobsMapper->getSchedulingRules();
		
		$assignedRules = array();
		$assignedRulesNames = array();
		if (!empty($rules)) foreach ($rules as $rule) {
			if ($rule['queue_id'] == $queueId) {
				$assignedRules[] = $rule;
				$assignedRulesNames[] = $rule['name'];
			}
		}
		
		// check if the queue has future jobs assigned on it (pending, running, waiting)
		/* @var \JobQueue\Model\JobsSet */
		$assignedJobs = $jobsMapper->getJobsList(array(
			'queue_ids' => array(
				$queueId,
			),
			'status' => array(
				JobQueueInterface::STATUS_PENDING, 
				JobQueueInterface::STATUS_RUNNING, 
				JobQueueInterface::STATUS_SCHEDULED, 
				JobQueueInterface::STATUS_SUSPENDED, 
			),
		), 100);
		
		
		if (!$params['delete_related_stuff']) {
			if (!empty($assignedRulesNames) || $assignedJobs->getTotal() > 0) {
				$msg = array();
				if (!empty($assignedRulesNames)) {
					if (count($assignedRulesNames) == 1) {
						$msg[] = 'there is a rule ("'.implode('", "', $assignedRulesNames).'")';
					} else {
						$msg[] = 'there are rules ("'.implode('", "', $assignedRulesNames).'")';
					}
				}
				if ($assignedJobs->getTotal() > 0) {
					$numOfJobs = $assignedJobs->getTotal() < 100 ? $assignedJobs->getTotal() : 'more than 100';
					$isOrAre = $assignedJobs->getTotal() == 1 ? 'is' : 'are';
					$jobs = $assignedJobs->getTotal() == 1 ? 'job' : 'jobs';
					$msg[] = "there {$isOrAre} {$numOfJobs} {$jobs}";
				}
				
				$exceptionMessage = ucfirst(implode(' and ', $msg) . ' assigned to the queue');
				$this->handleException(new WebAPI\Exception($exceptionMessage, WebAPI\Exception::INTERNAL_SERVER_ERROR), $exceptionMessage);
			}
		} else {
			
			// delete related rules
			if (!empty($assignedRules)) foreach ($assignedRules as $rule) {
				$jobsMapper->deleteRule($rule['id']);
			}
			
			// delete related jobs (only future jobs, keep history and running jobs)
			$jobsMapper->deleteJobsByFilter(array(
				'queue_ids' => array(
					$queueId,
				),
				'status' => array(
					JobQueueInterface::STATUS_PENDING, 
					// JobQueueInterface::STATUS_RUNNING, 
					JobQueueInterface::STATUS_SCHEDULED, 
					JobQueueInterface::STATUS_SUSPENDED, 
				),
			));
			
		}
		
		$result = $mapper->deleteQueue($queueId);
		
		$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_DELETE_QUEUE, 
			($result ? ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY : ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED), 
			array($params));
		
		if ($result) {
			return array('result' => 'success');
		}
		
		$this->handleException(new WebAPI\Exception(_t('Error deleting the queue'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Error deleting the queue');
	}
	

	/**
	 * suspend queue POST
	 * id*
	 */
	public function jobqueueSuspendQueueAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array(
			'id' => null,
		));
	
		try {
			$this->validateMandatoryParameters($params, array('id'));
			$this->validatePositiveInteger($params['id'], 'id');
		} catch (Exception $e) {
			$this->handleException($e, 'validation error');
		}
		 
		$mapper = $this->getQueuesMapper();
		$result = $mapper->suspendQueue($params['id']);
		
		// get queue name for the audit
		$queueData = $mapper->getQueue($params['id']);
		$params['name'] = $queueData['name'];
		
		$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_SUSPEND_QUEUE,
			($result ? ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY : ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED),
			array($params));
		 
		if ($result) {
			return array('result' => 'success');
		}
		 
		$this->handleException(new WebAPI\Exception(_t('Error suspending the queue'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Error suspending the queue');
	}
	
	/**
	 * activate queue POST
	 * id*
	 */
	public function jobqueueActivateQueueAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array(
			'id' => null,
		));
		
		try {
			$this->validateMandatoryParameters($params, array('id'));
			$this->validatePositiveInteger($params['id'], 'id');
		} catch (Exception $e) {
			$this->handleException($e, 'validation error');
		}
		 
		$mapper = $this->getQueuesMapper();
		$result = $mapper->activateQueue($params['id']);
		
		// get queue name for the audit
		$queueData = $mapper->getQueue($params['id']);
		$params['name'] = $queueData['name'];
		
		$audit = $this->auditMessage(Mapper::AUDIT_JOB_QUEUE_ACTIVATE_QUEUE,
			($result ? ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY : ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED),
			array($params));
		 
		if ($result) {
			return array('result' => 'success');
		}
		 
		$this->handleException(new WebAPI\Exception(_t('Error suspending the queue'), WebAPI\Exception::INTERNAL_SERVER_ERROR), 'Error suspending the queue');
	}
	
	/**
	 * export queues - export zipped sql file with `insert` statements
	 */
	public function jobqueueExportQueuesAction() {
		$this->isMethodGet();
		
		$mapper = $this->getQueuesMapper();
		$queuesExportSql = $mapper->getQueuesExportJson();
		
		// create temp zip file
		$file = tempnam(FS::getGuiTempDir(), "queues");
		
		$zip = new \ZipArchive();
		$zip->open($file, \ZipArchive::OVERWRITE);
		$zip->addFromString(self::QUEUES_JSON_FILE_NAME, $queuesExportSql);
		$zip->close();
		
		$date = strftime("%Y %m %d %H %M %S", time());
		$date = str_replace(" ", "_", $date);
		
		$headers = array();
		$headers[] = "Content-Disposition: attachment; filename=\"queues_{$date}.zip\"";
		$headers[] = "Content-type: application/zip";
		$headers[] = "Content-Length: ".filesize($file);
			
		$headersToSend = new \Zend\Http\Headers();
		$headersToSend->addHeaders($headers);
			
		/* @var $response \Zend\Http\PhpEnvironment\Response */
		$response = $this->getResponse();
		$response->setHeaders($headersToSend);
		$response->setContent(file_get_contents($file));
		
		$this->auditMessage(auditMapper::AUDIT_JOB_QUEUE_QUEUES_EXPORT, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		
		return $this->getResponse();	    	    
	}
	
	/**
	 * import queues. receive "multipart/form-data" file (as $_FILES['file']) and `delete_current` (1|0)
	 */
	public function jobqueueImportQueuesAction() {
		
		$this->isMethodPost();
		
		$params = $this->getParameters();
		$this->validateAllowedValues($params['delete_current'], 'delete_current', array('0', '1'));
		
		try {
			$guiTempDir = FS::getGuiTempDir();
			if (!is_dir($guiTempDir)) {
				throw new WebAPI\Exception(_t('Temporary folder does not exist'), WebAPI\Exception::MISSING_FILE);
			}
			if (!is_writable($guiTempDir)) {
				throw new WebAPI\Exception(_t('Temporary folder is not writable'), WebAPI\Exception::PERMISSIONS_ERROR);
			}
			
			// Get file from POST
			$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
			$fileTransfer->setDestination($guiTempDir);
			
			if (! $fileTransfer->receive()) {
				$errorMessages = $fileTransfer->getMessages();
				if (isset($errorMessages['fileUploadErrorNoFile'])) {
					throw new WebAPI\Exception(_t('No file uploaded'), WebAPI\Exception::INVALID_PARAMETER);
				} else {
					throw new WebAPI\Exception(current($errorMessages), WebAPI\Exception::INVALID_PARAMETER);
				}
			}
			
			// read the ZIP file
			$filePath = $fileTransfer->getFileName();
			if (!preg_match('%\.zip$%i', $filePath)) {
				throw new WebAPI\Exception('Not a ZIP archive', WebAPI\Exception::INVALID_PARAMETER);
			}
			$archive = FS::getZipArchive($filePath, 0);
			$queuesToImport = $archive->getFromName(self::QUEUES_JSON_FILE_NAME);
			$archive->close();
			
			if ($queuesToImport === false) {
				throw new WebAPI\Exception('Cannot find or read the file '.self::QUEUES_JSON_FILE_NAME.' inside the archive', WebAPI\Exception::INVALID_PARAMETER);
			}
			
			// check the JSON
			$queuesToImport = json_decode($queuesToImport, true);
			if (!$queuesToImport) {
				throw new WebAPI\Exception('Import file content is invalid', WebAPI\Exception::IMPORT_FAILED);
			}
			
			$mapper = $this->getQueuesMapper();
			
			// clear queues
			if ($params['delete_current'] == '1') {
				
				// check if the queues have related jobs or recurring jobs
				$jobsMapper = $this->getJobsMapper();
				
				// gather queue IDs
				$queueIds = array();
				foreach ($mapper->getQueues() as $queue) {
					if ($queue['id'] == \JobQueue\Db\Mapper::DEFAULT_QUEUE_ID) {
						continue;
					}
					
					$queueIds[] = $queue['id'];
				}
				
				
				
				if (!empty($queueIds)) {
					// get related rules
					$rules = $jobsMapper->getSchedulingRules (null, null, array('queue_ids'=>$queueIds));
					
					// get related future jobs
					$jobs = $jobsMapper->getJobsList(array(
						'queue_ids' => $queueIds,
						'status' => array(
							JobQueueInterface::STATUS_PENDING, 
							JobQueueInterface::STATUS_RUNNING, 
							JobQueueInterface::STATUS_SCHEDULED, 
							JobQueueInterface::STATUS_SUSPENDED, 
						)
					));
					
					
					$rulesAttached = (is_array($rules) && count($rules) > 0) || ($rules instanceof \ZendServer\Set && $rules->count() > 0);
					$jobsAttached = (is_array($jobs) && count($jobs) > 0) || ($jobs instanceof \ZendServer\Set && $jobs->count() > 0);
					
					if ($rulesAttached || $jobsAttached) {
						throw new WebAPI\Exception('Cannot delete existing queues. Jobs and recurring jobs are related to the existing queues', WebAPI\Exception::IMPORT_FAILED);
					}
					
					$mapper->deleteAllQueues();
				}
			}
			
			$importResult = $mapper->importQueues($queuesToImport);
			
			if (!$importResult) {
				throw new WebAPI\Exception('DB errors occurred during queues import process.<br>'.
					'Please check the <a href="'.($this->getRequest()->getBasePath()).'/Logs" target="_blank">logs</a> (zend_server_ui log) for more information', 
					WebAPI\Exception::IMPORT_FAILED);
			}
			
			$auditMessage = ($params['delete_current'] == '1') ? 'Current jobQueue queues deleted and new queues list imported' : 'JobQueue queues imported';
			$this->auditMessage(auditMapper::AUDIT_JOB_QUEUE_QUEUES_IMPORT, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(array($auditMessage)));
			return array('result' => 'success');
		} catch (\Exception $e) {
			// $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
	}
	
	/**
	 * @return DdMapper
	 */
	private function getDdMapper() {
		$zeMapFile = get_cfg_var('zend.install_dir') . DIRECTORY_SEPARATOR . 'share/zend_extensions_map.json';
		$content = file_get_contents($zeMapFile, Json::TYPE_OBJECT);
		return new DdMapper($content);
	}
	
	/**
	 * @param array $params
	 * @TODO implement
	 * related to jobqueueUpdateSettingsAction
	 */
	protected function _validateJobQueueEventDirectives($params) {
		if (!is_null($params['job_execution_delay_event_enabled'])) {
			$this->validateInteger($params['job_execution_delay_event_enabled'], 'job_execution_delay_event_enabled');
		}
		if (!is_null($params['job_execution_error_event_enabled'])) {
			$this->validateInteger($params['job_execution_error_event_enabled'], 'job_execution_error_event_enabled');
		}
		if (!is_null($params['job_logical_error_event_enabled'])) {
			$this->validateInteger($params['job_logical_error_event_enabled'], 'job_logical_error_event_enabled');
		}
		
		if (!is_null($params['job_execution_delay_event_severity'])) {
			$this->validateInteger($params['job_execution_delay_event_severity'], 'job_execution_delay_event_severity');
		}
		if (!is_null($params['job_execution_error_event_severity'])) {
			$this->validateInteger($params['job_execution_error_event_severity'], 'job_execution_error_event_severity');
		}
		if (!is_null($params['job_logical_error_event_severity'])) {
			$this->validateInteger($params['job_logical_error_event_severity'], 'job_logical_error_event_severity');
		}
		
		
		if (!is_null($params['job_execution_delay_event_email']) && !empty($params['job_execution_delay_event_email'])) {
			$this->validateEmailAddress($params['job_execution_delay_event_email'], 'job_execution_delay_event_email');
		}
		if (!is_null($params['job_execution_error_event_email']) && !empty($params['job_execution_error_event_email'])) {
			$this->validateEmailAddress($params['job_execution_error_event_email'], 'job_execution_error_event_email');
		}
		if (!is_null($params['job_logical_error_event_email']) && !empty($params['job_logical_error_event_email'])) {
			$this->validateEmailAddress($params['job_logical_error_event_email'], 'job_logical_error_event_email');
		}
		
		
		if (!empty($params['job_execution_delay_event_call_url']) && filter_var($params['job_execution_delay_event_call_url'], FILTER_VALIDATE_URL) === false) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid URL",array('job_execution_delay_event_call_url')), WebAPI\Exception::INVALID_PARAMETER);
		}
		if (!empty($params['job_execution_error_event_call_url']) && filter_var($params['job_execution_error_event_call_url'], FILTER_VALIDATE_URL) === false) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid URL",array('job_execution_error_event_call_url')), WebAPI\Exception::INVALID_PARAMETER);
		}
		if (!empty($params['job_logical_error_event_call_url']) && filter_var($params['job_logical_error_event_call_url'], FILTER_VALIDATE_URL) === false) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid URL",array('job_logical_error_event_call_url')), WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * related to jobqueueUpdateSettingsAction
	 * @param array $params
	 */
	protected function _validateJobQueueDirectives($params) {
		$ddMapper = $this->getDdMapper();
		
		// validate directives
		foreach ($params as $name => $value) {
			if (is_null($value)) continue;
		
			$nameWithZendJobqueue = strpos($name, 'zend_jobqueue') === false ? 'zend_jobqueue.'.$name : $name;
			$directiveExists = $this->getDirectivesMapper()->directiveExists($nameWithZendJobqueue);
			if (!$directiveExists) {
				throw new \Exception("Directive {$name} does not exist and cannot be validated", \WebAPI\Exception::NO_SUCH_DIRECTIVE);
			}
			 
			try {
				$directiveValidator = $ddMapper->directiveValidator($nameWithZendJobqueue); /* @var $directiveValidator \Zend\InputFilter\Input */
				$directiveValidator->setValue($value);
				if ($directiveValidator->allowEmpty() && empty($value)) {
					continue;
				}
			} catch (\Exception $e) {
				Log::err("Set directives failed: " . $e->getMessage());
				throw new WebAPI\Exception(_t('Setting directives failed: %s', array($e->getMessage())), WebAPI\Exception::INVALID_PARAMETER);
			}
		
			if (! $directiveValidator->isValid()) {
				Log::err("The directives validation failed on directive '$name': " .print_r($directiveValidator->getMessages(),true));
				throw new WebAPI\Exception(_t("Directive '%s' validation failed: %s", array($name, current($directiveValidator->getMessages()))), WebAPI\Exception::INVALID_PARAMETER);
			}
		}
		
	}
	
	
	protected function _saveEventDirectives($directives) {
		
		/* @var $mapper \MonitorRules\Model\Mapper */
		$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); 
		$rules = $mapper->findMonitorRules();
		
		// get rules ids
		$enableIds = array();
		$disableIds = array();
		foreach ($rules as $rule) {
			if ($rule->getName() == 'Job Execution Delay') {
				if ($directives['job_execution_delay_event_enabled'] == 1) {
				   $enableIds[] = $rule->getId();
				} else {
				   $disableIds[] = $rule->getId();
				}
			} elseif ($rule->getName() == 'Job Execution Error') {
				if ($directives['job_execution_error_event_enabled'] == 1) {
				   $enableIds[] = $rule->getId();
				} else {
				   $disableIds[] = $rule->getId();
				}
			} elseif ($rule->getName() == 'Job Logical Failure') {
				if ($directives['job_logical_error_event_enabled'] == 1) {
				   $enableIds[] = $rule->getId();
				} else {
				   $disableIds[] = $rule->getId();
				}
			}
		}
		
		// enable rules
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$request->setMethod('POST');
		if (!empty($enableIds)) {
			$request->setPost(new \Zend\Stdlib\Parameters(array(
				'rulesIds' => $enableIds
			)));
			$enableDisableRuleviewHelper = $this->forward()->dispatch('MonitorRulesWebApi-1_3', array(
				'controller' => 'MonitorRulesWebApi', 
				'action' => 'monitorEnableRules', 
			));
		}
		
		// disable rules
		if (!empty($disableIds)) {
			$request->setPost(new \Zend\Stdlib\Parameters(array(
				'rulesIds' => $disableIds
			)));
			$enableDisableRuleviewHelper = $this->forward()->dispatch('MonitorRulesWebApi-1_3', array(
				'controller' => 'MonitorRulesWebApi', 
				'action' => 'monitorDisableRules', 
			));
		}
		
		foreach ($rules as $rule) {
			$ruleDataArray = array(
				'ruleId' => $rule->getId(),
				'ruleProperties' => $rule->getProperties(),
			);
			if ($rule->getName() == 'Job Execution Delay') {
				$ruleDataArray['ruleProperties']['enabled'] = $directives['job_execution_delay_event_enabled'] ? '1' : '0';
				$request->setPost(new \Zend\Stdlib\Parameters(array_merge($ruleDataArray, array(
					'ruleTriggers' => array(
						array(
							'triggerProperties' => array(
								'trigger_id' => -1,
								'severity' => $directives['job_execution_delay_event_severity']
							),
							'triggerActions' => array(
								array(
									'action_id' => -1,
									'action_type' => \MonitorRules\Action::TYPE_MAIL,
									'send_to' => $directives['job_execution_delay_event_email'], 
								),
								array(
									'action_id' => -1,
									'action_type' => \MonitorRules\Action::TYPE_CALLBACK,
									'action_url' => $directives['job_execution_delay_event_call_url'],
								),
							),
						),
					),
				))));
			} elseif ($rule->getName() == 'Job Execution Error') {
				$ruleDataArray['ruleProperties']['enabled'] = $directives['job_execution_error_event_enabled'] ? '1' : '0';
				$request->setPost(new \Zend\Stdlib\Parameters(array_merge($ruleDataArray, array(
					'ruleTriggers' => array(
						array(
							'triggerProperties' => array(
								'trigger_id' => -1,
								'severity' => $directives['job_execution_error_event_severity']
							),
							'triggerActions' => array(
								array(
									'action_id' => -1,
									'action_type' => \MonitorRules\Action::TYPE_MAIL,
									'send_to' => $directives['job_execution_error_event_email'],
								),
								array(
									'action_id' => -1,
									'action_type' => \MonitorRules\Action::TYPE_CALLBACK,
									'action_url' => $directives['job_execution_error_event_call_url'],
								),
							),
						),
					),
				))));
			} elseif ($rule->getName() == 'Job Logical Failure') {
				$ruleDataArray['ruleProperties']['enabled'] = $directives['job_logical_error_event_enabled'] ? '1' : '0';
				$request->setPost(new \Zend\Stdlib\Parameters(array_merge($ruleDataArray, array(
					'ruleTriggers' => array(
						array(
							'triggerProperties' => array(
								'trigger_id' => -1,
								'severity' => $directives['job_logical_error_event_severity']
							),
							'triggerActions' => array(
								array(
									'action_id' => -1,
									'action_type' => \MonitorRules\Action::TYPE_MAIL,
									'send_to' => $directives['job_logical_error_event_email'],
								),
								array(
									'action_id' => -1,
									'action_type' => \MonitorRules\Action::TYPE_CALLBACK,
									'action_url' => $directives['job_logical_error_event_call_url'],
								),
							),
						),
					),
				))));
			} else {
				// non relevant rule
				continue;
			}
			
			$attempts = 10;
			while ($attempts-- > 0) {
				try {
					$enableDisableRuleviewHelper = $this->forward()->dispatch('MonitorRulesWebApi-1_3', array(
						'controller' => 'MonitorRulesWebApi', 
						'action' => 'monitorSetRule', 
					));
					break;
				} catch (\Exception $e) {
					if ($attempts <= 0) {
						throw $e;
					}
					
					Log::debug('Retrying set monitor rule for '.$rule->getName());
					usleep(100 * 1000); // 100 milliseconds
				}
			}
		}
	}
	
	/**
	 * Update jobqueue settings - directives and event trigger
	 */
	public function jobqueueUpdateSettingsAction() {
		$this->isMethodPost();
		
		// get parameters (directives and events settings)
		$jqDirectives = array(
			// JQ directives
			'history' => null,
			'history_failed' => null,
			'db_size_completed' => null,
			'db_size_failed' => null,
			'store_job_output' => 0,
			'store_only_failed_jobs_output' => 0, // new directive
			'max_job_output_size' => null,
			'job_time_skew_allowed' => null,
			'max_http_jobs' => null,
			'max_http_jobs_for_entire_cluster' => null,
		);
		$jqEventsDirectives = array(
			'job_execution_delay_event_enabled' => null,
			'job_execution_delay_event_severity' => null,
			'job_execution_delay_event_email' => null,
			'job_execution_delay_event_call_url' => null,
			
			'job_execution_error_event_enabled' => null,
			'job_execution_error_event_severity' => null,
			'job_execution_error_event_email' => null,
			'job_execution_error_event_call_url' => null,
			
			'job_logical_error_event_enabled' => null,
			'job_logical_error_event_severity' => null,
			'job_logical_error_event_email' => null,
			'job_logical_error_event_call_url' => null,
		);
		$params = $this->getParameters(array_merge($jqDirectives, $jqEventsDirectives));
		
		// directives with 'zend_jobqueue.' prefix for the directives mapper
		$jqDirectivesWithRealName = array();

		// validate and save directives
		$jqDirectivesFromParams = array();
		foreach ($jqDirectives as $k => $v) {
			$jqDirectivesWithRealName['zend_jobqueue.'.$k] = 
				$jqDirectivesFromParams[$k] = $params[$k];
		}
		
		$directivesMapper = $this->getDirectivesMapper();
		
		// get only modified directives (compare to the current values)
		$currentDirectivesValues = $directivesMapper->getDirectivesValues(array_keys($jqDirectivesWithRealName));
		$directivesToUpdate = array();
		foreach ($currentDirectivesValues as $directiveName => $directiveValue) {
			if ($jqDirectivesWithRealName[$directiveName] != $directiveValue) {
				$directivesToUpdate[$directiveName] = $jqDirectivesWithRealName[$directiveName];
			}
		}
		
		$this->_validateJobQueueDirectives($directivesToUpdate);
		if (!empty($directivesToUpdate)) {
			$directivesMapper->setDirectives($directivesToUpdate);
			$this->auditMessage(\Audit\Db\Mapper::AUDIT_DIRECTIVES_MODIFIED, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($directivesToUpdate));
		}
		
		// validate and save event directives
		$jqEventDirectivesFromParams = array();
		foreach ($jqEventsDirectives as $k => $v) {
			$jqEventDirectivesFromParams[$k] = $params[$k];
		}
	
		// check if event params were passed
		if (!is_null($jqEventDirectivesFromParams['job_execution_delay_event_enabled'])) {
			$this->_validateJobQueueEventDirectives($jqEventDirectivesFromParams);
			$this->_saveEventDirectives($jqEventDirectivesFromParams);
		}
		
		return array('result' => 'success');
	}
	
	
	/**
	 * Update jobqueue events - same as update settings, but without the directives
	 */
	public function jobqueueUpdateEventsAction() {
		$this->isMethodPost();

		$jqEventsDirectives = array(
			'job_time_skew_allowed' => null,
			
			'job_execution_delay_event_enabled' => null,
			'job_execution_delay_event_severity' => null,
			'job_execution_delay_event_email' => null,
			'job_execution_delay_event_call_url' => null,
			
			'job_execution_error_event_enabled' => null,
			'job_execution_error_event_severity' => null,
			'job_execution_error_event_email' => null,
			'job_execution_error_event_call_url' => null,
			
			'job_logical_error_event_enabled' => null,
			'job_logical_error_event_severity' => null,
			'job_logical_error_event_email' => null,
			'job_logical_error_event_call_url' => null,
		);
		$params = $this->getParameters($jqEventsDirectives);
		
		// save `job_time_skew_allowed` directive
		if (!is_null($params['job_time_skew_allowed'])) {
			$request = $this->getRequest();
			$request->setPost(new \Zend\Stdlib\Parameters(array(
				'directives' => array(
					'zend_jobqueue.job_time_skew_allowed' => $params['job_time_skew_allowed'],
				)
			)));
			$enableDisableRuleviewHelper = $this->forward()->dispatch('ConfigurationWebApi-1_3', array(
				'controller' => 'ConfigurationWebApi', 
				'action' => 'configurationStoreDirectives', 
			));
		}
		
		// validate and save event directives
		$jqEventDirectivesFromParams = array();
		foreach ($jqEventsDirectives as $k => $v) {
			if (!is_null($params[$k])) {
				$jqEventDirectivesFromParams[$k] = $params[$k];
			}
		}
		
		if (!empty($jqEventDirectivesFromParams)) {
			$this->_validateJobQueueEventDirectives($jqEventDirectivesFromParams);
			$this->_saveEventDirectives($jqEventDirectivesFromParams);
		}
		
		return array('result' => 'success');
	}
	
	public function jobqueueQueueStatsAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters(array(
			'queue_id' => null,
		));
		
		$queueId = $params['queue_id'];
		
		if (!is_null($queueId)) {
			$this->validatePositiveInteger($queueId, 'queue_id');
		}
		
		/* @var JobQueue\Db\Mapper */
		$jobsMapper = $this->getJobsMapper();
		
		return array(
			'queueStats' => $jobsMapper->getQueueStats($queueId),	        
		);
	}
	
	
	/**
	 * @return \JobQueue\Db\Mapper
	 */
	private function getJobsMapper() {
		return $this->getLocator()->get('JobQueue\Db\Mapper');
	}
	
	private function renameFilterKeys($filterData) { // adjusting keys from the global filter widget conventions
		if (isset($filterData['to'])) {
			$filterData['executed_before'] = $filterData['to'];
			unset($filterData['to']);
		}
	
		if (isset($filterData['from'])) {
			$filterData['executed_after'] = $filterData['from'];
			unset($filterData['from']);
		}
	
		return $filterData;
	}
	
	/**
	 *
	 * @param string $filterName
	 * @return \JobQueue\Filter\Filter
	 */
	private function getFilterObj($filterName, $filterData) {
		if (!$filterName) return new Filter($filterData);
	
		$filterList = $this->getFilterMapper()->getByTypeAndName(Filter::JOB_FILTER_TYPE, $filterName); /* @var $filterList \ZendServer\Filter\FilterList */
		if (! count($filterList)) {
			throw new WebAPI\Exception(_t("Cannot find JobQueue filter '%s' in '%s' table",array($filterName, $this->getFilterMapper()->getTableName())), WebAPI\Exception::INVALID_PARAMETER);
		}
	
		return new Filter($filterData + $filterList->current()->getData()); // actual data will take precedence over dbData
	}
	
	
	private function jobqueuePerformRulesAction($action) {
		try {
			$params = $this->getParameters();
				
			switch ($action) {
				case "resumeRule":
					$auditType = Mapper::AUDIT_JOB_QUEUE_RESUME_RULES;
					break;
				case "disableRule":
					$auditType = Mapper::AUDIT_JOB_QUEUE_DISABLE_RULES;
					break;
				default:
					break;
			}
				
			$this->isMethodPost();
			$this->validateMandatoryParameters($params, array('rules'));
			$this->validateArray($params['rules'], 'rules');
			foreach ($params['rules'] as $key => $ruleId) {
				$this->validatePositiveInteger($ruleId, "rules[$key]");
			}
		} catch (Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}

		$auditDetails = $this->auditJobRulesDetails($params['rules']);
		$audit = $this->auditMessage($auditType, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $auditDetails); /* @var $audit \Audit\Container */	
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED, $auditDetails);
		
		$rulesChanged = array();
		foreach ($params['rules'] as $key => $ruleId) {
			$this->getJobqueueMapper()->$action($ruleId);
		}	
	
		$rules = $this->getJobsMapper()->getSchedulingRules(null, null, array('rules' => $params['rules']));
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $auditDetails);
	
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
	
		$viewModel = new ViewModel(array('rules' => new RulesSet($rules), 'statuses' => $this->getDictionary()->getRuleStatusDictionary(), 'priorities' => $this->getDictionary()->getPriorityDictionary(), 'applications' => $applicationsSet));
		$viewModel->setTemplate('job-queue/web-api/jobqueue-rules-list');
		return $viewModel;
	}

	/**
	 * @param array $options
	 * @return array
	 */
	private function validateJobOptions($options) {
		
		$options = $this->validateArray($options, 'options');
		
		$translatedOptions = array();
		$validateSslDictionary = array_flip($this->getJobqueueMapper()->getValidateSslValues());
		foreach($options as $name => $option) {
			switch($name) {
				case 'validate_ssl':
					if (! in_array($option, $this->getJobqueueMapper()->getValidateSslValues())) {
						throw new \WebAPI\Exception(_t('validate_ssl option must be one of "'. implode('"|"', $this->getJobqueueMapper()->getValidateSslValues()) .'"'), WebAPI\Exception::INVALID_PARAMETER);
					} else {
						$option = $validateSslDictionary[$option];
					}
					break;
				case "queue_id":
					$this->validatePositiveInteger($option, "options[{$name}]");
					$option = intval($option);
					break;
				case "interval":
				case "schedule":
				case "name":
					$this->validateString($option, "options[{$name}]");
					if (strpbrk($option, '<>')) {
						throw new WebAPI\Exception("Name contains invalid characters", WebAPI\Exception::INVALID_PARAMETER);
					}
					
					if ($name == 'name' && !empty($option)) {
						$this->validateStringLength($option, 2, 32, 'name');
					}
					break;
				default:
			}
			$translatedOptions[$name] = $option;
		}
		return $translatedOptions;
	}
	
	/**
	 * @param integer $order
	 * @throws WebAPI\Exception
	 */
	private function validateOrder($order, $allowedColumns) {
		$allowedColumns = array_change_key_case($allowedColumns);
		if (! isset($allowedColumns[strtolower($order)])) {
			throw new WebAPI\Exception(_t('Parameter \'order\' must be one of %s', array(implode(', ', array_keys($allowedColumns)))), WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $order;
	}

	/**
	 * @param mixed $filters
	 * @throws WebAPI\Exception
	 */
	protected function validateFilters($filters) {
		if (! is_array($filters)) {
			throw new WebAPI\Exception(
					_t('Parameter \'filters\' must be an array of filter information'),
					WebAPI\Exception::INVALID_PARAMETER);
		}
	
		if (isset($filters['appId'])) {
			$this->validateApplicationIds($filters['appId']);
		}
	}
	
	private function isJobQueueLoaded() {
		if (is_null($this->jobqueueLoaded)) {
			$components = $this->getExtensionsMapper()->selectExtensions(array('Zend Job Queue'));
			$jobqueue = $components->current(); /* @var $jobqueue \Configuration\ExtensionContainer */
			
			$this->jobqueueLoaded = $jobqueue->isLoaded();
		}
		
		return $this->jobqueueLoaded;
	}
}
