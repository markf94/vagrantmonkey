<?php

namespace Issue\Controller;

use ZendServer\Mvc\Controller\WebAPIActionController,
	WebAPI,
	ZendServer,
	Application\Module,
	ZendServer\Set,
	Zend\Validator,
	Zend\Stdlib\Parameters;

class WebAPI12Controller extends WebAPIActionController {

	public function monitorGetIssueDetailsAction() {
		$this->getRequest()->getQuery()->set('limit', 0);
		return $this->forward()->dispatch('IssueWebAPI-1_3', array('action' => 'monitorGetIssueDetails'));
	}
	
	public function monitorGetIssuesByPredefinedFilterAction() {	
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array(
					'filters' => array(), 'limit' => Module::config('list', 'resultsPerPage'),
					'offset' => 0, 'order' => 'date', 'direction' => 'DESC'));
			$this->validateMandatoryParameters($params, array('filterId'));
			$this->validateFilters($params['filters']);
			$this->validateFilterId($params['filterId']);
			$this->validateLimit($params['limit']);
			$this->validateOffset($params['offset']);
			$this->validateOrder($params['order']);
			$this->validateDirection($params['direction']);
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		try {
			$issues = $this->getFilteredMapper()->getIssues(array_merge($params['filters'], array('filterId' => $params['filterId'])), $params['limit'], $params['offset'], $params['order'], $params['direction']);
		} catch (\Exception $e) {
			ZendServer\Log\Log::err(_t('Error in retrieving issues: ') . $e->getMessage());
			throw new WebAPI\Exception(_t('Error in retrieving issues. For more details see the UI log.'), WebAPI\Exception::NOT_SUPPORTED_BY_EDITION);
		}
		$newIssues = array();
			
		$issueIds = array();
		foreach ($issues as $issue) { /* @var $issue \Issue\Container */
			$issueIds[] = $issue->getId();
		}
			
		$eventsMapper = $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $eventsMapper \EventsGroup\Db\Mapper */
		$orderedLastEventsResults = array();
		$lastEventsResults = $eventsMapper->getIssuesLastEventGroupData($issueIds);
			
		foreach ($lastEventsResults as $lastEventsResult) { /* @var $lastEventsResult \EventsGroup\Container */
			$orderedLastEventsResults[$lastEventsResult->getIssueId()] = $lastEventsResult->toArray();
		}
			
		$lastEvents = new Set($orderedLastEventsResults, '\EventsGroup\Container');

		foreach ($issues as $issue) { /* @var $issue \Issue\Container */
			$maxEventGroup = $lastEvents[$issue->getId()]; /* @var $maxEventGroup \EventsGroup\Container */
			$issue->setMaxEventGroup($maxEventGroup);
			if ($maxEventGroup->hasCodetracing()) {
				$issue->setCodeTracingEventGroupId($maxEventGroup->getEventsGroupId());
			} else {
				$issue->setCodeTracingEventGroupId('');
			}
			
			$newIssues[] = $issue;
		}
			
		$issues = new Set($newIssues, null);
	
		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applicationsSet = $deploymentModel->getAllApplicationsInfo();
		$applicationsSet->setHydrateClass('\Deployment\Application\InfoContainer');
	
		return array('applications' => $applicationsSet, 'issues' => $issues, 'limitCount' => count($issues));
	}
	
	public function monitorChangeIssueStatusAction() {		
		try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('issueId', 'newStatus'));
			$issueId = $this->validateInteger($params['issueId'], 'issueId');
			$newStatus = $this->validateStringNonEmpty($params['newStatus'], 'newStatus');
			$this->validateNewStatus($newStatus);
		} catch (ZendServer\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
				
		$this->getRequest()->setPost(new Parameters(array('issuesIds'=>array($issueId))));
		return $this->forward()->dispatch('IssueWebApi-1_3', array('action' => 'monitorDeleteIssues'));
	}	

	protected function validateNewStatus($newStatus) {
		if (preg_match('@^Open$@i', $newStatus)) {
			throw new WebAPI\Exception('Parameter newStatus no longer accepts the Open value', WebAPI\Exception::PARAMETER_VALUE_NOT_SUPPORTED_BY_EDITION);
		}
	
		return $this->validateAllowedValues($newStatus, 'newStatus', array('Closed', 'Ignored'));
	}
	
	
	protected function validateRuleNames($ruleNames) {
	    $monitorRulesMapper = $this->getServiceLocator()->get('MonitorRules\Model\Mapper');
	    $rules = $monitorRulesMapper->getRuleIdsFromNames($ruleNames);
	    
	    $foundRuleNames = array_map(function($elem) { return $elem['NAME']; }, $rules);
	    if (!is_array($foundRuleNames)) $foundRuleNames = array();
	    
	    $notFoundRuleNames = array_diff($ruleNames, $foundRuleNames);
	    $notFoundRuleNamesCount = count($notFoundRuleNames);
	    
	    if ($notFoundRuleNamesCount > 1) {
	        $lastBadRuleName = array_pop($notFoundRuleNames);
	        throw new WebAPI\Exception(
	            'Rule names "' . implode('", "', $notFoundRuleNames).'" and "'.$lastBadRuleName.'" do not exist',
	            WebAPI\Exception::INVALID_PARAMETER);
	    } elseif ($notFoundRuleNamesCount > 0) {
	        $badRuleName = array_pop($notFoundRuleNames);
	        throw new WebAPI\Exception(
	            'Rule name "'.$badRuleName.'" does not exist',
	            WebAPI\Exception::INVALID_PARAMETER);
	    }
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
		
		// check rule names - return error if not all supplied rule names exist in the DB
		if (isset($filters['ruleNames'])) {
		    $this->validateRuleNames($filters['ruleNames']);
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
