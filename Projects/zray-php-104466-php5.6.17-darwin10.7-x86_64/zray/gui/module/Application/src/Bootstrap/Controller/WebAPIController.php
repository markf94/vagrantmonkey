<?php
namespace Bootstrap\Controller;

use WebAPI\Exception;

use Users\Identity;

use Audit\Db\ProgressMapper;

use Audit\Db\Mapper;

use Application\Module;

use ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Set,
	Zend\Mvc\Controller\ActionController,
	ZendServer\Log\Log,
	\Audit\Container,
	WebAPI;

use Zend\View\Model\ViewModel;

class WebAPIController extends WebAPIActionController {
	
	public function bootstrapSingleServerAction() {
		try {
				
			$this->isMethodPost();
			
			if (Module::isBootstrapCompleted()) {
				throw new WebAPI\Exception('Zend Server bootstrap was completed already', WebAPI\Exception::MALFORMED_REQUEST);
			}
			
			$params = $this->getParameters(array(
					'production' 		=> 'TRUE',
					'applicationUrl' 	=> '',
					'adminEmail' 		=> '',
					'developerPassword' => '',
					'orderNumber' 		=> '',
					'licenseKey' 		=> '',
					'dontWait'			=> 'FALSE',
			));
				
			$this->validateMandatoryParameters($params, array(
					'adminPassword',
			));
			
			$adminPassword = $this->validateStringNonEmpty($params['adminPassword'], 'adminPassword');
			if ($params['adminEmail']) {
				$this->validateEmailAddress($params['adminEmail'], 'adminEmail');
			}
			
			if ($params['applicationUrl']) {
				$this->validateStringNonEmpty($params['applicationUrl'], 'applicationUrl');
			}
			
			if ($params['developerPassword']) {
				$this->validateStringNonEmpty($params['developerPassword'], 'developerPassword');
			}
			
			$orderNumber = $params['orderNumber'];
			$licenseKey = $params['licenseKey'];
			
			$production = $this->validateBoolean($params['production'], 'production') ? 'production' : 'development';
			$dontWait = $this->validateBoolean($params['dontWait'], 'dontWait');
			
		} catch (WebAPI\Exception $ex) {
			$this->handleException($ex, 'Input validation failed');
		}
		
		$this->auditMessage()->setIdentity(new Identity(_t('Unknown')));
		$audit = $this->auditMessage(Mapper::AUDIT_GUI_BOOTSTRAP_SAVELICENSE);
		$success = true;
		try {
			$bootstrapModel = $this->getLocator('Bootstrap\Mapper'); /* @var $bootstrapModel \Bootstrap\Mapper */
			$bootstrapModel->setAdminPassword($adminPassword);
			$bootstrapModel->setApplicationUrl($params['applicationUrl']);
			$bootstrapModel->setAdminEmail($params['adminEmail']);
			$bootstrapModel->setDeveloperPassword($params['developerPassword']);
			$bootstrapModel->setLicenseKey($licenseKey);
			$bootstrapModel->setLicenseUser($orderNumber);
			$bootstrapModel->setProduction(Module::isCluster() ? 'cluster' : $production);
			 
			$bootstrapResult = $bootstrapModel->bootstrapSingleServer();
			
			// disabled only if dontWait is activated
			if (! $dontWait) {
				try {
					$this->getTasksMapper()->waitForTasksComplete(array(), array()); // we would like ensure that a flag such as bootstrap['zend_gui_completed] will be surely captured
				} catch (\ZendServer\Exception $ex) {
					Log::warn("Bootstrap hasn't finished yet, return soft failure");
					$success = false;
				}
			}  
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("bootstrap failed with the following error: " . $e->getMessage());
			Log::debug($e);
			throw new Exception($e->getMessage(), Exception::INTERNAL_SERVER_ERROR, $e);
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		$this->layout('layout/layout');

		$viewModel = new ViewModel();
		$viewModel->setTemplate('bootstrap/web-api/1x3/bootstrap-single-server');
		$viewModel->setVariable('success', $success);
		$viewModel->setVariable('newKey', $bootstrapResult['key']);
		return $viewModel;
	}
	
	public function setServerProfileAction() {
	    $this->isMethodPost();
	    
	    $params = $this->getParameters(array(
	        'production'   => 'TRUE',
	        'dontWait'     => 'FALSE',
	    ));
	    
	    
	    $production = $this->validateBoolean($params['production'], 'production') ? 'production' : 'development';
	    $dontWait = $this->validateBoolean($params['dontWait'], 'dontWait');
	    $audit = $this->auditMessage(Mapper::AUDIT_GUI_CHANGE_SERVER_PROFILE, ProgressMapper::AUDIT_NO_PROGRESS, array(array(
	        'profile' => $production
	    ))); /* @var $audit \Audit\Container */
	    
	    $success = true;
	    try {
	        $bootstrapModel = $this->getLocator('Bootstrap\Mapper'); /* @var $bootstrapModel \Bootstrap\Mapper */
	        $bootstrapModel->setProduction($production);
	    
	        $setProfileResult = $bootstrapModel->setProfileDirectives();
	        	
	        // disabled only if dontWait is activated
	        if (! $dontWait) {
	            try {
	                $this->getTasksMapper()->waitForTasksComplete(array(), array()); 
	            } catch (\ZendServer\Exception $ex) {
	                Log::warn("Set Server profile hasn't finished yet, return soft failure");
	                $success = false;
	            }
	        }
	    } catch (\Exception $e) {
	        $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
	        Log::err("set profile failed with the following error: " . $e->getMessage());
	        Log::debug($e);
	        throw new Exception($e->getMessage(), Exception::INTERNAL_SERVER_ERROR, $e);
	    }
	    
	    $this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
	    
	    $viewModel = new ViewModel();
	    $viewModel->setTemplate('bootstrap/web-api/1x10/set-server-profile');
	    $viewModel->setVariable('success', ($success && $setProfileResult));
	    $viewModel->setVariable('profile', $production);
	    return $viewModel;
	}
	
	public function getServerProfileAction() {
	    $this->isMethodGet();
	    
	    $directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); /* @var $directivesMapper \Configuration\MapperDirectives */
	    $serverProfile = $directivesMapper->getDirective('zend_gui.serverProfile');
	    $serverProfile = $serverProfile->getFileValue();
	    
	    $viewModel = new ViewModel();
	    $viewModel->setTemplate('bootstrap/web-api/1x10/get-server-profile');
	    $viewModel->setVariable('profile', $serverProfile);
	    return $viewModel;
	}
}
