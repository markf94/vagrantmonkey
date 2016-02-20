<?php
namespace GuiConfiguration\Mapper;


use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zsd\Db\TasksMapper;
use Zend\Db\TableGateway\TableGateway;
require_once 'tests/bootstrap.php';

class ConfigurationTest extends TestCase
{
	/**
	 * @var Configuration
	 */
	private $mapper;
	
	public function testSetGuiDirectives() {
		$this->mapper->setGuiDirectives(array('zend_gui.directive' => 'value'));
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT TASK_ID, EXTRA_DATA FROM ZSD_TASKS');
		self::assertEquals(array('TASK_ID' => '2', 'EXTRA_DATA' => '[{"name":"zend_gui.directive","value":"value"}]'), $queryTable->getRow(0));
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new Configuration();
		$this->mapper->setTasksMapper(new TasksMapper(new TableGateway('ZSD_TASKS', $this->getAdapter())));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'ZSD_TASKS' => array()
		));
	}
}