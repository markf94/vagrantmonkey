<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class SqlQueriesMapperTest extends TestCase
{
	/**
	 * @var SqlQueriesMapper
	 */
	private $mapper;

	public function testGetFirstRequests() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_sql_queries' => array(
				array('request_id' => '1@111@0', 'backtrace_id' => 0),
				array('request_id' => '1@111@0', 'backtrace_id' => 0),
				array('request_id' => '2@111@0', 'backtrace_id' => 0),
				array('request_id' => '2@111@0', 'backtrace_id' => 0),
				array('request_id' => '2@111@0', 'backtrace_id' => 0),
			),
		)));
		
		$queries = $this->mapper->getQueries('1@111@0');
		self::assertEquals('2', $queries->count());
		$queries = $this->mapper->getQueries('2@111@0');
		self::assertEquals('3', $queries->count());
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new SqlQueriesMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_sql_queries', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'devbar_sql_queries' => array(),
			'devbar_sql_statements' => array(),
		));
	}
}