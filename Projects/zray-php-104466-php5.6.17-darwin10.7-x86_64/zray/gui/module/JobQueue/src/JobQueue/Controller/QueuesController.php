<?php

namespace JobQueue\Controller;

use Zend\View\Model\ViewModel;
use ZendServer\Log\Log;
use ZendServer\Edition;
use ZendServer\Mvc\Controller\ActionController,
	Application\Module;
use JobQueue\Form\SettingsForm;
use Zend\View\Model\Zend\View\Model;

class QueuesController extends ActionController
{
	/**
	 * @var boolean
	 */
	private $jobqueueLoaded = null;

	/**
	 * Page with queues list
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
	public function indexAction() {
		if (! $this->isAclAllowedEdition('route:JobQueueWebApi')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('job-queue/index/index-marketing');
			return $viewModel;
		}
		
		/* @var \JobQueue\Db\Mapper */
		$mapper = $this->getQueuesMapper();
	
		/* @var \JobQueue\Form\QueueForm */
		$queueForm = $this->getServiceLocator()->get('queueForm');
	
		return array(
			'pageTitle' => 'Queues',
			'isJobQueueLoaded' => $this->isJobQueueLoaded(),
			'queues' => $mapper->getQueues(),
			'queueForm' => $queueForm,
		);
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
			unset($formFields[array_search('running_jobs_count', $formFields)]);
			unset($formFields[array_search('pending_jobs_count', $formFields)]);
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
	
	// addAction processes both add and edit actions, so just redirect
	public function editAction() {
		$viewModel = new ViewModel();
		$viewModel->setTemplate('job-queue/queues/add');
		$viewModel->setVariables($this->addAction());
		return $viewModel;
	}
	
	/**
	 * Add new queue page
	 * @return multitype:Ambigous <\Zend\Mvc\Controller\Plugin\Params, mixed> Ambigous <\Zend\ServiceManager\object, \Zend\ServiceManager\array> Ambigous <NULL, \JobQueue\Db\Ambigous>
	 */
	public function addAction() {
		if (! $this->isAclAllowedEdition('route:JobQueueWebApi')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('job-queue/index/index-marketing');
			return $viewModel;
		}
		
		$queueId = $this->params()->fromQuery('queue_id', null);
		
		$queueData = null;
		$mapper = $this->getQueuesMapper();
		
		/* @var \JobQueue\Form\QueueForm */
		$queueForm = $this->getServiceLocator()->get('queueForm');
		
		if (!is_null($queueId) && is_numeric($queueId)) {
			$pageTitle = 'Update Queue';
			$queueData = $mapper->getQueue($queueId);
		} else {
			$pageTitle = 'Add Queue';
			// take default queue data
			$queueData = $mapper->getQueue(\JobQueue\Db\Mapper::DEFAULT_QUEUE_ID);
			unset($queueData['name']);
		}
		
		// defaults fallback
		foreach ($queueForm->getElements() as $element) {
			$elementName = $element->getName();
			if (!isset($queueData[$elementName])) {
				$queueData[$elementName] = $element->getValue();
			}
		}
		
		return array(
			'pageTitle' => $pageTitle,
			'queueId' => $queueId,
			'queueData' => $queueData,
			'queueForm' => $queueForm,
			
			'maxHttpJobs' => intval($this->getDirectivesMapper()->getDirectiveValue('zend_jobqueue.max_http_jobs')),
		);
	}
	
	/**
	 * Export queue settings
	 * @return \Zend\Mvc\Controller\Plugin\mixed
	 */
	public function exportAction() {
		$exportView = $this->forward()->dispatch('JobQueueWebApi-1_10', array('action' => 'jobqueueExportQueues'));
		return $exportView;
	}

	/**
	 * Import queues from ZIP file
	 * @return multitype:Ambigous <object, multitype:, \JobQueue\Form\ImportForm>
	 */
	public function importAction() {
		if (! $this->isAclAllowedEdition('route:JobQueueWebApi')) {
			$viewModel = new ViewModel();
			$viewModel->setTemplate('job-queue/index/index-marketing');
			return $viewModel;
		}
		
		$error = false;
		if ($this->getRequest()->isPost()) {
			// receive the file
			try {
				$importError = false;
				try {
					$viewModel = $this->forward()->dispatch('JobQueueWebApi-1_10', array('action' => 'jobqueueImportQueues'));
				} catch (\Exception $e) {
					$importError = $e->getMessage();
				}
				
				$urlPlugin = $this->plugin('url');
				
				if ($importError === false) {
					$url = $urlPlugin->fromRoute('default', array('controller' => 'Queues', 'action' => 'index'));
					$url.= '?notification_message='.urlencode('Queues imported successfully');
				} else {
					$url = $urlPlugin->fromRoute('default', array('controller' => 'Queues', 'action' => 'import'));
					$url.= '?alert_message='.urlencode($importError);
				}
				
				return $this->Redirect()->toUrl($url);
			} catch (\Exception $e) {
				$error = $e->getMessage();
			}
		}
		 
		// display the form
		$importForm = $this->getServiceLocator()->get('importForm');
		
		return array(
			'pageTitle' => 'Import Queues',
			'error' => $error,
			'importForm' => $importForm,
		);
	}
	
	/**
	 * @return \JobQueue\Db\Mapper
	 */
	protected function getQueuesMapper() {
		return $this->getServiceLocator()->get('JobQueue\Queues\Mapper');
	}
	
	/**
	 * @brief check if `Zend Job Queue` is loaded
	 * @return bool
	 */
	protected function isJobQueueLoaded() {
		if (is_null($this->jobqueueLoaded)) {
			$components = $this->getExtensionsMapper()->selectExtensions(array('Zend Job Queue'));
			$jobqueue = $components->current(); /* @var $jobqueue \Configuration\ExtensionContainer */
				
			$this->jobqueueLoaded = $jobqueue->isLoaded();
		}
	
		return $this->jobqueueLoaded;
	}
	
}
