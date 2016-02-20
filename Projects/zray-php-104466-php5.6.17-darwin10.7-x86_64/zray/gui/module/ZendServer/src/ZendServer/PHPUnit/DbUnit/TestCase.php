<?php

namespace ZendServer\PHPUnit\DbUnit;

use Zend\Config\Config;

use Application\Module;

use ZendServer\Log\Log;

use Zend\Log\Writer\Mock;

use Zend\Log\Logger;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Platform\Sqlite;
use ZendServer\FS\FS;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase {

	/**
	 * @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 */
	private $db;
	/**
	 * @var \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
	 */
	protected $dataset;
	
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
	

	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getConnection()
	*/
	public function getConnection()
	{
		if (is_null($this->db)) {
			$pdo = new \PDO('sqlite::memory:');
			$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$filenames = array(
					'zsd_sqlite_create_schema.sql',
					'deployment_sqlite_create_database.sql',
					'gui_sqlite_create_database.sql',
					'gui_sqlite_acl_fixtures.sql',
					'jobqueue_sqlite_create_database.sql',
					'statistics_sqlite_create_database.sql',
					'stats_sqlite_fixtures.sql',
					'create_monitor_db.sql',
					'create_monitor_rules_db.sql',
					'create_monitor_rules_fixtures.sql',
					'create_monitor_tracing_db.sql',
					'create_pagecache_rules_db.sql',
					'sqlite_create_message_center_db.sql',
					'devbar_sqlite_create_database.sql',
					'devbar_fixtures.sql',
			);
			foreach ($filenames as $filename) {
				$sqlfile = FS::createPath("share",$filename);
				$sql = file_get_contents(FS::createPath($this->getZendInstallDir(), $sqlfile));
				$pdo->exec($sql);
			}
			$this->db = $this->createDefaultDBConnection($pdo, ':memory:');
		}
		return $this->db;
	}
	
	public function getAdapter() {
		if (is_null($this->db)) {
			throw new \Exception('db property not initialized, used parent::setup() ?');
		}
		$pdo = $this->db->getConnection();
		$driver = new Pdo($pdo);
		return new Adapter($driver, new Sqlite($driver));
	}
	
	/**
	 * Is there a better way to do this natively?
	 * 
	 * @param \PHPUnit_Extensions_Database_DataSet_AbstractDataSet $dataset
	 */
	public function updateDataSet(\PHPUnit_Extensions_Database_DataSet_AbstractDataSet $dataset) {
		$this->getDatabaseTester()->setDataSet($dataset);
		$this->getDatabaseTester()->onSetUp();
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

