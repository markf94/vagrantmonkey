<?php

namespace Issue\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Issue\Filter\Dictionary,
	Application\Module,
	ZendServer\Exception,
    ZendServer\Filter\Filter;

class ListController extends ActionController {
	public function indexAction() {
		$params = $this->getParameters(array('filterId' => 0));
		$this->getRequest()->getQuery()->set('filterId', $params['filterId']);
		
		$filterDictionary = $this->getLocator()->get('Issue\Filter\Dictionary');/* @var $filterDictionary \Issue\Filter\Dictionary */

		$deploymentModel = $this->getLocator()->get('Deployment\FilteredAccessMapper'); /* @var $deploymentModel \Deployment\FilteredAccessMapper */
		$applications = $deploymentModel->getMasterApplications();
		$applications->setHydrateClass('\Deployment\Application\Container');
		
		$applicationsDictionary = array();
		foreach ($applications as $app) {
			$applicationsDictionary[$app->getApplicationId()] = $app->getUserApplicationName();
		}
		
		$mapper = $this->getLocator()->get('MonitorRules\Model\Mapper'); /* @var $mapper \MonitorRules\Model\Mapper */
		$ruleNames = $mapper->getRuleNames();
		$ruleNamesArray = array();
		foreach($ruleNames as $name) {
			$ruleNamesArray[$name['NAME']] = $name['NAME'];
		}
		
		$internalFilters = array(	'severities' =>	array('name' => 'severities', 'label' => 'Severity', 'options' => $filterDictionary->getIssueSeverities()),
														  'eventTypes' => array('name' => 'eventTypes', 'label' => 'Event Type', 'options' => $filterDictionary->getIssueEventGroups()),
														  'ruleNames' => array('name' => 'ruleNames', 'label' => 'Rule Names', 'options' => $ruleNamesArray),
														  'applicationIds'=> array('name' => 'applicationIds', 'label' => 'Application', 'options' => $applicationsDictionary, 'noOptionsError' => _t('Currently no deployed/defined applications')),);
		
		$externalFilters = array(	array('name' => 'timeRange', 'label' => _t('Filter events by time range:'),
				'options' => $filterDictionary->getIssueTimeRange(), 'extra' => $filterDictionary->getTimeRanges(),
		));
		
		$mapper = $this->getLocator('ZendServer\Filter\Mapper');
		$existingFilters = array();
		foreach ($mapper->getByType('issue') as $filter) { /* @var $filter \ZendServer\Filter\Filter */
			$existingFilters[$filter->getName()] = array('id' => $filter->getId(),
					'name' => $filter->getName(), 'custom' => $filter->getCustom(), 'data' => $filter->getData());
		}
				
		return array('pageTitle' => 'Events',
					 'pageTitleDesc' => '',  /* Daniel */
					 'perPage' => Module::config('list', 'resultsPerPage'),
					 'internalFilters' => $internalFilters,
					 'externalFilters' => $externalFilters,
					 'existingFilters' => $existingFilters,
					'timeout'	=> Module::config('studioIntegration', 'zend_gui', 'studioClientTimeout'),
		);
	}
	
	public function appIconAction() {
		$params = $this->getRequest()->getQuery(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$id = isset($params['id']) ? $params['id'] : '';
		if (empty($id)) {
			header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
			exit;
		}
		
		try {
			$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
			$applicationsSet = $deploymentModel->getMasterApplication($id);
			$applicationsSet->setHydrateClass('\Deployment\Application\Container'); /* @var $applicationsSet \Deployment\Application\Container */
	
			$image = $applicationsSet->current()->getLogo();
		} catch (Exception $e) {
			header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
			exit;
		}
		
		if (empty($image)) {
			header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
			exit;
		}
		
		header('content-type: image/gif');
		echo $image;
		exit;
	}
}
