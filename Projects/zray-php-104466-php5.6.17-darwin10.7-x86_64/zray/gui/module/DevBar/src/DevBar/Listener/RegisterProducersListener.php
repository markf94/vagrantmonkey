<?php

namespace DevBar\Listener;

use DevBar\ModuleManager\Feature\DevBarProducerProviderInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use ZendServer\Log\Log;

class RegisterProducersListener implements ListenerAggregateInterface  {
	/**
	 * @var array
	 */
	private $listeners = array();

	/**
	 * Collect devbar producer objects from all modules, attach them to devbar production event
	 * @param MvcEvent $e
	 */
	public function injectDevBarProducers(MvcEvent $e) {
		if (! $e->getParam('devbar',false)) {
			return;
		}
		
		$moduleManager = $e->getApplication()->getServiceManager()->get('ModuleManager');

		foreach($moduleManager->getModules() as $moduleName => $module) {
			if (! is_object($module)) {
				$moduleName = $module;
				$module = $moduleManager->getModule($moduleName);
			}
			
			if (!$module instanceof DevBarProducerProviderInterface
			&& !method_exists($module, 'getDevBarProducers')
			) {
				continue;
			}
			
			$events = $moduleManager->getEventManager();
			
			$producers = $module->getDevBarProducers($e);
			if (is_array($producers) || $producers instanceof \Traversable) {
				foreach ($producers as $producer) {
					$events->attach($producer);
				}
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attach(\Zend\EventManager\EventManagerInterface $events) {
		$this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'injectDevBarProducers'), -10);
	}

	/* (non-PHPdoc)
	 * @see \Zend\EventManager\ListenerAggregateInterface::detach()
	 */
	public function detach(\Zend\EventManager\EventManagerInterface $events) {
		foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
	}

}

