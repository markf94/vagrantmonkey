<?php

namespace EventsGroup\Controller;

use ZendServer\Log\Log;

use ZendServer\Exception;

use ZendServer\Mvc\Controller\ActionController,
	Zend\View\Model\ViewModel;

class EventsGroupController extends ActionController {
	public function detailsAction() {
		
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$groupId = $request->getQuery()->get('eventsGroupId');

		$monitorEventsModel = $this->getLocator()->get('EventsGroup\Db\Mapper'); /* @var $monitorEventsModel \EventsGroup\Db\Mapper */
		$monitorIssuesModel = $this->getLocator()->get('Issue\Db\Mapper');
		$eventGroupData = $monitorEventsModel->getEventGroupData($groupId);
		
		$eventsGroup = $monitorEventsModel->getEventsGroup($groupId);
		
		
		
		$viewModel = new ViewModel(array('eventsGroup' => $eventsGroup, 'event' => $eventGroupData));
		$viewModel->setTerminal(true); // only render this view model, no layout is used
		return $viewModel;
	}
	
	public function highlightFileAction() {
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$groupId = $request->getQuery()->get('eventsGroupId');
		$backtraceNum = $request->getQuery()->get('backtraceNum');
		
		$retriever = $this->getLocator('EventsGroup\BacktraceSourceRetriever'); /* @var $retriever \EventsGroup\BacktraceSourceRetriever */
		try {
			$source = $retriever->getHighlightedSource($groupId, $backtraceNum);
			$rowToHighLight = $retriever->getHighlightedLine($groupId, $backtraceNum);
		} catch (Exception $e) {
			Log::warn($e->getMessage());
			Log::debug($e);
			
			$source = '';
			$rowToHighLight = 0;
		}
		
		$viewModel = new ViewModel(array('source' => $source, 'rowToHighLight' => $rowToHighLight));
		$viewModel->setTerminal(true); // only render this view model, no layout is used
		return $viewModel;
	}
}
