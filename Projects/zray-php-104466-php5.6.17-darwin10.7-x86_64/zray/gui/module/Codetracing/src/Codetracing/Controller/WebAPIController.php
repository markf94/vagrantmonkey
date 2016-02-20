<?php
namespace Codetracing\Controller;

use WebAPI\Exception;

use Zend\Http\Response\Stream;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use Audit\AuditTypeInterface;

use Codetracing\TraceFileContainer;

use Zend\Validator\Regex;

use Zend\Http\Headers;

use ZendServer\Mvc\Controller\WebAPIActionController;

use WebAPI,
	ZendServer\Log\Log,
	Zend\View\Model\ViewModel;

class WebAPIController extends WebAPIActionController
{
	
	const GENERATE_TRACE_DIRECTIVE = 'zend_monitor.event_generate_trace_file';
	const DEV_MODE_DIRECTIVE = 'zend.monitor_generate_unique_events';
	protected $traceDirectives = array(self::GENERATE_TRACE_DIRECTIVE, self::DEV_MODE_DIRECTIVE);
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function codetracingDeleteAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('force' => 'FALSE'));
		$force = $this->validateBoolean($params['force'], 'force');
		$this->validateMandatoryParameters($params, array('traceFile'));
		$traceFile = $this->validateStringOrArray($params['traceFile'], 'traceFile');
		
		if (! is_array($traceFile)) {
			$traceFile = array($traceFile);
		}
		
		foreach ($traceFile as $key => $traceId) {
			$this->validateTraceFileId($traceId, "traceFile[{$key}]");
		}
		
		$traceFileMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceFileMapper \Codetracing\TraceFilesMapper */

		$traceFileObjects = $codetracingPaths = array();		
		
		try {
			foreach ($traceFile as $traceId) {
				try {
					$traceFileRow = $traceFileMapper->findCodetraceById($traceId);
					if (! $traceFileRow) {
					    // add force feature - to ignore not found traces, bug #ZSRV-14053
					    if ($force) {
					        continue;
					    }
						throw new WebAPI\Exception(_t("Requested trace file was not found"), WebAPI\Exception::NO_SUCH_TRACE);
					}
					$traceFileRow = new TraceFileContainer($traceFileRow);
				} catch (\Exception $e) {
					throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
				}
				
				if (! $traceFileRow->getId()) {
					throw new WebAPI\Exception(_t("Trace %s was not found", array($traceId)), WebAPI\Exception::NO_SUCH_TRACE);
				}
				
				$traceFileObjects[$traceFileRow->getId()]= $traceFileRow;
			}
			
			foreach($traceFileObjects as $traceId => $traceFileRow) {/* @var $traceFileRow \Codetracing\TraceFileContainer */
				$codetracingPath = $traceFileRow->getFilePath();
				if ($traceFileRow->isMonitorTrace()) {
					$codetracingPaths[] = $codetracingPath; // we will later also remove monitor trace metadata
				}
				
				Log::debug("Retrieved traceFile path {$codetracingPath} from {$params['$stringtraceFile']}");
				
				try {
					/// handle files
					$codetraceFileRetriever = $this->getLocator('Codetracing\Trace\AmfFileRetriever'); /* @var $codetraceFileRetriever \Codetracing\Trace\AmfFileRetriever */
					$codetraceFileRetriever->deleteTrace($codetracingPath);
					/// remove db entries
					$traceFileMapper->deleteByTraceIds(array($traceFileRow->getId()));
				} catch (\Exception $e) {
				    Log::err("Could not delete trace file database row for {$traceId}: " . $e->getMessage());
				    throw new WebAPI\Exception(_t("Could not delete trace file database row for %s", array($traceId)), WebAPI\Exception::INTERNAL_SERVER_ERROR);
				}
			}
		} catch (\Exception $e) {
			$this->auditMessage(AuditTypeInterface::AUDIT_CODETRACING_DELETE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
				'errorMessage' => $e->getMessage()
			), $traceFile));
			throw $e;
		}
		
		$this->deleteTraceData($codetracingPaths); // remove monitor references to the deleted codetraces
		$this->auditMessage(AuditTypeInterface::AUDIT_CODETRACING_DELETE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($traceFile));
		return array('traces' => $traceFileObjects);
	}
	
	protected function deleteTraceData($codetracingPaths) {
		try {
			$monitorMapper = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorMapper \MonitorUi\Model\Model */
			return $monitorMapper->deleteTraceData($codetracingPaths);
		} catch (\Exception $e) {
			$this->handleException($e, "Failed to delete Code Traces metadata");
		}
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function codetracingDownloadTraceFileAction() {
		$this->isMethodGet();
		$params = $this->getParameters();
		
		if (! isset($params['traceFile']) && !isset($params['eventsGroupId'])) {
			throw new WebAPI\Exception(_t("Either 'traceFile' or 'eventsGroupId' parameters must be specified"), WebAPI\Exception::MISSING_PARAMETER);
		}
		
		if (isset($params['traceFile'])) {
			$traceFileId = $params['traceFile'];
			$this->validateTraceFileId($traceFileId, 'traceFile');
			$traceFileRow = $this->getTraceFileRow($traceFileId);
			$codetracingPath = $traceFileRow->getFilePath();
			Log::debug("Retrieved traceFile path {$codetracingPath} from {$params['traceFile']}");
		} elseif (isset($params['eventsGroupId'])) {
			$monitorMapper = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorMapper \MonitorUi\Model\Model */
			$eventsGroupData = $monitorMapper->getEventGroupData($params['eventsGroupId']);
			
			if (! $eventsGroupData->getIssueId()) { 
				throw new WebAPI\Exception(_t("Events group %s was not found", array($params['eventsGroupId'])), WebAPI\Exception::NO_SUCH_EVENTGROUP);
			}
			
			$codetracingPath = $eventsGroupData->getCodeTracingPath();
			$traceFileId = current(\Codetracing\Trace\AmfFileRetriever::extractTraceIdFromPath($codetracingPath));		
			$traceFileRow = $this->getTraceFileRow($traceFileId);
			
			Log::debug("Retrieved traceFile path {$codetracingPath} from {$params['eventsGroupId']}");
		}
		
		try {
			/* @var $codetraceFileRetriever \Codetracing\Trace\AmfFileRetriever */
			$codetraceFileRetriever = $this->getLocator('Codetracing\Trace\AmfFileRetriever'); 
			$amfFileObject = $codetraceFileRetriever->retrieveAmf($codetracingPath);
		} catch (\Exception $e) {
			Log::err('Could not retrieve Trace file: ' . $e->getMessage());
			throw new WebAPI\Exception(_t("Trace file was not found at '%s': %s", array($codetracingPath, $e->getMessage())), WebAPI\Exception::NO_SUCH_TRACE, $e);
		}
		
		$traceFileName = "trace-{$traceFileRow->getId()}-" . date('dMY-HisO', $traceFileRow->getDate()) . ".amf";
		$response = new Stream();
		$response->setStream(fopen($amfFileObject->getPathname(), 'r'));
		$response->setStreamName($traceFileName);
		$response->setStatusCode(200);
		$response->setContentLength($amfFileObject->getSize());
		$this->response = $response;
		
		$this->getEvent()->setParam('do-not-compress', true);
		
		$headers = new Headers();
		$headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$traceFileName\"");
		$headers->addHeaderLine('Content-type', "application/x-amf");
		$headers->addHeaderLine('Content-Length', $amfFileObject->getSize());
		$response->setHeaders($headers);
		
		return $response;
	}
	
	/**
	 * 
	 * @throws WebAPI\Exception
	 */
	public function codetracingListAction() {
		
		// The action is called by polling, therefore, closing the session for writing will prevent session locking.
		$this->getServiceLocator()->get('Zend\Session\SessionManager')->writeClose();

		$this->isMethodGet();
		$defaults = array(
		            'applications' => array(),
		            'freetext' => '',
		            'type' => '-1',
		            'limit' => 20,
		            'offset' => 0,
		            'orderBy' => 'Date',
		            'direction' => 'Desc'
                );
		$params = $this->getParameters($defaults);
		$this->validateArray($params['applications'], 'applications');
		$this->validateAllowedValues($params['orderBy'], 'orderBy', array('Id', 'Date', 'Url', 'CreatedBy', 'Filesize'));
		$this->validateAllowedValues($params['direction'], 'direction', array('Desc', 'Asc'));
		$this->validateAllowedValues($params['type'], 'type', array(-1, TraceFileContainer::ZCT_REASON_CODE_REQUEST, TraceFileContainer::ZCT_REASON_MONITOR_EVENT, TraceFileContainer::ZCT_REASON_MANUAL_REQUEST, TraceFileContainer::ZCT_REASON_SEQFAULT));
		$params['orderBy'] = $this->getOrderBy($params['orderBy']);
		
		$traceMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceMapper \Codetracing\TraceFilesMapper */
		$filters = array ('applications' => $params['applications'], 'freetext' => $params['freetext'], 'type' => $params['type']);
		try {
			$traceSet = $traceMapper->selectAllFileTraces($filters, $params['limit'], $params['offset'], $params['orderBy'], $params['direction']);
			$traceSet = $this->resolveRouteDetails($traceSet);
			$totalCount = $traceMapper->getTraceCount($filters);
		} catch (\Exception $ex) {
			throw new Exception(_t('Could not retrieve tracefiles\' information'), Exception::INTERNAL_SERVER_ERROR, $ex);
		}
		
		return array('traces' => $traceSet, 'tracesTotalCount' => $totalCount);
		
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function codetracingEnableAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('restartNow' => 'TRUE'));
		$restartNow = $this->validateBoolean($params['restartNow'], 'restartNow');
		
		$directives = array_fill_keys($this->traceDirectives, '1');
		
		$auditMessage = $this->auditMessage(Mapper::AUDIT_CODETRACING_DEVELOPER_ENABLE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($directives)); /* @var $auditMessage \Audit\Container */
		try {
			$this->getDirectivesMapper()->setDirectives($directives);
		} catch (\Exception $e) {
			Log::err("Set codetracing directive failed: " . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
				'errorMessage' => $e->getMessage()
			)));
			throw new WebAPI\Exception(_t('Setting codetracing directive failed: %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}

		$this->getTasksMapper()->waitForTasksComplete();
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		return $this->postProcessDisableEnable($restartNow, true);
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function codetracingDisableAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('restartNow' => 'TRUE'));
		$restartNow = $this->validateBoolean($params['restartNow'], 'restartNow');
	
		$directives = array_fill_keys($this->traceDirectives, '0');
		
		$auditMessage = $this->auditMessage(Mapper::AUDIT_CODETRACING_DEVELOPER_DISABLE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($directives)); /* @var $auditMessage \Audit\Container */
		try {
			$this->getDirectivesMapper()->setDirectives($directives);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
					'errorMessage' => $e->getMessage()
			)));
			Log::err("Set codetracing directive failed: " . $e->getMessage());
			throw new WebAPI\Exception(_t('Setting codetracing directive failed: %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	
		$this->getTasksMapper()->waitForTasksComplete();
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		return $this->postProcessDisableEnable($restartNow, false);
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function codetracingIsEnabledAction() {
		$this->isMethodGet();
		$viewModel = new ViewModel(array('status' => $this->getStatus()));
		$viewModel->setTemplate('codetracing/web-api/codetracing-status');
		return $viewModel;
	}
	
	/**
	 * 
	 * @throws WebAPI\Exception
	 * @return string$traceSet = $this->resolveRouteDetails($traceSet);
	 */
	public function codetracingCreateAction() {
        $this->validateLicenseValid();
	    $this->isMethodPost();
	    $params = $this->getParameters(array('traceMethod' => 'get'));
	    $this->validateMandatoryParameters($params, array('url'));
	    $traceMethod = $this->validateInArray(strtolower($params['traceMethod']), array('get', 'post'), 'traceMethod');
	    
		$url = $this->validateUri($params['url'], 'url');
		
		/* @var \Zend\Session\SessionManager */
		$sessionManager = $this->getServiceLocator()->get('Zend\Session\SessionManager');
		
		$client = new \Zend\Http\Client($url);
		
		$adapter = new \Zend\Http\Client\Adapter\Curl();
		$adapter = $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST,false);
		$adapter = $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER,false);
		$client->setAdapter($adapter);
		
		$method = $traceMethod == 'get' ? \Zend\Http\Request::METHOD_GET : \Zend\Http\Request::METHOD_POST;
		
		$client->setMethod($method);
		$client->setOptions(array(
			'timeout' => 30,
		));
		$client->setParameterGet(array('dump_data' => 1));
		try {
			try {
				$sessionManager->writeClose();
				
			    $response = $client->send();
			    Log::info("Codetracing request sent ({$traceMethod}) to: {$url}");
			} catch (\Zend\Http\Client\Adapter\Exception $e) {
			    Log::err('Zend Http Client Adapter Exception: ' . $e->getMessage());
			    throw new WebAPI\Exception(_t('Failed to connect to the server: %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			} catch (\Zend\Http\Client\Exception $e) {
			    Log::err('Zend Http Client Exception: ' . $e->getMessage());
			    throw new WebAPI\Exception(_t('The request to generate the dump has failed'), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			} catch (\Zend\Uri\Exception\InvalidUriException $e) {
			    Log::err('Zend Uri Exception: ' . $e->getMessage());
			    throw new WebAPI\Exception(_t('Parameter \'url\' is not valid'), WebAPI\Exception::INVALID_PARAMETER);
			}
			
			if (! $response->isOk() && (500 !== $response->getStatusCode())) {
			    throw new WebAPI\Exception(_t('Failed to generate the dump [%s]', array($response->getStatusCode())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
			$headers = $response->getHeaders();
			
			$zendCodeTracingHeader = $headers->get('X-Zend-Code-Tracing'); // @var $zendCodeTracingHeader \Zend\Http\Header\HeaderDescription
			
			if (!$zendCodeTracingHeader) {
				//X-Zend-Code-Tracing-Error: Host not allowed
				$zendCodeTracingHeader = $headers->get('X-Zend-Code-Tracing-Error'); // @var $zendCodeTracingHeader \Zend\Http\Header\HeaderDescription
				if ($zendCodeTracingHeader) {
					throw new WebAPI\Exception(_t('Code tracing was not generated (Your host is not in the debugger Allowed Hosts list)'), WebAPI\Exception::DIRECT_ACCESS_FORBIDDEN); 
				}
			    throw new WebAPI\Exception(_t('Code tracing was not generated (No X-Zend-Code-Tracing header found. Maybe your host is not in zend_debugger.allow_hosts?)'), WebAPI\Exception::INTERNAL_SERVER_ERROR);  
			}
			$traceFileId = $zendCodeTracingHeader->getFieldValue();
			
			if (! $traceFileId) {
			    throw new WebAPI\Exception(_t('Code tracing was not generated (trace id was not found after creation). Try refreshing the list.'), WebAPI\Exception::INTERNAL_SERVER_ERROR); 
			}
		} catch (\Exception $e) {
			$this->auditMessage(AuditTypeInterface::AUDIT_CODETRACING_CREATE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array(
					'errorMessage' => $e->getMessage()
			)));
			throw $e;
		}
		
		$this->auditMessage(AuditTypeInterface::AUDIT_CODETRACING_CREATE, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array(
			array('url' => $url, 'traceFileId'=>$traceFileId)
		));
		/* @var $traceMapper \Codetracing\TraceFilesMapper */
		$traceMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); 
		$trace = $traceMapper->findCodetraceById($traceFileId);
		$traceContainer = new TraceFileContainer($trace);
		if (! $trace) { // trace entry was not written yet to the db - let's have at least the id
			$traceContainer->setId($traceFileId);
		}
		
		$this->setHttpResponseCode('202', 'Accepted');
		return array('trace' => $traceContainer);
	}
	
	public function codetracingGetInfoAction() {
	    $this->isMethodGet();
	    $params = $this->getParameters();
	    $this->validateMandatoryParameters($params, array('id'));
	    $traceMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceMapper \Codetracing\TraceFilesMapper */
	    $trace = $traceMapper->findCodetraceById($params['id']);
	    $viewModel = new ViewModel(array('trace' => new TraceFileContainer($trace)));
	    $viewModel->setTemplate('codetracing/web-api/codetracing-create');
	    return $viewModel;
	}

	/**
	 * @param integer $traceFileId
	 * @return \Codetracing\TraceFileContainer
	 */
	protected function getTraceFileRow($traceFileId) {
		$traceFileMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceFileMapper \Codetracing\TraceFilesMapper */
		try {
			$traceData = $traceFileMapper->findCodetraceById($traceFileId);
			if (! $traceData) {
				throw new WebAPI\Exception(_t("Requested trace file was not found"), WebAPI\Exception::NO_SUCH_TRACE);
			}
			$traceFileRow = new TraceFileContainer($traceData);
		} catch (\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
			
		if (! $traceFileRow->getId()) {
			throw new WebAPI\Exception(_t("Trace %s was not found", array($traceFileId)), WebAPI\Exception::NO_SUCH_TRACE);
		}
	
		return $traceFileRow;
	}
	
	/**
	 *
	 * @param Boolean $restartNow - restartNow required or not
	 * @param Boolean $enable - enable/disable action
	 */
	protected function postProcessDisableEnable($restartNow, $enable) {
		if ($restartNow) {
			$exportView = $this->restartPhp();
		}else {
			$exportView = new ViewModel();
		}
	
		$this->setHttpResponseCode('202', 'Accepted');
		$status = $this->getStatus(!$restartNow, $enable);
		$enable ? $audit = AuditTypeInterface::AUDIT_CODETRACING_DEVELOPER_ENABLE : $audit = AuditTypeInterface::AUDIT_CODETRACING_DEVELOPER_DISABLE;
		$this->auditMessage($audit, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array($status));
	
		$exportView->setVariable('status', $status);
		$exportView->setTemplate('codetracing/web-api/1x3/codetracing-status');// Restoring original route
		
		return $exportView;
	}
	
	protected function getStatus($phpRestartIsRequired=null, $enabled=null) {
		if (is_bool($enabled)) { // if enabled/disabled were performed, we will simply takes the values set
			$traceEnabled = intval($enabled);
			$developerMode = intval($enabled);		
		} else { // taken from blueprint
			$traceEnabled = $this->getDirectivesMapper()->getDirectiveValue(self::GENERATE_TRACE_DIRECTIVE);
			$developerMode = $this->getDirectivesMapper()->getDirectiveValue(self::DEV_MODE_DIRECTIVE);
		}
		
		if (is_bool($phpRestartIsRequired)) { // explicitly passed
			$awaitsRestart = intval($phpRestartIsRequired);
		}else {
			$awaitsRestart = intval($this->getMessagesMapper()->isDirectivesAwaitingRestart($this->traceDirectives)); // checking the messages DB to see if our directives have any messages
		}
		
		if ($this->getExtensionsMapper()->isExtensionLoaded('Zend Code Tracing')) {
			$componentStatus = 'Active';
		}else {
			$componentStatus = 'Inactive';
		}

		$status = array (
				'componentStatus' => $componentStatus,
				'traceEnabled' => $traceEnabled,
				'developerMode' => $developerMode,
				'awaitsRestart' => $awaitsRestart,
		);

		return $status;
	}
	
	/**
	 * @param string $traceFileId
	 * @param string $parameterName
	 * @return string
	 * @throws WebAPI\Exception
	 */
	protected function validateTraceFileId($traceFileId, $parameterName) {
		$traceFileValidator = new Regex('#^\d+\.\d+\.\d+$#');
		if (! $traceFileValidator->isValid($traceFileId)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a valid trace file ID", array($parameterName)), WebAPI\Exception::INVALID_PARAMETER); 
		}
		return $traceFileId;
	}
	
	/**
	 * @return \Zend\Http\PhpEnvironment\Response
	 */
	protected function restartPhp() {
		$controller = $this->getEvent()->getRouteMatch()->getParam('controller');
		$action = $this->getEvent()->getRouteMatch()->getParam('action');
		
		$exportView = $this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'restartPhp'));/* @var $exportView \Zend\View\Model\ViewModel */
		$this->getEvent()->getRouteMatch()->setParam('controller', $controller); // back to orig
		$this->getEvent()->getRouteMatch()->setParam('action', $action); // back to orig		
		
		return $exportView;
	}
	
	protected function getOrderBy($orderBy) {
	    //'Id', 'Date', 'Url', 'CreatedBy', 'Filesize'
	    $traceMapper = $this->getLocator()->get('Codetracing\TraceFilesMapper'); /* @var $traceMapper \Codetracing\TraceFilesMapper */
	    
	    $fieldsMap = array (
	            'Id' => $traceMapper::TRACE_ID,
	            'Date' => $traceMapper::TRACE_TIME,
	            'Url' => $traceMapper::ORIGINAL_URL,
	            'CreatedBy' => $traceMapper::REASON,
	            'Filesize' => $traceMapper::TRACE_SIZE 
	    );
	    if (isset($fieldsMap[$orderBy])) {
	        return $fieldsMap[$orderBy];
	    }
	    return $traceMapper::TRACE_TIME;
	}
	

	protected function validateInArray($item, $validValues, $parameterName) {
		if (! in_array($item, $validValues)) {
			throw new WebAPI\Exception(_t("Parameter '%s' must be a one of %s", array($item, implode(',', $validValues))), WebAPI\Exception::INVALID_PARAMETER);
		}
		return $item;
	}
	
	private function resolveRouteDetails($traceSet)
	{
		$monitorModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorModel \MonitorUi\Model\Model */
	
		$traceFiles = array();
	
		$traces = current($traceSet);
		foreach ($traces as $trace) {
			$traceFiles[] = $trace['filepath'];
		}
		$issuesByTrace = $monitorModel->getIssueIdsByTraceFiles($traceFiles);
	
		$params = array ("issuesIds" => array_values($issuesByTrace));
	
		$issuesDbMapper = $this->getLocator()->get('Issue\Db\Mapper'); /* @var $issuesDbMapper \Issue\Db\Mapper */
		$tracesIssues = $issuesDbMapper->getIssues($params, count($issuesByTrace), 0, "id", "ASC")->toArray();
	
		// add the route details in each trace element
		foreach ($traces as $key => $trace) {
			$path = $trace['filepath'];
			if (isset($issuesByTrace[$path])) {
				$issueId = $issuesByTrace[$path];
				if (isset($tracesIssues[$issueId][ZM_DATA_REQUEST_COMPONENTS])) {
					$route = $tracesIssues[$issueId][ZM_DATA_REQUEST_COMPONENTS];
				} else {
					$route = array();
				}
			} else {
				$route = array();
			}
			$trace['routeDetails'] = $route;
			$traces[$key] = $trace;
	
		}
		$traceSet = new \ZendServer\Set($traces, 'Codetracing\TraceFileContainer');
	
		return $traceSet;
	}
	
}
