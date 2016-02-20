<?php

namespace Configuration\Controller;
use Zend\View\Model\ViewModel;
use ZendServer\FS\FS;
use WebAPI\Exception;
use ZendServer\Mvc\Controller\WebAPIActionController,
	WebAPI,
	Servers\View\Helper\ServerStatus,
	Zend\Json\Json,
	Configuration\DdMapper,
	ZendServer\Set,
	ZendServer\Log\Log,
	Application\Module,
	ZendServer\Exception as ZSException,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper,
	Zend\Validator\EmailAddress,
	Messages\MessageContainer,
	Zsd\Db\TasksMapper,
	Zend\Stdlib\Parameters;
use Audit\AuditTypeInterface;
use Notifications\NotificationContainer;
use Configuration\License\Validator\LicenseValidator;
use Zend\Code\Reflection\ClassReflection;
use ZendServer\Configuration\Manager;
use Snapshots\Controller\Plugin\CreateConfigurationSnapshot;
use ZendDeployment_PackageMetaData;

class WebAPIController extends WebAPIActionController
{
	
	const EXTENSIONS_STATUS = 'extensionsStatus';
	
	const EXPORT_SQL_DIRECTIVES = 'directives.sql';
	const EXPORT_SQL_EXTENSIONS = 'extensions.sql';
	const EXPORT_SQL_FILE = 'zs_config.sql';
	const EXPORT_MONITOR_FILE = 'monitor_rules.xml';
	const EXPORT_PAGECACHE_FILE = 'pagecache_rules.xml';
	const METADATA_FILE = 'metadata';
	
	protected $daemons = array( // mapping of daemons to extensions, reflects the data in zend_extensions_map.json
			'jb' 			=> 'Zend Java Bridge',
			'jqd' 			=> 'Zend Job Queue',			
			'monitor_node'	=> 'Zend Monitor',	
			'scd' 			=> 'Zend Session Clustering',			
			'zdd' 			=> 'Zend Deployment',
		);
	
	public function getSystemInfoAction() {
		$viewModel = $this->forward()->dispatch('ConfigurationWebApi-1_2', array('action' => 'getSystemInfo')); /* @var $viewModel \Zend\View\Model\ViewModel */

		$viewModel->setVariable('edition', $this->convertZSEdition());
		$viewModel->setVariable('status', $this->determineSystemStatus($viewModel->getVariable('serverLicenseInfo')));

		return $viewModel;
	}
	
	public function configurationRevertChangesAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array('doRestart'=>'false'));
		$serverId = $this->validateExistingServerId($params['serverId']);
		$doRestart = $this->validateBoolean($params['doRestart'], 'doRestart');
		
		$this->getTasksMapper()->insertTask($serverId, TasksMapper::COMMAND_APPLY_BLUEPRINT_WITH_EXTENSIONS);
		
		if ($doRestart) {
			$this->forward()->dispatch('ServersWebAPI-1_3', array('action' => 'restartPhp'));
		}
		
		return $this->returnServerInfo($serverId);
	}
	
	public function configurationApplyChangesAction() {
		$this->isMethodPost();
	
		$params = $this->getParameters();
		$serverId = $this->validateExistingServerId($params['serverId']);
		
		// no servers provided, retreive all servers
		$directives = array();
		$messages = $this->getMessagesMapper()->findServerMessages($serverId);
		foreach ($messages as $message) { /* @var $message \Messages\MessageContainer */
			if ($message->getMessageType() == \Messages\Db\MessageMapper::TYPE_MISSMATCH) {
				$details = $message->getMessageDetails();
				$directives[$message->getMessageKey()] = $details[2];
			}
		}
		
		if (! $directives) {
			Log::notice("No changes found");
			return $this->returnServerInfo($serverId);
		}		
		
		$this->getRequest()->getPost()->set('directives', $directives);
		$this->forward()->dispatch('ConfigurationWebApi-1_3', array('action' => 'configurationStoreDirectives'));
		
		$this->getTasksMapper()->insertTask($serverId, TasksMapper::COMMAND_APPLY_BLUEPRINT_WITH_EXTENSIONS);
	
		return $this->returnServerInfo($serverId);
	}
	
	/** 
	 * @throws WebAPI\Exception
	 */
	public function configurationExtensionsListAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('type' => 'all', 'order' => 'name', 'direction' => 'asc', 'filter' => ''));
			$type = $this->validateType($params['type']);			
			$order = $this->validateOrder($params['order']);
			$direction = $this->validateDirection($params['direction']);			
			$filter = $this->validateString($params['filter'], 'filter');			
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$extType = strtolower($type);
		switch ($extType) {
			case 'zend': 
				$extensions = $this->getExtensionsMapper()->selectAllZendExtensions();
				break;
			case 'php':
				$extensions = $this->getExtensionsMapper()->selectAllPHPExtensionsInstalled();
				break;
			case 'all':
				$extensions = $this->getExtensionsMapper()->selectAllExtensions();
				break;
			default:
				throw new WebAPI\Exception(_t("Unknown type: %s", array($extType)), WebAPI\Exception::INVALID_PARAMETER);
		}		
		
		$extensions = $this->convertSetToExtensionsArray($extensions);
		$extensions = $this->addDummyExtensions($extensions, $extType);
		$extensions = $this->addExtensionsData($extensions);
		$extensions = $this->convertSetToExtensionsArray($extensions);		
		$extensions = $this->searchExtensions($extensions, $filter);// FILTERing - in case $params['filter'] was passed		
		$extensions = $this->addExtensionsErrors($extensions);

		if (strtolower($order) === 'name') {
			$extensions = $this->sortExtensionsByName($extensions, $direction);
		}
		else {
			$extensions = $this->sortExtensionsByStatus($extensions, $direction);
		}
		
		return array('extensions' => new Set($extensions, null));
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function configurationComponentsListAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('filter' => ''));
			$filter = $this->validateString($params['filter'], 'filter');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$extensions = $this->getExtensionsMapper()->selectAllZendExtensions();
		$extensions = $this->convertSetToExtensionsArray($extensions);
		$extensions = $this->addExtensionsErrors($extensions);
		$extensions = $this->addExtensionsData($extensions);		
		$extensions = $this->tweakExtensionsProperties($extensions);
		
		$daemonsData = $this->getDaemonsData();
		
		$components = array();		
		$extensionsToSearch = $deamonsToSearch = array();
		foreach ($extensions as $extension) {
			$extensionsToSearch[] = $extension;
			$deamonsToSearch[] = $this->getDaemonDataByExtension($extension->getName(), $daemonsData);
			$components[$extension->getName()]['extension'] = $extension;
			$components[$extension->getName()]['daemon'] = $this->getDaemonDataByExtension($extension->getName(), $daemonsData);
		}
		
		// filtering is based on the extension and daemon data
		if ($filter) {
			// FILTERing - in case $params['filter'] was passed
			$foundExtensions = $this->searchExtensions($extensionsToSearch, $filter);
			$foundDaemons = $this->searchExtensions($deamonsToSearch, $filter);
			
			$foundComponents = array();
			foreach ($components as $name => $componentData) {
				if (!empty($foundExtensions) && in_array($componentData['extension']->getName(), array_keys($foundExtensions))) {
					$foundComponents[$name] = $componentData;
				} elseif (!empty($foundDaemons) && in_array($componentData['daemon']->getName(), array_keys($foundDaemons))) {
					$foundComponents[$name] = $componentData;
				}
			}
			
			$components = $foundComponents;
		}
		
		return array('components' => $components);
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function configurationValidateDirectivesAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('directives' => array(), 'extensions' => array()));

		$this->validateArray($params['directives'], 'directives');
		$this->validateArray($params['extensions'], 'extensions');

		foreach ($params['directives'] as $directive => $value) {
			$this->validateString($directive, "directives[{$directive}]");
			$this->validateString($value, "directives[{$directive}]");
		}
		
		$DdMapper = $this->getDdMapper();
		
		$directiveResults = array();
		$directiveMessages = array();
		$prerequsitesConflicts = array();
		$prerequsitesValidation = true;
		$prerequsitesPluginValidation = true;
		$brokenPlugin = null;
		foreach ($params['directives'] as $directive => $value) {
			
			$directiveExists = $this->getDirectivesMapper()->directiveExists($directive);
			if (! $directiveExists) {
				throw new Exception("Directive {$directive} does not exist and cannot be validated", Exception::NO_SUCH_DIRECTIVE);
			}
			
			$directiveValidator = $DdMapper->directiveValidator($directive); /* @var $directiveValidator \Zend\InputFilter\Input */
			$directiveValidator->setValue($value);
			if ($directiveValidator->allowEmpty() && empty($value)) {
				$directiveResults[$directive] = true;
			} else {
			    $directiveResults[$directive] = $directiveValidator->isValid();
			}
			
			try {
				if ($directiveResults[$directive]) {
					if (! $this->isDirectiveChangePrerequisitesValid($directive, $value)) {
						$prerequsitesValidation = false;
						$prerequsitesConflicts[] = $directive;
					}
					$brokenPlugin = null;
					if (! $this->isDirectiveChangePluginPrerequisitesValid($directive, $value, $brokenPlugin)) {
						$prerequsitesPluginValidation = false;
					}
				}
				$directiveMessages[$directive] = $directiveValidator->getMessages();
			} catch (\Exception $e) {
				$directiveMessages[$directive] = 'xxxx';
			}
		}
		
		foreach ($params['extensions'] as $extension => $value) {
		    $extension = str_replace('--', ' ', $extension); // translate Zend--Monitor => Zend Monitor
			if (! $this->isExtensionChangePrerequisitesValid($extension, $value)) {
				$prerequsitesValidation = false;
				$prerequsitesConflicts[] = $extension;
			}
			
			$brokenPlugin = null;
			if (! $this->isExtensionChangePluginPrerequisitesValid($extension, $value, $brokenPlugin)) {
			    $prerequsitesPluginValidation = false;
			    if (!in_array($extension, $prerequsitesConflicts)) {
				    $prerequsitesConflicts[] = $extension;
			    }
			}
			
		}
		$brokenPluginName = '';
		// if the plugin validation is broken, get the plugin name for disable plugin message
		if($brokenPlugin) {
		    $pluginsModel = $this->getLocator()->get('Plugins\Mapper');
		    $pluginContainer = $pluginsModel->getPluginById($brokenPlugin);
		    $brokenPluginName = $pluginContainer->getPluginName();
		}
		return array( 'results'                        => $directiveResults,
		              'messages'                       => $directiveMessages,
		              'prerequsitesValidation'         => $prerequsitesValidation,
		              'prerequsitesPluginValidation'   => $prerequsitesPluginValidation,
		              'prerequsitesConflicts'  		   => $prerequsitesConflicts,
		              'brokenPlugin'                   => $brokenPluginName,
		              'brokenPluginId'                 => $brokenPlugin,
		);
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function configurationDirectivesListAction() {
		try {
			$this->isMethodGet();
			$params = $this->getParameters(array('extension' => '', 'daemon' => '',  'filter' => '', 'hidden' => 'false'));
			$extension = $this->validateString($params['extension'], 'extension');
			$daemon = $this->validateString($params['daemon'], 'daemon');			
			$filter = $this->validateString($params['filter'], 'filter');
			$hidden = $this->validateBoolean($params['hidden'], 'hidden');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
		
		$extensionDirectives = $daemonDirectives = $allDirectives = array();
		if ($extension) {
			$extensionDirectives = $this->getDirectivesMapper()->selectAllExtensionDirectives($extension);
			/// we bounce around between arrays and Sets for performing modifications on the Set's information
			/// this approach is flawed - we should either open up Sets to be modified or restrict their use
			$extensionDirectives = $this->convertSetToDirectivesArray($extensionDirectives->toArray());
		}
		
		if ($daemon) {
			$daemonDirectives = $this->getDirectivesMapper()->selectAllDaemonDirectives($daemon);
			/// we bounce around between arrays and Sets for performing modifications on the Set's information
			/// this approach is flawed - we should either open up Sets to be modified or restrict their use
			$daemonDirectives = $this->convertSetToDirectivesArray($daemonDirectives->toArray());
		} 
		
		if (!$extension && !$daemon) {
			$allDirectives = $this->getDirectivesMapper()->selectAllDirectives();// retrieve all known directives
			/// we bounce around between arrays and Sets for performing modifications on the Set's information
			/// this approach is flawed - we should either open up Sets to be modified or restrict their use
			$allDirectives = $this->convertSetToDirectivesArray($allDirectives->toArray());
		}
		
		$directives = array_merge(array_merge($extensionDirectives, $daemonDirectives), $allDirectives);
		$filteredDirectives = $this->searchDirectives(new Set($directives,'Configuration\DirectiveContainer'), $filter);// FILTERing
		$directivesSet = $this->getDdMapper()->addDirectivesData($filteredDirectives);
		
		if (! $hidden) { // if was passed 'true' the webapi should return also the not visible in the GUI directives, defined as 'toAdmin'
			$directivesSet = $this->filterToUserDirectives($directivesSet); // Leave only toUser Directives
		}
		
		/// override with real file values if they mismatch the blueprint
		$directivesSet = $this->overrideDiskValueByMessage($directivesSet, $extension);
		$directivesSet = $this->addPreviousValue($directivesSet->toArray());
		$directivesSet->setHydrateClass('Configuration\DirectiveContainer');
		return array('directives' => $directivesSet);
	}

	/**
	 * @throws WebAPI\Exception
	 */	
	public function configurationExtensionsOnAction() {		
		$extensions = $this->preProcessDisableEnableActions();	
		
		$this->disbleEnableExtensions(true, $extensions);	
				
		return $this->postProcessDisableEnableActions($extensions, true);
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function configurationExtensionsOffAction() {
		$extensions = $this->preProcessDisableEnableActions();

		$this->disbleEnableExtensions(false, $extensions);
	
		return $this->postProcessDisableEnableActions($extensions, false);
	}	

	/**
	 * Validate and store a list of directives and their corresponding values in the servers configuration.
	 * Directives are validated according to their type and a predefined validation scheme
	 * 
	 * @throws WebAPI\Exception
	 * @throws Exception
	 * @return array
	 */
	public function configurationStoreDirectivesAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('directives' => array()));
		$directives = $this->validateArrayNonEmpty($params['directives'], 'directives');
		$ddMapper = $this->getDdMapper();
		$isValidPlugin = true;
		$brokenPlugin = '';
		
		//validate all directives
		foreach ($params['directives'] as $name => $value) {

			$directiveExists = $this->getDirectivesMapper()->directiveExists($name);			
			if (! $directiveExists) {
				throw new Exception("Directive {$name} does not exist and cannot be validated", Exception::NO_SUCH_DIRECTIVE);
			}
			
			if (!isAzureEnv()) {
    			try {
    				$directiveValidator = $ddMapper->directiveValidator($name); /* @var $directiveValidator \Zend\InputFilter\Input */
    				$directiveValidator->setValue($value);
    				if ($directiveValidator->allowEmpty() && empty($value)) {
    					continue;
    				}
    			} catch (\Exception $e) {
    				Log::err("Set directives failed: " . $e->getMessage());
    				throw new WebAPI\Exception(_t('Setting directives failed: %s', array($e->getMessage())), WebAPI\Exception::INVALID_PARAMETER);
    			}

    			if (! $directiveValidator->isValid()) {
    				Log::err("The directives validation failed on directive '$name': " .print_r($directiveValidator->getMessages(),true));
    				throw new WebAPI\Exception(_t("Directive '%s' validation failed: %s", array($name, current($directiveValidator->getMessages()))), WebAPI\Exception::INVALID_PARAMETER);
    			}
				
				// @TODO add the functionality to zray standalone
				if (!isZrayStandaloneEnv()) {
					$isValidPlugin = $isValidPlugin && $this->isDirectiveChangePluginPrerequisitesValid($name, $value, $brokenPlugin);
				}
			}
			
		}
		
		$directivesInfo = $this->getDirectivesMapper()->selectSpecificDirectives(array_keys($params['directives']));	
		$directivesSet = $ddMapper->addDirectivesData($directivesInfo);
		$azure = isAzureEnv();
		$zrayStandalone = isZrayStandaloneEnv();
		if (!$azure && !$zrayStandalone) {
    		$auditDirectivesData = array();
    		foreach ($directivesSet as $directive) { /* @var $directive \Configuration\DirectiveContainer */
    			$auditDirectivesData[] = array(
    					_t('Extension name: %s, Directive: %s, Old value: %s, New value: %s',
    							array($directive->getExtension(), $directive->getName(), $directive->getFileValue(), $directives[$directive->getName()])));
    		}
    		$directivesSet->rewind();
		}
		
		try {
		    if (!$azure && !$zrayStandalone) {
    			$auditMessage = $this->auditMessage(auditMapper::AUDIT_DIRECTIVES_MODIFIED,
    					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
    					array($auditDirectivesData)); /* @var $auditMessage \Audit\Container */
		    }
			$this->getDirectivesMapper()->setDirectives($directives);
			
			if (!$azure && !$zrayStandalone) {
			    if(!$isValidPlugin) { // and $ignoreFailures
			        $this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
			    } else {
			        $this->getNotificationsMapper()->deleteByType(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
			    }
			}
		} catch (\Exception $e) {
		    if (!$azure && !$zrayStandalone) {
			     $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
		    }	
			Log::err("Set directives failed: " . $e->getMessage());
			throw new WebAPI\Exception(_t('Setting directives failed: %s', array($e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		return array('directives' => $directivesSet);
	}
	
	public function setZendMonitorDefaultSettingsAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('applyToExisting' => 1, 'defaultEmail' => '', 'defaultCustomAction' => ''));
		
		$emailValidator = new EmailAddress();
		if (!empty($params['defaultEmail'])) {
			$this->validateEmailAddress($params['defaultEmail'], 'defaultEmail');
		}
		
		if (!empty($params['defaultCustomAction'])) {
			$this->validateUri($params['defaultCustomAction'], 'defaultCustomAction');
		}
		
		// add validator for custom action url
		
		$this->getGuiConfigurationMapper()->setGuiDirectives(array('defaultEmail' => $params['defaultEmail'], 'defaultCustomAction' => $params['defaultCustomAction']));
		
		if ($params['applyToExisting'] == '1') { // apply the email and customAction to existing rules
			if (isset($params['defaultEmail']))
				$this->getMonitorRulesMapper()->getActionsTable()->update(array('SEND_TO' => $params['defaultEmail']));
			
			if (isset($params['defaultCustomAction']))
				$this->getMonitorRulesMapper()->getActionsTable()->update(array('ACTION_URL' => $params['defaultCustomAction']));
		}
		
		$serversToNotify = $this->getServersMapper()->findRespondingServersIds();
		$tasks = $this->getLocator()->get('MonitorRules\Model\Tasks'); /* @var $tasks \MonitorRules\Model\Tasks */
		$tasks->syncMonitorRulesChanges($serversToNotify);
		return array();
	}
	
	/**
	 * Check if server has completed all of its assigned tasks. 
	 * 
	 * @return boolean
	 */
	public function tasksCompleteAction() {
	    $this->isMethodGet();
	    $params = $this->getParameters(array('servers' => array(), 'tasks' => array()));
	    try {
			if (isZrayStandaloneEnv()) {
				
				// complete all pending tasks, if there are any.
				$deploymentManager = new \ZendDeployment_Manager();
				$hasWaitingTasks = $deploymentManager->getRemoteDbHandler()->hasWaitingTasks();
				if ($hasWaitingTasks) {
					zrayStandaloneExecuteTasks();
				}
				
				// for standalone Z-Ray, there are no "waiting" tasks, they are executed immediately
				$tasksIsComplete = true;
				$tasksPerServerComplete = array();
			} else {
				$tasksIsComplete = $this->getTasksMapper()->tasksComplete($params['servers'], $params['tasks']);
				$tasksPerServerComplete = $this->getTasksMapper()->tasksPerServerComplete($params['servers']);
			}
	    } catch (\Exception $e) {
	    	throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
	    }
	    
	    return array('tasksIsComplete' => $tasksIsComplete, 'tasksPerServer' => $tasksPerServerComplete);
	}
	
	public function configurationExportAction() {
		$this->isMethodGet();
		$params = $this->getParameters(array('directivesBlacklist' => array(), 'snapshotName'=>''));
		$directivesBlacklist = $this->validateArray($params['directivesBlacklist'], 'directivesBlacklist');
		$snapshotName = $this->validateString($params['snapshotName'], 'snapshotName');
		
		try {			
			if ($snapshotName && $this->getSnapshotsMapper()->findSnapshotByName($snapshotName)->getId() > 0) { // snapshot found
				throw new WebAPI\Exception(_t("Snapshot {$snapshotName} already exists - will not overwrite it"), WebAPI\Exception::SNAPSHOT_ALREADY_EXISTS);
			}
			
			// creating the auditMessage after the snapshot bit - will fail silently if snapshot failed
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_CONFIGURATION_EXPORT, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $auditMessage \Audit\Container */
			
			try {
				$taskId = $this->getLocator('Configuration\Task\ConfigurationPackage')->exportConfiguration($snapshotName);
				$this->getTasksMapper()->waitForTasksComplete(array(), array($taskId));
			} catch (Exception $ex) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
				throw new WebAPI\Exception($ex->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $ex);
			}
			
			$reply = $this->getRepliesMapper()->getExportTaskReply($taskId);
			$reply = $reply->current(); /* @var $reply \Configuration\ReplyContainer */
			$content = $reply->getReply();
			
			$contentLength = strlen($content);
			
			$date = strftime("%Y %m %d %H %M %S", time());
			$date = str_replace(" ", "_", $date);
			
			$headers = array();
			$headers[] = "Content-Disposition: attachment; filename=\"zs_config_{$date}.zip\"";
			$headers[] = "Content-type: application/vnd.zend.serverconfig";
			$headers[] = "Content-Length: $contentLength";
			
			$headersToSend = new \Zend\Http\Headers();
			$headersToSend->addHeaders($headers);
			
			/* @var $response \Zend\Http\PhpEnvironment\Response */
			$response = $this->getResponse();
			$response->setHeaders($headersToSend);
			$response->setContent($content);	
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
			
			return $this->getResponse();
		} catch (\WebApi\Exception $e) {
			if (isset($auditMessage)) { // we silently fail if the snapshort part failed
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));				
			}
			throw $e;
		} catch (\Exception $e) {
			if (isset($auditMessage)) { // we silently fail if the snapshort part failed
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));				
			}

			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
	}
	
	public function configurationImportAction() {
		$this->isMethodPost();
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_CONFIGURATION_IMPORT, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $auditMessage \Audit\Container */
		
		try{
			//Get file from POST
			$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
			$fileTransfer->setDestination(FS::getGuiTempDir());
			
			if (! $fileTransfer->receive()) {
				$errorMessages = $fileTransfer->getMessages();
				if (isset($errorMessages['fileUploadErrorNoFile'])) {
					throw new WebAPI\Exception(_t('No file uploaded'), WebAPI\Exception::INVALID_PARAMETER);
				} else {
					throw new WebAPI\Exception(current($errorMessages), WebAPI\Exception::INVALID_PARAMETER);
				}
			}
			$this->importConfigurations($fileTransfer->getFileName());
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);

			$servers = $this->getServersMapper()->findAllServers();
			return array('servers' => $servers);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}
	}

	public function configurationResetAction() {
		$this->isMethodPost();
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_CONFIGURATION_RESET, ProgressMapper::AUDIT_PROGRESS_REQUESTED); /* @var $auditMessage \Audit\Container */
	
		try {
			$snapshot = $this->getSnapshotsMapper()->findSystemSnapshot();
			$snapshotId = $snapshot->getId();
			if (! $snapshotId) {
				throw new \Exception("Failed retrieving the bootStrap snapshot");
			}
			
			$data = $snapshot->getData();
			$filePath = FS::getGuiTempDir() . DIRECTORY_SEPARATOR . 'systemBoot.zip';
			file_put_contents($filePath, $data);
			$viewModel = $this->importConfigurations($filePath);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array('errorMessage' => $e->getMessage())));
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR, $e);
		}		

		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		$viewModel->setTemplate('configuration/web-api/1x3/configuration-reset');
		return $viewModel;
	}
	
	public function getServerInfoAction() {
		$this->isMethodGet();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('serverId'));
		$this->validateExistingServerId($params['serverId'], 'serverId');
		
		$serverData = $this->getRepliesMapper()->getServerInfoWithRetry($params['serverId']);
		
		$serverData['toolkitversion'] = '';
		$manager = new Manager();
		if ($manager->getOsType() == Manager::OS_TYPE_IBMI) {
			$toolkitPath = FS::createPath(getCfgVar('zend.install_dir'), 'share', 'ToolkitAPI', 'ToolkitService.php');
			if (FS::fileExists($toolkitPath)) {
				try {
					require_once($toolkitPath);
					if (class_exists('ToolkitService', false)) {
						$reflector = new ClassReflection('ToolkitService');
						if ($reflector->hasConstant('VERSION')) {
							$serverData['toolkitversion'] = $reflector->getConstant('VERSION');
						}
					}
				} catch (\Exception $e) {
					Log::notice('Could not retrieve toolkit version from IBMi Toolkit');
				}
			} else {
				Log::info('IBMi Toolkit cannot be found or is not installed');
			}
		}


		$serverData['gatewayversion'] = '';
		$gatewayModulePath = FS::createPath(getCfgVar('zend.install_dir'), 'share', 'ZendServerGateway', 'Module.php');
		if (FS::fileExists($gatewayModulePath)) {
			try {
				require_once($gatewayModulePath);
				if (class_exists('ZendServerGateway\Module', false)) {
					$module = new \ZendServerGateway\Module();
					$reflector = new ClassReflection('ZendServerGateway\Module');
					if ($reflector->hasMethod('getVersion')) {
						$serverData['gatewayversion'] = $reflector->getMethod('getVersion')->invoke($module);
					}
				}
			} catch (\Exception $e) {
				Log::info('Could not retrieve gateway version from ZendServerGateway module class'. $e->getMessage());
			}
		}

		$serverData['zsversion'] = Module::config('package', 'version');
		$serverData['build'] = Module::config('package', 'build'); //get the current build of server

        $libraries = $this->getDeploymentLibraryMapper()->getLibrariesListInfo();
		foreach ($libraries as $library) {
            if ($library['libraryName'] == 'Zend Framework 1') {
                $serverData['zfversion'] = $library['defaultVersion'];
            }
            if ($library['libraryName'] == 'Zend Framework 2') {
                $serverData['zf2version'] = $library['defaultVersion'];
            }
            if ($library['libraryName'] == 'Zend Server Gateway') {
                $serverData['gatewayversion'] = $library['greatestVersion'];
            }
        }
		
		return array('serverData' => $serverData);
		
	}

	public function licenseUpdatedAction() {
		$this->isMethodPost();
		$this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID,TasksMapper::COMMAND_LICENSE_UPDATED, TasksMapper::DUMMY_AUDIT_ID);
		
		return array();
	}
	
	public function serverStoreLicenseAction() {
		$parameters = $this->getParameters();
		try {
			$output = $this->forward()->dispatch('ConfigurationWebAPI-1_3', array('action' => 'serverValidateLicense'));
		} catch (Exception $ex) {
			$this->auditMessage(AuditTypeInterface::AUDIT_GUI_SAVELICENSE, ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('Order Number' => $parameters['licenseName'], 'Error Message' => $ex->getMessage()));
			throw $ex;
		}
		$output->setTemplate('configuration/web-api/1x3/server-validate-license');
		
		$directives = $this->getLocator('Configuration\MapperDirectives'); /* @var $directives \Configuration\MapperDirectives */
		$tasks = $this->getLocator('Zsd\Db\TasksMapper'); /* @var $tasks \Zsd\Db\TasksMapper */
		try {
			$auditId = $this->auditMessage(AuditTypeInterface::AUDIT_GUI_SAVELICENSE, ProgressMapper::AUDIT_NO_PROGRESS, array('Order Number' => $parameters['licenseName']))->getAuditId();
			$directives->setDirectives(array(
					'zend.serial_number' =>	$parameters['licenseValue'],
					'zend.user_name' =>	$parameters['licenseName'],
					));
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('step' => _t('Write to blueprint')));
			if (Module::isClusterManager()) {
				$directives->writeLicenseDirectivesToIni($parameters['licenseValue'], $parameters['licenseName']);
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array('step' => _t('Write to Zend Server ini file')));
			}

			$licTypes = array(NotificationContainer::TYPE_LICENSE_INVALID, NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE, NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45, NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15);	
			$this->getNotificationsMapper()->deleteByTypes($licTypes);
		} catch (\Exception $ex) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('Error Message' => $ex->getMessage()));
			Log::err("Could not store license: {$ex->getMessage()}");
			Log::debug($ex);
			throw new Exception(_t("Could not store license: %s", array($ex->getMessage())), Exception::INTERNAL_SERVER_ERROR, $ex);
		}
		return $output;
	}
	
	public function serverValidateLicenseAction() {		
		try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('licenseName', 'licenseValue'));
			$name = $this->validateString($params['licenseName'], 'licenseName');
			$serial = $this->validateString($params['licenseValue'], 'licenseValue');
		} catch (\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}
	
		$validator = new LicenseValidator($name);
		if (! $validator->isValid($serial)) {
			Log::err("non valid license received: " . print_r($validator->getMessages(), true));
			if (in_array(LicenseValidator::LICENSE_NOT_CLUSTER, array_keys($validator->getMessages()))) {
				throw new WebAPI\Exception(current($validator->getMessages()), WebAPI\Exception::CLUSTER_NOT_ALLOWED);
			}
			throw new WebAPI\Exception(current($validator->getMessages()), WebAPI\Exception::INVALID_PARAMETER); // @todo - create a INVALID_LICENSE constant?
		}

		try {
			$licenseDetails = $this->getLocator()->get('\Configuration\MapperDirectives')->getDirectivesValues(array('zend.serial_number', 'zend.user_name'));
			if (isset($licenseDetails['zend.user_name']) && isset($licenseDetails['zend.serial_number'])) {
				$currentLicenseSerial = $licenseDetails['zend.serial_number'];
				$currentLicenseName = $licenseDetails['zend.user_name'];
			} else {
				$currentLicenseSerial = $currentLicenseName = '';
			}
			
			$licenseChangeContainer = $this->getLicenseAnalyzer()->analyzeLicenseChange($serial, $name, $currentLicenseSerial, $currentLicenseName);
		} catch (\Exception $e) {
			Log::warn("Failed to analyze license change: " . $e->getMessage());
			$licenseChangeContainer = null;
		}		
		
		return array('licenseValidated' => true, 'licenseChangeContainer'=>$licenseChangeContainer);
	}
	
	// PRIVATE FUNCTIONS FROM HERE
	//----------------------------------------------------------------------------------------------------//

	/**
	 * @return \Configuration\License\LicenseChangeAnalyzer
	 */
	protected function getLicenseAnalyzer() {
		return $this->getLocator()->get('Configuration\License\LicenseChangeAnalyzer');
	}

	private function addSnapshot($name, $content) {
		log::info("will create new snapshot by name {$name}");
		$name == \Snapshots\Db\Mapper::SNAPSHOT_SYSTEM_BOOT ? $type = \Snapshots\Db\Mapper::SNAPSHOT_TYPE_SYSTEM : $type = \Snapshots\Db\Mapper::SNAPSHOT_TYPE_USER;
		return $this->getSnapshotsMapper()->addSnapshot($name, $content, $type);
	}
	
	private function returnServerInfo($serverId) {
		$this->getRequest()->setMethod('GET');
		$this->getRequest()->setQuery(new Parameters(array('servers'=>array($serverId))));
		return $this->forward()->dispatch('ServersWebAPI-1_2', array('action' => 'clusterGetServerStatus'));
	}
	
	private function determineSystemStatus($licenseInfo) {
		$systemStatus = $this->getServersMapper()->getSystemStatus();
		if ($systemStatus === ServerStatus::STATUS_RESTART_REQUIRED) {
			return ServerStatus::getServerStatusAsString(ServerStatus::STATUS_RESTART_REQUIRED);// if pendingRestart, then license problems might be solved after a restart
		}
	
		if (! $licenseInfo->isLicenseOk()) { // we assume in cluster, that the state of the node's license reflects the cluster license (we don't deal with the situation where in a certain node, someone edited manually the license directives)
			return WebAPI12Controller::STATUS_NOT_LICENSED;
		}
	
		if ($systemStatus === ServerStatus::STATUS_ERROR) {
			return ServerStatus::getServerStatusAsString(ServerStatus::STATUS_ERROR);
		}
	
		return ServerStatus::getServerStatusAsString(ServerStatus::STATUS_OK);
	}
	
	private function overrideDiskValueByMessage(Set $directivesSet, $extension) {
		$serversIds = $this->getServersMapper()->findRespondingServersIds();
				
		$zsdMessages = $this->getDirectivesMessages(array($extension => true));
		$directivesSet = $directivesSet->toArray();
		if (isset($zsdMessages[$extension]) && count($zsdMessages[$extension]) > 0) {
			foreach (current($zsdMessages[$extension]) as $message) {
				if (in_array($message['NODE_ID'], $serversIds)) {
					$directive = isset($directivesSet[$message['MSG_KEY']]) ? $directivesSet[$message['MSG_KEY']] : false; /* @var $directive \Configuration\DirectiveContainer */
					if ($directive) {
						$details = Json::decode($message['DETAILS']);
						$directive['DISK_VALUE'] = $details[2];
						$directivesSet[$message['MSG_KEY']] = $directive;
					}
				}
			}
		}
	
		return new Set($directivesSet);
	}
	
	/**
	 * @param string $filePath
	 * @return \Zend\View\Model\ViewModel
	 */
	private function importConfigurations($filePath) {
		/** @var $renderer \Zend\View\Renderer\PhpRenderer */
		$archive = FS::getZipArchive($filePath, 0);
		$monitorRules = $archive->getFromName(self::EXPORT_MONITOR_FILE);
		$pageCacheRules = $archive->getFromName(self::EXPORT_PAGECACHE_FILE);
		$configurationSql = $archive->getFromName(self::EXPORT_SQL_FILE);
		$directivesSql = $archive->getFromName(self::EXPORT_SQL_DIRECTIVES);
		$extensionsSql = $archive->getFromName(self::EXPORT_SQL_EXTENSIONS);
		$profileToImport = $archive->getFromName(self::METADATA_FILE);
		$archive->close();	

		if (! $configurationSql) {
			$configurationSql = $directivesSql.PHP_EOL.$extensionsSql;
		}
		
		if((! $configurationSql) || !$pageCacheRules || !$monitorRules){
			throw new WebAPI\Exception('Not a valid configuration export file.', WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		//----------------- CHECK THE PROFILE OF SUITABILITY --------------------
		$profile = $this->getNodesProfileMapper()->getProfile();

		Log::info("Package profile: '{$profileToImport}'");
		
		$profileToImport = explode(',', $profileToImport);
		
		// --- ZSRV-15005 ---
		// change webserver to nginx in case we detect webserver_type directive with ngnix
		$directivesRows = explode(PHP_EOL, $configurationSql);
		foreach ($directivesRows as $row) {
		    if (strpos($row, 'zend.webserver_type') !== false) { // check only the webserver_type row
		        if (strpos($row, 'nginx') !== false) {
		            $profileToImport[2] = 'Nginx';
		        }
		    }
		}
		
		$configurationSql = $this->filterDirectives($configurationSql);
		
		if (isset($profile['OS']) && isset($profileToImport[0])) {
			// for example, Linux != WINNT
			if ($profile['OS'] != $profileToImport[0]) {
				throw new WebAPI\Exception('Inappropriate configuration. Tried to import from \'' . $profileToImport[0] . '\' to \'' . $profile['OS'] . '\'.', WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
		}
		if (isset($profile['ARCH']) && isset($profileToImport[1])) {
			// for example, 64 != 32
			if ($profile['ARCH'] != $profileToImport[1]) {
				throw new WebAPI\Exception('Inappropriate configuration. Tried to import from ' . $profileToImport[1] . '-bit Operating System to ' . $profile['ARCH'] . '.', WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
		}
		if (isset($profile['PHPVERSION']) && isset($profileToImport[3])) {
			// sanitize php version so it consists only major.minor
			$profilePhpVersion = $this->sanitizePhpVersion($profile['PHPVERSION']);
			$importPhpVersion = $this->sanitizePhpVersion($profileToImport[3]);
			// for example, 5.3 != 5.4
			if ($profilePhpVersion != $importPhpVersion) {
				throw new WebAPI\Exception('Inappropriate configuration. Tried to import from PHP version \'' . $importPhpVersion . '\' to \'' . $profilePhpVersion . '\'.', WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
		}
		if (isset($profile['WEBSERVER']) && isset($profileToImport[2])) {
			// for example, Apache != IIS
			if ($profile['WEBSERVER'] != $profileToImport[2]) {
				throw new WebAPI\Exception('Inappropriate configuration. Tried to import from Web server \'' . $profileToImport[2] . '\' to \'' . $profile['WEBSERVER'] . '\'.', WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
		}
		
		//----------------- RUN THE SQL SCRIPT --------------------
		$dbImport = $this->getLocator('Configuration\DbImport'); /* @var $dbImport \Configuration\DbImport */
		try {
			$dbImport->importDatabase($configurationSql);
		} catch (\ZendServer\Exception $ex) {
			Log::debug($ex);
			throw new Exception($ex->getMessage(), Exception::INTERNAL_SERVER_ERROR, $ex);
		}
			
		//----------- ADD THE PAGE CACHE RULES FROM XML ------------
		$this->getRequest()->getPost()->set('paceCacheRules', $pageCacheRules);
		$pageCacheRulesXml = $this->forward()->dispatch('PageCacheWebApi-1_3'
				, array('action' => 'pagecacheImportRules')
		);
		
		//------------- ADD MONITORING RULES FROM XML ---------------
		$this->getRequest()->getPost()->set('monitorRules', $monitorRules);
		$monitorRulesXml = $this->forward()->dispatch('MonitorRulesWebApi-1_3'
				, array('action' => 'monitorImportRules')
		);
		
		return $monitorRulesXml;
	}
	
	// filter directives
	private function filterDirectives($configurationSql) {
	    $directivesToFilter = array('zray.extensions_dir');
	    
	    $directivesRows = explode(PHP_EOL, $configurationSql);
	    foreach ($directivesRows as $key => $row) {
	        foreach ($directivesToFilter as $directiveToFilter) {
	            if (strpos($row, $directiveToFilter) !== false) { // check only the webserver_type row
	                unset($directivesRows[$key]);
	            }
	        }
	    }
	     
	    return implode(PHP_EOL, $directivesRows);
	}
	
	private function tweakExtensionsProperties($extensions) {
		$modifiedExtensions = array();
		foreach ($extensions as $idx=>$extension) {/* @var $extension \Configuration\ExtensionContainer */
			$extName = $extension->getName();
			if (! $this->isExtensionDisplayable($extName)) {
				continue;
			}
			elseif ($extName === 'Zend Deployment') {
				$extension->setIsLoaded('true');
			}
			elseif ($extName === 'Zend Extension Manager') {
				$extension->setDummy('true');
			}
			elseif ($extName === 'Zend Utils') {
				$extension->setDummy('true');
			}
				
			$modifiedExtensions[$idx] = $extension;
		}
	
		return new Set($modifiedExtensions, null);//already containers
	}
	
	private function isExtensionDisplayable($extName) {
		if ($extName === 'Zend Monitor UI' || $extName === 'Zend Extension Manager' || $extName === 'Zend Utils') { // non-relevant or of no interest
			return false;
		} elseif ($extName === 'Zend Download Server' || $extName === 'Zend Cluster Utils') { // @todo - remove this, once ZSRV-7325 is fixed
			return false;
		} elseif ($extName === 'Zend Session Clustering' && Module::isSingleServer()) { // no SC in single
			return false;
		}
		
		return true;
	}
	
	private function addPreviousValue(array $directives) {
		$directiveMessages = $this->getMessagesMapper()->findAllDirectivesMessages();
		foreach ($directiveMessages as $message) { /* @var $message MessageContainer */
			$directive = $message->getMessageKey();
			if (isset($directives[$directive])) {
				$details = $message->getMessageDetails(); // has the form array(directiveName, oldValue, NewValue), without keys
				if (isset($details[1])) {
					$directives[$directive]['previousValue'] = $details[1];
				}
			}
		}
	
		return new Set($directives);
	}	
	
	private function getDaemonDataByExtension($extensionName, $daemonsData) {		
		if (($daemon = $this->getDaemonByExtension($extensionName)) !== false) {
			foreach ($daemonsData as $daemonContainer) { /* @var $daemonContainer \Configuration\DaemonContainer */				
				if ($daemonContainer->getName() === $daemon) {
					return $daemonContainer;
				}				
			}
		}
		
		return $this->createEmptyDaemonContainer();
	}
	
	private function createEmptyDaemonContainer() {
		return new \Configuration\DaemonContainer(array('status' => 'None'));
	}
	
	private function getDaemonByExtension($extensionName) {
		$daemonsWithExtensionKeys = array_flip($this->daemons);
		if (isset($daemonsWithExtensionKeys[$extensionName])) {
			return $daemonsWithExtensionKeys[$extensionName];
		}
	
		return false;
	}
	
	private function getDaemonsData() {
		$daemonsData = array();
		foreach (array_keys($this->daemons) as $daemon) {
			$daemonsData[$daemon]['name'] = $daemon;
		}
	
		foreach ($this->getDaemonsMessages() as $daemon => $messagesWithNode) {
			foreach ($messagesWithNode as $nodeName => $daemonMessages) {
				foreach ($daemonMessages as $daemonMessage) {
					$daemonsData[$daemon]['MessageList'][$nodeName][] = $daemonMessage;					
				}
			}
		}
		
		$daemonSet = new Set($daemonsData, '\Configuration\DaemonContainer');
		$daemonSet = $this->addDaemonsData($daemonSet);
		return $daemonSet;
	}		
	
	/**
	 * Filters the extensions list matching the filter in ext name and descriptions(long and short), 
	 * also in the directives names and their descriptions.
	 * 
	 * @param Set $extensions of ExtensionContainer|DaemonContainer
	 * @param string $filter
	 * @return Set
	 */
	private function searchExtensions($extensions, $filter) {		
		$filter = trim($filter); // trim all spaces		
		$filter = strtolower($filter); // case insensitive search
		if (! $filter) {
			return $extensions;
		}

		$ddMapper = $this->getDdMapper();
		$filteredExtensions = $extensionsToCheck = $directivesToCheck = array();
		foreach ($extensions as $extension) {			
			if (! $extension->getName()) {
				continue;
			}
			
			if ($ddMapper->matchExtensionStrings($extension, $filter) || strstr(strtolower($extension->getName()), $filter)) { // second check is in case we're dealing with user defined ext that has no metadata
				$filteredExtensions[$extension->getName()] = $extension;
			} elseif(($matchedDirectives = $ddMapper->matchExtensionDirectives($extension, $filter))) {
				$extensionsToCheck[$extension->getName()] = $extension;
				$directivesToCheck[$extension->getName()] = $matchedDirectives;
			}
		}
		
		if ($directivesToCheck) {
			foreach ($directivesToCheck as $extensionName => $directives) {
				$directives = $this->getDdMapper()->addDirectivesMetadataOnly($directives, $extensionName);
				foreach ($directives as $directive) { /* @var $directive \Configuration\DirectiveContainer */
					if ($directive->isVisible()) {
						$filteredExtensions[$extensionName] = $extensionsToCheck[$extensionName];
						break; // at least one visible directive is enough to show the extension
					}
				}
			}
		}

		return $filteredExtensions;
	}
	
	/**
	 * @param Set|Array $directives of DirectiveContainers
	 */
	private function filterToUserDirectives($directives) {
		$filteredDirectives = array();
		
		foreach ($directives as $idx=>$directive) {  /* @var $directive \Configuration\DirectiveContainer */
			if ($directive->isVisible()) {
				$filteredDirectives[$idx] = $directive->toArray();
			}
		}
		
		return new Set($filteredDirectives, null); // as we already have containers
	}
	
	/**
	 * @todo create a common function for extensions and directives
	 * Filters the directives list matching the filter in the directives names and their descriptions.
	 *
	 * @param \ZendServer\Set $directives
	 * @param string $filter
	 * @return \ZendServer\Set
	 */
	private function searchDirectives(Set $directives, $filter) {	
		$filter = trim($filter);// trim all spaces		
		$filter = strtolower($filter);// case insensitive search
		if (!$filter) return $directives;		

		$ddMapper = $this->getDdMapper();
		$filteredDirectives = array();
		foreach ($directives as $idx => $directive) {
			$isMatched = $ddMapper->matchDirective($directive, $filter);
			if ($isMatched) {
				$filteredDirectives[$idx] = $directive;
			}
		}
		
		return new Set($filteredDirectives, null); // already a set of \Configuration\DirectiveContainer

	}
	
	private function preProcessDisableEnableActions() {
		try {
			$this->isMethodPost();
			$params = $this->getParameters();
			$this->validateMandatoryParameters($params, array('extensions'));
			$extensions = $params['extensions'];
			$this->validateArrayNonEmpty($extensions, 'extensions');
		} catch (\Exception $e) {
			Log::err("{$this->getCmdName()} - input validation failed: %s", array($e->getMessage()));
			throw $e;
		}		 	
		
		return $this->filterExtensions($extensions);
	}
	
	private function disbleEnableExtensions($toEnable, $extensions) {
		try {
			$toEnable ? $type = auditMapper::AUDIT_EXTENSION_ENABLED : $type = auditMapper::AUDIT_EXTENSION_DISABLED;
			$auditMessage = $this->auditMessage($type, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array($extensions)); /* @var $auditMessage \Audit\Container */
						
			$this->getTasksMapper()->insertTask(TasksMapper::DUMMY_NODE_ID, $toEnable ? TasksMapper::COMMAND_ENABLE_EXTENSION : TasksMapper::COMMAND_DISABLE_EXTENSION, $extensions);
		} catch (\Exception $e) {
			Log::err("{$this->getCmdName()}  failed: %s" , array($e->getMessage()));
			throw new WebAPI\Exception(_t("%s failed: %s", array($this->getCmdName(), $e->getMessage())), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}		
	}
	
	private function postProcessDisableEnableActions($extensions, $isEnable) {
	    
	    $brokenPlugin = '';
	    $isValidPlugin = true;
	    foreach ($extensions as $extension) {
	        if (!$isEnable) {
	            $value = 'disabled';
	        } else {
	            $value = 'enabled';
	        }
	        $isValidPlugin = $isValidPlugin && $this->isExtensionChangePluginPrerequisitesValid($extension, $value, $brokenPlugin);
	    }
	    if(!$isValidPlugin) {
	        $this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
	    } else {
		    $this->getNotificationsMapper()->deleteByType(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
	    }
	    
		$extensionsData = $this->getExtensionsMapper()->selectExtensions($extensions);
		$extensionsData = $this->convertSetToExtensionsArray($extensionsData);
		
		$this->setHttpResponseCode('202', 'Accepted');
		$viewModel = new ViewModel(array('extensions' => $this->addExtensionsData($extensionsData)));
		$viewModel->setTemplate('configuration/web-api/configuration-extensions-list');
		return $viewModel;		
	}

	private function filterExtensions($extensions) {
	    $filteredExtensions = array();
	    foreach ($extensions as $extension) {
	        if (in_array(trim($extension), array('Zend Server Z-Ray', 'Zend URL Insight'))) {
	            continue;
	        }
	        $filteredExtensions[] = $extension; 
	    }
	    $extensions = $filteredExtensions;
	    
		$extensionsData = $this->getExtensionsMapper()->selectExtensions($extensions);
		
		if (sizeof($extensionsData) < sizeof($extensions)) { // some of the extensions passed are unknown
			$ExtensionsFound = array();
			foreach ($extensionsData as $extension) { /* @var $extension \Configuration\ExtensionContainer */
				$ExtensionsFound[] = $extension->getName();
			}
			
			$unknownMessages = implode(',', array_diff($extensions, $ExtensionsFound));
			Log::err("{$this->getCmdName()} failed - the following unknown extension(s) were passed: '{$unknownMessages}'");
			throw new WebAPI\Exception(_t("%s failed - the following unknown extension(s) were passed: %s", array($this->getCmdName(),$unknownMessages)), WebAPI\Exception::NO_SUCH_EXTENSION);			
		}
	
		$filteredExtensions = array();
		foreach($extensionsData as $extension) { /* @var $extension \Configuration\ExtensionContainer */
			if ($extension->isBuiltIn()) {
				Log::warn("extension '{$extension->getName()}' is built-In - cannot exec {$this->getCmdName()} on it");
				continue;
			}
	
			$filteredExtensions[] = $extension->getName();
		}
	
		if (!$filteredExtensions) {
			Log::err("{$this->getCmdName()} failed - no valid extension passed");
			throw new WebAPI\Exception(_t("%s failed - no valid extension passed (either unknown or built-in)",array($this->getCmdName())), WebAPI\Exception::INVALID_PARAMETER);			
		}
		
		return $filteredExtensions;
	}
	
	private function getExtensionsMessages($allExtensions) {
		$extensionsMessages = array();
		$serversNames = $this->getServersMapper()->findAllServersNamesByIds();
		$extensionsMessagesDb = $this->getMessagesMapper()->findAllExtensionsMessages(array_keys($serversNames));
		
		foreach ($extensionsMessagesDb as $extensionsMessage) { /* @var $extensionsMessage MessageContainer */
			$extension = $extensionsMessage->getMessageKey();
			if (!isset($allExtensions[$extension])) { // $allExtensions are actually a certain subset of the extension data (php|zend), hence not all errors are relevant
				continue;
			}
			
			if ($extensionsMessage->getMessageNodeId() === -1) {
				foreach ($serversNames as $serversName) {
					$extensionsMessages[$extension][$serversName][] = $extensionsMessage->toArray(); // convert toArray as to ease adding it to directivesMessage
				}
			}
			else {
				$extensionsMessages[$extension][$this->getNodeName($serversNames, $extensionsMessage)][] = $extensionsMessage->toArray(); // convert toArray as to ease adding it to directivesMessage
			}			
		}
		
		return $extensionsMessages;
	}	
	
	private function getDirectivesMessages($allExtensions) {
		$directivesMessages = array();
		$directivesMessagesDb = $this->getMessagesMapper()->findAllDirectivesMessages();
		$serversNames = $this->getServersMapper()->findAllServersNamesByIds();		
		$messageKeys = array_unique(array_map(function($directivesMessage) {
			return $directivesMessage['MSG_KEY'];
		}, $directivesMessagesDb->toArray()));
		
		$directivesWithMessages = $this->getDirectivesMapper()->selectSpecificDirectives($messageKeys)->toArray();
		$directivesWithMessagesHash = array();
		foreach ($directivesWithMessages as $directive) {
			$directivesWithMessagesHash[$directive['NAME']] = $directive;
		}
		
		foreach ($directivesMessagesDb as $directivesMessage) { /* @var $directivesMessage MessageContainer */
			$extension = $directivesWithMessagesHash[$directivesMessage->getMessageKey()]['EXTENSION'];
			$daemon = $directivesWithMessagesHash[$directivesMessage->getMessageKey()]['DAEMON'];
			/// If this is a daemon extension, unify it into the extension messages
			if ($daemon && isset($this->daemons[$daemon]) && isset($allExtensions[$this->daemons[$daemon]])) {
				$extension = $this->daemons[$daemon];
			}
			
			if (!isset($allExtensions[$extension])) {
				//ignoring {$extension} error - not a PHP extension
				continue;
				// @todo - cannot throw ext, as of zend extensions ...throw new ZSException("Could not parse error messages - extension {$extension} not found");
			}			
			
			if ($directivesMessage->getMessageNodeId() === -1) {
				foreach ($serversNames as $serversName) {
					$directivesMessages[$extension][$serversName][] = $directivesMessage->toArray(); // convert toArray as to ease adding it to directivesMessage
				}
			}
			else {
				$directivesMessages[$extension][$this->getNodeName($serversNames, $directivesMessage)][] = $directivesMessage->toArray(); // convert toArray as to ease adding it to directivesMessage
			}
		}
	
		return $directivesMessages;
	}

	private function getDaemonsMessages() {
		$Messages = array();
		$MessagesDb = $this->getMessagesMapper()->findAllDaemonsMessages();
		$serversNames = $this->getServersMapper()->findAllServersNamesByIds();
	
		foreach ($MessagesDb as $messageContainer) { /* @var $messageContainer MessageContainer */
			$daemon = $messageContainer->getMessageKey();
			if ($daemon === 'PHP' || $daemon === 'zend_database') {
				continue; // ignoring for now PHP|zend_database related messages
			}
			
			if (!isset($this->daemons[$daemon])) {
				//continue;
				throw new WebAPI\Exception(_t("Unknown daemon error found: %s", array($daemon)), WebAPI\Exception::INTERNAL_SERVER_ERROR); // @todo - remove this once stable
			}
				
			if ($messageContainer->getMessageNodeId() === -1) {
				foreach ($serversNames as $serversName) {
					$Messages[$daemon][$serversName][] = $messageContainer->toArray();
				}
			}
			else {
				$Messages[$daemon][$this->getNodeName($serversNames, $messageContainer)][] = $messageContainer->toArray();
			}
		}
	
		return $Messages;
	}	
	
	/**
	 * 
	 * @param array $serversNames
	 * @param MessageContainer $messageContainer
	 */
	private function getNodeName($serversNames, $messageContainer) {
		if (isset($serversNames[$messageContainer->getMessageNodeId()])) {
			return $serversNames[$messageContainer->getMessageNodeId()];
		}
	}
		
	private function addExtensionsData($extensions) {
		return $this->getDdMapper()->addExtensionsData($extensions);
	}	
	
	private function addDaemonsData($daemons) {
		return $this->getDdMapper()->addDaemonsData($daemons);
	}
	
	/**
	 * @param string $type
	 * @throws WebAPI\Exception
	 */
	private function validateType($type) {
		return $this->validateAllowedValues($type, 'type', array('all', 'zend', 'php'));
	}
	
	/**
	 * @param string $order
	 * @throws WebAPI\Exception
	 */
	private function validateOrder($order) {
		return $this->validateAllowedValues($order, 'order', array('name', 'status'));
	}	
	
	/**
	 * @return DdMapper
	 */
	private function getDdMapper() {
		return $this->getServiceLocator()->get('Configuration\DdMapper');
	}

	private function addExtensionsErrors($extensions) {
		$extensionMessages = $this->getExtensionsMessages($extensions);
		$directiveMessages = $this->getDirectivesMessages($extensions); // we would like also to display in the extension row, it's directive related messages	
		$this->addErrorsToContainer($extensions, $extensionMessages);
		$this->addErrorsToContainer($extensions, $directiveMessages);
		
		return $extensions;
	}
	
	private function addErrorsToContainer($extensions, $messages) {
		foreach ($messages as $extName => $extensionsWithNode) {
			foreach($extensionsWithNode as $nodeName => $extensionsMessages) {
				foreach ($extensionsMessages as $extensionsMessage) {
					$extensions[$extName]->setMessageList($extensionsMessage, $nodeName);
				}				
			}
		}	
	}
	
	/**
	 * 
	 * @param array $extensions
	 * @param string $direction - ASC/DESC - assuming that a->z is ASC
	 */
	private function sortExtensionsByName(array $extensionsByName, $direction='ASC') {
		uksort($extensionsByName, 'strcasecmp'); // non case sensitive
		
		if ($direction === 'DESC') {
			$extensionsByName = array_reverse($extensionsByName);
		}		
		
		return $extensionsByName;
	}
	
	
	/**
	 *
	 * @param array $extensions
	 * @param string $direction - ASC/DESC - assuming (arbitrarly) that error->not_loaded->dummy->loaded->builtin is ASC
	 */
	private function sortExtensionsByStatus(array $extensions, $direction='ASC') {		
		$extensionsError = $extensionsNotLoaded = $extensionsDummy = $extensionsLoaded = $extensionsBuiltIn = array();
		$extensionsByName = $this->sortExtensionsByName($extensions);
		
		foreach ($extensionsByName as $extName => $extension) {/* @var $extension \Configuration\ExtensionContainer */
			if ($extension->isDummy()) {
				$extensionsDummy[$extName] = $extension;
			}
			elseif ($extension->getMessageList()) {
				$extensionsError[$extName] = $extension; // not neccessarily error - could also be an extension that was disabled/enabled
			}
			elseif ($extension->isBuiltIn()) {
				$extensionsBuiltIn[$extName] = $extension;					
			}
			elseif ($extension->isLoaded()) {
				$extensionsLoaded[$extName] = $extension;
			}			
			else {
				$extensionsNotLoaded[$extName] = $extension;
			}
		}		

		if ($direction === 'ASC') {
			$extensionsByStatus = array_merge($extensionsError, $extensionsNotLoaded, $extensionsLoaded, $extensionsBuiltIn, $extensionsDummy);
		}
		else {
			$extensionsByStatus = array_merge($extensionsBuiltIn, $extensionsLoaded, $extensionsNotLoaded, $extensionsError, $extensionsDummy);
		}
		
		return $extensionsByStatus;
	}	
	
	/**
	 * this method takes a Set, and returns an associative array based on the extension name
	 * @param \ZendServer\Set $extensions
	 */
	private function convertSetToExtensionsArray(\ZendServer\Set $extensions) {
		$extensionsByName = array();
		foreach ($extensions as $extension) { /* @var $extension \Configuration\ExtensionContainer */
			$extensionsByName[$extension->getName()] = $extension; // names as keys
		}
	
		return $extensionsByName;
	}

	/**
	 * this method takes a Set, and returns an associative array based on the daemon name
	 * @param \ZendServer\Set $daemons
	 */
	private function convertSetToDaemonsArray(\ZendServer\Set $daemons) {
		$daemonsByName = array();
		foreach ($daemons as $daemon) { /* @var $extension \Configuration\DaemonContainer */
			$daemonsByName[$daemon->getName()] = $daemon; // names as keys
		}
	
		return $daemonsByName;
	}
	
	private function convertSetToDirectivesArray(array $directives) {
		$directivesByName = array();
		foreach ($directives as $directive) {
			$directivesByName[$directive['NAME'] . '_' . $directive['EXTENSION'] . '_' . $directive['DAEMON']] = $directive; // names as keys
		}
	
		return $directivesByName;
	}
	
	/**
	 * this method adds dummy flags to all extensions passed
	 * @param $extensions
	 */
	private function addDummyFlag($extensions) {
		$extensionsDummied = array();
		foreach ($extensions as $name=>$extension) { /* @var $extension \Configuration\ExtensionContainer */
			$extension->setDummy("true");
			$extensionsDummied[$name] = $extension;
		}
	
		return $extensionsDummied;
	}

	/**
	 * this method adds the dummy extensions to our list of named extensions
	 * @param array $extensionsByName
	 */
	private function addDummyExtensions($extensionsByName, $extType) {		
		$dummyExtensions = $this->getExtensionsMapper()->selectExtensions($this->getDdMapper()->getDummyExtensions());
		$dummyExtensions = $this->convertSetToExtensionsArray($dummyExtensions);
		$dummyExtensions = $this->filterDummyExtensions($dummyExtensions, $extType); // we don't want for instance a dummy php extension, if we asked for zend components
		$dummyExtensions = $this->addDummyFlag($dummyExtensions);
	
		$extensionsByName = array_merge($dummyExtensions, $extensionsByName);
	
		return $extensionsByName;
	}	
	
	private function filterDummyExtensions($dummyExtensions, $extType) {
		$dummyExtensionsFiltered = array();
		foreach ($dummyExtensions as $idx=>$extension) { /* @var $extension \Configuration\ExtensionContainer */
			if ($extension->isInType($extType) && !$this->isFilteredExtension($extension)) {
				$dummyExtensionsFiltered[$extension->getName()] = $extension;
			}
			
		}
		
		return $dummyExtensionsFiltered;		
	}

	private function isFilteredExtension(\Configuration\ExtensionContainer $extension) {
		return $extension->getName() === 'Zend Global Directives'; // We don't want to display this extension - holds directives which are not visible in the GUI anyhow
	}	
	
	private function convertZSEdition() {	
		if(Module::isClusterServer()) {
			return 'ZendServerCluster';
		}
	
		return 'ZendServer';
	}
	
	private function isDirectiveChangePrerequisitesValid($directiveName, $newValue) {
		$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
		$configurationContainer->createConfigurationSnapshot(array($directiveName), array());
		$directives = $configurationContainer->getDirectives();
	
		$directivesArray = $directives->toArray();
		$directivesArray[$directiveName]['DISK_VALUE'] = $newValue;
		$directives = new Set($directivesArray, '\Configuration\DirectiveContainer');
	
		$configurationContainer->setDirectives($directives);
	
		$deploymentModel = $this->getLocator()->get('Deployment\Model');
		
		try {
			$configurations = $deploymentModel->getAllApplicationsPrerequisited();
		} catch (\Exception $e) {
			$configurations = array();
			// ignore corrupted application packages, fixing bug #ZSRV-12845
		}
		
		foreach ($configurations as $configuration) {
			if (! $configuration->isValid($configurationContainer)) {
				$messages = $configuration->getMessages();
				if (isset($messages['directive'][$directiveName])) {
					$keys = array_keys($messages['directive'][$directiveName]);
					if (false === strpos($keys[0], 'valid')) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	private function isDirectiveChangePluginPrerequisitesValid($directiveName, $newValue, &$brokenPlugin) {
	    $configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
	    $configurationContainer->createConfigurationSnapshot(array($directiveName), array());
	    $directives = $configurationContainer->getDirectives();
	    
	    $directivesArray = $directives->toArray();
	    $directivesArray[$directiveName]['DISK_VALUE'] = $newValue;
	    $directives = new Set($directivesArray, '\Configuration\DirectiveContainer');
	    
	    $configurationContainer->setDirectives($directives);
	    
	    $deploymentModel = $this->getLocator()->get('Plugins\Mapper');
	    
	    // validate the plugins prerequisires
	    try {
	        $configurations = $deploymentModel->getAllPluginsPrerequisited();
	    } catch (\Exception $e) {
	        $configurations = array();
	        // ignore corrupted plugin packages
	    }
	    
	    foreach ($configurations as $plugin => $configuration) {
	        if (! $configuration->isValid($configurationContainer)) {
	            $messages = $configuration->getMessages();
	            if (isset($messages['directive'][$directiveName])) {
	                $keys = array_keys($messages['directive'][$directiveName]);
	                if (false === strpos($keys[0], 'valid')) {
	                    $brokenPlugin = $plugin;
	                    return false;
	                }
	            }
	        }
	    }
	    
	    return true;
	}
	
	private function isExtensionChangePluginPrerequisitesValid($extensionName, $newValue, &$brokenPlugin) {
		$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
		$configurationContainer->createConfigurationSnapshot();
		$extensions = $configurationContainer->getExtensions();
		
		$extensionsArray = $extensions->toArray();
		if ($newValue == 'disabled') {
			$extensionsArray[$extensionName]['IS_LOADED'] = 0;
		} else {
			$extensionsArray[$extensionName]['IS_LOADED'] = 1;
		}
		$extensions = new Set($extensionsArray, '\Configuration\ExtensionContainer');
		
		$configurationContainer->setExtensions($extensions);
	
		$deploymentModel = $this->getLocator()->get('Plugins\Mapper');
		$configurations = $deploymentModel->getAllPluginsPrerequisited();
	
		foreach ($configurations as $plugin => $configuration) {
			if (! $configuration->isValid($configurationContainer)) {
				$messages = $configuration->getMessages();
				if (isset($messages['extension'][$extensionName])) {
					$keys = array_keys($messages['extension'][$extensionName]);
					if (false === strpos($keys[0], 'valid')) {
					    $brokenPlugin = $plugin;
						return false;
					}
				}
				
				if (isset($messages['zendservercomponent'][strtolower($extensionName)])) {
				    $keys = array_keys($messages['zendservercomponent'][strtolower($extensionName)]);
				    if (false === strpos($keys[0], 'valid')) {
				        $brokenPlugin = $plugin;
				        return false;
				    }
				}
			}
		}
	
		return true;
	}
	
	private function isExtensionChangePrerequisitesValid($extensionName, $newValue) {
	    $configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
	    $configurationContainer->createConfigurationSnapshot();
	    $extensions = $configurationContainer->getExtensions();
	
	    $extensionsArray = $extensions->toArray();
	    if ($newValue == 'disabled') {
	        $extensionsArray[$extensionName]['IS_LOADED'] = 0;
	    } else {
	        $extensionsArray[$extensionName]['IS_LOADED'] = 1;
	    }
	    $extensions = new Set($extensionsArray, '\Configuration\ExtensionContainer');
	
	    $configurationContainer->setExtensions($extensions);
	
	    $deploymentModel = $this->getLocator()->get('Deployment\Model');
	    $configurations = $deploymentModel->getAllApplicationsPrerequisited();
	
	    foreach ($configurations as $configuration) {
	        if (! $configuration->isValid($configurationContainer)) {
	            $messages = $configuration->getMessages();
	            if (isset($messages['extension'][$extensionName])) {
	                $keys = array_keys($messages['extension'][$extensionName]);
	                if (false === strpos($keys[0], 'valid')) {
	                    return false;
	                }
	            }
	            if (isset($messages['zendservercomponent'][strtolower($extensionName)])) {
				    $keys = array_keys($messages['zendservercomponent'][strtolower($extensionName)]);
	                if (false === strpos($keys[0], 'valid')) {
	                    return false;
	                }
	            }
	        }
	    }
	
	    return true;
	}
	
	/**
	 * sanitize php version so it consists only major.minor
	 * @param string $version
	 * @return string
	 */
	private function sanitizePhpVersion($version) {
		$exploded = explode('.', $version);
		
		$major = 0;
		$minor = 0;
		
		if (isset($exploded[0]) && ! empty($exploded[0])) {
			$major = $exploded[0];
		}
		
		if (isset($exploded[1]) && ! empty($exploded[1])) {
			$minor = $exploded[1];
		}
		
		return $major . '.' . $minor;
	}
}
