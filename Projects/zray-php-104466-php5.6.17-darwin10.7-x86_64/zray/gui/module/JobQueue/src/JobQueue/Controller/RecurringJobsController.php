<?php
namespace JobQueue\Controller;

use Zend\View\Model\ViewModel;
use JobQueue\Model\RecurringJobSchedule;

use Zend\Json\Json;

use JobQueue\Model\RecurringJob;

use ZendServer\Log\Log;
use JobQueue\Model\RulesSet;
use ZendServer\Mvc\Controller\ActionController,
Application\Module;
use ZendServer\Edition;
use ZendServer\Exception;
use ZendServer\Configuration\Directives\Translator;

class RecurringJobsController extends ActionController
{
	/**
	 * @var boolean
	 */
	private $jobqueueLoaded = null;
	
	public function IndexAction() {
		if (! $this->isAclAllowedEdition('route:JobQueueWebApi')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('job-queue/recurring-jobs/index-marketing');
			return $viewModel;
		}
		
		/// is the local jobqueue daemon available
		$messageMapper = $this->getLocator()->get('Messages\Db\MessageMapper');
		$edition = new Edition();
		$localJqdOffline= $messageMapper->isDaemonOffline('jqd', $edition->getServerId());
		
		
		$mapper = $this->getLocator()->get('JobQueue\Db\Mapper'); /* @var $mapper \JobQueue\Db\Mapper */

		$params = $this->getParameters(array(
			'limit' => Module::config('list', 'resultsPerPage'),
			'offset' => 0,
			'order' => 'creationTime',
			'direction' => 'DESC'
		));
		
		// filters area
		/* @var $vhostDictionary \JobQueue\Filter\Dictionary */
		$jqDictionary = $this->getLocator()->get('JobQueue\Filter\Dictionary'); 
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applications = $deploymentModel->getMasterApplications();
		$applications->setHydrateClass('\Deployment\Application\Container');
		
		$applicationsDictionary = array();
		foreach ($applications as $app) {
			$applicationsDictionary[$app->getApplicationId()] = $app->getUserApplicationName();
		}
		
		// get queues list
		/* @var \JobQueue\Db\Mapper */
    	$mapper = $this->getLocator()->get('JobQueue\Queues\Mapper');
    	$queues = array();
		$queuesData = array();
    	try {
    		foreach ($mapper->getQueues(array('show_deleted' => true)) as $queue) {
				// store queue data
    			$queuesData[$queue['id']] = $queue;
				
				// get queue name (add `suspended` if needed)
    			$queues[$queue['id']] = '-';
				if (isset($queue['name']) && !empty($queue['name'])) {
					$queues[$queue['id']] = $queue['name'];
					if ($queue['status'] == \JobQueue\JobQueueInterface::QUEUE_SUSPENDED) {
						$queues[$queue['id']].= ' (suspended)';
					}
				}
    		}
    	} catch (\ZendJobQueueException $e) {
    		throw new \ZendServer\Exception("No Job Queues data available: " . $e->getMessage()); // will most probably never reach here
    	}
		
		$internalFilters = array(
			'status' => array('name' => 'status', 'label' => _t('Status'), 'options' => $jqDictionary->getRuleDictionaryForFiltering()),
			'applicationIds'=> array('name' => 'applicationIds', 'label' => 'Application', 'options' => $applicationsDictionary, 'noOptionsError' => _t('Currently no deployed/defined applications')),
			'queue_ids' => array('name' => 'queue_ids', 'label' => _t('Queue'), 'options' => $queues, 'noOptionsError' => _t('Currently no queues defined')),
		);
	
		$externalFilters = array();
		 
		$mapper = $this->getLocator('ZendServer\Filter\Mapper');
		$existingFilters = array();
		foreach ($mapper->getByType('job-rule') as $filter) { /* @var $filter \ZendServer\Filter\Filter */
			$existingFilters[$filter->getName()] = array('id' => $filter->getId(),
					'name' => $filter->getName(), 'custom' => $filter->getCustom(), 'data' => $filter->getData());
		}
		
		return array(
					'pageTitle' => 'Recurring Jobs',
					'pageTitleDesc' => '',  /* Daniel */
					'perPage' => Module::config('list', 'resultsPerPage'),
					'localJqdOffline' => $localJqdOffline,
					'internalFilters' => $internalFilters,
					'externalFilters' => $externalFilters,
					'existingFilters' => $existingFilters,
					'jqLoaded'		  => $this->isJobQueueLoaded(),
					'queuesData'	  => $queuesData,
					'statusesList'	  => $jqDictionary->getRuleStatusDictionary(),
		);
	}
	
	public function createAction() {
		
		/// is the local jobqueue daemon available
		$messageMapper = $this->getLocator()->get('Messages\Db\MessageMapper');
		$edition = new Edition();
		if ($messageMapper->isDaemonOffline('jqd', $edition->getServerId())) {
			throw new Exception('Job Queue service is currently offline, cannot add new recurring jobs'); 
		}
		
		$queuesMapper = $this->getLocator()->get('JobQueue\Queues\Mapper');
		
		$globalCertValidate = Translator::getRealFileValue($this->getDirectivesMapper()->getDirective('zend_jobqueue.validate_ssl')) ? true : false;
		$sslAvailable = Translator::getRealFileValue($this->getDirectivesMapper()->getDirective('zend_jobqueue.enable_https'));
		
		$this->getEvent()->getRouteMatch()->setParam('action', 'form');
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		$dictionary = $this->getLocator('jq_dictionary'); /* @var $dictionary \JobQueue\Filter\Dictionary */
		return array('rule' => null, 'parsedSchedule' => new RecurringJobSchedule(''),
					'statuses' => $dictionary->getRuleStatusDictionary(),
					'priorities' => $dictionary->getPriorityDictionary(),
					'applications' => $applicationsSet,
					'globalCertValidate' => $globalCertValidate,
					'sslAvailable' => $sslAvailable,
		            'queues' => $queuesMapper->getQueues(),
		);
	}
	
	public function updateAction() {
		/// is the local jobqueue daemon available
		$messageMapper = $this->getLocator()->get('Messages\Db\MessageMapper');
		$edition = new Edition();
		if ($messageMapper->isDaemonOffline('jqd', $edition->getServerId())) {
			throw new Exception('Job Queue service is currently offline, cannot edit recurring jobs'); 
		}
		
		$params = $this->getParameters(array('jobId' => 0));
		$jobId = $params['jobId'];
		
		$mapper = $this->getLocator()->get('JobQueue\Model\Mapper'); /* @var $mapper \JobQueue\Model\Mapper */
		$rule = $mapper->getSchedulingRule($jobId);
		
		$queuesMapper = $this->getLocator()->get('JobQueue\Queues\Mapper');
		
		// find queue id by name
		$queueId = $queuesMapper->getQueueIdByName($rule['queue_name']);
		if (!$queueId) {
		    throw new Exception('JobQueue rule is related to invalid queue');
		}
		$rule['queue_id'] = $queueId;
				
		$recurringJob = new RecurringJob($rule);
		$this->getEvent()->getRouteMatch()->setParam('action', 'form');
		
		$sslAvailable = Translator::getRealFileValue($this->getDirectivesMapper()->getDirective('zend_jobqueue.enable_https'));

		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		$globalCertValidate = Translator::getRealFileValue($this->getDirectivesMapper()->getDirective('zend_jobqueue.validate_ssl')) ? true : false;
		$dictionary = $this->getLocator('jq_dictionary'); /* @var $dictionary \JobQueue\Filter\Dictionary */
		
		
		return array('rule' => $rule, 'parsedSchedule' => $recurringJob->getParsedSchedule(),
					'statuses' => $dictionary->getRuleStatusDictionary(),
					'priorities' => $dictionary->getPriorityDictionary(),
					'applications' => $applicationsSet,
					'globalCertValidate' => $globalCertValidate,
					'sslAvailable' => $sslAvailable,
		            'queues' => $queuesMapper->getQueues(),
		            'queueId' => $queueId,
		);
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