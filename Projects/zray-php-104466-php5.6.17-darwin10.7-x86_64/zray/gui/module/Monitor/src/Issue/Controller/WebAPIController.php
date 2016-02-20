<?php

namespace Issue\Controller;

use ZendServer\Mvc\Controller\WebAPIActionController;

use Issue\Container as IssueContainer;

use Zend\Stdlib\Parameters;

use Acl\License\Exception;

use Zend\Mvc\Controller\ActionController,
	Application\Module,
	MonitorUi\Filter,
	WebAPI,
	Zend\Translator,
	Zend\Stdlib,
	ZendServer\Log\Log,
	Zend\Validator,
	ZendServer\Set,
	ZendServer,
	Zsd\Db\TasksMapper;

use Zend\View\Model\ViewModel;

class WebAPIController extends WebAPIActionController {
	
	public function monitorCountIssuesByPredefinedFilterAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('filters' => array()));
			$this->validateMandatoryParameters($params, array('filterId'));
			$this->validateFilters($params['filters']);
			$this->validateFilterId($params['filterId']);
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$totalCount = $this->getFilteredMapper()->getIssuesCount(array_merge($params['filters'], array('filterId' => $params['filterId'])));
		return array('totalCount' => $totalCount);
	}
	
	public function monitorGetIssueEventGroupsAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('issueId'));
			$this->validateIssueId($params['issueId']);
			$this->validateInteger($params['limit'], 'limit');
			$this->validateInteger($params['offset'], 'offset');
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		try {

			$eventsDbModel =  $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $eventsDbModel \EventsGroup\Db\Mapper */
			
			$eventsGroups = $eventsDbModel->getEventsGroups($params['issueId'], $params['limit'], $params['offset']);
								
			$ids = array();
			foreach ($eventsGroups as $eventsGroup) { /* @var $eventsGroup \EventsGroup\Container */
				$ids[] = $eventsGroup->getEventsGroupId();				
			}
			$events = $eventsDbModel->getEventGroupsData($ids)->toArray();
						
		} catch (ZendServer\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::NO_SUCH_ISSUE);
		}
				
		return array('eventsGroups' => $eventsGroups, 'events' => $events);
	}
	
	public function monitorGetIssueDetailsAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('limit' => 0));
			$this->validateMandatoryParameters($params, array('issueId'));
			$this->validateIssueId($params['issueId']);
			$this->validateInteger($params['limit'], 'limit');
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}

		try {
			
			$issuesDbModel = $this->getLocator()->get('Issue\Db\Mapper'); /* @var $issuesDbModel \Issue\Db\Mapper */
			$eventsDbModel = $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $eventsDbModel \EventsGroup\Db\Mapper */
			
			$issue = $issuesDbModel->getIssue($params['issueId']);
			$issue->setMaxEventGroup($this->getMaxEventGroup($issue->getId()));
			$eventsGroups = $eventsDbModel->getEventsGroups($params['issueId'], $params['limit']);
			
			$mvcIssueData = $this->getFilteredMapper()->getIssueMapper()->getRelevantMvc(array($issue->getId()));
		    if (isset($mvcIssueData[$issue->getId()])) {
			    $issue->setMvcData($mvcIssueData[$issue->getId()]);
			}
			$eventGroup = $eventsGroups->current(); /* @var $eventGroup \EventsGroup\Container */
			$eventsData = $eventsDbModel->getEventGroupsData(array($eventGroup->getEventsGroupId()));
			
			$serverIdstoNames = $this->getServersNames($eventsGroups);			
		} catch (ZendServer\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::NO_SUCH_ISSUE);
		}		
		
		$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
		
		return array('applications' => $applicationsSet, 'issue' => $issue, 'eventsGroups' => $eventsGroups, 'serverIdstoNames'=>$serverIdstoNames, 'totalCount' => $eventsGroups->count(), 'eventsData' => $eventsData);
	}

	public function monitorDeleteIssuesByPredefinedFilterAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('filters' => array(), 'filterId'=>'dummy'));
			$this->validateFilters($params['filters']);
			$this->validateFilterId($params['filterId']);
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$monitorUiModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
		$szDeleted = $monitorUiModel->deleteIssuesByFilter(array_merge($params['filters'], array('filterId' => $params['filterId'])));
		
		$this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_MONITOR_RESET_CACHE, array());
		
		return $this->postProcessDelete($szDeleted);
	}

	public function monitorDeleteIssuesAction() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('issuesIds' => array()));
			$this->validateMandatoryParameters($params, array('issuesIds'));
			$issuesIds = $this->validateArrayNonEmpty($params['issuesIds'], 'issuesIds');
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		try {
			$monitorUiModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
			$szDeleted = $monitorUiModel->deleteIssues($issuesIds);
			
			$this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, TasksMapper::COMMAND_MONITOR_RESET_CACHE, array());
		} catch (ZendServer\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::NO_SUCH_ISSUE);
		}
	
		return $this->postProcessDelete($szDeleted);
	}
	
	public function monitorChangeIssueStatusAction() {
		return $this->forward()->dispatch('IssueWebApi-1_2', array('action' => 'monitorChangeIssueStatus')); /* @var $viewModel \Zend\View\Model\ViewModel */
	}

	/**
	 *
	 * @param ZendServer\Set[\EventsGroup\Container] $eventsGroups
	 */
	protected function getServersNames($eventsGroups) {
		$allServersIds = $this->getServersMapper()->findAllServersNamesByIds();
		$eventGroupServerIds = array();
		foreach($eventsGroups as $eventsGroup) {
			$eventGroupServerIds[$eventsGroup->getServerId()] = 'WILL_BE_OVERWRITTEN';
		}
	
		return array_intersect_key($allServersIds, $eventGroupServerIds); // return intersected ids, values are the serverNames
	}
	
	protected function postProcessDelete($szDeleted) {
		$this->setHttpResponseCode('202', 'Accepted');
		$viewModel = new ViewModel(array('szDeleted' => $szDeleted));
		$viewModel->setTemplate('issue/web-api/monitor-delete-issues');
		return $viewModel;
	}
	
	/**
	 * @param integer $issueId
	 * @throws WebAPI\Exception
	 */
	protected function validateIssueId($issueId) {
		return $this->validateInteger($issueId, 'issueId');
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

	/**
	 * @param string $filterId
	 * @throws WebAPI\Exception
	 */
	protected function validateFilterId($filterId) {
		$filterIdValidator = new Validator\Regex('#^[[:word:] ]+$#');
		if (! $filterIdValidator->isValid($filterId)) {
			throw new WebAPI\Exception(
					_t('Parameter \'filterId\' must be a valid filter identifier'),
					WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	/**
	 * @param mixed $applicationIds
	 * @throws WebAPI\Exception
	 */
	protected function validateApplicationIds($applicationIds) {
		if (! is_array($applicationIds)) {
			throw new WebAPI\Exception(
					_t('Filter \'appId\' must be an array of application IDs'),
					WebAPI\Exception::INVALID_PARAMETER);
		}
		
		/// if not all values are numeric
		if (! array_reduce($applicationIds, function($v, $w) {
				return $v ? is_numeric($w) : $v;
			}, true)) {
			throw new WebAPI\Exception(
					_t('Filter \'filterId\' must be an array of integers'),
					WebAPI\Exception::INVALID_PARAMETER);
		}
	}
	
	
	protected function getLastCodeTracingGroupId(\Issue\Container $issue) {
		if (! $issue->hasTrace()) return '';
		
		$eventsGroups = $this->getMonitorUiModel()->getEventsGroups($issue->getId());
		foreach ($eventsGroups as $eventGroup) {/* @var $eventGroup \EventsGroup\Container */
			if ($eventGroup->hasCodetracing()) {
				return $eventGroup->getEventsGroupId(); // getEventsGroups() returns the group from latest to oldest, hence the first one we find is the latest trace
			}			
		}
	
		return '';
	}
	
	protected function getMaxEventGroup($issueId) {
		$eventsGroups = $this->getLocator()->get('\EventsGroup\Db\Mapper')->getEventsGroups($issueId);
		$maxEventGroup = null;/* @var $maxEventGroup \EventsGroup\Container */
		foreach ($eventsGroups as $eventGroup) {
			$maxEventGroup = $this->getLargerEventGroup($maxEventGroup, $eventGroup);
		}
	
		return $maxEventGroup;
	}
	
	protected function getLargerEventGroup($eventsGroup1, $eventsGroup2) {
		return $eventsGroup2;//@todo - place some logic here
	}
	

	/**
	 * @param integer $order
	 * @throws WebAPI\Exception
	 */
	protected function validateOrder($order) {
		$order = strtolower($order);
		$sortColumns = array_change_key_case($this->getMonitorUiModel()->getSortColumnsDictionary());
		if (! isset($sortColumns[$order])) {
			throw new WebAPI\Exception(
					_t('Parameter \'order\' must be one of %s',
							array(implode(', ', array_keys($this->getMonitorUiModel()->getSortColumnsDictionary())))),
					WebAPI\Exception::INVALID_PARAMETER);
		}
	}
}
