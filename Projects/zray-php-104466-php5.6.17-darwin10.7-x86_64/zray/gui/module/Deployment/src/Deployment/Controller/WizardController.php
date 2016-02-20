<?php

namespace Deployment\Controller;
use Application\Module;

use Zend\InputFilter\InputFilter,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper,
	ZendServer\Mvc\Controller\ActionController, 
	Deployment\SessionStorage, 
	Deployment\Application\Package, 
	Deployment\Forms\SetInstallation, 
	Deployment\Model,
 	ZendServer\Text, 
	ZendServer\Exception, 
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
		$applicationId = isset($get['applicationId']) ? $get['applicationId'] : '';
		
		$viewModel->external = false;
		if ($action == 'update') {
			// get baseUrl and put it in the session for the update process
			$model = $this->getLocator()->get('Deployment\Model');
			$applicationInfo = $model->getApplicationById($applicationId);
			$sessionStorage->setBaseUrl($applicationInfo->getBaseUrl());
			$sessionStorage->setApplicationId($applicationId);
		} elseif ($action == 'download') {
			$viewModel->external = true;
			if ($get['name'] == self::DEMO_APP_NAME || $get['name'] == self::SAMPLES_APP_NAME) {
				try {
					$pinger = @file_get_contents('http://www.zend.com/products/server/license/ping');
				} catch (\Exception $e) {
					// do nothing
				}
				// no internet connection - deploy internally
				if ($pinger === false) {
					$viewModel->external = false;
					
					if ($get['name'] == self::DEMO_APP_NAME) {
						$filepath = FS::createPath(ZEND_SERVER_GUI_PATH, 'data', 'demo.zpk');
					} elseif ($get['name'] == self::SAMPLES_APP_NAME) {
						$filepath = FS::createPath(ZEND_SERVER_GUI_PATH, 'data', 'PHPSamplesForIBMi.zpk');
					}
					
					$result = $this->storeDownloadedPackage($sessionStorage, $filepath, false);
					
					$viewModel->setVariable('message', $result['message']);
					$viewModel->setVariable('success', $result['success']);
					
				} else { // have internet connection - download demo application
					$viewModel->downloadParams = array('url' => $get['url'], 'name' => $get['name'], 'version' => $get['version']);
				}
			} else {
				$viewModel->downloadParams = array('url' => $get['url'], 'name' => $get['name'], 'version' => $get['version']);
			}
		}
		
		$viewModel->wizardAction = $action;
		$viewModel->applicationId = $applicationId;
		$viewModel->wizardId = $wizardId;
		
		return $viewModel;
	}
	
	public function retryDownloadAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
	
		$params = $this->getParameters(array('url' => ''));
	
		$library = $this->getDeploymentDbMapper()->deleteByUrl($params['url']);
	
		return $viewModel;
	}
	
	public function downloadAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$params = $this->getParameters(array('url' => '', 'name' => '', 'version' => ''));
		
		// get directive that indicates if we allowed to download or not
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
		$autoDownload = $directivesMapper->getDirective('zend_deployment.auto_download');
		$allowDownloading = $autoDownload->getFileValue();
	
		// add env params to the url
		$zsVersion = Module::config('package', 'version');
		$phpVersion = phpversion();
		$osName = FS::getOSAsString();
		$arch = php_uname('m');
		
		if (strtolower($osName) == 'linux') {
		    $osName = $this->getLinuxDistro();
		    if (empty($osName)) {
		        $osName = 'Linux';
		    }
		}
		
		$uniqueId = Module::config('license', 'zend_gui', 'uniqueId');
		
		$url = $params['url'];
		$uri = \Zend\Uri\UriFactory::factory($url);
		$queryParams = array_merge($uri->getQueryAsArray(), array('zs' => $zsVersion, 'php' => $phpVersion, 'os' => $osName, 'arch' => $arch, 'uid' => $uniqueId));
		$uri->setQuery($queryParams);
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
				$onlyFile = basename($packagePath);
				$message = 'Package ' . $onlyFile . ' successfully downloaded';
			} catch ( Exception $e ) {
				Log::err ( 'Could not retrieve the application package by base URL: ' . $e->getMessage()  );
				$message = new Text ( 'The application package was uploaded but could not be retrieved' );
					
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
			$baseUrl = $sessionStorage->getBaseUrl();
			
			$model = $this->getLocator('Deployment\Model'); /* @var $model \Deployment\Model */
			$package = $model->getPendingDeploymentByBaseUrl($baseUrl);
			$zendParams = $package->getZendParams();
			$userAppName = $zendParams['userApplicationName'];
			
			$packagePath = $sessionStorage->getPackageFilePath();
		} catch ( Exception $e ) {
			Log::err ( 'Could not retrieve the application package by base URL: '. $e->getMessage() );
			throw new Exception ( 'Deployment package is missing.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
		
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_DEPLOY, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(_t('Application name: %s', array($userAppName)))), $baseUrl); /* @var $auditMessage \Audit\Container */
		$this->getLocator()->get('Deployment\Mapper\Deploy')->deployApplication($baseUrl);
		
		if (file_exists($packagePath)) {
		    unlink($packagePath);
		}
		
		$application = $this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl);
		$viewModel->setVariable('application', $application);
		return $viewModel;
	}
	
	public function updateAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setTemplate('deployment/wizard/deploy');
		try {
			$sessionStorage = new SessionStorage($wizardId);
			$baseUrl = $sessionStorage->getBaseUrl();
		} catch ( Exception $e ) {
			Log::err ( 'Could not retrieve the application package by base URL: '. $e->getMessage() );
			throw new Exception ( 'The deployment package could not be found.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}	

		$application = $this->getDeploymentMapper()->getApplicationByBaseUrl($baseUrl);
		
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_APPLICATION_UPGRADE, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array('Application name' => $application->getUserApplicationName())), $baseUrl); /* @var $auditMessage \Audit\Container */
		$this->getDeploymentMapper()->updateApplication($baseUrl, $application->getApplicationId(), $this->getLocator());
	
		$viewModel->setVariable('application', $application);
		
		return $viewModel;
	}
	
	public function uploaderAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		//TODO is this suppose to be empty.phtml?
		$this->Layout ( 'layout/login' );
		//$viewModel = new \Zend\View\Model\ViewModel ();
		//$viewModel->setTerminal ( true );
		
		try {
			$sessionStorage = new SessionStorage ($wizardId);
			$baseUrl = $sessionStorage->getBaseUrl ();
			$action = 'Upload-Update';
		} catch ( Exception $e ) {
			$action = 'Upload';
		}
		
		return array ('action' => $action, 'controller' => 'Wizard', 'wizardId' => $wizardId);
		
		//$viewModel->action = $action;
		//return $viewModel;
	}
	
	public function uploadUpdateAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$success = true;
		
		$sessionStorage = new SessionStorage($wizardId);
		try {
			$baseUrl = $sessionStorage->getBaseUrl();
		} catch ( Exception $e ) {
			Log::err ( 'Base URL was not provided for the deployment action' );
			$success = false;
			
			$viewModel->message = 'This application does not exist';
			$viewModel->success = false;
			return $viewModel;
		}
		
		$model = $this->getLocator()->get('Deployment\Model');
		$application = $model->getApplicationByBaseUrl($baseUrl);
		
		if (Model::STATUS_NOT_EXISTS ==	$application->getStatus()) {
			Log::err ( 'Could not retrieve package by base URL' );
			
			$viewModel->message = 'This application does not exist';
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
				try {
					$package = Package::generate ( $packagePath );
					$sessionStorage->setStoredPackage($package);
					$onlyFile = basename($packagePath);
					
					// Verify it is the same package
					if ($package->getName() != $application->getApplicationName()) {
						$viewModel->message = 'The deployed application does not match the supplied package';
						$viewModel->success = false;
						return $viewModel;
					}
					
					// store pending deployment
					try {
						$userAppName = $application->getUserApplicationName();
						$zendParams = $model->createZendParams($userAppName, false, $baseUrl);
						$deploymentPackage = $model->storePendingDeployment(
								$packagePath,
								array(),
								$zendParams
						);
						$message = 'Package ' . $onlyFile . ' successfully uploaded';
					} catch (Exception $e) {
						Log::err ( 'Could not store the pending deployment package: '. $e->getMessage() );
						$message = new Text ( 'Zend Deployment failed while trying to store the application package' );
						
						$success = false;
					}
				} catch ( Exception $e ) {
					Log::err ( 'Could not retrieve the application package by base URL: '. $e->getMessage() );
					$message = new Text ( 'The application package was uploaded but could not be retrieved' );
					
					$success = false;
				}
			} else {
				$message = _t('The uploaded file is not a valid application package. Further details can be found in deployment.log');
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
		$viewModel->setTemplate('deployment/wizard/upload');
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
					try {
						$package = Package::generate ( $packagePath );
						$sessionStorage->setStoredPackage($package);
						$onlyFile = basename($packagePath);
						$message = 'Package ' . $onlyFile . ' successfully uploaded';
					} catch ( Exception $e ) {
						Log::err ( 'Could not retrieve the application package by base URL: ' . $e->getMessage()  );
						$message = new Text ( 'The application package was uploaded but could not be retrieved' );
						
						$success = false;
					}
				} else {
					// $deploymentLog = ApplicationModel::getLogDirectorypath() .
					// DIRECTORY_SEPARATOR .
					// ZwasComponents_Deployment_Model::EXTENSION_LOG;
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
		
		//
		// move_uploaded_file($_FILES['url']['tmp_name'][$count-1], 'c:\\Zend\\'
		// . $_FILES['url']['name'][$count-1]);
		
		// $deploymentLog =
		// Zwas_Path::create(ApplicationModel::getLogDirectorypath(),
		// ZwasComponents_Deployment_Model::EXTENSION_LOG);
		// The uploaded file is not a valid Zend Deployable Package. Further
		// details can be found in %s'
		
		$viewModel->message = $message;
		$viewModel->success = $success;
		return $viewModel;
	}
	
	public function installationAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		
		$valid = false;
		
		$path = '';
		try {
			$sessionStorage = new SessionStorage ($wizardId);
			$path = $sessionStorage->getPackageFilePath ();
			$package = $sessionStorage->getStoredPackage();
		} catch ( Exception $e ) {
			Log::err ( 'Could not retrieve the application package by file path: ' . $e->getMessage()  );
			throw new Exception ( 'The aplication package cannot be found.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
		
		
		$model = $this->getLocator()->get('Deployment\Model');
		
		/* @var $form \Deployment\Forms\SetInstallation */
		$form = $this->getLocator('Deployment\Form\SetInstallation');
		$form->fillWithPackageData($package);
		
		$request = $this->getRequest (); /*
		                                 * @var $request
		                                 * \Zend\Http\PhpEnvironment\Request
		                                 */
		if ($request->isPost ()) {
			$params = $request->getPost()->toArray ();
			if (isset ( $params ['vhosts'] )) { // Form was submitted
				$form->setData($params);
				$form->setSessionStorage($sessionStorage);
				$valid = $form->isValid();
				$errorMessages = array();
				if ($valid) {
					try {
						$form->process ($model, $path);
					} catch (Exception $ex) {
						$errorMessages[] = $ex->getMessage();
						$valid = false;
					}
				}
				if (!$valid) {
					// TODO: add error message check
					$viewModel->errorMessage = 'Invalid data';
										
					foreach ($form->getMessages() as $elementName => $message) {
						if (! empty($message)) {
							$label = $form->get($elementName)->getLabel();
							foreach ($message as $errorMsg) {
								$errorMessages[] = $label . ': ' . $errorMsg;
							}
						}
					}
					
					$viewModel->errorMessage = implode(',' , $errorMessages);
				}
			}
		}
		
		$viewModel->defaultPort = $this->getDeploymentMapper()->getDefaultServerPort();
		$viewModel->defaultHost = $_SERVER['SERVER_NAME'];
		$viewModel->valid = $valid;
		$viewModel->package = $package;
		$viewModel->form = $form;
		
		return $viewModel;
	}
	
	public function readmeAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$viewModel->setTemplate('deployment/wizard/readme');
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
		$viewModel->setTemplate('deployment/wizard/eula');
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
		$viewModel->setTemplate('deployment/wizard/prerequisites');
		
		try {
			$sessionStorage = new SessionStorage ($wizardId);
			$filePath = $sessionStorage->getPackageFilePath ();
		} catch ( Exception $e ) {
			throw new Exception ( 'Zend Deployment could not locate the application package. Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
		
		$model = $this->getLocator()->get('Deployment\Model');
		$package = $sessionStorage->getStoredPackage();
		$prerequisites = $package->getPrerequisites();
		$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
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
		$viewModel->isValid = $isValid;
		$viewModel->messages =  $configuration->getMessages();
		$viewModel->wizardId = $wizardId;
		
		return $viewModel;
	}
	
	public function userParamsAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$valid = false;
		$model = $this->getLocator()->get('Deployment\Model');
		$sessionStorage = new SessionStorage ($wizardId);
		
		
		$requiredParams = array();
		if ($this->getRequest ()->isPost ()) {
			$params = $this->getRequest ()->getPost ()->toArray ();
			
			//TODO this if-else has so much similar code it can make little hamsters sad
			if (! $this->isWizardRequest($params)) {
				// get required params by baseUrl
				try {
					$baseUrl = $sessionStorage->getBaseUrl ();
				} catch ( \ZendServer\Exception $e ) {
					Log::err ( 'BaseUrl was not provided for the deployment action' );
					return $viewModel->setVariable ( 'success', false );
				}
				
				$pendingDeployment = $model->getPendingDeploymentByBaseUrl ( $baseUrl );
				$packageFile = $pendingDeployment->getDeploymentPackage ();
				$requiredParams = $packageFile->getRequiredParams();				
				
				$form = $model->getUserParamsForm ( $requiredParams );
				$sessionStorage->setUserParams ( $params );
				Log::info('Stored user params', array($params));
				
				if ($sessionStorage->hasUserParams ()) {
					$form->setData($sessionStorage->getUserParams ());
					$valid = $form->isValid ();
				} else {
					$valid = true;
				}
				
				if ($valid) {
					try {
						$model->storePendingDeployment ( $sessionStorage->getPackageFilePath (), $sessionStorage->getUserParams (), $pendingDeployment->getZendParams () );
					} catch ( \Exception $e ) {
						Log::err ( 'Could not store user parameters' );
					}
				} else {
					Log::err ( 'Submitted user parameters are invalid' );
				}
			} else {
				
				// get required params by filePath
				try {
					$filePath = $sessionStorage->getPackageFilePath ();
				} catch ( Exception $e ) {
					throw new Exception ( 'Zend Deployment could not locate the application package. Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
				}
				
				$package = $sessionStorage->getStoredPackage();
				$requiredParams = $package->getRequiredParams();
				
				if (empty($requiredParams) || !array_key_exists('elements',$requiredParams)) {
					return $viewModel;
				}

				$userParams = array();
				if ($sessionStorage->hasApplicationId()) {
					$application = $model->getApplicationById($sessionStorage->getApplicationId());
					$userParams = $application->getUserParams();
				} else {
					$userParams = array();
				}
				
				$form = $model->getUserParamsForm ( $requiredParams, $userParams );
				if ($sessionStorage->hasUserParams() && $sessionStorage->getUserParams()) {
					$valid = $form->isValid ();
				} else {
					$valid = true;
				}
				
			}
		}
		
		// ZSRV-7469 added protection using enter key to submit
		$form->setAttribute('onsubmit', 'return false;');
		$viewModel->form = $form;
		$viewModel->valid = $valid;
		
		return $viewModel;
	}
	
	public function summaryAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setVariable ('success', true);
		
		$sessionStorage = new SessionStorage($wizardId);
		try {
			$baseUrl = $sessionStorage->getBaseUrl();
		} catch ( Exception $e ) {
			Log::err ( 'BaseUrl was not provided for the deployment action' );
			return $viewModel->setVariable ( 'success', false );
		}
		
		$deploymentModel = new \Deployment\Model ();
		$pendingDeployment = $deploymentModel->getPendingDeploymentByBaseUrl ( $baseUrl );
		$packageFile = $pendingDeployment->getDeploymentPackage ();
		$userParamsForm = $deploymentModel->getUserParamsForm ($packageFile->getRequiredParams(), $pendingDeployment->getUserParams());
		
		$viewModel->applicationName = $packageFile->getName();
		$viewModel->version = $packageFile->getVersion();
		$viewModel->logo = $packageFile->getLogo();
		
		$monitorRulePackes = $packageFile->getMonitorRules();
		if (empty($monitorRulePackes)) {
			$viewModel->containMonitorRules = false; 
		} else {
			$viewModel->containMonitorRules = true;
		}
		
		$pagecacheRulePackes = $packageFile->getPageCacheRules();
		if (empty($pagecacheRulePackes)) {
			$viewModel->containPageCacheRules = false;
		} else {
			$viewModel->containPageCacheRules = true;
		}
		
		$zendParams = $pendingDeployment->getZendParams();
		$viewModel->userApplicationName = $zendParams['userApplicationName'];
		$viewModel->baseUrl = $zendParams['baseUrl'];

		$viewModel->userParamsForm = $userParamsForm;
		
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
			$baseUrl = $sessionStorage->getBaseUrl ();
		} catch ( Exception $e ) {
			Log::err ( 'BaseUrl was not provided for the deployment action' );
			return $viewModel->setVariable ( 'success', false );
		}
		
		$deploymentModel = $this->getLocator ()->get ( 'deploymentModel' ); /* @var $deploymentModel \Deployment\Model */

		try {
			$deploymentModel->deployApplication ( $baseUrl, 0 );
		} catch ( Exception $e ) {
			Log::err ( 'deployApplication call failed' );
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
			$baseUrl = $sessionStorage->getBaseUrl ();
		} catch ( Exception $e ) {
			Log::notice ( 'BaseUrl was not provided for the cancel action' );
			$sessionStorage->clear ();
			return $viewModel;
		}
		
		$deploymentModel = $this->getLocator ()->get ( 'deploymentModel' ); /*
		                                                                 * @var
		                                                                 * $deploymentModel
		                                                                 * \Deployment\Model
		                                                                 */
		$deploymentModel->cancelPendingDeployment ( $baseUrl );
		
		$sessionStorage->clear ();
		
		return $viewModel;
	}
	
	/**
	 * @param string $packagePath
	 * @throws \Deployment\Exception
	 */
	protected function validateUploadedPackage($packagePath) {
		$this->getDeploymentMapper()->validatePackage($packagePath);
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
}
