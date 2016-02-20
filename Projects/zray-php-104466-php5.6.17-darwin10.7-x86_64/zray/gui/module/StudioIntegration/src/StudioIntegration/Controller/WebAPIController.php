<?php

namespace StudioIntegration\Controller;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use Audit\AuditTypeInterface;

use StudioIntegration\exportIssue;

use ZendServer\Mvc\Controller\WebAPIActionController;

use Zend\Mvc\Controller\ActionController,
	Application\Module,
	WebAPI,
	Zend\Http\PhpEnvironment\Request as PhpRequest,
	ZendServer\FS\FS,
	ZendServer\Log\Log,
	ZendServer\Exception as ZSException,
	Zend\Validator,
	StudioIntegration\Model as StudioIntegrationModel,
	StudioIntegration\Configuration as StudioIntegrationConfiguration,
	StudioIntegration\MonitorIssueGroupData as StudioMonitorIssueGroupData;

use Zend\View\Model\ViewModel;
use ZendServer\Validator\Integer;
use Zend\Validator\Between;
use Zend\Http\Header\SetCookie;

class WebAPIController extends WebAPIActionController
{
	public function saveAlternateServerAction() {
		
		$this->isMethodPost();
		
		$params = $this->getParameters(array('debugServer', 'alternateServer'));
	
		if ('alternate' == $params['debugServer']) {
			$alternateServer = $params['alternateServer'];
			
			if (0 < preg_match('#^(?P<host>[[:alnum:]\-_\.]+):(?P<port>[[:digit:]]+)$#', $alternateServer, $matches)) {
					
				$hostValidator = new \Zend\Validator\Hostname(\Zend\Validator\Hostname::ALLOW_ALL); // allow flexibility in setting the logical name
				if (!$hostValidator->isValid($matches['host'])) {
					throw new WebAPI\Exception(_t("'%s' is not a valid alternate server. Enter a valid IP or server name", array($matches['host']) ), WebAPI\Exception::INVALID_PARAMETER);
				}
				
				$portValidator = new Between(array('min' => 1, 'max' => pow(2, 16), 'inclusive' => true));
				if (!$portValidator->isValid($matches['port'])) {
					throw new WebAPI\Exception(_t("'%s' is not a valid port number", array($matches['port']) ), WebAPI\Exception::INVALID_PARAMETER); 
				}
				
			} else {
				$hostValidator = new \Zend\Validator\Hostname(\Zend\Validator\Hostname::ALLOW_ALL); // allow flexibility in setting the logical name
				if (!$hostValidator->isValid($alternateServer)) {
					throw new WebAPI\Exception(_t("'%s' is not a valid alternate server. Enter a valid IP or server name", array($alternateServer) ), WebAPI\Exception::INVALID_PARAMETER);
				}
			}
			// set alternate debug server
			$this->setStudioIntegrationAlternateServer($alternateServer);
		} else {
			// set an empty debug server for local
			$this->setStudioIntegrationAlternateServer('');
		}
	
		return array();
	}
	
	public function studioStartDebugAction() {	
		$viewModel = new ViewModel();
		$viewModel->setTemplate('studio-integration/web-api/studio');
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('noRemote' => 'TRUE', 'overrideHost'=>null, 'debug_host' => null, 'debug_port' => null, 'use_ssl' => null));
			$this->validateMandatoryParameters($params, array('eventsGroupId'));
			$this->validateInteger($params['eventsGroupId'], 'eventsGroupId');
			$this->validateBoolean($params['noRemote'], 'noRemote');
			if (!is_null($params['overrideHost'])) $this->validateHost($params['overrideHost'], 'overrideHost');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		set_time_limit(StudioIntegrationModel::REQUEST_TIME_LIMIT);// this request may take longer than a regular request, an extended timeout is needed
	
		try {
			$monitorUiModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
			$event = $monitorUiModel->getEventGroupData($params['eventsGroupId']);
			$issueId = $event->getIssueId();
				
			$model = $this->getLocator()->get('StudioIntegrationModel'); /* @var $model \StudioIntegration\Model */				
			$client	= $model->getDebugClient(new StudioMonitorIssueGroupData($issueId, $params['eventsGroupId'], $monitorUiModel));				
			$client->addDebuggerParam('no_remote', $params['noRemote'] == 'TRUE');
			if ($params['debug_host']) {
				$client->addDebuggerParam('debug_host', $params['debug_host']);
				$client->addDebuggerParam('debug_port', $params['debug_port']);
				$client->addDebuggerParam('use_ssl', $params['use_ssl']);
			}
			$audit = $this->auditMessage(Mapper::AUDIT_STUDIO_DEBUG, ProgressMapper::AUDIT_PROGRESS_STARTED, array($params));
			
			$model->connect($client);
				
			$this->setHttpResponseCode('200', 'OK');
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
			$viewModel->setVariables(array('content' => 'Debug session completed successfully', 'success' => '1'));
		} catch (\Exception $e) {
			if (isset($audit)) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($e->getMessage())));
			} else {
				$this->auditMessage(Mapper::AUDIT_STUDIO_DEBUG, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params + array($e->getMessage())));
			}
			
			Log::err(_t('Debug session failed : %s', array($e->getMessage())));
			$viewModel->setVariables(array('content' => 'Debug session failed: ' . $e->getMessage(), 'success' => '0'));
		}
		
		return $viewModel;
	}
	

	public function studioStartProfileAction() {

		$viewModel = new ViewModel();
		$viewModel->setTemplate('studio-integration/web-api/studio');
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('overrideHost'=>null, 'debug_host' => null, 'debug_port' => StudioIntegrationModel::DEBUGGER_DEFAULT_PORT, 'use_ssl' => '0'));
			$this->validateMandatoryParameters($params, array('eventsGroupId'));
			$this->validateInteger($params['eventsGroupId'], 'eventsGroupId');
			if (!is_null($params['overrideHost'])) $this->validateHost($params['overrideHost'], 'overrideHost');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		set_time_limit(StudioIntegrationModel::REQUEST_TIME_LIMIT);// this request may take longer than a regular request, an extended timeout is needed		
		
		try {
			$monitorUiModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
			$event = $monitorUiModel->getEventGroupData($params['eventsGroupId']);
			$issueId = $event->getIssueId();
		
			$model = $this->getLocator()->get('StudioIntegrationModel'); /* @var $model \StudioIntegration\Model */		
			$client	= $model->getProfileClient(new StudioMonitorIssueGroupData($issueId, $params['eventsGroupId'], $monitorUiModel));
			if ($params['debug_host']) {
				$client->addDebuggerParam('debug_host', $params['debug_host']);
				$client->addDebuggerParam('debug_port', $params['debug_port']);
				$client->addDebuggerParam('use_ssl', $params['use_ssl']);
			}
			$audit = $this->auditMessage(Mapper::AUDIT_STUDIO_PROFILE, ProgressMapper::AUDIT_PROGRESS_STARTED, array($params));
			$model->connect($client);
		
			$this->setHttpResponseCode('200', 'OK');
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
			$viewModel->setVariables(array('content' => 'Profile session completed successfully', 'success' => '1'));
		} catch (\Exception $e) {
		if (isset($audit)) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($e->getMessage())));
			} else {
				$this->auditMessage(Mapper::AUDIT_STUDIO_DEBUG, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array($params + array($e->getMessage())));
			}
			Log::err(_t('Profile session failed : %s. full trace: %s', array($e->getMessage(), $e->getTraceAsString())));
			$viewModel->setVariables(array('content' => 'Profile session failed: ' . $e->getMessage(), 'success' => '0'));
		}
		return $viewModel;
	}	
	
	/**
	 * Show source in IDE either by eventGroupId or directly by filePath, line, fullUrl
	 * @throws WebAPI\Exception
	 * @return \Zend\View\Model\ViewModel
	 */
	public function studioShowSourceAction() {
		$viewModel = new ViewModel();
		$viewModel->setTemplate('studio-integration/web-api/studio');
		try {
			$this->isMethodPost();
			$params = $this->getParameters(array('overrideHost'=>null, 'debug_host' => null, 'debug_port' => StudioIntegrationModel::DEBUGGER_DEFAULT_PORT, 'use_ssl' => '0'));
			
			// validate either 
			if (isset($params['eventsGroupId'])) {
				$this->validateMandatoryParameters($params, array('eventsGroupId'));
				$this->validateInteger($params['eventsGroupId'], 'eventsGroupId');
			} elseif (isset($params['filePath']) && isset($params['line']) && isset($params['fullUrl'])) {
				$this->validateMandatoryParameters($params, array('filePath', 'line', 'fullUrl'));
				$this->validateInteger($params['line'], 'line');
				$this->validateStringNonEmpty($params['filePath'], 'filePath');
				$this->validateStringNonEmpty($params['fullUrl'], 'fullUrl');
			} else {
				Log::debug(_t('This action requires either eventsGroupId or filePath, line and fullUrl'));
				throw new WebAPI\Exception(_t('This action requires either eventsGroupId or filePath, line and fullUrl'));
			}
			
			if (!is_null($params['overrideHost'])) $this->validateHost($params['overrideHost'], 'overrideHost');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		set_time_limit(StudioIntegrationModel::REQUEST_TIME_LIMIT);// this request may take longer than a regular request, an extended timeout is needed		
		
		try {
			$monitorUiModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
			$model = $this->getLocator()->get('StudioIntegrationModel'); /* @var $model \StudioIntegration\Model */		
			if (isset($params['eventsGroupId'])) {
				$event = $monitorUiModel->getEventGroupData($params['eventsGroupId']);
				$issueId = $event->getIssueId();
				$studioMonitorGroupData = new StudioMonitorIssueGroupData($issueId, $params['eventsGroupId'], $monitorUiModel);
				if (!$studioMonitorGroupData->getFileName()) {
					throw new WebAPI\Exception(_t('Cannot point on a specific source code element'));
				}
				$client	= $model->getShowSourceClientByEventGroup($studioMonitorGroupData);
			} else {
				$client	= $model->getShowSourceClient($params['filePath'], $params['line'], $params['fullUrl']);
			}
			
			if ($params['debug_host']) {
				$client->addDebuggerParam('debug_host', $params['debug_host']);
				$client->addDebuggerParam('debug_port', $params['debug_port']);
				$client->addDebuggerParam('use_ssl', $params['use_ssl']);
			}
			$audit = $this->auditMessage(Mapper::AUDIT_STUDIO_SOURCE, ProgressMapper::AUDIT_PROGRESS_STARTED, array($params));
			$model->connect($client);
		
			$this->setHttpResponseCode('200', 'OK');
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
			$viewModel->setVariables(array('content' => 'Show source session completed successfully', 'success' => '1'));
		} catch (\Exception $e) {
			if (isset($audit)) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($e->getMessage())));
			} else {
				$this->auditMessage(Mapper::AUDIT_STUDIO_DEBUG, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array_merge_recursive($params, array($e->getMessage()))));
			}
			Log::err(_t('Show source session failed : %s. full trace: %s', array($e->getMessage(), $e->getTraceAsString())));
			$viewModel->setVariables(array('content' => 'Show source session failed: ' . $e->getMessage(), 'success' => '0'));
		}
		return $viewModel;
	}
	
	public function monitorExportIssueByEventsGroupAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('eventsGroupId'));
			$this->validateInteger($params['eventsGroupId'], 'eventsGroupId');
			if (!is_null($params['overrideHost'])) $this->validateHost($params['overrideHost'], 'overrideHost');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		try {
			$monitorUiModel = $this->getLocator()->get('MonitorUi\Model\Model'); /* @var $monitorUiModel \MonitorUi\Model\Model */
			$event = $monitorUiModel->getEventGroupData($params['eventsGroupId']);
			$issueId = $event->getIssueId();
			
			$exportIssue = $this->getLocator()->get('StudioIntegration\exportIssue'); /* @var $exportIssue \StudioIntegration\exportIssue */
			$traceFilepath = $event->getCodeTracingPath();
			
			$zipArchivePath = $exportIssue->createFile($event->getIssueId(), $params['eventsGroupId'], $traceFilepath);
			$zipArchive = FS::getFileObject($zipArchivePath);
			
			// prepare the environment for a file download		
			$this->setHttpResponseCode('200', 'OK');
			$response = $this->getResponse(); /* @var $response \Zend\Http\PhpEnvironment\Response */			
			$response->getHeaders()->addHeaders(array(
					'Content-Length'	=> $zipArchive->getSize(),
					'Content-Type'		=>  'application/vnd.zend.eventexport',
					'Content-Disposition'	=>  'attachment; filename="' . basename($zipArchivePath) . '"',
			));
			$response->sendHeaders();
			$response->setContent($zipArchive->readAll()); // @todo - used to be appendBody()
			$response->send();
			$this->getEvent()->setParam('do-not-compress', true);
		} catch (ZSException $e) {
			Log::logException('Error exporting issue details', $e);
			throw new \WebAPI\Exception($e->getMessage(), \WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
	
	}
	
	/**
	 *
	 * @return \StudioIntegration\Model
	 */
	protected function getModel() {
		return $this->getLocator()->get('StudioIntegrationModel');
	}
	
	public function studioStartDebugModeAction() {
		
		$started = true;
		$audit = $this->auditMessage(AuditTypeInterface::AUDIT_STUDIO_DEBUG_MODE_START, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $audit \Audit\Container */
		
		try {
			$this->isMethodPost();
			$params = $this->getParameters ( array (
					'filters' => array(),
					'options' => array()
					
			) );
			
			$this->validateMandatoryParameters($params, array('filters'));
			
			$this->validateArray($params['options'], 'options');
			$this->validateArray($params['filters'], 'filters');
						
		} catch (\Exception $e) {
			$started = false;
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			
			$this->handleException($e, 'Input validation failed');
		}		
			

		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED);
		
		try {
			$this->getModel()->debuggerStartDebugMode($params['options'], $params['filters']);
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);

		} catch (\Exception $ex) {
			$started = false;
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			throw $ex;
		}

		
		return array ('started' => $started);
	}	
	
	public function studioStopDebugModeAction() {
	
		$started = false;
		
		$audit = $this->auditMessage(AuditTypeInterface::AUDIT_STUDIO_DEBUG_MODE_STOP, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $audit \Audit\Container */
		
		try {
			$this->isMethodPost();
			$params = $this->getParameters ( array () );
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			$started = true;
			$this->handleException($e, 'Input validation failed');
		}
			
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_STARTED);
	
		try {
			$this->getModel()->debuggerStopDebugMode();
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		} catch (\Exception $ex) {
			$started = true;
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			throw $ex;
		}		
		
		return array ('started' => $started);
	}
	
	public function studioIsDebugModeEnabledAction() {	
		$started = false;
	
		try {
			$this->isMethodGet();
			$params = $this->getParameters ( array () );
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
			
		try {
			$started = $this->getModel()->debuggerIsDebugModeEnabled();
		} catch (\Exception $ex) {
			throw $ex;
		}
	
		return array ('started' => $started);
	}

	/**
	 * GET/POST /Api/enableXdebug
	 * @brief Enable Xdebug extension (zend_extension) with its basic parameters
	 * @return array
	 */
	protected function enableXdebugAction() {
	    $directivesNames = array(
	        'xdebug.remote_enable',
	        'xdebug.remote_handler',
	        'xdebug.remote_host',
	        'xdebug.remote_port',
	        'xdebug.idekey',
	    );
	     
	    /* @var $directivesMapper \Configuration\MapperDirectives */
	    $directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives');
	    $directivesValues = $directivesMapper->getDirectivesValues($directivesNames);
	     
		// receive basic Xdebug parameters
		$params = $this->getParameters(array(
			'remote_enable'  => isset($directivesValues['xdebug.remote_enable']) ? intval($directivesValues['xdebug.remote_enable']) : 0,
			'remote_handler' => isset($directivesValues['xdebug.remote_handler']) ? $directivesValues['xdebug.remote_handler'] : 'dbgp',
			'remote_host'    => isset($directivesValues['xdebug.remote_host']) ? $directivesValues['xdebug.remote_host'] : '127.0.0.1',
			'remote_port'    => isset($directivesValues['xdebug.remote_port']) ? intval($directivesValues['xdebug.remote_port']) : 9000,
			'idekey'         => isset($directivesValues['xdebug.idekey']) ? $directivesValues['xdebug.idekey'] : '',
		));
		
		$this->validateInteger($params['remote_enable'], 'remote_enable');
		$this->validateString($params['remote_handler'], 'remote_handler');
		$this->validateString($params['remote_host'], 'remote_host');
		$this->validateString($params['idekey'], 'idekey');
		$this->validateInteger($params['remote_port'], 'remote_port');
		
		// enable the extension
		// @var Configuration\MapperExtensions
		$extensionsMapper = $this->getServiceLocator()->get('Configuration\MapperExtensions');
		
		// check if xdebug is installed
		if (!$extensionsMapper->isExtensionInstalled('xdebug')) {
			return array(
				'success' => '0',
				'content' => 'xdebug extension is not installed',
			);
		}
		
		// prepare directives for update
		$directivesToUpdate = array(
			'xdebug.remote_enable'   => $params['remote_enable'] ? '1' : '0',
			'xdebug.remote_handler'  => $params['remote_handler'],
			'xdebug.remote_host'     => $params['remote_host'],
			'xdebug.remote_port'     => $params['remote_port'],
			'xdebug.idekey'          => $params['idekey'],
		);
		
		$audit = $this->auditMessage(Mapper::AUDIT_DEBUGGER_EDITED, ProgressMapper::AUDIT_PROGRESS_STARTED, $directivesToUpdate); /* @var $audit \Audit\Container */
		
		// enable xdebug
		$result = $this->changeExtensionStatus('xdebug', true);
		
		// restart the server to load the directives into the DB
		$this->stopStartWebServer();
		
		// update xdebug directives
		$this->updateDirectives($directivesToUpdate);
		
		// restar the server to update the directive in INI file
		$this->restartServerSelective();
		
		$this->auditMessage(Mapper::AUDIT_DEBUGGER_EDITED, 
			ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, 
			array(array_merge(array('debugger' => 'Xdebug'), $directivesToUpdate))
		);
		
		return array('success' => '1', 'content' => '');
	}
	
	/**
	 * @brief Enable Zend Debugger extension
	 * @return  
	 */
	protected function enableZendDebuggerAction() {
	    
	    $directivesNames = array(
	        'zend_debugger.allow_hosts',
	        'zend_debugger.deny_hosts',
	        'zend_gui.studioAutoDetection',
	        'zend_gui.studioAutoDetectionEnabled',
	        'zend_gui.studioBreakOnFirstLine',
	        'zend_gui.studioHost',
	        'zend_gui.studioPort',
	        'zend_gui.studioUseRemote',
	        'zend_gui.studioUseSsl',
	    );
	    
	    /* @var $directivesMapper \Configuration\MapperDirectives */
	    $directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives');
	    $directivesValues = $directivesMapper->getDirectivesValues($directivesNames);
	        
		// receive Zend Debugger parameters
		$params = $this->getParameters(array(
			'studioAllowedHostsList'     => isset($directivesValues['zend_debugger.allow_hosts']) ? $directivesValues['zend_debugger.allow_hosts'] : '',
			'studioDeniedHostsList'      => isset($directivesValues['zend_debugger.deny_hosts']) ? $directivesValues['zend_debugger.deny_hosts'] : '',
		    
			'studioAutoDetection'        => isset($directivesValues['zend_gui.studioAutoDetection']) ? intval($directivesValues['zend_gui.studioAutoDetection']) : 1,
			'studioHost'                 => isset($directivesValues['zend_gui.studioHost']) ? $directivesValues['zend_gui.studioHost'] : '127.0.0.1',
			'studioAutoDetectionEnabled' => isset($directivesValues['zend_gui.studioAutoDetectionEnabled']) ? intval($directivesValues['zend_gui.studioAutoDetectionEnabled']) : 1,
		    
			'studioPort'                 => isset($directivesValues['zend_gui.studioPort']) ? intval($directivesValues['zend_gui.studioPort']) : 10137,
			'studioUseSsl'               => isset($directivesValues['zend_gui.studioUseSsl']) ? intval($directivesValues['zend_gui.studioUseSsl']) : 0,
			'studioBreakOnFirstLine'     => isset($directivesValues['zend_gui.studioBreakOnFirstLine']) ? intval($directivesValues['zend_gui.studioBreakOnFirstLine']) : 1,
			'studioUseRemote'            => isset($directivesValues['zend_gui.studioUseRemote']) ? intval($directivesValues['zend_gui.studioUseRemote']) : 1,
		));
		
		$this->validateIpAddresses($params['studioAllowedHostsList'], 'studioAllowedHostsList');
		$this->validateIpAddresses($params['studioDeniedHostsList'], 'studioDeniedHostsList');
		$this->validateInteger($params['studioAutoDetection'], 'studioAutoDetection');
		$this->validateString($params['studioHost'], 'studioHost');
		$this->validateInteger($params['studioAutoDetectionEnabled'], 'studioAutoDetectionEnabled');
		$this->validateInteger($params['studioPort'], 'studioPort');
		$this->validateInteger($params['studioUseSsl'], 'studioUseSsl');
		$this->validateInteger($params['studioBreakOnFirstLine'], 'studioBreakOnFirstLine');
		$this->validateInteger($params['studioUseRemote'], 'studioUseRemote');
		
		$directivesToUpdate = array(
			'zend_debugger.allow_hosts' => $params['studioAllowedHostsList'],
			'zend_debugger.deny_hosts' => $params['studioDeniedHostsList'],
			'zend_gui.studioAutoDetection' => $params['studioAutoDetection'],
			'zend_gui.studioAutoDetectionEnabled' => $params['studioAutoDetectionEnabled'],
			'zend_gui.studioBreakOnFirstLine' => $params['studioBreakOnFirstLine'],
			'zend_gui.studioHost' => $params['studioHost'],
			'zend_gui.studioPort' => $params['studioPort'],
			'zend_gui.studioUseRemote' => $params['studioUseRemote'],
			'zend_gui.studioUseSsl' => $params['studioUseSsl'],
		);
		
		$audit = $this->auditMessage(Mapper::AUDIT_DEBUGGER_EDITED, ProgressMapper::AUDIT_PROGRESS_STARTED, $directivesToUpdate); /* @var $audit \Audit\Container */
		
		// update zend debugger directives
		$this->updateDirectives($directivesToUpdate);
		
		// enable zend debugger
		$this->changeExtensionStatus('Zend Debugger', true);
		
		// restart the server to apply the changes
		$this->stopStartWebServer();
		
		$this->auditMessage(Mapper::AUDIT_DEBUGGER_EDITED, 
			ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, 
			array(array_merge(array('debugger' => 'Zend Debugger'), $directivesToUpdate))
		);
		
		return array('success' => '1','content' => '');
	}
	
	protected function disableBothDebuggers() {
	    
	    $audit = $this->auditMessage(Mapper::AUDIT_DEBUGGER_EDITED, ProgressMapper::AUDIT_PROGRESS_STARTED, array(array('Zend Debugger' => false, 'xdebug' => false))); /* @var $audit \Audit\Container */
		
	    $this->changeExtensionStatus('Zend Debugger', false);
		$this->changeExtensionStatus('xdebug', false);
		
		// restart the server to apply the changes
		$this->stopStartWebServer();

		$this->auditMessage(Mapper::AUDIT_DEBUGGER_EDITED, 
			ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, 
			array(array('debugger' => 'None'))
		);

		return array('success' => '1', 'content' => '');
	}
	
	/**
	 * @brief debugger settings web api - all the configuration in one place
	 * @return 
	 */
	public function debuggerSettingsAction() {
		
	    $this->isMethodPost();
	    
		$params = $this->getParameters();
		
		$this->validateMandatoryParameters($params, array('activeDebugger'));
		
		if ($params['activeDebugger'] == 'Zend Debugger') {
			return $this->enableZendDebuggerAction();
		} elseif ($params['activeDebugger'] == 'xdebug') {
			return $this->enableXdebugAction();
		} else {
			return $this->disableBothDebuggers();
		}
	}
	
	/**
	 * 
	 * @param array $directives
	 */
	protected function updateDirectives($directives) {
		if (!is_array($directives) || empty($directives)) {
			return false;
		}
		
		/* @var $directivesMapper \Configuration\MapperDirectives */
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); 
		
		try {
			$directivesMapper->setDirectives($directives);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			Log::err("Set directives failed: " . $e->getMessage());
			throw new \WebAPI\Exception(_t('Setting directives failed: %s', array($e->getMessage())), \WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	}
	
	/**
	 * 
	 * @param string $extension
	 * @param bool $newStatus
	 */
	protected function changeExtensionStatus($extension, $newStatus) {
		try {
			$newStatus === true ? Mapper::AUDIT_EXTENSION_ENABLED : Mapper::AUDIT_EXTENSION_DISABLED;
			/* @var \Zsd\Db\TasksMapper */
			$taskMapper = $this->getTasksMapper();
			$taskMapper->insertTask($taskMapper::DUMMY_NODE_ID, $newStatus ? $taskMapper::COMMAND_ENABLE_EXTENSION : $taskMapper::COMMAND_DISABLE_EXTENSION, array($extension));
		} catch (\Exception $e) {
			Log::err("{$this->getCmdName()}  failed: %s" , array($e->getMessage()));
			throw new WebAPI\Exception(_t("%s failed: %s", array($this->getCmdName(), $e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	}
	
	/**
	 * @brief restart server with `selective` parameter
	 * @return  
	 */
	protected function restartServerSelective() {
		try {
			/* @var \Zsd\Db\TasksMapper */
			$taskMapper = $this->getTasksMapper();
			Log::info('Starting selective restart');
			$taskMapper->insertTask($taskMapper::DUMMY_NODE_ID, $taskMapper::COMMAND_RESTART_SERVER, array($taskMapper::RESTART_TYPE_SELECTIVE));
		} catch (\Exception $e) {
			Log::err("restart PHP failed: %s" , array($e->getMessage()));
			throw new WebAPI\Exception(_t("Restart PHP failed: %s", array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	}	
	/**
	 * @brief another kind of restart - patch for changing the debugger in windows
	 * @return  
	 */
	protected function stopStartWebServer() {
		try {
			/* @var \Zsd\Db\TasksMapper */
			$taskMapper = $this->getTasksMapper();
			Log::info('Starting selective restart (stop & start)');
			
			$servers = $this->getServersMapper()->findAllServers();
			$serversIds = array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());			
			$taskMapper->insertTasksServers($serversIds, $taskMapper::COMMAND_RESTART_SERVER, array($taskMapper::RESTART_TYPE_SELECTIVE));
		} catch (\Exception $e) {
			Log::err("restart web server failed: %s" , array($e->getMessage()));
			throw new WebAPI\Exception(_t("Restart web server failed: %s", array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	}
	
	
	/**
	 * Alternate server host
	 * @param string $host
	 */
	private function setStudioIntegrationAlternateServer($host) {
		$directives = array('alternateDebugServer' => $host);
		try { // TODO - use special audit type rather than AUDIT_DIRECTIVES_MODIFIED
			$auditMessage = $this->auditMessage(Mapper::AUDIT_DIRECTIVES_MODIFIED,	ProgressMapper::AUDIT_PROGRESS_REQUESTED, $directives); /* @var $auditMessage \Audit\Container */
			$this->getGuiConfigurationMapper()->setGuiDirectives($directives);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			Log::err("Set UI directives failed: " . $e->getMessage());
			throw new \WebAPI\Exception(_t('Setting UI directives failed: %s', array($e->getMessage())), \WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	}
	
}
