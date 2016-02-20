<?php

namespace Plugins;

use Zend\ModuleManager\ModuleManager,
	Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Application\View,
	ZendServer\Log\Log;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\EventManager\EventInterface;
use Application\Db\Connector;
use Plugins\Db\Mapper as PluginsDbMapper;
use Plugins\Mapper;
use Plugins\Model;
use Plugins\Mapper\Deploy;
use Plugins\Db\UpdatesMapper;
use Plugins\Controller\SetUpdateCookie;

class Module implements AutoloaderProvider, ServiceProviderInterface
{
	
	public function onBootstrap(EventInterface $e) {
		$serviceManager = $e->getApplication()->getServiceManager();
		
		if (!isZrayStandaloneEnv()) {
			$serviceManager->addInitializer(function ($instance) use ($serviceManager) {
				if ($instance instanceof IdentityApplicationsAwareInterface) {
					$instance->setIdentityFilter($serviceManager->get('Deployment\IdentityFilter'));
				}
			});
		}
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

	public function getServiceConfig() {
	    $module = $this;
		return array(
		    'factories' => array(
		        'Plugins\Db\Mapper' => function($sm) use($module) {
		            $mapper = new PluginsDbMapper(new TableGateway('deployment_plugins', $sm->get(Connector::DB_CONTEXT_ZDD)));
                    return $mapper;
                },
                'Plugins\Db\UpdatesMapper' => function($sm) use($module) {
                    $mapper = new UpdatesMapper(new TableGateway('GUI_PLUGIN_UPDATES', $sm->get(Connector::DB_CONTEXT_GUI)));
                    return $mapper;
                },
                'Plugins\Mapper' => function ($sm) use ($module) {
                    $mapper = new Mapper();
                    return $mapper;
                },
                'Plugins\Model' => function ($sm) use ($module) {
                    $model = new Model();
                    $model->setManager(new \ZendDeployment_Manager());
					$model->setDeploymentDbAdapter($sm->get(Connector::DB_CONTEXT_ZDD));
                    return $model;
                },
                'Plugins\Mapper\Deploy' => function ($sm) use ($module) {
                    $mapper = new Deploy();
                    $mapper->setManager(new \ZendDeployment_Manager());
                    $mapper->setDeploymentMapper($sm->get('Plugins\Model'));
                    $mapper->setMapper($sm->get('Plugins\Mapper'));
                    return $mapper;
                }
		)
       );
	}
	
	public function getControllerPluginConfig() {
	    $module = $this;
	    return array(
	        'factories' => array(
	            'SetPluginsUpdateCookie' => function($sm) { /* @var $sm \Zend\Mvc\Controller\PluginManager */
	                $plugin = new SetUpdateCookie();
	                $plugin->setAcl($sm->getServiceLocator()->get('ZendServerAcl'));
	                $plugin->setPluginsModel($sm->getServiceLocator()->get('Plugins\Model'));
	                return $plugin;
	            },
	        ));
	}
	
}
