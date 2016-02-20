<?php
namespace ZendDeployment;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;

class Module implements AutoloaderProvider {

	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\ClassMapAutoloader' => array(
						__DIR__ . '/autoload_classmap.php',
				),
		);
	}
}

