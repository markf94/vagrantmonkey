<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class LogEntriesMapperTest extends TestCase
{
	/**
	 * @var LogEntriesMapper
	 */
	private $mapper;

	public function testGetEntries() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_log_entries' => array(
				array('request_id' => '1@111@0', 'backtrace_id' => 0, 'sequence_id' => 0),
				array('request_id' => '1@111@0', 'backtrace_id' => 0, 'sequence_id' => 0),
				array('request_id' => '2@111@0', 'backtrace_id' => 0, 'sequence_id' => 0),
				array('request_id' => '2@111@0', 'backtrace_id' => 0, 'sequence_id' => 0),
				array('request_id' => '2@111@0', 'backtrace_id' => 0, 'sequence_id' => 0),
			),
		)));
		
		$queries = $this->mapper->getEntries('1@111@0');
		self::assertEquals('2', $queries->count());
		$queries = $this->mapper->getEntries('2@111@0');
		self::assertEquals('3', $queries->count());
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new LogEntriesMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_log_entries', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'devbar_log_entries' => array(),
		));
	}
}