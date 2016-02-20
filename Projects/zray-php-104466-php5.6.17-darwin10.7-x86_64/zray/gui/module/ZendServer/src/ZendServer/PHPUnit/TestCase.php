<?php

namespace ZendServer\PHPUnit;

use Zend\Config\Config;

use Application\Module;

use ZendServer\Log\Log;

use Zend\Log\Writer\Mock;

use Zend\Log\Logger;

class TestCase extends \PHPUnit_Framework_TestCase {
	
	public static function assertArrayValues(array $values, array $array) {
		foreach ($values as $value) {
			self::assertTrue(in_array($value, $array, true), "{$value} expected to in the array ". print_r($array, true));
		}
	}
	
	public static function assertArrayHasKeys(array $keys, array $array) {
		foreach ($keys as $key) {
			self::assertArrayHasKey(strtolower($key), array_change_key_case($array));
		}
	}

	protected function getZendInstallDir() {
		$path=getCfgVar('zend.install_dir');
		if (strlen($path) > 0 && ! file_exists($path)) {
			self::fail("Could not find zend server installation to retrieve SQL files ({$path})");
		} elseif (strpos(strtolower(PHP_OS), 'linux') !== false) {
			return '/usr/local/zend';
		} elseif (strpos(strtolower(PHP_OS), 'win') !== false) {
			return 'C:/Program Files (x86)/Zend/ZendServer';
		}
		
		return $path;
	}
	
	protected function setUp()
	{
		parent::setUp();
		$logger = new Logger();
		$logger->addWriter(new Mock());
		Log::init($logger, 'DEBUG');
		Module::setConfig(new Config(array('deployment' => array('zend_gui'=>array('defaultServer' => '')))));
	}
	
	protected function tearDown()
	{
		Module::setConfig(null);
		Log::clean();
		parent::tearDown();
	}
}

