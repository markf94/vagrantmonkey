<?php

namespace Cache\Controller;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZendServer\FS\FS;
use Application\Module;
use Zend\Config\Config;
use Zend\Config\Reader\Ini;
use Zend\Stdlib\Parameters;
use Zend\Log\Logger;
use Zend\Log\Writer\Mock;
use ZendServer\Log\Log;

require_once 'tests/bootstrap.php';

class WebAPIControllerTest extends AbstractHttpControllerTestCase {
	public function setUp() {
		self::markTestIncomplete('Virtualization of fs is incomplete');
		$this->setApplicationConfig(include FS::createPath(ZEND_SERVER_GUI_PATH, 'config','application.config.php'));
		
		$global = include FS::createPath(ZEND_SERVER_GUI_PATH, 'config','autoload','global.config.php');
		$global['debugMode']['zend_gui']['debugModeEnabled'] = true;
		$config = new Config($global);
		$ini = new Ini();
		$config->merge(new Config($ini->fromFile(FS::createPath(ZEND_SERVER_REAL_ROOT, 'etc','zend_database.ini'))));
		
		Module::setConfig($config);
		
		\ZendDeployment_Logger::initLogNull();
		$logger = new Logger();
		$logger->addWriter(new Mock());
		Log::init($logger, 'DEBUG');
		parent::setUp();
	}
	
	public function testDatacacheClearAction() {
		self::markTestIncomplete('Controller testing breaks due to concrete resources being used and broken');
		$this->getRequest()->setMethod('POST');
		$this->getRequest()->setServer(new Parameters(array('REQUEST_URI' => '/ZendServer')));
		$this->dispatch('/ZendServer/Api/datacacheClear');
	}
}

