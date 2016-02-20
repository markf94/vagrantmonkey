<?php

namespace Deployment\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Prerequisites\Validator\Generator,
	Prerequisites\Validate\Collection,
	Prerequisites\Validate\Configuration,
	Application\Module,
	ZendServer\Configuration\Container as ConfigurationContainer;
use Zend\View\Model\ViewModel;

class IndexController extends ActionController
{
    public function indexAction() {
        $output = new ViewModel();
        $output->setTemplate('deployment/index/index');// Restoring original route
        
        $deploymentModel = $this->getLocator()->get('Deployment\Model');
        $defineableApplications = $deploymentModel->getDefineableApplications();        
        $defaultServer = Module::config('deployment', 'defaultServer');        
        
        $appsData = array();
        if (is_array($defineableApplications)) {
	        foreach ($defineableApplications as $defineableApplication) {
	        	$appsData[] = $defineableApplication;
	        }
        }
        $output->setVariable('supportedByWebserver', $deploymentModel->isDeploySupportedByWebserver());
        $output->setVariable('appsData', $appsData);
        $output->setVariable('defaultServerIsSet', $defaultServer != '<default-server>');       
        $output->setVariable('currentHost', $_SERVER['SERVER_NAME']);
    	$output->setVariable('pageTitle', 'Manage Applications');
		$output->setVariable('pageTitleDesc', ''); /* Daniel */

        return $output;	
    }
    
    public function getApplicationPrerequisitesAction() {
    	$params = $this->getParameters(array('app_id' => array()));
    	$this->validateMandatoryParameters($params, array('app_id'));
    	
    	$viewModel = new \Zend\View\Model\ViewModel ();
    	$viewModel->setTerminal ( true );

    	$appId = $params['app_id'];
    	$model = $this->getLocator()->get('Deployment\Model');
    	$app = $model->getApplicationById($appId);
    	
    	$prerequisites = $app->getPackageMetaData()->getPrerequisites();
    	
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
}
