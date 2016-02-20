<?php

namespace DeploymentLibrary\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Application\Module,
	ZendServer\Exception,
	ZendServer\Log\Log,
	Notifications\NotificationContainer,
	Zend\View\Model\ViewModel;

class IndexController extends ActionController {
    public function indexAction() {
    	$mapper = $this->getLocator()->get('DeploymentLibrary\Db\Mapper'); /* @var $mapper \DeploymentLibrary\Db\Mapper */
    	$updates = $mapper->getUpdates();
    	$libraryUpdates = array();
    	foreach ($updates as $update) {
    		$libraryUpdates[$update['NAME']] = $update; 
    	}
    	
    	return array('pageTitle' => 'Libraries',
					 'pageTitleDesc' => '',  /* Daniel */
					 'libraryUpdates' => $libraryUpdates);
    }
    
    public function newUpdateAction() {
    	$params = $this->getParameters(array('name' => '', 'version' => '', 'extraData' => array()));
    	
    	$name = trim($params['name']);
    	$version = trim($params['version']);
    	$extraData = json_encode($params['extraData']);
    	
    	if (! empty($name) && ! empty($version)) {
    		// insert notification about new update
    		$mapper = $this->getNotificationsMapper();
    		$mapper->insertNotification(NotificationContainer::TYPE_LIBRARY_UPDATE_AVAILABLE, array($name, $version));
    		
    		$mapper = $this->getLocator()->get('DeploymentLibrary\Db\Mapper'); /* @var $mapper \DeploymentLibrary\Db\Mapper */
    		
    		// delete old update and put the new one - creates fresh timestamp
    		$mapper->deleteUpdate($name);
    		$mapper->addUpdate($name, $version, $extraData);
    	}
    	
    	
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	$viewModel->setTemplate('deployment-library/index/new-update');
    	
    	return $viewModel;
	}
	
	public function NoUpdateAction() {
		$params = $this->getParameters(array('name' => '', 'version' => '', 'extraData' => array()));
		 
		$name = trim($params['name']);
		$version = trim($params['version']);
		$extraData = json_encode($params['extraData']);
		 
		if (! empty($name) && ! empty($version)) {
			$mapper = $this->getLocator()->get('DeploymentLibrary\Db\Mapper'); /* @var $mapper \DeploymentLibrary\Db\Mapper */
			$update = $mapper->getUpdate($name)->current();
	    	if ($update != false) {
	    		$oldVersion = $update['VERSION'];
	    		if (version_compare($version, $oldVersion) > 0) {
	    			$mapper->deleteUpdate($name);
	    			
	    			// check if there are any updates left and if not, remove notification message
	    			$updates = $mapper->getUpdates();
	    			if (count($updates->toArray()) == 0) {
	    				$notificationsMapper = $this->getNotificationsMapper();
	    				$notificationsMapper->deleteByType(NotificationContainer::TYPE_LIBRARY_UPDATE_AVAILABLE);
	    			}
	    		}
	    	}
		}
		
		$viewModel = new ViewModel();
		$viewModel->setTerminal(true);
		$viewModel->setTemplate('deployment-library/index/new-update');
		
		return $viewModel;
	}
    
    public function getLibraryPrerequisitesAction() {
    	$params = $this->getParameters(array('library_id' => array()));
    	$this->validateMandatoryParameters($params, array('library_id'));
    	 
    	$viewModel = new \Zend\View\Model\ViewModel ();
    	$viewModel->setTerminal ( true );
    
    	$libraryId = $params['library_id'];
    	$mapper = $this->getLocator()->get('DeploymentLibrary\Mapper'); /* @var $mapper \DeploymentLibrary\Mapper */
    	
    	$prerequisites = $mapper->getLibraryVersionPrerequisites($libraryId);
    	
    	// check if the prerequisites empty
    	try {
    		if (! \Prerequisites\Validator\Generator::hasPrerequisites($prerequisites)) {
    			$viewModel->setVariable('isEmpty', true);
    			return $viewModel;
    		}
    	} catch (Exception $e) {
    		Log::logException('Invalid XML provided', $e);
    		$viewModel->setVariable('isEmpty', true);
    		return $viewModel;
    	}
    	
    	$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
    	 
    	$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
    	$configurationContainer->createConfigurationSnapshot(
    			$configuration->getGenerator()->getDirectives(),
    			$configuration->getGenerator()->getExtensions(),
    			$configuration->getGenerator()->getLibraries(),
    			$configuration->getGenerator()->needServerData());
    	$isValid = false;
    	if ($configuration->isValid($configurationContainer)) {
    		$isValid = true;
    	}
    	$viewModel->isValid = $isValid;
    	$viewModel->messages =  $configuration->getMessages();
    	 
    	return $viewModel;
    }
    
    public function libraryIconAction() {
    	$params = $this->getRequest()->getQuery(); /* @var $request \Zend\Http\PhpEnvironment\Request */
    	$id = isset($params['id']) ? $params['id'] : '';
    	if (empty($id)) {
    		header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
    		exit;
    	}
    
    	try {
    		$mapper = $this->getLocator()->get('DeploymentLibrary\Mapper'); /* @var $mapper \DeploymentLibrary\Mapper */
    		$metaData = $mapper->getLibraryVersionPackageMetaData($id);
    		
    		$image = $metaData->getLogo();
    	} catch (Exception $e) {
    		header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
    		exit;
    	}
    
    	if (empty($image)) {
    		header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
    		exit;
    	}
    
    	header('content-type: image/gif');
    	echo $image;
    	exit;
    }
}
