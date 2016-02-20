<?php
namespace DevBar;

use ZendServer\PHPUnit\TestCase;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;
use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\EventManager;
use DevBar\Listener\RegisterProducersListener;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\EventManager\SharedEventManager;
use Zend\Stdlib\CallbackHandler;
use DevBar\Producer\StudioIntegration;
use DevBar\Producer\ServerInfo;
use DevBar\Producer\Bootstrap;
use DevBar\Producer\FunctionStats;
use DevBar\Producer\Superglobals;
use DevBar\Producer\Secure;
use DevBar\Producer\RunTime;
use DevBar\Producer\Events;
use DevBar\Producer\LogEntries;
use DevBar\Producer\Queries;
use DevBar\Producer\Extension\DefaultTables;

require_once 'tests/bootstrap.php';

class RegisterProducersListenerTest extends TestCase
{
	public function testInjectDevBarProducers() {
		
		$event = $this->generateMvcEvent();
		$event->setParam('devbar', true);
		$eventManager = $event->getApplication()->getEventManager();
		
		$listener = new RegisterProducersListener();
		$listener->injectDevBarProducers($event);
		self::assertGreaterThan(0, $eventManager->getSharedManager()->getListeners('devbar', 'DevBarModules')->count());
		$producers = $eventManager->getSharedManager()->getListeners('devbar', 'DevBarModules')->toArray();
		
		$shared = $eventManager->getSharedManager();
		
		$producers = array_map(function(CallbackHandler $callable) use ($eventManager) {
			return $callable->getCallback();
		}, $producers);

		
		foreach($producers as $producer) {
			self::assertInstanceOf('DevBar\Listener\AbstractDevBarProducer', $producer);
		}
	}
	
	public function testInjectDevBarProducersDevBarFalse() {
		
		$event = $this->generateMvcEvent();
		$event->setParam('devbar', false);
		$eventManager = $event->getApplication()->getEventManager();
		
		$listener = new RegisterProducersListener();
		$listener->injectDevBarProducers($event);
		self::assertFalse($eventManager->getSharedManager()->getListeners('devbar', 'DevBarModules'));
		
	}
	
	public function testInjectDevBarProducersNotAProducerModule() {
	
		$event = $this->generateMvcEvent(array('Application' => new \Application\Module()));
		$event->setParam('devbar', true);
		$eventManager = $event->getApplication()->getEventManager();
	
		$listener = new RegisterProducersListener();
		$listener->injectDevBarProducers($event);
		
		self::assertFalse($eventManager->getSharedManager()->getListeners('devbar', 'DevBarModules'));
	}
	
	public function testInjectDevBarProducersReturnedNotTraversable() {
	
		$event = $this->generateMvcEvent();
		$event->setParam('devbar', true);
		$eventManager = $event->getApplication()->getEventManager();
	
		$listener = new RegisterProducersListener();
		$listener->injectDevBarProducers($event);
		self::assertGreaterThan(0, $eventManager->getSharedManager()->getListeners('devbar', 'DevBarModules')->count());
		$producers = $eventManager->getSharedManager()->getListeners('devbar', 'DevBarModules')->toArray();
	
		$shared = $eventManager->getSharedManager();
	
		$producers = array_map(function(CallbackHandler $callable) use ($eventManager) {
			return $callable->getCallback();
		}, $producers);
	
	
			foreach($producers as $producer) {
				self::assertInstanceOf('DevBar\Listener\AbstractDevBarProducer', $producer);
			}
	}
	
	/**
	 * @param array $modules
	 * @return \Zend\Mvc\MvcEvent
	 */
	private function generateMvcEvent(array $modules = null) {
		if (is_null($modules)) {
			$modules = array('DevBar' => new Module());
		}
		
		$event = new MvcEvent();
		$serviceManager = new ServiceManager();
		$eventManager = new EventManager();
		$eventManager->setSharedManager(new SharedEventManager());
		$moduleManager = new ModuleManager($modules, $eventManager);
		
		$serviceManager->setAllowOverride(true);
		
		$serviceManager->setService('ModuleManager', $moduleManager);
		$serviceManager->setService('EventManager', $eventManager);
		$serviceManager->setService('Request', new Request());
		$serviceManager->setService('Response', new Response());
		$serviceManager->setService('DevBar\Producer\StudioIntegration', new StudioIntegration());
		$serviceManager->setService('DevBar\Producer\ServerInfo', new ServerInfo());
		$serviceManager->setService('DevBar\Producer\FunctionStats', new FunctionStats());
		$serviceManager->setService('DevBar\Producer\Superglobals', new Superglobals());
		$serviceManager->setService('DevBar\Producer\Secure', new Secure());
		$serviceManager->setService('DevBar\Producer\RunTime', new RunTime());
		$serviceManager->setService('DevBar\Producer\Events', new Events());
		$serviceManager->setService('DevBar\Producer\LogEntries', new LogEntries());
		$serviceManager->setService('DevBar\Producer\Queries', new Queries());
		$serviceManager->setService('DevBar\Producer\Extension\DefaultTables', new DefaultTables());
		
		$serviceManager->setService('Configuration', array('bootstrap' => array('zend_gui' => array('completed' => '1'))));
		
		$application = new Application(array(), $serviceManager);
		$event->setApplication($application);
		
		$moduleManager->loadModules();
		return $event;
	}
}