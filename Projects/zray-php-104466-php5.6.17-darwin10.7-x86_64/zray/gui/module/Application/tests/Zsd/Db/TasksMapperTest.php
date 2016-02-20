<?php
namespace Zsd\Db;
use ZendServer\PHPUnit\DbUnit\TestCase;
use Audit\Controller\Plugin\AuditMessage;
use Audit\Container;
use ZendServer\Exception;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class TasksMapperTest extends TestCase {
	/**
	 * @var TasksMapper
	 */
	private $mapper;
	
	public function testGetTaskDaemonName() {
		$this->mapper->getTaskDaemonName('jqd');
		$this->mapper->getTaskDaemonName('scd');
		$this->mapper->getTaskDaemonName('scd');
		$this->mapper->getTaskDaemonName('monitor_node');
		$this->mapper->getTaskDaemonName('zdd');
		
		try {
			$this->mapper->getTaskDaemonName('unknown');
			self::fail('Exception expected');
		} catch (Exception $ex) {}
	}
	
	public function testInsertTask() {
		self::assertEquals(2, $this->mapper->insertTask(1, 1));
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT ZSD_TASKS_SEQUENCE, NODE_ID, TASK_ID,AUDIT_ID,EXTRA_DATA FROM ZSD_TASKS');
		$expected = $this->getDataSet()->getTable('ZSD_TASKS');
		$expected->addRow(array(
				'ZSD_TASKS_SEQUENCE' => '2',
				'NODE_ID' => '1',
				'TASK_ID' => '1',
				'AUDIT_ID'=> TasksMapper::DUMMY_AUDIT_ID,
				'EXTRA_DATA' => '[]'
		));
		self::assertTablesEqual($expected, $queryTable);
	}
	
	public function testTasksCompleteSingleServer() {
		$this->updateDataSet(new ArrayDataSet(array('ZSD_TASKS' => array())));
		self::assertTrue($this->mapper->tasksComplete());
		$this->mapper->insertTask(0, 1);
		self::assertFalse($this->mapper->tasksComplete());
	}
	
	public function testTasksCompleteClusterServer() {
		$this->updateDataSet(new ArrayDataSet(array('ZSD_TASKS' => array())));
		
		$edition = $this->getMock('ZendServer\Edition');
		/// generally use single server
		$edition->expects($this->any())->method('isClusterServer')->will($this->returnValue(true));
		$this->mapper->setEdition($edition);
		
		self::assertTrue($this->mapper->tasksComplete());
		/// bad cluster action, serverid=0 tasks should not be counted
		$this->mapper->insertTask(0, 1);
		self::assertTrue($this->mapper->tasksComplete());
		
		
		$this->mapper->insertTask(1, 1);
		$this->mapper->insertTask(2, 1);
		self::assertFalse($this->mapper->tasksComplete());
		
		
	}
	
	public function testInsertTaskAuditId() {

		$this->mapper->getAuditMessage()->setMessage(new Container(array(
				'AUDIT_ID' => '5',
				'USERNAME' => '',
				'REQUEST_INTERFACE' => '',
				'REMOTE_ADDR' => '',
				'AUDIT_TYPE' => '',
				'BASE_URL' => '',
				'CREATION_TIME' => '',
				'EXTRA_DATA' => '',
		)));

		self::assertEquals(2, $this->mapper->insertTask(1, 1));
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT ZSD_TASKS_SEQUENCE, NODE_ID, TASK_ID, AUDIT_ID, EXTRA_DATA FROM ZSD_TASKS');
		$expected = $this->getDataSet()->getTable('ZSD_TASKS');
		$expected->addRow(array(
				'ZSD_TASKS_SEQUENCE' => '2',
				'NODE_ID' => '1',
				'TASK_ID' => '1',
				'AUDIT_ID'=> '5',
				'EXTRA_DATA' => '[]'
		));
		self::assertTablesEqual($expected, $queryTable);
	}
	
	public function testInsertTaskWithExtraData() {
		
		self::assertEquals(2, $this->mapper->insertTask(1, 1,array('extradata')));
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT ZSD_TASKS_SEQUENCE, NODE_ID, TASK_ID, AUDIT_ID, EXTRA_DATA FROM ZSD_TASKS');
		$expected = $this->getDataSet()->getTable('ZSD_TASKS');
		$expected->addRow(array(
				'ZSD_TASKS_SEQUENCE' => '2',
				'NODE_ID' => '1',
				'TASK_ID' => '1',
				'AUDIT_ID'=> TasksMapper::DUMMY_AUDIT_ID,
				'EXTRA_DATA' => '["extradata"]'
		));
		self::assertTablesEqual($expected, $queryTable);
		
	}
	
	public function testTasksCompleteGlobal() {

		$this->updateDataSet(new ArrayDataSet(array('ZSD_TASKS' => array())));

		self::assertTrue($this->mapper->tasksComplete());
	}
	
	public function testTasksCompleteServers() {
		/// We have only a serverId=2 task
		self::assertFalse($this->mapper->tasksComplete(array(2)));
		self::assertTrue($this->mapper->tasksComplete(array(1)));
		self::assertFalse($this->mapper->tasksComplete(array(1,2)));
		self::assertTrue($this->mapper->tasksComplete(array(1,3)));
	}
	
	public function testTasksCompleteTasks() {
		/// We have only a ZSD_TASKS_SEQUENCE=1 task
		self::assertTrue($this->mapper->tasksComplete(array(), array(3)));
		self::assertTrue($this->mapper->tasksComplete(array(), array(2)));
		self::assertFalse($this->mapper->tasksComplete(array(), array(1,3)));
		self::assertFalse($this->mapper->tasksComplete(array(), array(1,2)));
	}
	
	public function testTasksCompleteTasksAndServers() {
		/// We have only a serverId=2, ZSD_TASKS_SEQUENCE=3 task
		self::assertTrue($this->mapper->tasksComplete(array(2), array(3)));
		self::assertTrue($this->mapper->tasksComplete(array(2), array(2)));
		self::assertFalse($this->mapper->tasksComplete(array(2), array(1,3)));
		self::assertFalse($this->mapper->tasksComplete(array(2), array(1,2)));
		
		self::assertTrue($this->mapper->tasksComplete(array(3), array(3)));
		self::assertTrue($this->mapper->tasksComplete(array(3), array(2)));
		self::assertTrue($this->mapper->tasksComplete(array(3), array(1,3)));
		self::assertTrue($this->mapper->tasksComplete(array(3), array(1,2)));
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new TasksMapper();
		$this->mapper->setTableGateway(new TableGateway('ZSD_TASKS', $this->getAdapter()));
		$this->mapper->setAuditMessage(new AuditMessage());
		
		$edition = $this->getMock('ZendServer\Edition');
		/// generally use single server
		$edition->expects($this->any())->method('isClusterServer')->will($this->returnValue(false));
		$this->mapper->setEdition($edition);
	}
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'ZSD_TASKS' => array(array(
				'ZSD_TASKS_SEQUENCE' => 1,
				'NODE_ID' => 2,
				'TASK_ID' => 3,
				'AUDIT_ID' => 1,
				'EXTRA_DATA' => '[]'
			))
		));
	}

}