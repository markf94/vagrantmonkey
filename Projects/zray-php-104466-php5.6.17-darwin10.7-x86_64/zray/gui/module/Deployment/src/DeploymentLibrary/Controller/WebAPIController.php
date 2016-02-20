<?php
namespace DeploymentLibrary\Controller;

use ZendServer\Mvc\Controller\WebAPIActionController,
	Deployment\Application\Package,
	ZendDeployment_Manager,
	ZendServer\Log\Log,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper,
	Zend\View\Model\ViewModel,
	ZendServer\FS\FS,
	ZendServer\Text,
	ZendServer\Set,
	Zend\Http\Headers,
	Notifications\NotificationContainer,
	ZendServer\Exception;
use DeploymentLibrary\Prerequisites\Validator\Dependents\HasDependentsLibrary;
use DeploymentLibrary\Prerequisites\Validator\Dependents\HasDependentsLibraryVersion;

class WebAPIController extends WebAPIActionController
{
	public function libraryGetStatusAction() {
		$this->isMethodGet();
		$params = $this->getParameters(
			array('libraries' => array(), 'direction' => 'ASC')
		);
		
		$libraries = $this->validateArray($params['libraries'], 'libraries');
		foreach ($libraries as $idx=>$library) {
			$this->validateString($library, "libraries[{$idx}]");
		}
		$deploymentLibraryMapper = $this->getDeploymentLibraryMapper(); /* @var $deploymentLibraryMapper \DeploymentLibrary\Mapper */
		$deployedLibraries = $deploymentLibraryMapper->getLibrariesByIds($libraries, $params['direction']);

		$serversMapper = $this->getServersMapper();
		$serversData = $serversMapper->findAllServers();
		
		return array('libraries' => $deployedLibraries, 'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers()) , 'serversInfoData' => $serversData);
	}
	
	public function libraryVersionGetStatusAction() {
		$this->isMethodGet();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('libraryVersionId'));
		$libraryVersionId = $this->validateInteger($params['libraryVersionId'], 'libraryVersionId');
	
		$deploymentLibraryMapper = $this->getDeploymentLibraryMapper(); /* @var $deploymentLibraryMapper \DeploymentLibrary\Mapper */
		$deployedLibraries = $deploymentLibraryMapper->getLibrariesByIds(array());
		
		$libraryToReturn = null;
		foreach ($deployedLibraries as $lib) { /* @var $lib \DeploymentLibrary\Container */
			foreach ($lib->getVersions() as $version) {
				if ($version['libraryVersionId'] == $libraryVersionId) {
					$libraryToReturn = $lib;
					$libraryToReturn->setVersions(array($version['libraryVersionId'] => $version));
					$libsSet = new Set(array($libraryToReturn->toArray()));
					$libsSet->setHydrateClass('\DeploymentLibrary\Container');
					$prerequisites = $deploymentLibraryMapper->getLibraryVersionPrerequisites($libraryVersionId);
					// use the library get server view to show the single library version + depedencies (if has) and its library info
					$viewModel = new ViewModel(array(	'libraries' => $libsSet,
														'prerequisites' => $prerequisites,
														'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers())));
					$viewModel->setTemplate('deployment-library/web-api/library-get-status');
					return $viewModel;
				}
			}
		}
		
		throw new \WebAPI\Exception(_t("This library version %s does not exist", array($libraryVersionId)), \WebAPI\Exception::NO_SUCH_LIBRARY_VERSION);
		
	}
	
	public function libraryVersionDeployAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		
		$params = $this->getParameters(
			array(
				'userParams' => array(),
				'isDefault' => 'FALSE',
			)
		);
		
		$isDefault = $this->validateBoolean($params['isDefault'], 'isDefault');
		unset($params['isDefault']);
		
		$this->validateUserParams($params['userParams']);
		
		$fileTransfer = $this->setFileTransfer();
		
		$package = Package::generate($fileTransfer->getFilename());
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_LIBRARY_DEPLOY, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(_t('Library name: %s (%s). Filename: \'%s\'', array($package->getName(), $package->getVersion(), $fileTransfer->getFilename()))))); /* @var $auditMessage \Audit\Container */
		try {
			// before the deploy to validate the prerequisites of the package
			$prerequisites = $package->getPrerequisites();
			$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
			$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
			$configurationContainer->createConfigurationSnapshot(
					$configuration->getGenerator()->getDirectives(),
					$configuration->getGenerator()->getExtensions(),
					null,
					$configuration->getGenerator()->needServerData()
			);
			if (! $configuration->isValid($configurationContainer)) {
				// collect all messages to print out in the error message
				$messages = $configuration->getMessages();
				$messagesPrint = $this->collectInvalidMessages($messages);
				
				throw new \WebAPI\Exception(_t('Could not deploy library: %s', array(implode(', ', $messagesPrint))), \WebAPI\Exception::UNMET_DEPENDENCY);
			}

			if ($package->hasRequiredParams() && $package->getRequiredParams()) {
				$form = $this->getDeploymentMapper()->getUserParamsForm($package->getRequiredParams(), $params['userParams']);
				$mergedParams = $this->populateUserParams($params['userParams'], $package->getRequiredParams());
				
				if (count($mergedParams) == 0) {
					throw new \WebAPI\Exception(_t('no userParams specified'), \WebAPI\Exception::INVALID_PARAMETER);
				}
				
				$form->setData($mergedParams);
				
				if (! $form->isValid()) {
					$messages = current($form->getMessages());
					$message = current($messages);
					$key = key($form->getMessages());
					throw new \WebAPI\Exception("UserParams required value '$key': $message", \WebAPI\Exception::INVALID_PARAMETER);
				}
			}
			
			$deployedLibraryVersion = $this->getDeploymentLibraryMapper()->deployLibrary($fileTransfer->getFilename(), $isDefault, $params['userParams']);
			
			// remove library notification if needed
			$currentLibraryVersion = current($deployedLibraryVersion['versions']);
			$libraryVersion = $currentLibraryVersion['version'];
			$mapper = $this->getLocator()->get('DeploymentLibrary\Db\Mapper'); /* @var $mapper \DeploymentLibrary\Db\Mapper */
			$update = $mapper->getUpdate($deployedLibraryVersion['libraryName'])->current();
			if ($update != false) {
				$oldVersion = $update['VERSION'];
				if (version_compare($libraryVersion, $oldVersion) >= 0) {
					$mapper->deleteUpdate($deployedLibraryVersion['libraryName']);
						
					// check if there are any updates left and if not, remove notification message
					$updates = $mapper->getUpdates();
					if (count($updates->toArray()) == 0) {
						$notificationsMapper = $this->getNotificationsMapper();
						$notificationsMapper->deleteByType(\Notifications\NotificationContainer::TYPE_LIBRARY_UPDATE_AVAILABLE);
					}
				}
			}
		} catch (\Deployment\Exception $ex) {
			switch ($ex->getCode()) {
				case \Deployment\Exception::EXISTING_BASE_URL_ERROR:
					$code = \WebAPI\Exception::LIBRARY_ALREADY_EXISTS;
					break;
				default:
					$code = \WebAPI\Exception::INTERNAL_SERVER_ERROR;
			}
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($ex->getMessage())));
			throw new \WebAPI\Exception(_t('Could not deploy library: %s', array($ex->getMessage())), $code, $ex);
		} catch (\WebAPI\Exception $ex) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($ex->getMessage())));
			throw new \WebAPI\Exception(_t('Could not deploy library: %s', array($ex->getMessage())), \WebAPI\Exception::INTERNAL_SERVER_ERROR, $ex);
		}
		// make the library set to use the same view as the library-get-status
		$deployedLibraryVersion = array($deployedLibraryVersion['libraryId'] => $deployedLibraryVersion);
		$libsSet =  new Set($deployedLibraryVersion);
		$libsSet->setHydrateClass('\DeploymentLibrary\Container');
		
		Log::info("Library Version has been deployed");
		$this->setHttpResponseCode('202', 'Accepted');
		
		$viewModel = new ViewModel(array(	'libraries' => $libsSet,
											'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers())));
		$viewModel->setTemplate('deployment-library/web-api/library-get-status');
		return $viewModel;
	}
	
	/**
	 * @param array $userParams
	 * @param \Deployment\Application\Package $deploymentPackage
	 * @return array
	 */
	protected function populateUserParams($userParams, $requiredParams) {
		if (!isset($requiredParams['elements'])) return $userParams;
	
		foreach ($requiredParams['elements'] as $element) {
			$elementAttributes = $element['spec']['attributes'];
			$elementName = $element['spec']['name'];
	
			if (!isset($userParams[$elementName])) {
				isset($elementAttributes['value']) ? $value = $elementAttributes['value'] : $value = '';
				$userParams[$elementName] = $value; // @todo - adding empty fileds as of ZF2 B4 bug, that misses validation messages when missing required fields are not passed
			}
		}
	
		return $userParams;
	}
	
	public function libraryVersionSynchronizeAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('libraryVersionId'));
		$libraryVersionId = $this->validateInteger($params['libraryVersionId'], 'libraryVersionId');
		
		try {
			
			$libInfo = $this->getDeploymentLibraryMapper()->getLibraryByVersionId($libraryVersionId);
			$versionInfo = $this->getDeploymentLibraryMapper()->getLibraryVersionById($libraryVersionId);
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_LIBRARY_REDEPLOY,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Library : %s (%s)', array($libInfo->getLibraryName(), $versionInfo['version']))))); /* @var $auditMessage \Audit\Container */
			$this->getDeploymentLibraryMapper()->redeployLibrary($libraryVersionId);
		
		} catch (\Exception $e) {
			Log::err("Failed to redeploy library version: $libraryVersionId");
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($e->getMessage())));
			throw new\WebAPI\Exception(
					_t('Failed to redeploy library version %s', array($libraryVersionId)),
					\WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$libraryVersionsUpdated = $this->getDeploymentLibraryMapper()->getLibraryVersionsByIds(array($libraryVersionId));
		$libInfo = $this->getDeploymentLibraryMapper()->getLibraryByVersionId($libraryVersionId);
		$libInfo->setVersions($libraryVersionsUpdated);
		$libsSet = new Set(array($libInfo->toArray()));
		$libsSet->setHydrateClass('\DeploymentLibrary\Container');
		
		// use the library get server view to show the single library version and its library info
		$viewModel = new ViewModel(array(	'libraries' => $libsSet,
											'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers())));
		$viewModel->setTemplate('deployment-library/web-api/library-get-status');
		return $viewModel;
	}

    public function libraryVersionCheckDependentsAction() {
        $this->isMethodGet();
        $params = $this->getParameters();
        $this->validateMandatoryParameters($params, array('libraryVersionId'));

        $identifier = $this->validateInteger($params['libraryVersionId'], 'libraryVersionId');

        $brokenPlugin = $brokenPluginName = "";
        $dependentsFail = $this->libraryVersionFailsDependents($identifier, $brokenPlugin);

        if($brokenPlugin) {
            $pluginsModel = $this->getLocator()->get('Plugins\Mapper');
            $pluginContainer = $pluginsModel->getPluginById($brokenPlugin);
            $brokenPluginName = $pluginContainer->getPluginName();
        }
        return array('valid' => (! $dependentsFail), 'type' => 'libraryVersion', 'identifier' => $identifier, 'brokenPlugin' => $brokenPlugin, 'brokenPluginName' => $brokenPluginName);
    }

    public function libraryCheckDependentsAction() {
        $this->isMethodGet();
        $params = $this->getParameters();
        $this->validateMandatoryParameters($params, array('libraryId'));

        $identifier = $this->validateInteger($params['libraryId'], 'libraryId');

        $brokenPlugin = $brokenPluginName = "";
        $dependentsFail = $this->libraryFailsDependents($identifier, $brokenPlugin);

        if($brokenPlugin) {
            $pluginsModel = $this->getLocator()->get('Plugins\Mapper');
            $pluginContainer = $pluginsModel->getPluginById($brokenPlugin);
            $brokenPluginName = $pluginContainer->getPluginName();
        }
        $model = new ViewModel(array('valid' => (! $dependentsFail), 'type' => 'library', 'identifier' => $identifier, 'brokenPlugin' => $brokenPlugin, 'brokenPluginName' => $brokenPluginName));
        $model->setTemplate('deployment-library/web-api/library-version-check-dependents');
        return $model;
    }
    
    public function librarySetDefaultAction() {
    	$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('libraryVersionId'));
		$libraryVersionId = $this->validateInteger($params['libraryVersionId'], 'libraryVersionId');
	
		$deploymentLibraryMapper = $this->getDeploymentLibraryMapper(); /* @var $deploymentLibraryMapper \DeploymentLibrary\Mapper */
		
		// TODO: Check that the library version id is one of actual libary version

		$deploymentLibraryMapper->setDefaultLibrary($this->getServersMapper()->findRespondingServersIds(), $params['libraryVersionId']);
		
		$library = $deploymentLibraryMapper->getLibraryByVersionId($params['libraryVersionId']);
		$libraryVersion = $deploymentLibraryMapper->getLibraryVersionById($params['libraryVersionId']);
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_LIBRARY_SET_DEFAULT,
				ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY,
				array(array(_t('Library Version: %s (%s)', array($library->getLibraryName(), $libraryVersion['version']))))); /* @var $auditMessage \Audit\Container */
		
		$deployedLibraries = $deploymentLibraryMapper->getLibrariesByIds(array());
		
		$libraryToReturn = null;
		foreach ($deployedLibraries as $lib) { /* @var $lib \DeploymentLibrary\Container */
			foreach ($lib->getVersions() as $version) {
				if ($version['libraryVersionId'] == $libraryVersionId) {
					$libraryToReturn = $lib;
					$libraryToReturn->setVersions(array($version['libraryVersionId'] => $version));
					$libsSet = new Set(array($libraryToReturn->toArray()));
					$libsSet->setHydrateClass('\DeploymentLibrary\Container');
					$prerequisites = $deploymentLibraryMapper->getLibraryVersionPrerequisites($libraryVersionId);
					// use the library get server view to show the single library version + depedencies (if has) and its library info
					$viewModel = new ViewModel(array(	'libraries' => $libsSet,
														'prerequisites' => $prerequisites,
														'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers())));
					$viewModel->setTemplate('deployment-library/web-api/library-get-status');
					return $viewModel;
				}
			}
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
		throw new \WebAPI\Exception(_t("This library version %s does not exist", array($libraryVersionId)), \WebAPI\Exception::NO_SUCH_LIBRARY_VERSION);
    }

    /**
     * @param integer $libraryId
     * @return bool
     */
    private function libraryFailsDependents($libraryId, &$brokenPlugin) {

        $dependentsFailureValidator = new HasDependentsLibrary();
        $dependentsFailureValidator->setConfigurationContainer($this->getLocator()->get('ZendServer\Configuration\Container'));
        $dependentsFailureValidator->setDeploymentMapper($this->getDeploymentMapper());
        $dependentsFailureValidator->setLibrariesMapper($this->getDeploymentLibraryMapper());
        $dependentsFailureValidator->setPluginsMapper($this->getPluginsMapper());
        
        return $dependentsFailureValidator->breaksDependents($libraryId, $brokenPlugin);
    }

    /**
     * @param integer $libVersionId
     * @return bool
     */
    private function libraryVersionFailsDependents($libVersionId, &$brokenPlugin) {

        $dependentsFailureValidator = new HasDependentsLibraryVersion();
        $dependentsFailureValidator->setConfigurationContainer($this->getLocator()->get('ZendServer\Configuration\Container'));
        $dependentsFailureValidator->setDeploymentMapper($this->getDeploymentMapper());
        $dependentsFailureValidator->setLibrariesMapper($this->getDeploymentLibraryMapper());
        $dependentsFailureValidator->setPluginsMapper($this->getPluginsMapper());
        
        return $dependentsFailureValidator->breaksDependents($libVersionId, $brokenPlugin);
    }

	public function libraryRemoveAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(array('ignoreFailures' => 'FALSE'));
		$this->validateMandatoryParameters($params, array('libId'));
		$libId = $params['libId'];
		$this->validateInteger($libId, 'libId');
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		
		$library = $this->getDeploymentLibraryMapper()->getLibraryById($libId);

		// not found
		if ($library->getLibraryId() === NULL) {
			throw new \WebAPI\Exception(_t("This library %s does not exist", array($libId)), \WebAPI\Exception::NO_SUCH_LIBRARY);
		}

		$brokenPlugin = "";
		$isLibraryFailsDependents = $this->libraryFailsDependents($libId, $brokenPlugin);
        if ((! $ignoreFailures) && $isLibraryFailsDependents) {
            throw new\WebAPI\Exception(_t('Cannot remove this library version. It is a requisite for another application or library or plugin'), \WebAPI\Exception::UNMET_DEPENDENCY); 
        }
        
		try {
			$libraryName = $library->getLibraryName();
			
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_LIBRARY_REMOVE,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Library : %s (%s)', array($library->getLibraryName(), implode(',', array_map(function($item){
						return $item['version'];
					}, $library->getVersions()))))))); /* @var $auditMessage \Audit\Container */
			$this->getDeploymentLibraryMapper()->removeLibrary($libId, $ignoreFailures);
			
			$this->getLibraryUpdatesMapper()->deleteByName($libraryName);
				
		} catch (\Exception $e) {
			Log::err("Failed to remove library: $libId");
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($e->getMessage())));
			throw new\WebAPI\Exception(
					_t('Failed to remove library %s', array($libId)),
					\WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		if($isLibraryFailsDependents) { // and $ignoreFailures
		    $this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
		} else {
			$this->getNotificationsMapper()->deleteByType(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
	    }
	
		Log::info("Library $libId has been removed");
		
		$this->setHttpResponseCode('202', 'Accepted');
		$library = $this->getDeploymentLibraryMapper()->getLibrariesByIds(array($libId));
		$serversMapper = $this->getServersMapper();
		$serversData = $serversMapper->findAllServers();
		
		$viewModel = new ViewModel(array('libraries' => $library, 'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers()), 'serversInfoData' => $serversData));
		
		$viewModel->setTemplate('deployment-library/web-api/library-get-status');
		return $viewModel;
	}



	public function libraryVersionRemoveAction() {
        $this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(array('ignoreFailures' => 'FALSE'));
		$this->validateMandatoryParameters($params, array('libVerId'));
		$libVerId = $params['libVerId'];
		$this->validateInteger($libVerId, 'libVerId');
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		
		$library = $this->getDeploymentLibraryMapper()->getLibraryById($this->getDeploymentLibraryMapper()->getLibraryIdByLibraryVersionId($libVerId));
		if (is_null($library)) {
			throw new \WebAPI\Exception(_t("Library version %s does not exist", array($libVerId)), \WebAPI\Exception::NO_SUCH_LIBRARY);
		}
		
		$libraryVersionsCount = count($library->getVersions());
		$libraryName = $library->getLibraryName();
		
		$libraryVersion = $this->getDeploymentLibraryMapper()->getLibraryVersionById($libVerId);
		if (is_null($libraryVersion)) {
			throw new \WebAPI\Exception(_t("Library version %s does not exist", array($libVerId)), \WebAPI\Exception::NO_SUCH_LIBRARY);
		}

		$brokenPlugin = "";
		$isLibraryVersionFailsDependents = $this->libraryVersionFailsDependents($libVerId, $brokenPlugin);
        if ((! $ignoreFailures) && $isLibraryVersionFailsDependents) {
            throw new\WebAPI\Exception(_t('Cannot remove this library version. It is a requisite for another application or library'), \WebAPI\Exception::UNMET_DEPENDENCY);
        }
        
        if ($libraryVersion['default']) {
        	$maxVersion = '0';
        	$maxVersionId = 0;
        	foreach ($library->getVersions() as $version) {
        		if ($libVerId != $version['libraryVersionId']) {
        			if (version_compare($version['version'], $maxVersion) == 1) {
        				$maxVersion = $version['version'];
        				$maxVersionId = $version['libraryVersionId'];
        			}
        		}
        	}
        	$this->getDeploymentLibraryMapper()->setDefaultLibrary($this->getServersMapper()->findRespondingServersIds(), $maxVersionId);
        }
        
		try {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_LIBRARY_VERSION_REMOVE,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Library Version is: %s (%s)', array($library->getLibraryName(), $libraryVersion['version']))))); /* @var $auditMessage \Audit\Container */
			$this->getDeploymentLibraryMapper()->removeLibraryVersion($libVerId, $ignoreFailures);

			// remove last library version
			if ($libraryVersionsCount == 1) {
				$this->getLibraryUpdatesMapper()->deleteByName($libraryName);
			}
			
		} catch (\Exception $e) {
			Log::err("Failed to remove library version:" . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(array($e->getMessage())));
			throw new\WebAPI\Exception(_t('Failed to remove library version %s', array($e->getMessage())), \WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		if($isLibraryVersionFailsDependents) { // and $ignoreFailures
		    $this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY);
		}
		
		Log::info("Library version is $libVerId has been removed");
		$this->setHttpResponseCode('202', 'Accepted');
		
		$libraryVersionsUpdated = $this->getDeploymentLibraryMapper()->getLibraryVersionsByIds(array($libVerId));
		$libInfo = $this->getDeploymentLibraryMapper()->getLibraryByVersionId($libVerId);
		$libInfo->setVersions($libraryVersionsUpdated);
		$libsSet = new Set(array($libInfo->toArray()));
		$libsSet->setHydrateClass('\DeploymentLibrary\Container');
		
		// use the library get server view to show the single library version and its library info
		$viewModel = new ViewModel(array(	'libraries' => $libsSet,
											'respondingServersCount' => count($this->getLocator('Servers\Db\Mapper')->findRespondingServers())));
		$viewModel->setTemplate('deployment-library/web-api/library-get-status');
		return $viewModel;
	}

	public function downloadLibraryVersionFileAction() {
		$this->isMethodGet();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('libVersionId'));
		$libraryVersionId = $this->validateInteger($params['libVersionId'], 'libVersionId');
		
		$deploymentLibraryMapper = $this->getDeploymentLibraryMapper(); /* @var $deploymentLibraryMapper \DeploymentLibrary\Mapper */
		
		$response = $this->getResponse(); /* @var $response \Zend\Http\PhpEnvironment\Response */
		
		$libraryVersionFileName = "libraryVersion-{$params['libVersionId']}-" . date('dMY-His') . ".zpk";
		
		$headers = new Headers();
		$headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$libraryVersionFileName\"");
		$headers->addHeaderLine('Content-type', "application/vnd.zend.zpk");
		$response->setHeaders($headers);
		
		$response->setContent('Test!');
		return $response;
	}
	
	protected function getGuiTempDir() {
		return FS::getGuiTempDir();
	}
	
	protected function setFileTransfer() {
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer->setDestination($uploaddir);
		if (! $fileTransfer->receive()) {
			throw new \WebAPI\Exception(_t("Package file upload failed"), \WebAPI\Exception::INVALID_PARAMETER);
		}
	
		Log::debug('File is uploaded to ' . $uploaddir);
		return $fileTransfer;
	}

	private function collectInvalidMessages($messages) {
		$messagesPrint = array();
		$validatorList = array (\Prerequisites\Validator\Generator::EXTENSION_VALIDATOR_ELEMENT => array(	\Prerequisites\Validator\Extension\Conflicts::NOT_CONFLICTS,
																											\Prerequisites\Validator\Extension\Equal::NOT_EQUAL,
																											\Prerequisites\Validator\Extension\Exclude::NOT_EXCLUDE,
																											\Prerequisites\Validator\Extension\Loaded::NOT_LOADED,
																											\Prerequisites\Validator\Extension\Conflicts::NOT_CONFLICTS,
																											\Prerequisites\Validator\Extension\Max::NOT_MAX,
																											\Prerequisites\Validator\Extension\Min::NOT_MIN),
				
								\Prerequisites\Validator\Generator::DIRECTIVE_VALIDATOR_ELEMENT => array(	\Prerequisites\Validator\Directive\Min::NOT_MIN,
																											\Prerequisites\Validator\Directive\Max::NOT_MAX,
																											\Prerequisites\Validator\Directive\Equal::NOT_EQUAL,
																											\Prerequisites\Validator\Directive\Exists::NOT_EXISTS),
				
								\Prerequisites\Validator\Generator::LIBRARY_VALIDATOR_ELEMENT => array(		\DeploymentLibrary\Prerequisites\Validator\Library\Equals::NOT_VALID,
																											\DeploymentLibrary\Prerequisites\Validator\Library\Min::NOT_MIN,
																											\DeploymentLibrary\Prerequisites\Validator\Library\Max::NOT_MAX),
				
								\Prerequisites\Validator\Generator::VERSION_VALIDATOR_ELEMENT => array(		\Prerequisites\Validator\Version\Min::NOT_MIN,
																											\Prerequisites\Validator\Version\Equal::NOT_EQUAL,
																											\Prerequisites\Validator\Version\Max::NOT_MAX,
																											\Prerequisites\Validator\Version\Exclude::NOT_EXCLUDE),
				
								\Prerequisites\Validator\Generator::COMPONENT_VALIDATOR_ELEMENT => array(	\Prerequisites\Validator\Component\Min::NOT_MIN,
																											\Prerequisites\Validator\Component\Equal::NOT_EQUAL,
																											\Prerequisites\Validator\Component\Max::NOT_MAX,
																											\Prerequisites\Validator\Component\Exclude::NOT_EXCLUDE,
																											\Prerequisites\Validator\Component\Conflicts::NOT_CONFLICTS,
																											\Prerequisites\Validator\Component\Loaded::NOT_LOADED));
		foreach ($messages as $type => $typeMessages) {
			
			if (in_array($type, array_keys($validatorList))) {
				foreach ($typeMessages as $ext => $msg) {
					foreach ($validatorList[$type] as $error) {
						if (isset($msg[$error])) {
							$messagesPrint[] = $msg[$error]; // $msg[\Prerequisites\Validator\Extension\Exclude::NOT_EXCLUDE]
						}
					}
				}
			}
		}

		return $messagesPrint;
	}
	

	/**
	 *
	 * @param array $params
	 * @return array
	 * @throws WebAPI\Exception
	 */
	protected function validateUserParams($userParams)
	{
		if (! is_array($userParams)) {
			throw new \WebAPI\Exception(
					_t("Parameter 'userParams' must be an array of values for the uploaded application package"),
					\WebAPI\Exception::INVALID_PARAMETER
			);
		}
	}
}
