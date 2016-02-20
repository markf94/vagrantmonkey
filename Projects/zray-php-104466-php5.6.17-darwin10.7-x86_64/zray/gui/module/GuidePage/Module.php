<?php

namespace GuidePage;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;

class Module implements AutoloaderProvider, ServiceProviderInterface
{
	
	/**
	 * @var \Zend\Mvc\Application
	 */
	protected $application = null;
	
	public function getServiceConfig() {
		return array (
			'invokables' => array(
			),
			'factories' => array(
			),
		);
	}
	
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
}
