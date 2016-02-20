<?php

namespace PageCache;

use Zend\ModuleManager\ModuleManager,
    Zend\EventManager\StaticEventManager,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Application\View,
	ZendServer\Log\Log,
	Zend\EventManager\Event as ManagerEvent,
	Zend\Mvc\MvcEvent as MvcEvent,
	Zend\Http\Header\Accept;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use PageCache\Model\Mapper;
use PageCache\Model\Tasks;
use Zend\Db\TableGateway\TableGateway;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;

class Module implements AutoloaderProvider, ServiceProviderInterface
{
	
	/**
	 * @var \Zend\Mvc\Application
	 */
	protected $application = null;
	
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
		return array(
				'factories' => array(
                        'PageCache\Model\Mapper' => function ($sm) {
                            $mapper = new Mapper();
                            $mapper->setTableGateway(new TableGateway('ZSD_PAGECACHE_RULES', $sm->get(Connector::DB_CONTEXT_ZSD)));
                            return $mapper;
                        },
						'PageCache\Model\Tasks' => function ($sm) {
							$mapper = new Tasks();
							$mapper->setTasksMapper($sm->get('Zsd\Db\TasksMapper'));
							return $mapper;
						}
				)
		);
	}
	
	public function onBootstrap(MvcEvent $e) {
		$this->application = $e->getApplication();
		if ($e->getParam('dbConnected', true)) {
			$this->application->getServiceManager()->get('Deployment\Model')->getEventManager()->attach('preRemove', array($this, 'onDeploymentPostRemove'));
		}
	}
	
	public function onDeploymentPostRemove(ManagerEvent $e) {
			
		/* @var $app \Deployment\Application\Container */
		$app = $e->getTarget();
		$params = $e->getParams();
	
		Log::debug("PageCache: onDeploymentPostRemove with app id " . $app->getApplicationId() . " and params " . var_export($params, true));
	
		
		/* @var $rulesMapper \PageCache\Model\Mapper */
		$rulesMapper = $this->application->getServiceManager()->get('PageCache\Model\Mapper');

		Log::debug("Page Cache: Removing rules of application " . $app->getApplicationId());

		$rules = $rulesMapper->getRules(array(), array($app->getApplicationId()));
		$tasksMapper = $this->application->getServiceManager()->get('PageCache\Model\Tasks'); /* @var $tasksMapper \PageCache\Model\Tasks */
		$tasks = $this->application->getServiceManager()->get('PageCache\Model\Tasks');
		foreach ($rules as $rule ) { /* @var $rule \PageCache\Model\Rule */
			if ($rule->getAppId() == $app->getApplicationId()) {				
				Log::debug("Page Cache: clearing cache items for rule " . $rule->getName() );
				$tasks->clearCache(array('ruleName' => $rule->getName()));
			}
		}			
		
		$serversMapper = $this->application->getServiceManager()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$deleted = $rulesMapper->deleteRulesByApplicationId($app->getApplicationId());
		if ($deleted) {	
			$tasksMapper->syncPageCacheRulesChanges($serversMapper->findRespondingServersIds());
		}
	}
}
