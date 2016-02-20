<?php
namespace Notifications\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;
use Servers\Db\Mapper;

require_once 'tests/bootstrap.php';

class MapperTest extends TestCase
{
	/**
	 * @var NotificationsMapper
	 */
	private $mapper;
	
	public function testFindMissingServersTwoMissing() {
		$this->updateDataSet(new ArrayDataSet(array(
				'ZSD_NOTIFICATIONS' => array(
						array('ID' => '2', 'TYPE' => '3', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '3'),
						array('ID' => '3', 'TYPE' => '4', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '4'),
				),
				'ZSD_NODES' => array(
						array('NODE_ID' => '1'),
						array('NODE_ID' => '2'),
				)
		)));
		
		$result = $this->mapper->findMissingServers();
		
		self::assertInternalType('array', $result);
		self::assertCount(2, $result);
	}
	
	public function testFindMissingServersOneMissing() {
		$this->updateDataSet(new ArrayDataSet(array(
				'ZSD_NOTIFICATIONS' => array(
						array('ID' => '2', 'TYPE' => '3', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '3'),
				),
				'ZSD_NODES' => array(
						array('NODE_ID' => '1'),
						array('NODE_ID' => '2'),
				)
		)));
		
		$result = $this->mapper->findMissingServers();
		
		self::assertInternalType('array', $result);
		self::assertCount(1, $result);
	}
	
	public function testFindMissingServersOneCorrect() {
		$this->updateDataSet(new ArrayDataSet(array(
				'ZSD_NOTIFICATIONS' => array(
						array('ID' => '2', 'TYPE' => '3', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '2'),
				),
				'ZSD_NODES' => array(
						array('NODE_ID' => '1'),
				)
		)));
		
		$result = $this->mapper->findMissingServers();
		
		self::assertInternalType('array', $result);
		self::assertCount(1, $result);
	}
	
	public function testFindMissingServersEmpty() {
		$this->updateDataSet(new ArrayDataSet(array(
				'ZSD_NOTIFICATIONS' => array(
				),
				'ZSD_NODES' => array(
				)
		)));
		
		$result = $this->mapper->findMissingServers();
		
		self::assertInternalType('array', $result);
		self::assertCount(0, $result);
	}
	
	public function testCleanNotificationsForMissingServersEmptyNotifications() {

		$this->updateDataSet(new ArrayDataSet(array(
			'ZSD_NOTIFICATIONS' => array(
			),
			'ZSD_NODES' => array(
				array('NODE_ID' => '1'),
				array('NODE_ID' => '2'),
			)
		)));
		
		$this->mapper->cleanNotificationsForMissingServers($this->mapper->findMissingServers());
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_NOTIFICATIONS', 'SELECT ID, TYPE, REPEATS, SHOW_AT, NOTIFIED, NODE_ID FROM ZSD_NOTIFICATIONS');
		self::assertEquals(0, $queryTable->getRowCount());
	}
	
	public function testCleanNotificationsForMissingServersOnlyAssociatedNotifications() {

		$dataSet = new ArrayDataSet(array(
			'ZSD_NOTIFICATIONS' => array(
				array('ID' => '2', 'TYPE' => '3', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '1'),
			),
			'ZSD_NODES' => array(
				array('NODE_ID' => '1'),
				array('NODE_ID' => '2'),
			)
		));

		$this->updateDataSet($dataSet);
		
		$result = $this->mapper->cleanNotificationsForMissingServers($this->mapper->findMissingServers());

		$queryTable = $this->getConnection()->createQueryTable('ZSD_NOTIFICATIONS', 'SELECT ID, TYPE, REPEATS, SHOW_AT, NOTIFIED, NODE_ID FROM ZSD_NOTIFICATIONS');
		self::assertTablesEqual($dataSet->getTable('ZSD_NOTIFICATIONS'), $queryTable, 'Should not delete notification records');
		self::assertEquals(0, $result);
	}
	
	public function testCleanNotificationsForMissingServers() {

		$result = $this->mapper->cleanNotificationsForMissingServers($this->mapper->findMissingServers());
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_NOTIFICATIONS', 'SELECT ID, TYPE, REPEATS, SHOW_AT, NOTIFIED, NODE_ID FROM ZSD_NOTIFICATIONS');
		$expected = new ArrayDataSet(array(
			'ZSD_NOTIFICATIONS' => array(
				/// only the 2nd row is left
				array('ID' => '2', 'TYPE' => '3', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '1'),
			),
			'ZSD_NODES' => array(
				array('NODE_ID' => '1'),
				array('NODE_ID' => '2'),
			)
		));
		self::assertTablesEqual($expected->getTable('ZSD_NOTIFICATIONS'), $queryTable);
		self::assertEquals(1, $result);
	}
	
	public function testInsertNotification() {
		$this->mapper->insertNotification(0);
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_NOTIFICATIONS', 'SELECT ID, TYPE, REPEATS, SHOW_AT, NOTIFIED, NODE_ID FROM ZSD_NOTIFICATIONS');
		$expected = $this->getDataSet();
		$expected->getTable('ZSD_NOTIFICATIONS')->addRow(array(
				'ID' => '3', 'TYPE' => '0', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '0'
		));
		self::assertTablesEqual($expected->getTable('ZSD_NOTIFICATIONS'), $queryTable);
		self::assertEquals(3, $expected->getTable('ZSD_NOTIFICATIONS')->getRowCount());
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'ZSD_NOTIFICATIONS' => array(
				array('ID' => '1', 'TYPE' => '2', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '4'),
				array('ID' => '2', 'TYPE' => '3', 'REPEATS' => '0', 'SHOW_AT' => (string)time(), 'NOTIFIED' => '0', 'NODE_ID' => '1'),
			),
			'ZSD_NODES' => array(
				array('NODE_ID' => '1'),
				array('NODE_ID' => '2'),
			)
		));
	}

	protected function setUp() {
		parent::setUp();
		$this->mapper = new NotificationsMapper(new TableGateway('ZSD_NOTIFICATIONS', $this->getAdapter()));
		$this->mapper->setServersMapper(new Mapper(new TableGateway('ZSD_NODES', $this->getAdapter())));
	}
}