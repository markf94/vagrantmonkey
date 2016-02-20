<?php

namespace StudioIntegration;

use Application\Module as appModule;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;
use Zend\ModuleManager\Feature\ServiceProviderInterface;


class Module implements AutoloaderProvider, ServiceProviderInterface
{
	
	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						),
				),
		);
	}
	
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}	
	
	/*
	 * (non-PHPdoc)
	* @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
	*/
	public function getServiceConfig() {
		$module = $this;
		return array(
				'factories' => array(
						'StudioIntegration\Mapper' => function($sm) {
							$mapper = new Mapper();
							$studioConfigArr = appModule::config('studioIntegration')->toArray();
							$mapper->setModuleConfiguration($studioConfigArr);
							return $mapper;
						},
						'StudioIntegrationModel' => function($sm) {
							$model = new \StudioIntegration\Model();
							$model->setDebuggerWrapper(new \StudioIntegration\Debugger\Wrapper());
							$model->setExtensionsMapper($sm->get('Configuration\MapperExtensions'));
							$model->setAlternateDebugServer(appModule::config('studioIntegration', 'alternateDebugServer'));
							$model->setStudioMapper($sm->get('StudioIntegration\Mapper'));
							return $model;
						},
						'StudioIntegration\exportIssue' => function($sm) {
							$exportIssue = new exportIssue($sm->get('MonitorUi\Model\Model'));
							$exportIssue->setcodetracingModel($sm->get('Codetracing\Model'));
							return $exportIssue;
						}		
				),
		);
	}	
	
	
}
