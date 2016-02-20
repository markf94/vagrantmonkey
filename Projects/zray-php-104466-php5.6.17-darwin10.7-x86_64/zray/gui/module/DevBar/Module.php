<?php

namespace DevBar;

use Zend\ModuleManager\Feature\ServiceProviderInterface;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface as AutoloaderProvider,
	Zend\ServiceManager\ServiceManager,
	Zend\Db\TableGateway\TableGateway,
	Application\Db\Connector;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use ZendServer\Log\Log;
use Zend\Mvc\MvcEvent;
use DevBar\Producer\ServerInfo;
use DevBar\ModuleManager\Feature\DevBarProducerProviderInterface;
use DevBar\Producer\RequestInfo;
use DevBar\Producer\RunTime;
use DevBar\Producer\Events;
use DevBar\Producer\FunctionStats;
use DevBar\Producer\Superglobals;
use DevBar\Producer\StudioIntegration;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
use WebAPI\Exception;
use DevBar\Producer\Controls;
use DevBar\Producer\Secure;
use DevBar\Producer\Message;
use DevBar\Producer\LoadingCustom;
use DevBar\Producer\Extension\DefaultTables;
use DevBar\Filter\Dictionary;

/// module has an internal dependency
require_once ('src/DevBar/ModuleManager/Feature/DevBarProducerProviderInterface.php');

class Module implements AutoloaderProvider, ServiceProviderInterface, BootstrapListenerInterface, DevBarProducerProviderInterface, ControllerPluginProviderInterface {
	
	const ACL_ROLE_DEVBAR = 'devbar';
	/**
	 * @var \Zend\Mvc\Application
	 */
	protected $application = null;
	
	public function getServiceConfig() {
		return array (
			'invokables' => array(
				'DevBar\Listener\RegisterProducersListener' => 'DevBar\Listener\RegisterProducersListener',
				'DevBar\Producer\RunTime' => 'DevBar\Producer\RunTime',
				'DevBar\Producer\Events' => 'DevBar\Producer\Events',
				'DevBar\Producer\LogEntries' => 'DevBar\Producer\LogEntries',
				'DevBar\Producer\Queries' => 'DevBar\Producer\Queries',
				'DevBar\Producer\Superglobals' => 'DevBar\Producer\Superglobals',
			    'DevBar\Producer\Notifications' => 'DevBar\Producer\Notifications',
			    'DevBar\Producer\Message' => 'DevBar\Producer\Message',
			),
			'factories' => array(
				'DevBar\Db\TokenMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\TokenMapper();
					$mapper->setTableGateway(new TableGateway('devbar_tokens', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\AccessTokensMapper' => function (ServiceManager $sm) {
				    $mapper = new \DevBar\Db\AccessTokensMapper();
				    $mapper->setTableGateway(new TableGateway('devbar_access_tokens', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
				    return $mapper;
				},
				'DevBar\Db\RequestsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\RequestsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_requests', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\RuntimeMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\RuntimeMapper();
					$mapper->setTableGateway(new TableGateway('devbar_processing_breakdown', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\FunctionsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\FunctionsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_functions_stats', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\BacktraceMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\BacktraceMapper();
					$mapper->setTableGateway(new TableGateway('devbar_backtrace', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\MonitorEventsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\MonitorEventsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_monitor_events', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\SqlQueriesMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\SqlQueriesMapper();
					$mapper->setTableGateway(new TableGateway('devbar_sql_queries', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\LogEntriesMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\LogEntriesMapper();
					$mapper->setTableGateway(new TableGateway('devbar_log_entries', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\ExceptionsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\ExceptionsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_exceptions', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\ExtensionsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\ExtensionsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_user_data', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\RequestsUrlsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\RequestsUrlsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_requests_urls', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\SqlStatementsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\SqlStatementsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_sql_statements', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\ExtensionsMetadataMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\ExtensionsMetadataMapper();
					$mapper->setTableGateway(new TableGateway('devbar_extension_metadata', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Db\SuperglobalsMapper' => function (ServiceManager $sm) {
					$mapper = new \DevBar\Db\SuperglobalsMapper();
					$mapper->setTableGateway(new TableGateway('devbar_superglobals_data', $sm->get(Connector::DB_CONTEXT_DEVBAR)));
					return $mapper;
				},
				'DevBar\Producer\FunctionStats' => function(ServiceManager $sm) {
					$config = $sm->get('Configuration');
					$functionStats = new FunctionStats();
					$functionStats->setDefaultNamespaces($config['zray']['zend_gui']['custom_namespaces']);
					$functionStats->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					return $functionStats;
				},
				'DevBar\Producer\StudioIntegration' => function(ServiceManager $sm) {
					$studioMapper = $sm->get('StudioIntegration\Mapper'); /* @var $studioMapper \StudioIntegration\Mapper */
					$configurationMapper = $sm->get('Configuration\MapperExtensions');
					
					$studioIntegration = new StudioIntegration();
					$studioIntegration->setStudioConfig($studioMapper->getConfiguration());
					$studioIntegration->setDebuggerComponent($configurationMapper->selectExtension('Zend Debugger'));
					
					return $studioIntegration;
				},
				'DevBar\Producer\ServerInfo' => function(ServiceManager $sm) {
					$serverInfo = new ServerInfo();

					$url = $sm->get('Request')->getUriString();
					$baseUrl = substr($url, 0, strpos($url, '/ZendServer/') + strlen('/ZendServer'));
					$serverInfo->setBaseUrl($baseUrl);
					
					return $serverInfo;
				},
				'DevBar\Producer\Secure' => function(ServiceManager $sm) {
					$producer = new Secure();
					$producer->setDirectivesMapper($sm->get('Configuration\MapperDirectives'));
					$producer->setTokenMapper($sm->get('DevBar\Db\AccessTokensMapper'));
					$producer->setRequest($sm->get('Request'));
					
					return $producer;
				},
				'DevBar\Filter\Dictionary' => function($sm) {
					$dictionary = new Dictionary();
					return $dictionary;
				}
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
	
	/**
	 * @param MvcEvent $e
	 */
	public function detectDevBarRequest(MvcEvent $e) {
		Log::debug(__METHOD__);
		$request = $e->getRequest(); /* @var $request \Zend\Http\Request */

		$routematch = $e->getRouteMatch();
		if ($e->getParam('devbar', false) || $routematch->getParam('devbar', false)) {
			$app = $e->getApplication();
			$directiveMapper = $app->getServiceManager()->get('Configuration\MapperDirectives'); /* @var $directiveMapper \Configuration\MapperDirectives */
			if (! $e->getParam('webapi_ui', false) && ! $directiveMapper->getDirectiveValue('zray.enable')) {
				Log::notice('The DevBar is not enabled on this server');
				if ($e->getParam('webapi', false)) {
					$newEvent = $app->getMvcEvent();
					$newEvent->setError(\Zend\Mvc\Application::ERROR_EXCEPTION);
					$newEvent->setParam('exception', new Exception('The DevBar is not enabled on this server', Exception::NOT_IMPLEMENTED_BY_EDITION)); 
					$app->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $newEvent);
				}
			}
		}

		if ($routematch->getParam('skipauth', false)) {
			$config = $e->getApplication()->getServiceManager()->get('Configuration');
			if (! $config['zray']['zend_gui']['enforceAccessControl']) { /// if devbar access control is disabled
				$e->setParam('useSessionControl', false);
			}
		}
	}
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\BootstrapListenerInterface::onBootstrap()
	 */
	public function onBootstrap(\Zend\EventManager\EventInterface $e) {
		$app = $e->getApplication();
		$app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'detectDevBarRequest'), -1000);
		
		$request = $app->getRequest();
		if (strstr($request->getUri()->getPath(), 'ZendServer/Z-Ray')) {
			$e->setParam('devbar', true);
		}
		
		$this->initializeDebugMode($e);
	}
	/* (non-PHPdoc)
	 * @see \DevBar\ModuleManager\Feature\DevBarModuleProviderInterface::getDevBarProducers()
	 */
	public function getDevBarProducers(\Zend\EventManager\EventInterface $e) {
	    $azure = isAzureEnv();
	    $zrayStandalone = isZrayStandaloneEnv();
	    
		$serverInfo = $e->getApplication()->getServiceManager()->get('DevBar\Producer\ServerInfo');
		$runTime = $e->getApplication()->getServiceManager()->get('DevBar\Producer\RunTime');
		$events = $e->getApplication()->getServiceManager()->get('DevBar\Producer\Events');
		$logEntries = $e->getApplication()->getServiceManager()->get('DevBar\Producer\LogEntries');
		$queries = $e->getApplication()->getServiceManager()->get('DevBar\Producer\Queries');
		$functionStats = $e->getApplication()->getServiceManager()->get('DevBar\Producer\FunctionStats');
		$superglobals = $e->getApplication()->getServiceManager()->get('DevBar\Producer\Superglobals');
		$secure = $e->getApplication()->getServiceManager()->get('DevBar\Producer\Secure');
		$notifications = $e->getApplication()->getServiceManager()->get('DevBar\Producer\Notifications');
		
		$producers = array($serverInfo, $secure, new Message(), new RequestInfo(), new Controls(), $runTime);
		
		if (!$azure && !$zrayStandalone) {
		  $producers[] = $events;
		}
		
		$producers = array_merge($producers, array($logEntries, $queries, $functionStats, $superglobals, new LoadingCustom()));
		
		if (!$azure && !$zrayStandalone) {
		    $studioIntegration = $e->getApplication()->getServiceManager()->get('DevBar\Producer\StudioIntegration');
		    $producers[] = $notifications;
		    $producers[] = $studioIntegration;
		}
		
		$producers[] = new DefaultTables();

		return array_reverse($producers);
	}
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\ControllerPluginProviderInterface::getControllerPluginConfig()
	 */
	public function getControllerPluginConfig() {
		return array('invokables' => array(
			'ElevateRole' => 'DevBar\Controller\Plugin\ElevateRole',
			'DemoteRole' => 'DevBar\Controller\Plugin\DemoteRole'
		));
	}

	public function initializeDebugMode(MvcEvent $e) {
		$configuration = $e->getApplication()->getServiceManager()->get('Configuration');

		if (! $configuration['debugMode']['zend_gui']['debugModeEnabled']) {
			if (function_exists('zray_disable')) {
				/// false until engine figure out ZSRV-13333
				// now set to true, and it works, but need to be checked
				\zray_disable(true);    
			}
			
			if (function_exists('zend_urlinsight_disable')) {
				\zend_urlinsight_disable();
			}
		}
	}

}
