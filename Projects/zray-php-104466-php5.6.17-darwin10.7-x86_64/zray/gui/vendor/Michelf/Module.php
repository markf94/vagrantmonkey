<?php
namespace Michelf;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;
use Zend\Loader\StandardAutoloader;

class Module implements AutoloaderProvider {

	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						StandardAutoloader::LOAD_NS => array(
    						__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				)),
		);
	}
}

