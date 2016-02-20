<?php

namespace Deployment\Controller;
use ZendServer\Mvc\Controller\ActionController;
use ZendServer\Log\Log;
use Deployment\Forms\DefineApplicationForm;
use Deployment\SessionStorage;
use ZendServer\Exception;

class DefineWizardController extends ActionController {
	
	public function wizardAction() {
                
		$deploymentModel = $this->getLocator()->get('Deployment\Model'); /* @var $deploymentModel \Deployment\Model */
		
		$defineApplicationForm = $this->getLocator('Deployment\Form\DefineApplicationForm');
                
        $defineableApplications = $deploymentModel->getDefineableApplications();
        
        $appsBaseUrl = array('Base URL' => "0");
        if (is_array($defineableApplications)) {
	        foreach ($defineableApplications as $defineableApplication) {
	        	$appsBaseUrl[$defineableApplication['base_url']] = $defineableApplication['base_url'];
	        }
        }
        
        $viewModel = new \Zend\View\Model\ViewModel ();
        $viewModel->setTerminal ( true );
        $viewModel->defineApplicationForm = $defineApplicationForm;
        
        return $viewModel;
	}
	
	public function uploaderAction() {
		$this->Layout ( 'layout/login' );
	
		return array ();
	}

	public function uploadAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal ( true );
		$success = true;
		$logo = '';
		
		$transfer = new \Zend\File\Transfer\Adapter\Http ();
		$uploaddir = $this->getGuiTempDir();
		$transfer->setDestination($uploaddir);
		
		if ($transfer->isValid()) {
			if ($transfer->receive ( array ( 'url' ) )) {
				$packagePath = $transfer->getFileName ( 'url', true );
				if (is_array ( $packagePath )) {
					$packagePath = end ( $packagePath );
				}
				
				$extension = substr($packagePath, strrpos($packagePath, '.') + 1);
				if (in_array($extension, array('jpg', 'png', 'gif'))) {
					$onlyFile = basename($packagePath);
					$message = 'Logo ' . $onlyFile . ' Uploaded successfully';
					$logo = urlencode($packagePath);
				} else {
					$message = _t('Logo must be a valid image file');
					$success = false;
				}
			}
		} else {
			$message = $transfer->getErrors();
		}
				
		$viewModel->message = $message;
		$viewModel->success = $success;
		$viewModel->logo = $logo;
		return $viewModel;
	}
	
	protected function getGuiTempDir() {
		return \ZendServer\FS\FS::getGuiTempDir();
	}
}
