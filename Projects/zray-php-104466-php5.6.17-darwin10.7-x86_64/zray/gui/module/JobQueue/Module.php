<?php

namespace JobQueue;

use JobQueue\Db\Mapper;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider;
use ZendServer\Log\Log;
use Zend\Mvc\MvcEvent as MvcEvent;
use Zend\EventManager\Event as ManagerEvent;
use JobQueue\Filter\Dictionary; 
use ZendServer\Exception;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;

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
				'JobQueue\Model\Mapper' => function($sm) {
					$modelMapper = new \JobQueue\Model\Mapper();
					$modelMapper->setQueuesMapper($sm->get('JobQueue\Queues\Mapper'));
					
					return $modelMapper;
				},
				'JobQueue\Db\Mapper' => function($sm) {
					$mapper = new Mapper(new TableGateway('jobqueue_job', $sm->get(Connector::DB_CONTEXT_JOBQUEUE)));
					
					$serversMapper = $sm->get('Servers\Db\Mapper');
					$mapper->setServersMapper($serversMapper);
					
					return $mapper;
				},
				'JobQueue\Queues\Mapper' => function($sm) {
					$mapper = new Mapper(new TableGateway('jobqueue_queue', $sm->get(Connector::DB_CONTEXT_JOBQUEUE)));
					return $mapper;
				},
				'JobQueue\QueuesStats\Mapper' => function($sm) {
					$mapper = new Mapper(new TableGateway('jobqueue_queue_stats', $sm->get(Connector::DB_CONTEXT_JOBQUEUE)));
					return $mapper;
				},
				'JobQueue\Filter\Dictionary' => function($sm) {
					$dictionary = new Dictionary();
					return $dictionary;
				}
			)
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
	
	public function onBootstrap(MvcEvent $e) {
		if (class_exists("ZendJobQueue")) {
			$this->application = $e->getApplication();
			if ($e->getParam('dbConnected', true)) {
				$this->application->getServiceManager()->get('Deployment\Model')->getEventManager()->attach('preRemove', array($this, 'onDeploymentPostRemove'));
			}
		}
	}
	
	public function onDeploymentPostRemove(ManagerEvent $e) {
			
		/* @var $app \Deployment\Application\Container */
		$app = $e->getTarget();
		$params = $e->getParams();
	
		Log::debug("JobQueue: onDeploymentPostRemove with app id " . $app->getApplicationId() . " and params " . var_export($params, true));
	
		/* @var $rulesMapper \JobQueue\Db\Mapper */
		$rulesMapper = $this->application->getServiceManager()->get('JobQueue\Db\Mapper');

		Log::debug("Job Queue: Removing rules of application " . $app->getApplicationId());
		$rulesMapper->deleteRulesByAppId($app->getApplicationId());				
		
	}
}
