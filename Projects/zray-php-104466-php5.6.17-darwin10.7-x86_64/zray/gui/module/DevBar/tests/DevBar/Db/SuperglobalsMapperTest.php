<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class SuperglobalsMapperTest extends TestCase
{
	/**
	 * @var SuperglobalsMapper
	 */
	private $mapper;

	public function testGetEntriesSessionOnly() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_superglobals_data' => array(
				array('request_id' => '1', 'sg_name' => '_SESSION', 'data' => serialize(array('user_id' => 1)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_SESSION', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_END'),
				array('request_id' => '2', 'sg_name' => '_SESSION', 'data' => serialize(array('user_id' => 3)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '3', 'sg_name' => '_SESSION', 'data' => serialize(array('user_id' => 2)), 'sample_type' => 'SAMPLE_START'),
			),
		)));
		
		$superglobals = $this->mapper->getSuperglobals('1');
		self::assertEquals('1', count($superglobals));
		$superglobals = $this->mapper->getSuperglobals('2');
		self::assertEquals('1', count($superglobals));
	}

	public function testGetEntriesKeysToSuperglobalType() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_superglobals_data' => array(
				array('request_id' => '1', 'sg_name' => '_SESSION', 'data' => serialize(array('user_id' => 1)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_SESSION', 'data' => serialize(array('user_id' => 1)), 'sample_type' => 'SAMPLE_END'),
				array('request_id' => '1', 'sg_name' => '_GET', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_POST', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_SERVER', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_REQUEST', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_ENV', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_START'),
				array('request_id' => '1', 'sg_name' => '_COOKIE', 'data' => serialize(array('user_id' => 4)), 'sample_type' => 'SAMPLE_START'),
			),
		)));
		
		$superglobals = $this->mapper->getSuperglobals('1');
		self::assertEquals('7', count($superglobals));
		self::assertEquals(array('_SESSION', '_GET', '_POST', '_SERVER', '_REQUEST', '_ENV', '_COOKIE'), array_keys($superglobals));
		self::assertEquals(2, count($superglobals['_SESSION']));
		self::assertInstanceOf('DevBar\SuperGlobalContainer', $superglobals['_SESSION'][0]);
		self::assertEquals('SAMPLE_START', $superglobals['_SESSION'][0]->getSampleType());
		self::assertInstanceOf('DevBar\SuperGlobalContainer', $superglobals['_SESSION'][1]);
		self::assertEquals('SAMPLE_END', $superglobals['_SESSION'][1]->getSampleType());
		self::assertInstanceOf('DevBar\SuperGlobalContainer', $superglobals['_SERVER'][0]);
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new SuperglobalsMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_superglobals_data', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'devbar_superglobals_data' => array(),
		));
	}
}