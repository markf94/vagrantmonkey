<?php

namespace Statistics;

use Application\Module as appModule;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\ModuleManager,
    Zend\EventManager\StaticEventManager,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Application\View,
	Zend\Http\Header\Accept;

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
	
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
	*/
	public function getServiceConfig() {
		return array(
            'aliases' => array(
                'statsModel' => 'Statistics\Model',
            ),
			'factories' => array(
                'Statistics\Model' => function($sm) {
                    $model = new Model();
                    $model->setAdapter($sm->get('statsDbAdapter'));
                    return $model;
                }
			)
		);
	}
}
