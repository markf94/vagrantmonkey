<?php

namespace JobQueue\Controller;

use Zend\View\Model\ViewModel;
use ZendServer\Log\Log;
use ZendServer\Edition;
use ZendServer\Mvc\Controller\ActionController,
	Application\Module;
use JobQueue\Form\SettingsForm;
use JobQueue\Form\SettingsEventsForm;

class IndexController extends ActionController
{
	/**
	 * @var boolean
	 */
	private $jobqueueLoaded = null;

	public function ListAction() {
		$output = $this->forward()->dispatch('JobQueue', array('action' => 'Index'));
		$output->setTemplate('job-queue/index/index');
		return $output;
	}
	
	public function IndexAction() {
		if (! $this->isAclAllowedEdition('route:JobQueueWebApi')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('job-queue/index/index-marketing');
			return $viewModel;
		}

		$jobqueueView = $this->forward()->dispatch('JobQueueWebApi-1_3', array('action' => 'jobqueueJobsList')); /* @var $jobqueueView \Zend\View\Model\ViewModel */
		$jobqueueView->setTemplate('job-queue/index/index');// Restoring original route
		$jobqueueView->setVariable('perPage', Module::config('list', 'resultsPerPage'));

		/// is the local jobqueue daemon available
		$messageMapper = $this->getLocator()->get('Messages\Db\MessageMapper');
		$edition = new Edition();
		$localJqdOffline= $messageMapper->isDaemonOffline('jqd', $edition->getServerId());
		
		$filterDictionary = $this->getLocator()->get('JobQueue\Filter\Dictionary'); /* @var $filterDictionary \JobQueue\Filter\Dictionary */
		
		$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
		$applications = $deploymentModel->getMasterApplications();
		$applications->setHydrateClass('\Deployment\Application\Container');
		$applicationsDictionary = array();
		foreach ($applications as $app) {
			$applicationsDictionary[$app->getApplicationId()] = $app->getUserApplicationName();
		}
		
		// prepare rules
		$mapper = $this->getLocator()->get('JobQueue\Db\Mapper'); /* @var $mapper \JobQueue\Db\Mapper */
		$schedulingRules = array();
		try {
			foreach ($mapper->getSchedulingRules() as $rule) {
				$schedulingRules[$rule['id']] = $rule['name'];
			}   		
		} catch (\ZendJobQueueException $e) {
			throw new \ZendServer\Exception("No Job Queue data available: " . $e->getMessage()); // will most probably never reach here
		}
		
		// get queues list
		$mapper = $this->getLocator()->get('JobQueue\Queues\Mapper'); /* @var $mapper \JobQueue\Db\Mapper */
		$queues = array();
		try {
			foreach ($mapper->getQueues(array('show_deleted' => true)) as $queue) {
				if (!empty($queue['name'])) {
					$queues[$queue['id']] = $queue['name'];
					
					if ($queue['status'] == \JobQueue\JobQueueInterface::QUEUE_SUSPENDED) {
						$queues[$queue['id']].= ' (suspended)';
					}
				} else {
					$queues[$queue['id']] = '-';
				}
			}   		
		} catch (\ZendJobQueueException $e) {
			throw new \ZendServer\Exception("No Job Queues data available: " . $e->getMessage()); // will most probably never reach here
		}

		$statuses = $filterDictionary->getStatusDictionaryForFiltering();
		// do not display suspended and removed filter statuses
		unset($statuses['Suspended']);
		unset($statuses['Removed']);
		
		$jobqueueView->setVariable('internalFilters', array(	
			'priority' =>	array('name' => 'priority', 'label' => _t('Priority'), 'options' => $filterDictionary->getPriorities()),
			'status' => array('name' => 'status', 'label' => _t('Status'), 'options' => $statuses),
			'app_ids'=> array('name' => 'app_ids', 'label' => _t('Application'), 'options' => $applicationsDictionary, 'noOptionsError' => _t('Currently no deployed/defined applications')),
			'rule_ids' => array('name' => 'rule_ids', 'label' => _t('Scheduling Rule'), 'options' => $schedulingRules, 'noOptionsError' => _t('Currently no defined recurring jobs')),
			'queue_ids' => array('name' => 'queue_ids', 'label' => _t('Queue'), 'options' => $queues, 'noOptionsError' => _t('Currently no queues defined')),
		));
		
		$jobqueueView->setVariable('externalFilters', array(	array('name' => 'timeRange', 'label' => '', 'options' => $filterDictionary->getJQTimeRange(), 'extra' => $filterDictionary->getTimeRanges())));
		$mapper = $this->getLocator('ZendServer\Filter\Mapper');
		$existingFilters = array();
		foreach ($mapper->getByType('job') as $filter) { /* @var $filter \ZendServer\Filter\Filter */
			$existingFilters[$filter->getName()] = array('id' => $filter->getId(),
					'name' => $filter->getName(), 'custom' => $filter->getCustom(), 'data' => $filter->getData());
		}
		
		$jobqueueView->setVariable('existingFilters', $existingFilters);
		$jobqueueView->setVariable('singleServer', $edition->isSingleServer());
		$jobqueueView->setVariable('localJqdOffline', $localJqdOffline);
		
		$jobqueueView->setVariable('jqLoaded', $this->isJobQueueLoaded());
		$jobqueueView->setVariable('pageTitle', 'Jobs');
		$jobqueueView->setVariable('pageTitleDesc', ''); /* Daniel */
		
		return $jobqueueView;
	}
	
	public function JobInfoAction() {
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true);
		
		/* @var $mapper \JobQueue\Db\Mapper */
		$mapper = $this->getLocator()->get('JobQueue\Db\Mapper'); 
//     	$mapper = $this->getLocator()->get('JobQueue\Model\Mapper'); /* @var $mapper \JobQueue\Model\Mapper */
		$dictionary = $this->getLocator()->get('jq_dictionary'); /* @var $dictionary \JobQueue\Filter\Dictionary */
		
		$messageMapper = $this->getLocator()->get('Messages\Db\MessageMapper');
		$edition = new Edition();
		$localJqdOffline= $messageMapper->isDaemonOffline('jqd', $edition->getServerId());
		
		$params = $this->getParameters(array(
					'id' => ''));
		$this->validateMandatoryParameters($params, array('id'));
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$job = $mapper->getJob($params['id']);

		$viewModel->setVariable('job', $job);
		$viewModel->setVariable('priorities', $dictionary->getPriorityDictionary());
		$viewModel->setVariable('applications', $applicationsSet);
		
		// by default set to false the job rule 
		$viewModel->setVariable('jobRule', 0);
		if (isset($job['schedule_id']) && $job['schedule_id']) {
			
			try {
				$rule = $mapper->getSchedulingRule($job['schedule_id']);
			} catch (\Exception $e) {
				// no such rule with $job['schedule_id']
			}
			if (isset($rule) && isset($rule['id'])) {
				$viewModel->setVariable('jobRule', 1);
			}
		}
		$viewModel->setVariable('localJqdOffline', $localJqdOffline);
		$viewModel->setVariable('jqLoaded', $this->isJobQueueLoaded());
		
		return $viewModel;
	}
	
	public function RuleInfoAction() {
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true);
		
		$mapper = $this->getLocator()->get('JobQueue\Model\Mapper'); /* @var $mapper \JobQueue\Model\Mapper */
		$dictionary = $this->getLocator()->get('jq_dictionary'); /* @var $dictionary \JobQueue\Filter\Dictionary */
		
		$params = $this->getParameters(array(
					'id' => ''));
		$this->validateMandatoryParameters($params, array('id'));
		
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		$rule = $mapper->getSchedulingRule($params['id']);
		$viewModel->setVariable('rule', $rule);
		$viewModel->setVariable('priorities', $dictionary->getPriorityDictionary());
		$viewModel->setVariable('applications', $applicationsSet);
		
		return $viewModel;
	}
	
	public function queueInfoAction() {
		$params = $this->getParameters(array('id' => null));
		$this->validateMandatoryParameters($params, array('id'));
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true);
		
		if (!empty($params['id']) && is_numeric($params['id'])) {
			// get the data
			$mapper = $this->getQueuesMapper();
			$queueData = $mapper->getQueue($params['id']);
			$viewModel->setVariable('queueData', $queueData);
			
			// set form fields
			$formFields = $mapper->getQueueFields();
			unset($formFields[array_search('id', $formFields)]);
			unset($formFields[array_search('status', $formFields)]);
			$viewModel->setVariable('formFields', $formFields);
			
			// use form to display data
			$queueForm = $this->getServiceLocator()->get('queueForm');
			foreach ($queueData as $fld => $val) {
				if (!in_array($fld, $formFields)) continue;
				$queueForm->get($fld)->setValue($val);
			}
			
			$viewModel->setVariable('queueForm', $queueForm);
			
			
		}
		
		return $viewModel;
	}
	
	public function queuesAction() {
		/* @var \JobQueue\Db\Mapper */
		$mapper = $this->getQueuesMapper();
		
		/* @var \JobQueue\Form\QueueForm */
		$queueForm = $this->getServiceLocator()->get('queueForm');
		
		return array(
			'pageTitle' => 'Queues',
			'queues' => $mapper->getQueues(),
			'queueForm' => $queueForm,
		);
	}
	
	public function addQueueAction() {
		$queueId = $this->params('queue_id', null);
		
		$queueData = null;
		$mapper = $this->getQueuesMapper();
		if (!is_null($queueId) && is_numeric($queueId)) {
			// take queue data
			foreach($mapper->getQueues() as $queue) {
				if ($queue['id'] == $queueId) {
					$queueData = $queue;
					break;
				}
			}
		} else {
			// take default queue data
			$queueData = $mapper->getQueue(\JobQueue\Db\Mapper::DEFAULT_QUEUE_ID);
			unset($queueData['name']);
		}
		
		/* @var \JobQueue\Form\QueueForm */
		$queueForm = $this->getServiceLocator()->get('queueForm');
		return array(
			'queueId' => $queueId,
			'queueData' => $queueData,
			'queueForm' => $queueForm,
		);
	}
	
	/**
	 * Export queue settings
	 * @return \Zend\Mvc\Controller\Plugin\mixed
	 */
	public function exportQueuesAction() {
		$exportView = $this->forward()->dispatch('JobQueueWebApi-1_10', array('action' => 'jobqueueExportQueues'));
		return $exportView;
	}

	/**
	 * Import queues from ZIP file
	 * @return multitype:Ambigous <object, multitype:, \JobQueue\Form\ImportForm>
	 */
	public function importQueuesAction() {
		$error = false;
		if ($this->getRequest()->isPost()) {
			// receive the file
			try {
				$viewModel = $this->forward()->dispatch('JobQueueWebApi-1_10', array('action' => 'importQueues'));
				return $this->Redirect()->toRoute('default', array('controller' => 'JobQueue', 'action' => 'queues'));
			} catch (\Exception $e) {
				$error = $e->getMessage();
			}
		}
		 
		// display the form
		$importForm = $this->getServiceLocator()->get('importForm');
		
		return array(
			'error' => $error,
			'importForm' => $importForm,
		);
	}
	
	/**
	 * settings page
	 * @return multitype:\JobQueue\Form\SettingsForm
	 */
	public function settingsAction() {
		if (! $this->isAclAllowedEdition('route:JobQueueWebApi')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('job-queue/index/index-marketing');
			return $viewModel;
		}
		
		// check if the server is in a cluster
		// `max_http_jobs_for_entire_cluster` is displayed only for cluster
		$edition = new \ZendServer\Edition();
		$isCluster = !$edition->isSingleServer();
		
		$settingsForm = new SettingsForm(null, $isCluster);
		$settingsEventsForm = new SettingsEventsForm();
		
		$directivesValues = array();
		$requiredDirectives = array(
			'zend_jobqueue.history',
			'zend_jobqueue.history_failed',
			'zend_jobqueue.db_size_completed',
			'zend_jobqueue.db_size_failed',
			'zend_jobqueue.store_job_output',
			'zend_jobqueue.store_only_failed_jobs_output', // new directive 
			'zend_jobqueue.max_job_output_size',
			'zend_jobqueue.job_time_skew_allowed',
			
			'zend_jobqueue.max_http_jobs',
			'zend_jobqueue.max_http_jobs_for_entire_cluster',
		);
		
		// get default email for sending email on event
		// ( [zend_gui.defaultCustomAction] => [zend_gui.defaultEmail] => gregory.c@zend.com )
		$monitorDefaults = $this->getDirectivesMapper()->getDirectivesValues(array(
			'zend_gui.defaultCustomAction',
			'zend_gui.defaultEmail',
		));
		$defaultEmail = $monitorDefaults['zend_gui.defaultEmail'];
		$defaultUrl = $monitorDefaults['zend_gui.defaultCustomAction'];
		
		
		// get directives data
		$jobQueueDirectives = $this->getDirectivesMapper()->selectAllDaemonDirectives('jqd');
		
		foreach ($jobQueueDirectives as $directive) {
			if (in_array($directive->getName(), $requiredDirectives)) {
				$key = str_replace('zend_jobqueue.', '', $directive->getName());
				$directivesValues[$key] = $directive->getFileValue();
			}
		}
		
		if ($directivesValues['store_job_output'] && !$directivesValues['store_only_failed_jobs_output']) {
			$directivesValues['jobs_output'] = '1';
		} elseif ($directivesValues['store_job_output'] && $directivesValues['store_only_failed_jobs_output']) {
			$directivesValues['jobs_output'] = '2';
		} elseif (!$directivesValues['store_job_output'] && !$directivesValues['store_only_failed_jobs_output']) {
			$directivesValues['jobs_output'] = '0';
		} else {
			// should not reach here
			$directivesValues['jobs_output'] = '0';
		}
		
		// get jobQueue monitor events
		$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
		$rules = $mapper->findMonitorRules();
		
		foreach ($rules as $rule) {
			$ruleName = $rule->getName();
			if (stripos($ruleName, 'Job') !== 0) continue;
			
			// get the trigger
			if ($trigger = $rule->getTriggers()) {
				$trigger = current($trigger);
			}
			
			$triggerIsSet = ($trigger instanceof \MonitorRules\Trigger);
			
			// get URL or Email
			$callUrl = '';
			$sendEmail = '';
			$actions = $triggerIsSet ? $trigger->getActions() : array();
			if (!empty($actions)) {
				foreach ($actions as $act) {
					if ($act->getType() == \MonitorRules\Action::TYPE_MAIL) {
						$sendEmail = $act->getSendToAddress();
					} elseif ($act->getType() == \MonitorRules\Action::TYPE_CALLBACK) {
						$callUrl = $act->getUrl();
					}
				}
			}
			
			switch ($ruleName) {
				case 'Job Execution Delay':
					$directivesValues['job_execution_delay_event_enabled'] = $rule->getEnabled() ? 1 : 0;
					$directivesValues['job_execution_delay_event_severity'] = ($triggerIsSet ? $trigger->getSeverity() : 0);
					$directivesValues['job_execution_delay_event_email'] = !empty($sendEmail) ? $sendEmail : $defaultEmail;
					$directivesValues['job_execution_delay_event_email_enabled'] = !empty($sendEmail);
					$directivesValues['job_execution_delay_event_call_url'] = !empty($callUrl) ? $callUrl : $defaultUrl;
					$directivesValues['job_execution_delay_event_call_url_enabled'] = !empty($callUrl);
					break;
				case 'Job Execution Error': 
					$directivesValues['job_execution_error_event_enabled'] = $rule->getEnabled() ? 1 : 0;
					$directivesValues['job_execution_error_event_severity'] = ($triggerIsSet ? $trigger->getSeverity() : 0);
					$directivesValues['job_execution_error_event_email'] = !empty($sendEmail) ? $sendEmail : $defaultEmail;
					$directivesValues['job_execution_error_event_email_enabled'] = !empty($sendEmail);
					$directivesValues['job_execution_error_event_call_url'] = !empty($callUrl) ? $callUrl : $defaultUrl;
					$directivesValues['job_execution_error_event_call_url_enabled'] = !empty($callUrl);
					break;
				case 'Job Logical Failure': 
					$directivesValues['job_logical_error_event_enabled'] = $rule->getEnabled() ? 1 : 0;
					$directivesValues['job_logical_error_event_severity'] = ($triggerIsSet ? $trigger->getSeverity() : 0);
					$directivesValues['job_logical_error_event_email'] = !empty($sendEmail) ? $sendEmail : $defaultEmail;
					$directivesValues['job_logical_error_event_email_enabled'] = !empty($sendEmail);
					$directivesValues['job_logical_error_event_call_url'] = !empty($callUrl) ? $callUrl : $defaultUrl;
					$directivesValues['job_logical_error_event_call_url_enabled'] = !empty($callUrl);
					break;
			}
		   
		}
		
		return array(
			'pageTitle' => 'Settings',
			'directivesValues' => $directivesValues,
			'settingsForm' => $settingsForm,
			'settingsEventsForm' => $settingsEventsForm,
		);
	}
	
	/**
	 * @return \JobQueue\Db\Mapper
	 */
	protected function getQueuesMapper() {
		return $this->getServiceLocator()->get('JobQueue\Queues\Mapper');
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
