<?php
namespace Issue\Controller;

use ZendServer\Set;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module as applicationModule,
	MonitorUi\Filter,
	Monitor\Module,
	ZendServer\Exception,
	ZendServer\Log\Log;

class IssueController extends ActionController {
	
	protected $serversData = array();
	
	public function indexAction() {
		$this->getLocator('Navigation')->findByLabel('Events')->setActive(true);

		try {
			$params = $this->getParameters(array());
			$this->validateMandatoryParameters($params, array('issueId'));
			$this->validateIssueId($params['issueId']);
		} catch (\Exception $e) {
			Log::err(__METHOD__ . ": input validation failed");
			Log::debug($e);
			throw $e;
		}
		
		$monitorIssuesModel = $this->getLocator()->get('Issue\Db\Mapper'); /* @var $monitorIssuesModel \Issue\Db\Mapper */
		$issue = $monitorIssuesModel->getIssue($params['issueId']);
		$mvcIssueData = $monitorIssuesModel->getRelevantMvc(array($issue->getId()));
		if (isset($mvcIssueData[$issue->getId()])) {
		    $issue->setMvcData($mvcIssueData[$issue->getId()]);
		}
		
		$eventsGroupsLimit = 20;
		
		$monitorEventsModel = $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $monitorEventsModel \EventsGroup\Db\Mapper */
		
		$eventsGroups = $monitorEventsModel->getEventsGroups($params['issueId'], $eventsGroupsLimit);

		$applicationIds = $this->removeDummyAppId(array($issue->getApplicationId()));
		if ($applicationIds) {
			$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
			$applicationsSet = $deploymentModel->getMasterApplicationsByIds($applicationIds);
			$applicationsSet->setHydrateClass('\Deployment\Application\Container');
		} else {
			$applicationsSet = new Set(array());
		}
		
		$eventsGroupsData = $serversIds = array();
		
		$this->serversData = $this->getServersMapper()->findAllServersNamesByIds();
				
		$hasEmail = $this->hasEmail($eventsGroups);
		$hasCustomAction = $this->hasCustomAction($eventsGroups);
		
		$eventsGroups->rewind();
		$data = $monitorEventsModel->getEventGroupData(
						$eventsGroups->current()->getEventsGroupId());
		$eventsGroupsData = array($data);
		
		// get IDE configurations
		$ideConfigMapper = $this->getLocator()->get('StudioIntegration\Mapper');
		$ideConfig = $ideConfigMapper->getConfiguration();
		
		return array('issue' => $issue, 
				'eventsGroups' => $eventsGroups, 
				'hasEmail' => $hasEmail,
				'hasCustomAction' => $hasCustomAction,
				'events' => $eventsGroupsData, 
				'applications' => $applicationsSet, 
				'serversIds' => $this->serversData,
				'eventsGroupsLimit' => $eventsGroupsLimit,
				'alternateServer' => applicationModule::config('studioIntegration', 'alternateDebugServer'),
				'timeout'	=> applicationModule::config('studioIntegration', 'zend_gui', 'studioClientTimeout'),
		        'ideConfig' => $ideConfig,
		);
	}
	
	protected function removeDummyAppId($applicationIds) {
		foreach ($applicationIds as $idx=>$applicationId) {
			if ($applicationId == -1) {
				unset($applicationIds[$idx]);
			}
		}
		
		return $applicationIds;
	}
	
	/**
	 * @param integer $issueId
	 * @throws WebAPI\Exception
	 */
	protected function validateIssueId($issueId) {
		$issueIdValidator = new \Zend\Validator\Digits();
		if (! $issueIdValidator->isValid($issueId)) {
			throw new Exception(
					_t('Parameter \'issueId\' must be an integer'),
					Exception::INVALID_PARAMETER);
		}
	}
	
	/**
	 * @param \ZendServer\Set $eventsGroups
	 * @return boolean
	 */
	protected function hasEmail($eventsGroups) {
		foreach($eventsGroups as $eventsGroupId => $eventsGroup) { /* @var $eventsGroup \Monitor\Db\Container */
			
			if ($eventsGroup->getEmailAction()) {
				return true;
			}
		}
	
		return false;
	}
	
	/**
	 * @param \ZendServer\Set $eventsGroups
	 * @return boolean
	 */
	public function hasCustomAction($eventsGroups) {
		foreach($eventsGroups as $eventsGroupId => $eventsGroup) { /* @var $eventsGroup \Monitor\Db\Container */
			if ($eventsGroup->getUrlAction()) {
				return true;
			}
		}
	
		return false;
	}
}
