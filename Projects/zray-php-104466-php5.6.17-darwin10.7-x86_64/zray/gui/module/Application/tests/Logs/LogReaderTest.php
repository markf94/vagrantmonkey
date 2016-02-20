<?php

namespace Logs;

use Logs\Db\Mapper;
use Zend\Db\TableGateway\TableGateway;
use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;

class LogReaderTest extends TestCase {
	/**
	 * @var LogReader
	 */
	private $mapper;
	
	public function testReadLog() {
		self::markTestIncomplete('LogReader class requires filesystem virtualization');
		$this->updateDataSet(new ArrayDataSet(array(
			'GUI_AVAILABLE_LOGS' => array(array('NAME' => 'logfile', 'ENABLED' => '1', 'FILEPATH' => '/path/to/log'))
		)));
		$this->mapper->getFileObj()->fwrite('log log log');
		self::assertEquals('log log log', $this->mapper->readLog('logfile', 200));
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new LogReader();
		$this->mapper->setFileObj(new \SplTempFileObject(-(2*1024*1000)));
		$this->mapper->setLogsDbMapper(new Mapper(new TableGateway('GUI_AVAILABLE_LOGS', $this->getAdapter())));
	}
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'GUI_AVAILABLE_LOGS' => array()
		));
	}

}

