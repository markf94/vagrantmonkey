<?php
namespace Plugins\Controller;

use WebAPI\Exception,
	ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Log\Log,
	Deployment\SessionStorage,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper,
	Zend\View\Model\ViewModel,
	Deployment\Application\Package,
	ZendDeployment_PackageFile,
	ZendDeployment_PackageMetaData,
	Notifications\NotificationContainer,
	WebAPI;
use Plugins\PluginContainer;

class WebAPIController extends WebAPIActionController {
	
	public function pluginGetListAction() {
		
		$this->isMethodGet();
		
		$params = $this->getParameters(array('order' => 'name', 'direction' => 'Asc'));
		try {
			
			$this->validateAllowedValues($params['order'], 'order', array('id', 'name', 'version', 'creationTimeTimestamp'));
			$this->validateAllowedValues($params['direction'], 'direction', array('Desc', 'Asc'));
			$params['order'] = $this->getOrderBy($params['order']);
			
		} catch (\WebAPI\Exception $e) {
			$this->handleException($e, 'Input validation failed');
		}	
			
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  /* @var $pluginsModel \Plugins\Model */
		$pluginsMapper = $this->getLocator()->get('Plugins\Db\Mapper');  /* @var $pluginsMapper \Plugins\Db\Mapper */
		
		try {
			$plugins = $pluginsModel->getMasterPluginsByIds(array(), $params['direction'], $params['order']);
		} catch (\Exception $e) {
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INVALID_SERVER_RESPONSE, $e);
		}
		
		try {
			$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
			$plugins = $pluginsModel->updatePrerequisitesIsValidFlags($plugins, $configurationContainer);
			
			$mapper = $this->getLocator()->get('Plugins\Db\UpdatesMapper'); /* @var $mapper \Plugins\Db\UpdatesMapper */
			$updates = $mapper->getUpdates();
			$updates = $pluginsModel->removeBrokenUpdates($updates, $configurationContainer);
		} catch (\Exception $e) {
		  throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
		
		return array('plugins' => $plugins, 'updates' => $updates);
	}
	
	public function pluginSaveSingleUpdateAction() {
		$this->isMethodPost();
		$params = $this->getParameters(array('name' => '', 'version' => ''));
		
		$name = trim($params['name']);
		$version = trim($params['version']);
		
		if (! empty($name) && ! empty($version)) {
			// insert notification about new update
			$mapper = $this->getNotificationsMapper();
			$mapper->insertNotification(NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE, array($name, $version));
			
			// Send Email - if it's still wasn't sent
			$this->sendMailPluginUpdateNotification($mapper);
			
			$mapper = $this->getLocator()->get('Plugins\Db\UpdatesMapper'); /* @var $mapper \Plugins\Db\UpdatesMapper */
			
			// delete old update and put the new one - creates fresh timestamp
			$mapper->deleteUpdate($name);
			$mapper->addUpdate($name, $version, '');
		}
		
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setTemplate('plugins/web-api/save-single-update');
		
		return $viewModel;
	}
	
	public function pluginSaveUpdatesAction() {
		$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('plugins'));
		$plugins = $params['plugins'];
		$this->validateArray($plugins, 'plugins');
		
		foreach ($plugins as $plugin) {
			
			$name = $plugin['name'];
			$version = $plugin['version'];
			$pluginId = $plugin['id'];
			$needsUpdate = isset($plugin['needs_update']) ? $plugin['needs_update'] : false;
			$prerequisites = isset($plugin['prerequisites']) ? json_decode($plugin['prerequisites'],true) : false;
			$extraData = $this->jsonEncodePluginExtraData($plugin['download_id'], $needsUpdate, $pluginId, $prerequisites);
			
			
			if (! empty($name) && ! empty($version)) {
				$mapper = $this->getLocator()->get('Plugins\Db\UpdatesMapper'); /* @var $mapper \Plugins\Db\UpdatesMapper */
				$update = $mapper->getUpdate($name)->current();
				$notificationsMapper = $this->getNotificationsMapper();
				if ($update != false) {
					$oldVersion = $update['VERSION'];
					// $version > $oldVersion: 1) after the plugin was updated 2) we got the version newer than update needed version
					if (version_compare($version, $oldVersion) > 0 || $update != $needsUpdate) {
						if (!$needsUpdate) {
						   $mapper->deleteUpdate($name);
						} else {
							//update the update version of plugin:
							$prerequisites = isset($plugin['prerequisites']) ? json_decode($plugin['prerequisites'],true) : false;
							$mapper->addUpdate($name, $version, $extraData);
						}
			
						// check if there are any updates left and if not, remove notification message
						$updates = $mapper->getUpdates();
						if ($updates && count($updates) == 0) {
							if (!isZrayStandaloneEnv()) {
								$notificationsMapper->deleteByType(NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE);
							}
						}
					}
				} else { // new update
					// doesn't exist in store
					if ($pluginId == -1) { 
					   $mapper->addUpdate($name, $version, $this->jsonEncodePluginExtraData($plugin['download_id'], -1, $pluginId, ''));
					} else {
					   $needsUpdate = isset($plugin['needs_update']) ? $plugin['needs_update'] : false;
					   $prerequisites = isset($plugin['prerequisites']) ? json_decode($plugin['prerequisites'],true) : false;
					   
					   $mapper->addUpdate($name, $version, $extraData);
					   
					   $pluginsModel = $this->getLocator()->get('Plugins\Model');  /* @var $pluginsModel \Plugins\Model */
					   $configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
					   if($needsUpdate == 'true' && (!$prerequisites || $pluginsModel->checkPluginDependencies($prerequisites, $configurationContainer))) {
						   if (!isZrayStandaloneEnv()) {
								$notificationsMapper->insertNotification(NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE, array($name, $version));
						   
								// Send Email - if it's still wasn't sent
								$this->sendMailPluginUpdateNotification($notificationsMapper);
						   }
					   }
					}
				}
			}
		}
		
		return array('success' => true);
		
	}
	
	public function pluginCancelPendingDeploymentAction() {
		$this->isMethodPost();
	
		$wizardId = $this->getRequest()->getQuery('wizardId', 0);
	
		try {
			$sessionStorage = new SessionStorage($wizardId);
			$path = $sessionStorage->getPackageFilePath();
				
			if (file_exists($path)) {
				unlink($path);
			}
		} catch (\Exception $e) {
			// Do nothing, may be the package was invalid, so it wasn't stored
		}
	
		return array();
	}

	public function pluginGetDetailsAction() {
		$this->isMethodGet();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('plugin'));
	
		$this->validateInteger($params['plugin'], 'plugin');
	
		$masterPlugins = $this->getPluginsMapper()->getMasterPlugins(array(), array($params['plugin']));
		$existingPlugin = (isset($masterPlugins[$params['plugin']])) ? $masterPlugins[$params['plugin']] : null;
		
		if (!($existingPlugin instanceof PluginContainer)) {
			throw new \WebAPI\Exception(_t('Plugin \'%s\' does not exist', array($params['plugin'])), \WebAPI\Exception::NO_SUCH_PLUGIN);
		}
	
		$prerequisites = '';
		$metadata =  new ZendDeployment_PackageMetaData();
		$metadata->setPackageDescriptor($existingPlugin->getPackageMetadataJson());
		
		if ($metadata instanceof \ZendDeployment_PackageMetaData_Interface) {
			$prerequisites = $metadata->getPrerequisites();
			// remove <?xmlversion="1.0"? from the xml string if exists
			$prerequisites = substr($prerequisites, strpos($prerequisites, '?'.'>') + 2);
			$prerequisites = trim($prerequisites);
		}
	
		if (isZrayStandaloneEnv()) {
			$prerequisitesIsValid = true;
		} else {
			try {
				$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
				$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
				$configurationContainer->createConfigurationSnapshot(
					$configuration->getGenerator()->getDirectives(),
					$configuration->getGenerator()->getExtensions(),
					$configuration->getGenerator()->getLibraries(),
					$configuration->getGenerator()->needServerData());
			} catch (\Exception $e) {
				throw new WebAPI\Exception('Package prerequisites could not be validated: ' . $e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
			$prerequisitesIsValid = $configuration->isValid($configurationContainer);
		}

		if (!$prerequisitesIsValid) {
		   $existingPlugin->setPluginMessage(_t('%s The required prerequisites for this plugin have not been met.', array($existingPlugin->getPluginMessage())));
		}
		
		$servers = $this->getPluginsMapper()->getServersStatusByPluginId($params['plugin']);
		$serversIds = $this->getServersMapper()->findRespondingServersIds();
		// if the server is not responding we exclude it from the list. Bug #ZSRV-9670
		foreach ($servers as $id => $serverData) {
			if (! in_array($id, $serversIds)) {
				unset($servers[$id]);
			}
		}
	
		return array (
			'plugin' => $existingPlugin,
			'prerequisites' => $prerequisites,
			'prerequisitesIsValid' => $prerequisitesIsValid,
			'servers' => $servers,
		);
	}
	
	public function pluginRemoveAction() {
		
		$this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(array('ignoreFailures' => 'FALSE'));
		$this->validateMandatoryParameters($params, array('pluginId'));
		$pluginId = $params['pluginId'];
		$removePluginData = $params['removePluginData']; //flag -> 1 for removing, 0 for keeping!
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
	
		$this->validateInteger($pluginId, 'pluginId');
		// @todo: Is component loaded?
		// @todo: Verify has target? Check if zend server is responding?
	
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  /* @var $pluginsModel \Plugins\Model */
		$plugins = $pluginsModel->getMasterPluginsByIds(array($pluginId));
		$existingPlugin = null;
		if (isset($plugins[$pluginId])) {
			$existingPlugin = $plugins[$pluginId];
		}
		
		if (!$existingPlugin) {
			Log::err("Failed to remove plugin - pluginId $pluginId does not exist");
			throw new WebAPI\Exception(
				_t("This plugin does not exist"),
				WebAPI\Exception::NO_SUCH_PLUGIN
			);
		}
	
		// @todo: Check if application isBeingDeployed or isBeingRolledback or isBeingRemoved
		try {
			if (!isZrayStandaloneEnv()) {
				$auditMessage = $this->auditMessage(auditMapper::AUDIT_PLUGIN_REMOVE,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Plugin name: %s', array($existingPlugin->getPluginName()))))); /* @var $auditMessage \Audit\Container */
			}
			
			$this->getPluginsMapper()->removePlugin($pluginId, $removePluginData);
		} catch (\Exception $e) {
			Log::err("Failed to remove plugin:" . $e->getMessage());
				
			throw new WebAPI\Exception(
				_t('Failed to remove plugin %s', array($e->getMessage())),
				WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$this->cleanPluginUpdates($existingPlugin->getPluginName());
		
		if (!isZrayStandaloneEnv()) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD tasks
			zrayStandaloneExecuteTasks(true);
		}
		
		Log::info("Plugin with id: $pluginId has been removed");
		$this->setHttpResponseCode('202', 'Accepted');
		
		$servers = $this->getPluginsMapper()->getServersStatusByPluginId($existingPlugin->getPluginId());
		$viewModel = new ViewModel(array('plugin' => $existingPlugin, 'servers' => $servers));
		$viewModel->setTemplate('plugins/web-api/plugin-info');
		return $viewModel;
	}
	
	/*
	* @throws WebAPI\Exception
	*/
	public function enablePluginsAction() {
		$plugins = $this->preProcessDisableEnableActions();
	
		try {
			if (!isZrayStandaloneEnv()) {
				$auditMessage = $this->auditMessage(auditMapper::AUDIT_PLUGIN_ENABLE,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Plugins are: %s', array(implode(', ', $plugins)))))); /* @var $auditMessage \Audit\Container */
			}
			
			$this->getPluginsMapper()->enablePlugins($plugins);
				
		} catch (\Exception $e) {
			Log::err("Failed to enable plugins:" . $e->getMessage());
				
			throw new WebAPI\Exception(
				_t('Failed to enable plugins %s', array($e->getMessage())),
				WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD to process tasks
			zrayStandaloneExecuteTasks();
		}
	
		if (!isZrayStandaloneEnv()) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		}
		
		Log::info("Plugins :" . implode(', ', $plugins) . " have been enabled");
		$this->setHttpResponseCode('202', 'Accepted');
	   
		return $this->postProcessDisableEnableActions();
	}
	
	/**
	 * @throws WebAPI\Exception
	 */
	public function disablePluginsAction() {
		// check that the requested plugins exist in "master plugins" list
		$plugins = $this->preProcessDisableEnableActions();
		try {
			if (!isZrayStandaloneEnv()) {
				/* @var $auditMessage \Audit\Container */
				$auditMessage = $this->auditMessage(
					auditMapper::AUDIT_PLUGIN_DISABLE,
					ProgressMapper::AUDIT_PROGRESS_REQUESTED,
					array(array(_t('Plugins are: %s', array(implode(', ', $plugins)))))
				); 
			}
			
			
			
			$this->getPluginsMapper()->disblePlugins($plugins);
			
		} catch (\Exception $e) {
			Log::err("Failed to disable plugins:" . $e->getMessage());
				
			throw new WebAPI\Exception(
				_t('Failed to disable plugins %s', array($e->getMessage())),
				WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD to process tasks
			zrayStandaloneExecuteTasks();
		}
	
		if (!isZrayStandaloneEnv()) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, array());
		}
		
		Log::info("Plugins :" . implode(', ', $plugins) . " have been disabled");
		$this->setHttpResponseCode('202', 'Accepted');
	
		// get master plugins and create a "view" with the plugins as a parameter
		return $this->postProcessDisableEnableActions();
	}
	
	public function pluginSynchronizeAction() {
		$this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters(
			array(
				'ignoreFailures' => 'FALSE'
			)
		);
	
		$this->validateMandatoryParameters($params, array('pluginId'));
		$pluginId = $params['pluginId'];
		$this->validateInteger($pluginId, 'pluginId');
	
		$ignoreFailures = $this->validateBoolean($params['ignoreFailures'], 'ignoreFailures');
		$existingPlugin = $this->getPluginsMapper()->getPluginById($pluginId);
		
		if (empty($existingPlugin->toArray())) {
			Log::err("Failed to synchronize plugin - 'pluginId' $pluginId does not exist");
			throw new WebAPI\Exception(
				_t("This plugin $pluginId does not exist"),
				WebAPI\Exception::NO_SUCH_PLUGIN
			);
		}
	
		try {
			if (!isZrayStandaloneEnv()) {
				$auditMessage = $this->auditMessage(auditMapper::AUDIT_PLUGIN_REDEPLOY, ProgressMapper::AUDIT_PROGRESS_REQUESTED,
													array(array('Plugin Name' => $existingPlugin->getPluginName()))); /* @var $auditMessage \Audit\Container */
			}
			$this->getPluginsMapper()->redeployPlugin($existingPlugin, $ignoreFailures);
		   
		} catch (\Exception $e) {
			Log::err("Failed to synchronize plugin:" . $e->getMessage());
			throw new WebAPI\Exception(
				_t('Failed to synchronize plugin: %s', array($e->getMessage())),
				WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD tasks
			zrayStandaloneExecuteTasks();
		}
	
		$this->setHttpResponseCode('202', 'Accepted');
	
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  /* @var $pluginsModel \Plugins\Model */
		$masterPlugins = $pluginsModel->getMasterPluginsByIds(array($pluginId));
		
		$viewModel = new ViewModel(array('plugins' => $masterPlugins));
		$viewModel->setTemplate('plugins/web-api/plugins-info');
		return $viewModel;
	}
	
	public function pluginUpdateAction() {
		$this->validateLicenseValid();
		$this->isMethodPost();
		
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer = $this->setFileTransfer();
		
		$deployedPlugin = $this->deployPlugin($fileTransfer->getFilename(), true);
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD tasks
			zrayStandaloneExecuteTasks();
		}
		
		Log::info("Plugin has been deployed");
		$this->setHttpResponseCode('202', 'Accepted');
		
		$viewModel = new ViewModel(array('plugin' => $deployedPlugin, 'servers' => array()));
		$viewModel->setTemplate('plugins/web-api/plugin-info');
		return $viewModel;
	}
	
	public function pluginDeployAction() {
		$this->validateLicenseValid();
		$this->isMethodPost();
		
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer = $this->setFileTransfer();
		
		$deployedPlugin = $this->deployPlugin($fileTransfer->getFilename());
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD tasks
			zrayStandaloneExecuteTasks();
		}
		
		Log::info("Plugin has been deployed");
		$this->setHttpResponseCode('202', 'Accepted');
		
		$viewModel = new ViewModel(array('plugin' => $deployedPlugin, 'servers' => array()));
		$viewModel->setTemplate('plugins/web-api/plugin-info');
		return $viewModel;
	}
	
	protected function getGuiTempDir() {
		return \ZendServer\FS\FS::getGuiTempDir();
	}
	
	protected function setFileTransfer() {
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer->setDestination($uploaddir);
		if (! $fileTransfer->receive()) {
			throw new WebAPI\Exception(
				_t("Package file upload failed"),
				WebAPI\Exception::INVALID_PARAMETER
			);
		}
	
		Log::debug('File is uploaded to ' . $uploaddir);
		return $fileTransfer;
	}

	private function jsonEncodePluginExtraData($downloadId, $needsUpdate, $pluginId, $prerequisites) {
		$encoded = json_encode(array(  'download_id'   => $downloadId,
										'needs_update'  => $needsUpdate,
										'id'            => $pluginId,
										'prerequisites' => $prerequisites ));
		return $encoded;
	}
	
	private function cleanPluginUpdates($name) {
	
		$mapper = $this->getLocator()->get('Plugins\Db\UpdatesMapper'); /* @var $mapper \Plugins\Db\UpdatesMapper */
		$mapper->deleteUpdate($name);
	
		// check if there are any updates left and if not, remove notification message
		if (!isZrayStandaloneEnv()) {
			$updates = $mapper->getActiveUpdates();
			if (empty($updates)) {
				$notificationsMapper = $this->getNotificationsMapper();
				$notificationsMapper->deleteByType(NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE);
			}
		}
	
		$pluginsModel = $this->getLocator()->get('Plugins\Model');
		$setUpdateCookieObj = new SetUpdateCookie();
		$setUpdateCookieObj->resetCookieContent($pluginsModel);
	}
	
	private function deployPlugin($filename, $isUpgrade=false) {
	  
		$deploymentPackage = $this->generatePackage($filename);
		$this->getPluginsMapper()->validatePackage($filename, $isUpgrade);
		
		$model = $this->getLocator()->get('Plugins\Model');  /* @var $model \Plugins\Model */
		try {
		   $deploymentPackage = $model->storePendingDeployment($filename, $deploymentPackage->getName());
		} catch (\Exception $e) {
			throw new WebAPI\Exception('Deployment failed: ' . $e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		}
	
		if (file_exists($filename)) {
			unlink($filename);
		}
	
		// skip prerequisites validation for Z-Ray standalone
		if (!isZrayStandaloneEnv()) {
			try {
				$prerequisites = $deploymentPackage->getPrerequisites();
				$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
				$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
				$configurationContainer->createConfigurationSnapshot(
					$configuration->getGenerator()->getDirectives(),
					$configuration->getGenerator()->getExtensions(),
					$configuration->getGenerator()->getLibraries(),
					$configuration->getGenerator()->needServerData());
			} catch (\Exception $e) {
				$model->cancelPendingDeployment($deploymentPackage->getName());
				throw new WebAPI\Exception('Package prerequisites could not be validated: ' . $e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
			}
			if (! $configuration->isValid($configurationContainer)) {
				$model->cancelPendingDeployment($deploymentPackage->getName());
				$messagesFilter = new \Prerequisites\MessagesFilter();
				$messages = $messagesFilter->filter($configuration->getMessages());
				Log::err(print_r($this->flattenMessagesArray($messages), true));
				throw new WebAPI\Exception(PHP_EOL . implode(PHP_EOL, $this->flattenMessagesArray($messages)), WebAPI\Exception::UNMET_DEPENDENCY);
			}
		}
	
		try {
			if (!isZrayStandaloneEnv()) {
				$audit = auditMapper::AUDIT_PLUGIN_DEPLOY;
				if ($isUpgrade) {
					$audit = auditMapper::AUDIT_PLUGIN_UPGRADE;
				}
				$auditMessage = $this->auditMessage($audit, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(_t('Plugin name: %s', array($deploymentPackage->getName())))), $deploymentPackage->getName()); /* @var $auditMessage \Audit\Container */
			}
			
			if (!$isUpgrade) {
			   $this->getLocator()->get('Plugins\Mapper\Deploy')->deployPlugin($deploymentPackage->getName());
			} else {
			   $model->updatePlugin($deploymentPackage->getName(), $model->getStoredTaskDescriptorId());
			}
	
			$deployedPlugin = $model->getPluginByName($deploymentPackage->getName());
		} catch (\Deployment\Exception $e) {
			Log::err("Deployment failed: " . $e->getMessage());
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
				'errorMessage' => $e->getMessage()));
			throw new WebAPI\Exception($e->getMessage(), WebAPI\Exception::INTERNAL_SERVER_ERROR);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array(
				'errorMessage' => $e->getMessage()));
			Log::err("Deployment failed: " . $e->getMessage());
			$model->cancelPendingDeployment($deploymentPackage->getName());
			throw new WebAPI\Exception(
				_t('Deployment failed %s', array($e->getMessage())),
				WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
	
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		
		if ($isUpgrade) {
			$this->cleanPluginUpdates($deploymentPackage->getName());
		}
		return $deployedPlugin;
	}
	
	/**
	 *
	 * @param string $filename
	 * @throws WebAPI\Exception
	 * @return \Deployment\Application\Package
	 */
	protected function generatePackage($filename) {
		try {
			$package = Package::generate ($filename);
		} catch (\Exception $e) {
			Log::err("Failed to validate plugin package: " . $e->getMessage());
			throw new WebAPI\Exception(
				_t("Failed to validate plugin package: %s", array($e->getMessage())),
				WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
			
		return $package;
	}
	
	private function preProcessDisableEnableActions() {
		$this->validateLicenseValid();
		$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('plugins'));
		$plugins = $params['plugins'];
	
		$this->validateArray($plugins, 'plugins');
	   
		/* @var $pluginsModel \Plugins\Model */
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  
		$masterPlugins = $pluginsModel->getMasterPluginsByIds($plugins);
		$masterPluginsIds = array_keys($masterPlugins);
		foreach($plugins as $pluginToEnableDisable) {
			if (!in_array($pluginToEnableDisable, $masterPluginsIds)) {
				
				Log::err("Failed to remove plugin - pluginId $pluginToEnableDisable does not exist");
				throw new WebAPI\Exception(
					_t("This plugin does not exist"),
					WebAPI\Exception::NO_SUCH_PLUGIN
				);
			}
		}
		
		return $plugins;
	}

	private function postProcessDisableEnableActions() {
		$params = $this->getParameters();
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  /* @var $pluginsModel \Plugins\Model */
		$masterPlugins = $pluginsModel->getMasterPluginsByIds($params['plugins']);
		$viewModel = new ViewModel(array('plugins' => $masterPlugins));
		$viewModel->setTemplate('plugins/web-api/plugins-info');
		return $viewModel;
	}

	protected function getOrderBy($orderBy) {
		$pluginsModel = $this->getLocator()->get('Plugins\Db\Mapper'); /* @var $pluginsModel \Plugins\Db\Mapper */
		$fieldsMap = array (
			'id' => $pluginsModel::ID,
			'name' => $pluginsModel::NAME,
			'version' => $pluginsModel::VERSION,
			'creationTimeTimestamp' => $pluginsModel::CREATION_TIME,
		);
		if (isset($fieldsMap[$orderBy])) {
			return $fieldsMap[$orderBy];
		}
		return $pluginsModel::ID;
	}
	
	/**
	 * @param array $namespaces
	 * @return array
	 */
	private function flattenMessagesArray(array $namespaces) {
		$flatMessages = array();
		foreach ($namespaces as $namespace => $elements) {
			foreach ($elements as $name => $messages) {
				foreach ($messages as $message) {
					$flatMessages[] = _t('(%s) %s: %s', array(ucfirst($namespace), $name, $message));
				}
			}
		}
		return $flatMessages;
	}
	
	private function sendMailPluginUpdateNotification($notificationsMapper) {
		// Send Email - if it's still wasn't sent
		if (empty($notificationsMapper->getNotifiedFlag(NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE))) {
			$renderer = $this->getLocator( 'Zend\View\Renderer\PhpRenderer' ); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
			$resolver = $renderer->resolver();
	
			$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
			$request->setPost(new \Zend\Stdlib\Parameters(array('type' => 'pluginUpdateAvailable')));
			$request->setMethod('POST');
			$viewModel = $this->forward()->dispatch('NotificationsWebApi-1_3', array('action' => 'sendNotification')); /* @var $viewModel \Zend\View\Model\ViewModel */
			$renderer->setResolver($resolver);
			 
			$notificationsMapper->updateNotifiedFlag(NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE);
		}
	}
	
}
