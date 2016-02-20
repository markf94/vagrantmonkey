<?php

namespace Application\Db;

use Zend\EventManager\EventsCapableInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Application\ConfigAwareInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZendServer\Log\Log;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

class AbstractFactoryConnector extends Connector implements AbstractFactoryInterface, ConfigAwareInterface, ServiceManagerAwareInterface {
	/**
	 * @var EventManager
	 */
	private $eventsManager;

	/**
	 * @var ServierManager
	 */
	private $serviceManager;
	

	public function __construct() {
		$this->getEventManager()->attach('missingMetadata', array($this, 'recreateGuiDb'));
	}
	
	/**
	 * @param ServiceManager $serviceManager
	 * @return AbstractFactoryConnector
	 */
	public function setServiceManager(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\ServiceManager\AbstractFactoryInterface::canCreateServiceWithName()
	*/
	public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
		if (isset($this->dsns[strtolower($name)])) {
			return true;
		}
		return false;
	}

	/* (non-PHPdoc)
	 * @see \Zend\ServiceManager\AbstractFactoryInterface::createServiceWithName()
	*/
	public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
		return $this->createDbAdapter($name);
	}

	/**
	 * @param Event $event
	 */
	public function recreateGuiDb(Event $event) {
		$adapter = $event->getParam('adapter', null);
		Log::warn('gui db looks empty, attempt to rebuild');
		$creator = new SqliteDbCreator($adapter);
		$creator->createGuiDb();
		Log::info('gui db rebuild completed');
		$bootstrap = $this->serviceManager->get('Bootstrap\Mapper\Reset');
		$bootstrap->resetBootstrap();
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::getAwareNamespace()
	*/
	public function getAwareNamespace() {
		return array('zend', 'database');
	}
	
	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::setConfig()
	*/
	public function setConfig($config) {
		$this->dbConfig = $config->database;
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\EventManager\EventsCapableInterface::getEventManager()
	 */
	public function getEventManager() {
		if (is_null($this->eventsManager)) {
			$this->eventsManager = new EventManager();
		}
		return $this->eventsManager;
	}
}

