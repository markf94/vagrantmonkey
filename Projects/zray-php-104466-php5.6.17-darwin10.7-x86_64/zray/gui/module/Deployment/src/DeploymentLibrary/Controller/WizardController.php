<?php

namespace DeploymentLibrary\Controller;

use ZendDeployment_Manager,
	Audit\Db\Mapper as auditMapper,
	Audit\Db\ProgressMapper,
	Deployment\Controller\WizardController as baseWizardController,
	Deployment\SessionStorage,
 	ZendServer\Text, 
	ZendServer\Exception, 
	ZendServer\Log\Log;
use Zend\View\Model\ViewModel;
use Deployment\Application\Package;
use ZendServer\FS\FS;
use Zend\Form\Form;

class WizardController extends baseWizardController {
	
	public function UpdateWizardAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$sessionStorage = new SessionStorage($wizardId);
		$sessionStorage->clear();
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
		$viewModel->setTemplate('deployment-library/wizard/wizard');
		
		$params = $this->getParameters(array('libraryId' => ''));
		
		$viewModel->setVariable('libraryId', $params['libraryId']);
		$viewModel->setVariable('wizardId', $wizardId);
		
		return $viewModel;
	} 
	
	public function retryDownloadAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
		
		$params = $this->getParameters(array('libraryId' => ''));
		
		$library = $this->getDeploymentDbMapper()->deleteByLibraryId($params['libraryId']);
		
		return $viewModel;
	}
	
	public function finishDownloadAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal(true);
		
		$params = $this->getParameters(array('path' => ''));
		
		$success = true;
		$message = '';
		
		$sessionStorage = new SessionStorage($wizardId);
		
		$packagePath = $params['path'];
		if (Package::isValid ( $packagePath )) {
			try {
				$this->validateUploadedPackage($packagePath);
			} catch (\Deployment\Exception $ex) {
				if ($ex->getCode() <= \Deployment\Exception::WRONG_TYPE || $ex->getCode() >= \Deployment\Exception::UNKNOWN_ERROR) {
					$message = $ex->getMessage();
					$success = false;
		
					$viewModel->setVariable('message', $message);
					$viewModel->setVariable('success', $success);
					
					FS::unlink($packagePath);
					return $viewModel;
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
		
		$viewModel->setVariable('message', $message);
		$viewModel->setVariable('success', $success);
			
		return $viewModel;
	}
	
	public function downloadAction() {
		$params = $this->getParameters(array('libraryId' => ''));
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		// get directive that indicates if we allowed to download or not
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
		$autoDownload = $directivesMapper->getDirective('zend_deployment.auto_download');
		$allowDownloading = $autoDownload->getFileValue();
		
		$deploymentLibraryMapper = $this->getDeploymentLibraryMapper(); /* @var $deploymentLibraryMapper \DeploymentLibrary\Mapper */
		$deployedLibrary = $deploymentLibraryMapper->getLibraryById($params['libraryId']);
			
		$mapper = $this->getLocator()->get('DeploymentLibrary\Db\Mapper'); /* @var $mapper \DeploymentLibrary\Db\Mapper */
		$update = $mapper->getUpdate($deployedLibrary->getLibraryName())->current();
		$extraData = json_decode($update['EXTRA_DATA'], true);
		$extraData['name'] = $deployedLibrary->getLibraryName();
		
		$this->getRequest()->getPost()->url = $extraData['downloadUrl'];
		$this->getRequest()->getPost()->name = $extraData['name'];
		$this->getRequest()->getPost()->version = $extraData['version'];
		$this->getRequest()->getPost()->override = 'TRUE';
		
		if ($allowDownloading) {
			try {
				$output = $this->forward()->dispatch('DeploymentWebAPI-1_6', array('action' => 'deploymentDownloadFile'));
				
				$download = $this->getDeploymentDbMapper()->findByUrl($extraData['downloadUrl']);
				
				$sessionStorage = new SessionStorage($wizardId);
				$sessionStorage->setDownloadId($download['id']);
			} catch (\Exception $e) {
				
			}
		}

		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
		
		$viewModel->setVariable('url', $extraData['downloadUrl']);
		$viewModel->setVariable('name', $extraData['name']);
		$viewModel->setVariable('version', $extraData['version']);
		$viewModel->setVariable('allowDownloading', $allowDownloading);
		$viewModel->setVariable('wizardId', $wizardId);
		
		return $viewModel;
	}
	
	public function wizardAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$sessionStorage = new SessionStorage($wizardId);
		$sessionStorage->clear();
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
		
		$viewModel->setVariable('wizardId', $wizardId);
		
		return $viewModel;
	}
	
	public function uploaderAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$this->Layout ( 'layout/login' );
		$viewModel = new ViewModel();
		$viewModel->setTemplate('deployment/wizard/uploader');
		$viewModel->setVariable('controller', 'LibraryWizard');
		$viewModel->setVariable('action', 'Upload');
		$viewModel->setVariable('wizardId', $wizardId);
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
	
		$form = null;
	
		$requiredParams = array();
		if ($this->getRequest ()->isPost ()) {
			$params = $this->getRequest ()->getPost ()->toArray ();
				
			if (! $this->isWizardRequest($params)) {
				$package = $sessionStorage->getStoredPackage();
				$requiredParams = $package->getRequiredParams();
	
				$form = $model->getUserParamsForm ( $requiredParams );
				$sessionStorage->setUserParams ( $params );
				Log::info('Stored user params', array($params));
	
				$form->setData($sessionStorage->getUserParams ());
				$valid = $form->isValid ();
			} else {
				
				$package = $sessionStorage->getStoredPackage();

				if ($package->hasRequiredParams()) {
					$requiredParams = $package->getRequiredParams();
					$params = array();
					if ($sessionStorage->hasUserParams()) {
						$params = $sessionStorage->getUserParams ();
					}
					$form = $model->getUserParamsForm ( $requiredParams, $params );
					$valid = true;
				}
			}
			if ($form instanceof Form) {
				// ZSRV-7469 added protection using enter key to submit
				$form->setAttribute('onsubmit', 'return false;');
			}
		}
	
		$viewModel->form = $form;
		$viewModel->valid = $valid;
		$viewModel->controller = 'LibraryWizard';
		
		$viewModel->setTemplate('deployment/wizard/user-params.phtml');
		
		return $viewModel;
	}
	
	
	public function setDefaultAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
	
		if ($this->getRequest()->isPost()) {
			$params = $this->getRequest()->getPost ()->toArray();
			
			$params['isDefault'] = $this->validateBoolean($params['isDefault'], 'isDefault');
			
			$sessionStorage = new SessionStorage($wizardId);
			$sessionStorage->setUserParams($params);
		}
		
		return $this->getResponse()->setContent('');
	}
	
	public function summaryAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal (true);
		$viewModel->setVariable ('success', true);
		
		$sessionStorage = new SessionStorage($wizardId);
		$package = $sessionStorage->getStoredPackage();
		
		$viewModel->setVariable('logo', $package->getLogo());
		$viewModel->setVariable('libraryName', $package->getName());
		$viewModel->setVariable('version', $package->getVersion());
		$viewModel->setVariable('wizardId', $wizardId);
		return $viewModel;
	}
	
	public function deployAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$viewModel = new \Zend\View\Model\ViewModel();
		$viewModel->setTerminal(true);
	
		try {
			$sessionStorage = new SessionStorage($wizardId);
			
			$isDefault = false;
			$userParams = array();
			if ($sessionStorage->hasUserParams()) {
				$userParams = $sessionStorage->getUserParams();
				$isDefault = $userParams['isDefault'];
				unset($userParams['isDefault']);
			}
			
			$packagePath = $sessionStorage->getPackageFilePath();
			$package = $sessionStorage->getStoredPackage();
			$packageName = $package->getName();
			$model = $this->getLocator('Deployment\Model'); /* @var $model \Deployment\Model */
		} catch ( Exception $e ) {
			Log::err ( 'Could not retrieve the library package by base URL: '. $e->getMessage() );
			throw new Exception ( 'Library package is missing.  Retry your last action and if this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center' );
		}
	
		$auditMessage = $this->auditMessage(auditMapper::AUDIT_LIBRARY_DEPLOY, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array(_t('Library name: %s (%s)', array($packageName, $package->getVersion())))), $packagePath); /* @var $auditMessage \Audit\Container */
		$library = $this->getDeploymentLibraryMapper()->deployLibrary($packagePath, $isDefault, $userParams);
		FS::unlink($packagePath);
		
		// PHP 5 >= 5.4.0
		$fsIterator = new \FilesystemIterator(dirname($packagePath));
		$isDirEmpty = !$fsIterator->valid();
		if ($isDirEmpty) {
		  FS::unlink(dirname($packagePath)); // try to clean the parent directory as well
		}
		
		// remove library notification if needed
		$currentLibraryVersion = current($library['versions']);
		$libraryVersion = $currentLibraryVersion['version'];
		$mapper = $this->getLocator()->get('DeploymentLibrary\Db\Mapper'); /* @var $mapper \DeploymentLibrary\Db\Mapper */
		$update = $mapper->getUpdate($library['libraryName'])->current();
		if ($update != false) {
			$oldVersion = $update['VERSION'];
			if (version_compare($libraryVersion, $oldVersion) >= 0) {
				$mapper->deleteUpdate($library['libraryName']);
					
				// check if there are any updates left and if not, remove notification message
				$updates = $mapper->getUpdates();
				if (count($updates->toArray()) == 0) {
					$notificationsMapper = $this->getNotificationsMapper();
					$notificationsMapper->deleteByType(\Notifications\NotificationContainer::TYPE_LIBRARY_UPDATE_AVAILABLE);
				}
			}
		}
		
		$viewModel->setVariable('libraryInfo', $library);
		
		return $viewModel;
	}
	
	public function cancelAction() {
		$wizardId = $this->getRequest()->getQuery('wizardId');
		$this->validateInteger($wizardId, 'wizardId'); // mt_rand(100000,999999)
		
		$sessionStorage = new SessionStorage($wizardId);
		
		if ($sessionStorage->hasPackageFilePath()) {
			$filepath = $sessionStorage->getPackageFilePath();
			// remove file
			if (file_exists($filepath)) {
				unlink($filepath);
			}
		}
		
		if ($sessionStorage->hasDownloadId()) {
			$downloadIs = $sessionStorage->getDownloadId();
			$this->getLocator()->get('Deployment\Mapper\Deploy')->cancelDownloadFile($downloadIs);
		}
		
		$sessionStorage->clear();
		
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal (true);
		
		return $viewModel;
	}
	
	protected function validateUploadedPackage($packagePath) {
		$this->getDeploymentLibraryMapper()->validatePackage($packagePath);
	}
	
	/**
	 * Get EULA text
	 *
	 * @todo implement
	 * @return string
	 */
	protected function getEulaContents() {
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
	protected function isWizardRequest($params) {
		return isset($params['wizardAjax']);
	}
}
