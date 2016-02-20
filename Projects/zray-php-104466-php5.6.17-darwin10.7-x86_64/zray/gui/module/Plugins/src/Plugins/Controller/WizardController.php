<?php

namespace Plugins\Controller;
use Application\Module;

use Zend\InputFilter\InputFilter,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper,
	ZendServer\Mvc\Controller\ActionController, 
	Plugins\SessionStorage, 
	Deployment\Application\Package, 
	Deployment\Forms\SetInstallation, 
	Deployment\Model,
 	ZendServer\Text, 
	ZendServer\Exception, 
	Notifications\NotificationContainer,
	Plugins\Controller\SetUpdateCookie,
	ZendServer\Log\Log;

use ZendServer\FS\FS;

class WizardController extends ActionController {
	const DEMO_APP_NAME = 'Zend Demo Application';
	const DEMO_APP_PACKAGE_NAME = 'ZendDemoApp';
	const SAMPLES_APP_NAME = 'PHP Samples For IBMI';
	const SAMPLES_APP_PACKAGE_NAME = 'ZendSamplesApp';
	const WEBAPI_SAMPLES_APP_PACKAGE_NAME = 'WebAPI Samples';
	
	public function wizardAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$sessionStorage = new SessionStorage($wizardId);
		$sessionStorage->clear();
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		
		$get = $this->getRequest()->getQuery()->toArray();
		$action = isset($get['action']) ? $get['action'] : 'deploy';
		$pluginId = isset($get['pluginId']) ? $get['pluginId'] : '';
	
		$viewModel->external = false;
		if ($action == 'update') {
			$model = $this->getLocator()->get('Plugins\Model');  /* @var $model \Plugins\Model */
			$plugins = $model->getMasterPluginsByIds(array($pluginId));
			// put plugin name in the session for the update process
			$sessionStorage->setName($plugins[$pluginId]->getPluginName());
		} elseif ($action == 'download' || $action == 'update-download') {
			$viewModel->external = true;
			$viewModel->downloadParams = array('url' => $get['url'], 'name' => $get['name']);
		}
		
		$viewModel->wizardAction = $action;
		$viewModel->pluginId = $pluginId;
		$viewModel->wizardId = $wizardId;
		
		return $viewModel;
	}
	
	public function downloadAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
	
		$params = $this->getParameters(array('url' => '', 'name' => '', 'version' => ''));
		
		// get directive that indicates if we allowed to download or not
		/* @var $directivesMapper \Configuration\MapperDirectives */
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); 
		$autoDownload = $directivesMapper->getDirective('zend_deployment.auto_download');
		$allowDownloading = $autoDownload->getFileValue();
		
		$url = $params['url'];
		$uri = \Zend\Uri\UriFactory::factory($url);
		$url = $uri->toString();
		
		$this->getRequest()->getPost()->url = $url;
		$this->getRequest()->getPost()->name = $params['name'];
		$this->getRequest()->getPost()->version = $params['version'];
		$this->getRequest()->getPost()->override = 'TRUE';
		if ($allowDownloading) {
			try {
				$output = $this->forward()->dispatch('DeploymentWebAPI-1_6', array('action' => 'deploymentDownloadFile'));
				$download = $this->getDeploymentDbMapper()->findByUrl($params['url']);
	
				$sessionStorage = new SessionStorage($wizardId);
				$sessionStorage->setDownloadId($download['id']);
				
			} catch (\Exception $e) {
			}
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD to perform the download
			// in case of the "download" action, execute ZDD asyncly. i.e. 
			// this PHP process will end, but ZDD will continue working - downloading the file.
			zrayStandaloneExecuteTasks($__asyncExecution = true);
		}
	
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
	
		$viewModel->setVariable('url', $url);
		$viewModel->setVariable('name', $params['name']);
		$viewModel->setVariable('version', $params['version']);
		$viewModel->setVariable('allowDownloading', $allowDownloading);
		$viewModel->setVariable('wizardId', $wizardId);
	
		return $viewModel;
	}
	
	public function finishDownloadAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
	
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal(true);
	
		$params = $this->getParameters(array('path' => ''));
	
		$sessionStorage = new SessionStorage($wizardId);

		$result = $this->storeDownloadedPackage($sessionStorage, $params['path']);
		
		$viewModel->setVariable('message', $result['message']);
		$viewModel->setVariable('success', $result['success']);
			
		return $viewModel;
	}
	
	private function storeDownloadedPackage($sessionStorage, $packagePath, $deleteOnComplete = true) {
		$success = true;
		$message = '';
		
		if (Package::isValid ( $packagePath )) {
			try {
				$this->validateUploadedPackage($packagePath);
			} catch (\Deployment\Exception $ex) {
				if ($ex->getCode() <= \Deployment\Exception::WRONG_TYPE || $ex->getCode() >= \Deployment\Exception::UNKNOWN_ERROR) {
					$message = $ex->getMessage();
					$success = false;
		
					if ($deleteOnComplete) {
						FS::unlink($packagePath);
					}
					
					return array('message' => $message, 'success' => $success);
				}
			}
		
			$sessionStorage->setPackageFilePath ( $packagePath );
		
			$package = null;
			try {
				$package = Package::generate ( $packagePath );
				$sessionStorage->setStoredPackage($package);
				$sessionStorage->setName($package->getName());
				
				$model = $this->getLocator('Plugins\Model'); /* @var $model \Plugins\Model */
				$deploymentPackage = $model->storePendingDeployment($packagePath, array(), array());
				
				$onlyFile = basename($packagePath);
				$message = 'Package ' . $onlyFile . ' successfully downloaded';
			} catch ( Exception $e ) {
				Log::err ( 'Could not retrieve the plugin package: ' . $e->getMessage()  );
				$message = new Text ( 'The plugin package was uploaded but could not be retrieved' );
					
				$success = false;
			}
		} else {
			$message = _t('The uploaded file is not a valid package. Further details can be found in deployment.log');
			$success = false;
		}
		
		return array('message' => $message, 'success' => $success);
	}
	
	public function deployAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true); 
		
		try {
			$sessionStorage = new SessionStorage($wizardId);
			$name = $sessionStorage->getName();

			// if plugin exists - update the existing plugin: bug #ZSRV-14633
			$model = $this->getLocator('Plugins\Model'); /* @var $model \Plugins\Model */
			$plugin = $model->getPluginByName($name);
			
			if ($plugin) {
			    return $output = $this->forward()->dispatch('PluginsWizard', array('action' => 'update', 'wizardId' => $wizardId));
			}
			
			$package = $model->getPendingDeploymentByName($name);
		} catch ( Exception $e ) {
			Log::err ( "Could not retrieve the plugin package by name: $name, ". $e->getMessage() );
			throw new Exception ( 'Deployment package is missing.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
		
		if (!isZrayStandaloneEnv()) {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_PLUGIN_DEPLOY, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(_t('Plugin name: %s', array($name)))), $name); /* @var $auditMessage \Audit\Container */
		}
		
		$this->getLocator()->get('Plugins\Mapper\Deploy')->deployPlugin($name);
		
		if (!isZrayStandaloneEnv()) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD to process deployment tasks
			zrayStandaloneExecuteTasks();
		}
		
		$pluginObject = $model->getPluginByName($name);
		$this->resetPluginsUpdateCookie(array('name' => $name, 'version' => $pluginObject->getPluginVersion()));
		$viewModel->setVariable('plugin', $pluginObject);
		return $viewModel;
	}
	
	public function updateAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setTemplate('plugins/wizard/deploy');
		try {
			$sessionStorage = new SessionStorage($wizardId);
			$name = $sessionStorage->getName();
		} catch ( Exception $e ) {
			if (isset($name)) {
				Log::err ( 'Could not retrieve the plugin package by name: '. $name . ' ' . $e->getMessage() );
			} else {
				Log::err ( 'Could not retrieve the plugin package by name. ' . $e->getMessage() );
			}
			throw new Exception ( 'The deployment package could not be found.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}	

		$model = $this->getLocator('Plugins\Model'); /* @var $model \Plugins\Model */
		$plugin = $model->getPluginByName($name);
		
		if (!isZrayStandaloneEnv()) {
			$auditMessage = $this->auditMessage(auditMapper::AUDIT_PLUGIN_UPGRADE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array('Name' => $name)), $name); /* @var $auditMessage \Audit\Container */
		}
		$model->updatePlugin($name);
	
		$this->cleanPluginUpdates($name);
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD tasks execution
			zrayStandaloneExecuteTasks();
		}
		
		$viewModel->setVariable('plugin', $plugin);
		return $viewModel;
	}
	
	public function uploaderAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$this->Layout ( 'layout/login' );
		
		try {
			$sessionStorage = new SessionStorage ($wizardId);
			$name = $sessionStorage->getName();
			$action = 'Upload-Update';
		} catch ( Exception $e ) {
			$action = 'Upload';
		}
		
		return array ('action' => $action, 'controller' => 'PluginsWizard', 'wizardId' => $wizardId);
	}
	
	public function uploadUpdateAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$success = true;
		
		$sessionStorage = new SessionStorage($wizardId);
		try {
			$name = $sessionStorage->getName();
		} catch ( Exception $e ) {
			Log::err ( 'Name was not provided for the deployment action' );
			$success = false;
			
			$viewModel->message = 'This plugin does not exist';
			$viewModel->success = false;
			return $viewModel;
		}
		
		$model = $this->getLocator()->get('Plugins\Model');
		$plugin = $model->getPluginByName($name);
		
		if (Model::STATUS_NOT_EXISTS ==	$plugin->getMasterStatus()) {
			Log::err ( 'Could not retrieve package by name' );
			
			$viewModel->message = 'This plugin does not exist';
			$viewModel->success = false;
			return $viewModel;
		}
		
		$transfer = new \Zend\File\Transfer\Adapter\Http ();
		$uploaddir = $this->getGuiTempDir();
		$transfer->setDestination($uploaddir);
		
		if ($transfer->receive ( array (
				'url' 
		) )) {
			$packagePath = $transfer->getFileName ( 'url', true );
			if (is_array ( $packagePath )) {
				$packagePath = end ( $packagePath );
			}
			
			if (Package::isValid ( $packagePath )) {
				try {
					$this->validateUploadedPackage($packagePath, true);
				} catch (\Deployment\Exception $ex) {
					if ($ex->getCode() <= \Deployment\Exception::WRONG_TYPE || $ex->getCode() >= \Deployment\Exception::UNKNOWN_ERROR) {
						$message = $ex->getMessage();
						$success = false;
						$viewModel->message = $message;
						$viewModel->success = $success;
						return $viewModel;
					}
				}
				
				$sessionStorage->setPackageFilePath ( $packagePath );
				
				$package = null;
				try {
					$package = Package::generate ( $packagePath );
					$sessionStorage->setStoredPackage($package);
					$onlyFile = basename($packagePath);
					
					// store pending deployment
					try {
						
						$deploymentPackage = $model->storePendingDeployment(
								$packagePath,
								array(),
								array()
						);
						$message = 'Package ' . $onlyFile . ' successfully uploaded';
					} catch (Exception $e) {
						Log::err ( 'Could not store the pending deployment package: '. $e->getMessage() );
						$message = new Text ( 'Zend Deployment failed while trying to store the plugin package' );
						
						$success = false;
					}
				} catch ( Exception $e ) {
					Log::err ( 'Could not retrieve the plugin package by name : '. $e->getMessage() );
					$message = new Text ( 'The plugin package was uploaded but could not be retrieved' );
					
					$success = false;
				}
			} else {
				$message = _t('The uploaded file is not a valid plugin package. Further details can be found in deployment.log');
				$success = false;
			}
		} else {
			$success = false;
			$message = array_values ( $transfer->getMessages () );
		}
		
		$viewModel->message = $message;
		$viewModel->success = $success;
		return $viewModel;
	}
	
	public function uploadAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate('plugins/wizard/upload');
		$success = true;
		
		$transfer = new \Zend\File\Transfer\Adapter\Http ();
		$uploaddir = $this->getGuiTempDir();
		$transfer->setDestination($uploaddir);
		
		try {
			if ($transfer->receive ( array (
					'url' 
			) )) {
				$packagePath = $transfer->getFileName ( 'url', true );
				if (is_array ( $packagePath )) {
					$packagePath = end ( $packagePath );
				}
				
				// validate the name of the package
				$this->validateZpkPath($packagePath, 'url');
				
				// ZwasComponents_Deployment_Api_Package::isValid($packagePath)
				if (Package::isValid ( $packagePath )) {
					$sessionStorage = new SessionStorage ($wizardId);
					
					try {
						$this->validateUploadedPackage($packagePath);
					} catch (\Deployment\Exception $ex) {
						if ($ex->getCode() <= \Deployment\Exception::WRONG_TYPE || $ex->getCode() >= \Deployment\Exception::UNKNOWN_ERROR) {
							$message = $ex->getMessage();
							$success = false;
							$viewModel->message = $message;
							$viewModel->success = $success;
							return $viewModel;
						}
					}
					
					$sessionStorage->setPackageFilePath ( $packagePath );
					
					$package = null;
					$model = $this->getLocator()->get('Plugins\Model');
					try {
						/* @var \Deployment\Application\Package */
						$package = Package::generate ( $packagePath );
						
						// delete pending tasks of the plugin $package. (Delete not relevant records related to tasks)
					    $model->cancelPendingDeployment($package->getName());
						
					    $model->storePendingDeployment($packagePath, $package->getName());
						
						$sessionStorage->setStoredPackage($package);
						$sessionStorage->setName($package->getName());
						$onlyFile = basename($packagePath);
						$message = 'Package ' . $onlyFile . ' successfully uploaded';
					} catch ( Exception $e ) {
						Log::err ( 'Could not retrieve the plugin: ' . $e->getMessage()  );
						$message = new Text ( 'The plugin package was uploaded but could not be retrieved' );
						$success = false;
					}
				} else {
					$message = _t('The uploaded file is not a valid package. Further details can be found in deployment.log');
					$success = false;
				}
				// echo $packagePath;
			} else {
				$success = false;
				
				$errorMessages = $transfer->getMessages();
	
				// set specific message for max ini size upload errors
				if (isset($errorMessages['fileUploadErrorIniSize'])) {				
					if (\ZendServer\FS\FS::hasLighttpd()) {
						$message = _t('The deployment filesize exceeds PHP ini settings. Consider increasing \'post_max_size\' and \'upload_max_filesize\' values in your lighttpd server.');						
					}else {
						$message = _t('The deployment filesize exceeds PHP ini settings. Consider increasing \'post_max_size\' and \'upload_max_filesize\' values in your server configuration.'); 
					}
				} else {
					// get only the first error message
					$messages = array_values($errorMessages);
					$message = $messages[0];
				}
			}
		} catch (\Exception $e) {
			$success = false;
			$message = $e->getMessage();
		}
		
		if (isZrayStandaloneEnv()) {
			// manually trigger ZDD to process deployment tasks
			zrayStandaloneExecuteTasks();
		}
		
		$viewModel->message = $message;
		$viewModel->success = $success;
		return $viewModel;
	}
	
	public function installationAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		try {
			$sessionStorage = new SessionStorage ($wizardId);
			$package = $sessionStorage->getStoredPackage();
		} catch ( Exception $e ) {
			Log::err ( 'Could not retrieve the plugin package by file path: ' . $e->getMessage()  );
			throw new Exception ( 'The plugin package cannot be found.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
		
		$viewModel->package = $package;
		$viewModel->valid = true;
		return $viewModel;
	}
	
	public function readmeAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate('plugins/wizard/readme');
		$form = new \Zend\Form\Form ();
		$form->setAttribute('id', 'deployment-readme');
		
		$eulaContent = new \Zend\Form\Element\Textarea('readmeContent');
		$eulaContent->setName('readmeContent');
		$eulaContent->setAttributes(array (
				'id' => 'readmeContent',
				'rows' => '22',
				'readonly' => 'readonly', 
				'style' => 'resize:none; font-family:Arial, Helvetica, sans-serif; font-size:12px;',
				'value' => $this->getReadmeContents ()
		));
		
		$form->add($eulaContent);

		$viewModel->hasReadme = ($this->getReadmeContents ()) ? true : false;
		$viewModel->form = $form;
		
		return $viewModel;
	}
	
	public function eulaAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$form = new \Zend\Form\Form ();
		$form->setAttribute('id', 'deployment-eula');
		
		$eulaContent = new \Zend\Form\Element\Textarea('eulaContent');
		$eulaContent->setName('eulaContent');
		$eulaContent->setAttributes(array (
				'id' => 'eulaContent',
				'rows' => '22',
				'readonly' => 'readonly', 
				'style' => 'resize:none; font-family:Arial, Helvetica, sans-serif; font-size:12px;',
				'value' => $this->getEulaContents ()
		));
		
		$form->add($eulaContent);
		
		$acceptTerms = new \Zend\Form\Element\Checkbox('acceptTerms');
		$acceptTerms
			->setLabel(_t('I have read and agree to the license agreement'))
			->setValue('0')
			->setAttribute('id', 'accept-terms')
			->setAttribute('onclick', 'enableNextButton()');
		
		$form->add($acceptTerms);
		
		$viewModel->hasEula = ($this->getEulaContents ()) ? true : false;
		$viewModel->form = $form;
		
		return $viewModel;
	}
	
	public function prerequisitesAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		
		try {
			$sessionStorage = new SessionStorage ($wizardId);
			$filePath = $sessionStorage->getPackageFilePath ();
		} catch ( Exception $e ) {
			throw new Exception ( 'Zend Deployment could not locate the plugin package. Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
		
		$model = $this->getLocator()->get('Plugins\Model');
		$package = $sessionStorage->getStoredPackage();
		$prerequisites = $package->getPrerequisites();
		$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
		
		if (!isZrayStandaloneEnv()) {
			$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
			$configurationContainer->createConfigurationSnapshot(
						$configuration->getGenerator()->getDirectives(),
						$configuration->getGenerator()->getExtensions(),
						null,
						$configuration->getGenerator()->needServerData()
					);
			$isValid = false;
			if ($configuration->isValid($configurationContainer)) {
				$isValid = true;
			}
			$messages = $configuration->getMessages();
		} else {
			// @TODO: implement!
			$messages = array();
			$isValid = true;
		}
		$viewModel->isValid = $isValid;
		$viewModel->messages = $messages;
		$viewModel->wizardId = $wizardId;
		
		return $viewModel;
	}
	
	public function summaryAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)

		$sessionStorage = new SessionStorage ($wizardId);
		$package = $sessionStorage->getStoredPackage();
		 
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setVariable ('success', true);
		$viewModel->name = $package->getDisplayName();
		$viewModel->version = $package->getVersion();
		$viewModel->logo = $package->getLogo();
		
		return $viewModel;
	}
	
	
	public function submitAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		
		$viewModel->setVariable ( 'success', true );
		
		$sessionStorage = new SessionStorage ($wizardId);
		try {
			$name = $sessionStorage->getName ();
		} catch ( Exception $e ) {
			Log::err ( 'Name was not provided for the deployment action' );
			return $viewModel->setVariable ( 'success', false );
		}
		
		$model = $this->getLocator()->get('Plugins\Model');  /* @var $deploymentModel \Plugins\Model */

		try {
			//$model->deployPlugin($name, 0);
		} catch ( Exception $e ) {
			Log::err ( 'deployPlugin call failed' );
			return $viewModel->setVariable ( 'success', false );
		}
		
		return $viewModel;
	}
	
	public function cancelAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		
		$viewModel->setVariable ( 'success', true );
		
		$sessionStorage = new SessionStorage ($wizardId);
		
		try {
			$name = $sessionStorage->getName ();
		} catch ( Exception $e ) {
			Log::notice ( 'Name was not provided for the cancel action' );
			$sessionStorage->clear ();
			return $viewModel;
		}
		
		$deploymentModel = $this->getLocator ()->get ( 'deploymentModel' ); /*
		                                                                 * @var
		                                                                 * $deploymentModel
		                                                                 * \Deployment\Model
		                                                                 */
		$deploymentModel->cancelPendingDeployment ( $name );
		
		$sessionStorage->clear ();
		
		return $viewModel;
	}
	
	/**
	 * @param string $packagePath
	 * @throws \Deployment\Exception
	 */
	protected function validateUploadedPackage($packagePath, $isUpdate = false) {
		$this->getPluginsMapper()->validatePackage($packagePath, $isUpdate);
	}
	
	protected function getGuiTempDir() {
		return \ZendServer\FS\FS::getGuiTempDir();
	}
	
	/**
	 * Get readme text
	 *
	 * @return string
	 */
	private function getReadmeContents() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$sessionStorage = new SessionStorage ($wizardId);
		$package = $sessionStorage->getStoredPackage();
		return $package->getReadme();
	}
	
	/**
	 * Get EULA text
	 *
	 * @return string
	 */
	private function getEulaContents() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$sessionStorage = new SessionStorage ($wizardId);
		$package = $sessionStorage->getStoredPackage();
		return $package->getEula ();
	}
	
	/**
	 * @param array $params
	 * @return boolean
	 */
	private function isWizardRequest($params) {
		return isset($params['wizardAjax']);
	}
	
	/**
	 * Return the linux distro name
	 * @return mixed
	 */
	private function getLinuxDistro() {
	    exec('less /etc/issue', $output);
	    if (count($output) > 0) {
	
	        $distros = array(
	            'Ubuntu' => 'Ubuntu',
	            'Fedora' => 'Fedora',
	            'OEL'	 => 'Oracle',
	            'RHEL'	 => 'Red Hat',
	            'OpenSUSE'	=> 'openSUSE',
	            'SUSE'	 => 'SUSE',
	            'Debian' => 'Debian',
	            'CentOS' => 'CentOS',
	        );
	        	
	        foreach ($distros as $distro => $keyword) {
	            foreach ($output as $outputRow) {
	                if (strpos($outputRow, $keyword) !== false) {
	                    return $distro;
	                }
	            }
	        }
	    }
	
	    return '';
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
        
        $this->resetPluginsUpdateCookie();
	}
	
	private function resetPluginsUpdateCookie($newPlugin=null) {
	    $pluginsModel = $this->getLocator()->get('Plugins\Model');
	    SetUpdateCookie::resetCookieContent($pluginsModel, $newPlugin);
	}
}
