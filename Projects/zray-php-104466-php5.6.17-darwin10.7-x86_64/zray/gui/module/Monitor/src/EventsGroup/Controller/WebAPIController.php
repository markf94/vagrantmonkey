<?php

namespace EventsGroup\Controller;

use ZendServer\Exception;

use ZendServer\Mvc\Controller\WebAPIActionController;

use Zend\Mvc\Controller\ActionController,
	Application\Module,
	ZendServer,
	WebAPI,
	ZendServer\Text,
	Zend\Validator\StringLength,
	ZendServer\Log\Log;

class WebAPIController extends WebAPIActionController
{
	public function monitorGetBacktraceFileAction() {
		$this->isMethodGet();
		
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('eventsGroupId', 'backtraceNum'));
		
		$groupId = $params['eventsGroupId'];
		$backtraceNum = $params['backtraceNum'];
		
		$retriever = $this->getLocator('EventsGroup\BacktraceSourceRetriever'); /* @var $retriever \EventsGroup\BacktraceSourceRetriever */
		try {
			$source = $retriever->getHighlightedSource($groupId, $backtraceNum);
			$rowToHighLight = $retriever->getHighlightedLine($groupId, $backtraceNum);
		} catch (Exception $e) {
			Log::warn($e->getMessage());
			Log::debug($e);
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		return array('sourcePayload' => base64_encode($source), 'highlightLine' => $rowToHighLight);
	}
		
	/**
	 *
	 * @param string $requestUid
	 * @reutrn string
	 * @throws WebAPI\Exception
	 */
	private function getRequestTrace($requestUid) {
		try {
			$tracePath = $this->getMonitorUiModel()->prepareRequestTrace($requestUid);
		} catch (\Exception $e) {
			throw new WebAPI\Exception(_t('Trace not found: %s', array($e->getMessageObject())), WebAPI\Exception::NO_SUCH_TRACE);
		}
		
		$traceId = $this->getCodetracingModel()->getDumpIdByFile($tracePath);
		Log::debug("found traceId '{$traceId}' from request '{$requestUid}' (used path '{$tracePath}')");
		
		return $traceId;
	}	
	
	public function monitorGetEventGroupDetailsAction()
	{
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('limit' => 0));
			
			/// backwards compatibility issue for version 1.2, cover both parameter names
			if ((! isset($params['eventsGroupId'])) && (isset($params['eventGroupId']))) {
				$params['eventsGroupId'] = $params['eventGroupId'];
			}
			
			$this->validateMandatoryParameters($params, array('eventsGroupId'));
			$this->validateEventsGroupId($params['eventsGroupId']);
			$this->validateLimit($params['limit']);
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$monitorEventDb = $this->getMonitorEventsMapper();
			
		try {
			$event = $monitorEventDb->getEventGroupData($params['eventsGroupId']);
		} catch (ZendServer\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::NO_SUCH_EVENTGROUP);
		}
		$monitorUiModel = $this->getMonitorUiModel();
		$issueDetails = $monitorUiModel->getIssue($event->getIssueId());
		$eventsGroups = $monitorUiModel->getEventsGroups($event->getIssueId());
		$eventsGroup = $eventsGroups[$params['eventsGroupId']];
		
		return array('issue' => $issueDetails, 'event' => $event, 'eventsGroup' => $eventsGroup);
	}	
	
	/**
	 *
	 * @param integer $limit        	
	 * @throws WebAPI\Exception
	 */
	protected function validateLimit($limit) {
		$this->validateInteger($limit, 'limit');
	}
	
	/**
	 * @param integer $eventsGroupId        	
	 * @throws WebAPI\Exception
	 */
	protected function validateEventsGroupId($eventsGroupId) {
		$this->validateInteger($eventsGroupId, 'eventsGroupId');
	}
	
	/**
	 * @return \MonitorUi\Model\Model
	 */
	protected function getMonitorUiModel() {
		return $this->getLocator()->get('MonitorUi\Model\Model');
	}
	
	/*
	 * @return \Monitor\Db\Mapper
	 */
	protected function getMonitorEventsMapper() {
		return $this->getLocator()->get('EventsGroup\Db\Mapper');
	}	
}
