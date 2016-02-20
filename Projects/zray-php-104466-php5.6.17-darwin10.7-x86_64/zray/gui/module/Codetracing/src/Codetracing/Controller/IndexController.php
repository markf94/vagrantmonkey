<?php
namespace Codetracing\Controller;
use Codetracing\Trace\AmfFileRetriever;

use ZendServer\Exception;

use Codetracing\TraceFileContainer;
use Application\Module;


use ZendServer\Mvc\Controller\ActionController,
ZendServer\Log\Log,
Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;

class IndexController extends ActionController {
    const DEVELOPMENT_MAX_DISK  = 500;
    const PRODUCTION_MAX_DISK   = 1000;
    
	public function indexAction() {
	    $limit = Module::config('list', 'resultsPerPage');
	    $traceMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceMapper \Codetracing\TraceFilesMapper */
	    
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$serversSet = $serversMapper->findAllServers();
		
		$components = $this->getExtensionsMapper()->selectExtensions(array('Zend Code Tracing'));
		$codetracing = $components->current(); /* @var $codetracing \Configuration\ExtensionContainer */
		
		$output = new ViewModel();
		$output->setVariable('servers', $serversSet);
		$output->setVariable('limit', $limit);
		$output->setVariable('codetracingDisabled', !$codetracing->isLoaded());
		$output->setVariable('pageTitle', 'Code Tracing');
		$output->setVariable('pageTitleDesc', ''); /* Daniel */
		
		return $output;
	}
	
	
	public function AMFDataAction() {
		$traceFile = $this->getEvent()->getRouteMatch()->getParam('traceFile');
		$this->getRequest()->getQuery()->traceFile = $traceFile;
		$output = $this->forward()->dispatch('CodetracingWebApi-1_3', array('action' => 'codetracingDownloadTraceFile', 'traceFile' => $traceFile));
		$this->getEvent()->setParam('do-not-compress', true);
		return $output;
	}
	
	public function detailsAction() {
		
		$traceFileId = $this->getRequest()->getQuery()->get('traceId', 0);
		$eventsGroupId = $this->getRequest()->getQuery()->get('eventsGroupId', 0);
		
		$traceMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceMapper \Codetracing\TraceFilesMapper */
		$monitorModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorModel \MonitorUi\Model\Model */

		if ($eventsGroupId != 0) {
			$eventsGroupId = $monitorModel->getEventGroupData($eventsGroupId);
			$codetracing = $eventsGroupId->getCodeTracingPath();
			$traceIdInfo = AmfFileRetriever::extractTraceIdFromPath($codetracing);
			$traceFileId = $traceIdInfo[0];
		}
		
		if ($traceFileId != 0) {
			
			$traceRow = $traceMapper->findCodetraceById($traceFileId);
			
			if (! $traceRow) {
				throw new Exception(_t('Requested trace was not found'));
			}
			
			$traceRow = new TraceFileContainer($traceRow);
			
			$eventsGroupMapper = $this->getLocator()->get('EventsGroup\Db\Mapper');
			$result = $eventsGroupMapper->getEventGroupByTraceFile($traceRow->getId());
			if (isset($result[0]) && $row = $result[0]) {/* @var $row \Zend\Db\ResultSet\Row */
				try {
					$issue = $monitorModel->getIssue($row['cluster_issue_id']);
				} catch (Exception $ex) {
					$issue = new \Issue\Container(array(), 0);
				}
			} else {
				$row = array();
				$issue = new \Issue\Container(array(), 0);
			}
			
		} else {
			throw new Exception(_t("Missing required parameters: 'traceId' or 'eventsGroupId'"));
		}

		$serverMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serverMapper \Servers\Db\Mapper */
		$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
		if ($traceRow->getApplicationId() > 0) {
			$application = $deploymentModel->getApplicationById($traceRow->getApplicationId());
		} else {
			$application = new \Deployment\Application\Container(new \ZendDeployment_Application());
		}
		$server = $serverMapper->findServerById($traceRow->getNodeId());
		
		
		return array('trace' => $traceRow, 'server' => $server, 'application' => $application, 'issue' => $issue, 'eventRow' => $row);
	}
	
	public function exportAction() {
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$request->setQuery(new Parameters($request->getQuery()->toArray()));
		$this->response = $this->forward()->dispatch('CodetracingWebApi-1_3', array('action' => 'codetracingDownloadTraceFile'));
		$this->getEvent()->setParam('do-not-compress', true);
		return $this->getResponse();
	}
}