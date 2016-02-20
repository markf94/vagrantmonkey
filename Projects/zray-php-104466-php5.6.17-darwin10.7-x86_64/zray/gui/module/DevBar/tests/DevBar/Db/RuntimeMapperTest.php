<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class RuntimeMapperTest extends TestCase
{
	/**
	 * @var RuntimeMapper
	 */
	private $mapper;

	public function testGetRuntime() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_processing_breakdown' => array(
				array('request_id' => '1', 'database_time' => '1', 'network_time' => '2'),
				array('request_id' => '2', 'database_time' => '2', 'network_time' => '2'),
			),
		)));
		
		$runtime = $this->mapper->getRuntime('1');
		self::assertInstanceOf('DevBar\RuntimeContainer', $runtime);
		$runtime = $this->mapper->getRuntime('2');
		self::assertInstanceOf('DevBar\RuntimeContainer', $runtime);
		$runtime = $this->mapper->getRuntime('3');
		self::assertInstanceOf('DevBar\RuntimeContainer', $runtime);
		self::assertEquals(0, $runtime->getPhpTime());
	}

	public function testGetRequestsRuntime() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_processing_breakdown' => array(
				array('request_id' => '1', 'database_time' => '1', 'network_time' => '2'),
				array('request_id' => '2', 'database_time' => '2', 'network_time' => '2'),
			),
		)));
		
		$runtime = $this->mapper->getRequestsRuntime(array('1'));
		self::assertEquals(1, $runtime->count());
		$runtime = $this->mapper->getRequestsRuntime(array('1', '2'));
		self::assertEquals(2, $runtime->count());
		$runtime = $this->mapper->getRequestsRuntime(array('3'));
		self::assertEquals(0, $runtime->count());
	}
	
	public function testGetRequestsRuntimeEmpty() {
		$runtime = $this->mapper->getRequestsRuntime(array());
		self::assertEquals(0, $runtime->count());
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new RuntimeMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_processing_breakdown', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'devbar_processing_breakdown' => array(),
		));
	}
}