<?php
namespace JobQueue\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class MapperTest extends TestCase
{
	/**
	 * @var Mapper
	 */
	private $mapper;

	public function testSchedulingRulesSearchesURL() {
		$this->updateDataSet($this->getSchedulingRulesArraySet());
		
		$result = $this->mapper->getSchedulingRules(10, 0, array('freeText' => 'test'));
		self::assertEquals(0, count($this->mapper->getSchedulingRules(10, 0, array('freeText' => 'no-t-e-s-t-string-mentioned'))));
		self::assertEquals(1, count($this->mapper->getSchedulingRules(10, 0, array('freeText' => 'nameonly'))));
		self::assertEquals(1, count($this->mapper->getSchedulingRules(10, 0, array('freeText' => 'nameandscript'))));
		self::assertEquals(1, count($this->mapper->getSchedulingRules(10, 0, array('freeText' => 'scriptonly'))));
		self::assertEquals(3, count($this->mapper->getSchedulingRules(10, 0, array('freeText' => 'name'))));
	}

	public function testCountSchedulingRulesSearchesURL() {
		$this->updateDataSet($this->getSchedulingRulesArraySet());
		
		self::assertEquals(0, $this->mapper->countSchedulingRules(array('freeText' => 'no-t-e-s-t-string-mentioned')));
		self::assertEquals(1, $this->mapper->countSchedulingRules(array('freeText' => 'nameonly')));
		self::assertEquals(1, $this->mapper->countSchedulingRules(array('freeText' => 'nameandscript')));
		self::assertEquals(1, $this->mapper->countSchedulingRules(array('freeText' => 'scriptonly')));
		self::assertEquals(3, $this->mapper->countSchedulingRules(array('freeText' => 'name')));
	}
	
	/**
	 * @return \ZendServer\PHPUnit\DbUnit\ArrayDataSet
	 */
	private function getSchedulingRulesArraySet() {
		return new ArrayDataSet(array(
			'jobqueue_schedule' => array(
				array(
					'id' => '2',
					'status' => '10',
					'queue_id' => '1',
					'application_id' => '38',
					'type' => '1',
					'priority' => '2',
					'persistent' => '0',
					'timeout' => '0',
					'schedule' => 'H 1',
					'name' => 'some-other-name-that-is-not-t-e-s-t',
					'script' => 'http://localhost/test',
					'vars' => '{}',
					'http_headers' => null,
					'options' => '{}',
				),
				array(
					'id' => '3',
					'status' => '10',
					'queue_id' => '1',
					'application_id' => '38',
					'type' => '1',
					'priority' => '2',
					'persistent' => '0',
					'timeout' => '0',
					'schedule' => 'H 1',
					'name' => 'nameonly',
					'script' => 'http://localhost/test',
					'vars' => '{}',
					'http_headers' => null,
					'options' => '{}',
				),
				array(
					'id' => '4',
					'status' => '10',
					'queue_id' => '1',
					'application_id' => '38',
					'type' => '1',
					'priority' => '2',
					'persistent' => '0',
					'timeout' => '0',
					'schedule' => 'H 1',
					'name' => 'nameandscript',
					'script' => 'http://localhost/nameandscript',
					'vars' => '{}',
					'http_headers' => null,
					'options' => '{}',
				),
				array(
					'id' => '5',
					'status' => '10',
					'queue_id' => '1',
					'application_id' => '38',
					'type' => '1',
					'priority' => '2',
					'persistent' => '0',
					'timeout' => '0',
					'schedule' => 'H 1',
					'name' => 'something-else',
					'script' => 'http://localhost/scriptonly',
					'vars' => '{}',
					'http_headers' => null,
					'options' => '{}',
				),
			),
			'jobqueue_job' => array(array(
						'queue_id' => '1',
						'type' => '1',
						'priority' => '2',
						'status' => '5',
						'schedule_id' => '2',
						'name' => 'some-other-name-that-is-not-t-e-s-t',
						'script' => 'http://localhost/test',
					),
					array(
						'queue_id' => '1',
						'type' => '1',
						'priority' => '2',
						'status' => '5',
						'schedule_id' => '3',
						'name' => 'nameonly',
						'script' => 'http://localhost/test',
					),
					array(
						'queue_id' => '1',
						'type' => '1',
						'priority' => '3',
						'status' => '5',
						'schedule_id' => '3',
						'name' => 'nameonly',
						'script' => 'http://localhost/test',
					),
					array(
						'queue_id' => '1',
						'type' => '1',
						'priority' => '3',
						'status' => '5',
						'schedule_id' => '4',
						'name' => 'nameandscript',
						'script' => 'http://localhost/nameandscript',
					),
					array(
						'queue_id' => '1',
						'type' => '1',
						'priority' => '3',
						'status' => '5',
						'schedule_id' => '5',
						'name' => 'something-else',
						'script' => 'http://localhost/scriptonly',
					),
			),
			'jobqueue_queue' => array(array(
				'id' => '1',
				'name' => '',
			)),
		));
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new Mapper();
		$this->mapper->setTableGateway(new TableGateway('jobqueue_job', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'jobqueue_schedule' => array(),
			'jobqueue_job' => array(),
			'jobqueue_queue' => array(),
		));
	}
}