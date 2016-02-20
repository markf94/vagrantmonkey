<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class FunctionsMapperTest extends TestCase
{
	/**
	 * @var FunctionsMapper
	 */
	private $mapper;

	public function testGetEntries() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_functions_stats' => array(
				array('request_id' => '1', 'function_name' => '__construct', 'function_scope' => 'MyNS\MyClass'),
				array('request_id' => '1', 'function_name' => 'function', 'function_scope' => 'MyNS\MyClass'),
				array('request_id' => '2', 'function_name' => '__construct', 'function_scope' => 'MyNS\MyClass'),
			),
		)));
		
		$functions = $this->mapper->getFunctions('1');
		self::assertEquals('2', $functions->count());
		$functions = $this->mapper->getFunctions('2');
		self::assertEquals('1', $functions->count());
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new FunctionsMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_functions_stats', $this->getAdapter()));
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