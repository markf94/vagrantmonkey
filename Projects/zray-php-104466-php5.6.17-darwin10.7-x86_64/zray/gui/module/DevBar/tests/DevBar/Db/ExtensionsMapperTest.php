<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class ExtensionsMapperTest extends TestCase
{
	/**
	 * @var ExtensionsMapper
	 */
	private $mapper;

	public function testFindDataTypesMapEmpty() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_user_data' => array(
			),
		)));
		
		$dataTypesMap = $this->mapper->findRequestDataTypesMap('1');
		self::assertCount(0, $dataTypesMap);
		self::assertEquals(array(), $dataTypesMap);
	}

	public function testFindCustomDataForRequestId() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_user_data' => array(
					array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type1', 'serialized_data' => serialize(array(array('event' => 'event_route', 'target' => 'Resolver', 'listener' => 'match')))),
					array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type3', 'serialized_data' => serialize(array(array('event' => 'event_route', 'target' => 'Resolver', 'listener' => 'match')))),
					array('request_id' => '1', 'namespace' => 'extension2', 'data_type' => 'type2', 'serialized_data' => serialize(array(array('event' => 'event_route', 'target' => 'Resolver', 'listener' => 'match')))),
			),
		)));
		
		$resultSet = $this->mapper->findCustomDataForRequestId('1');
		self::assertInstanceOf('ZendServer\Set', $resultSet);
		self::assertEquals(3, $resultSet->count());
	}

	public function testFindCustomDataForRequestIdEmpty() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_user_data' => array(
			),
		)));
		
		$resultSet = $this->mapper->findCustomDataForRequestId('2');
		self::assertInstanceOf('ZendServer\Set', $resultSet);
		self::assertEquals(0, $resultSet->count());
	}

	public function testFindCustomDataForRequestIdWrongId() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_user_data' => array(
					array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type1'),
					array('request_id' => '1', 'namespace' => 'extension2', 'data_type' => 'type2'),
			),
		)));
		
		$resultSet = $this->mapper->findCustomDataForRequestId('2');
		self::assertInstanceOf('ZendServer\Set', $resultSet);
		self::assertEquals(0, $resultSet->count());
	}

	public function testFindDataTypesMapNoEntriesForRequestId() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_user_data' => array(
					array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type1'),
					array('request_id' => '1', 'namespace' => 'extension2', 'data_type' => 'type2'),
			),
		)));
		
		$dataTypesMap = $this->mapper->findRequestDataTypesMap('2');
		self::assertCount(0, $dataTypesMap);
		self::assertEquals(array(), $dataTypesMap);
	}

	public function testFindDataTypesMap() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_user_data' => array(
				array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type1'),
				array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type2'),
				array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type3'),
				array('request_id' => '1', 'namespace' => 'extension1', 'data_type' => 'type3'), /// two identical rows, should be grouped
				array('request_id' => '1', 'namespace' => 'extension2', 'data_type' => 'type1'),
				array('request_id' => '1', 'namespace' => 'extension2', 'data_type' => 'type2'),
			),
		)));
		
		$dataTypesMap = $this->mapper->findRequestDataTypesMap('1');
		self::assertCount(2, $dataTypesMap);
		self::assertCount(3, $dataTypesMap['extension1']);
		self::assertArrayValues(array('type1', 'type2', 'type3'), $dataTypesMap['extension1']);
		self::assertCount(2, $dataTypesMap['extension2']);
		self::assertArrayValues(array('type1', 'type2'), $dataTypesMap['extension2']);
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new ExtensionsMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_user_data', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'devbar_user_data' => array(),
		));
	}
}