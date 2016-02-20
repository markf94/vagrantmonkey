<?php
namespace Messages;

use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Messages\Db\MessageMapper;
use Zend\Db\TableGateway\TableGateway;
require_once 'tests/bootstrap.php';

class MessageMapperTest extends \ZendServer\PHPUnit\DbUnit\TestCase
{
	/**
	 * @var MessageMapper
	 */
	private $mapper;
	
	public function testIsDaemonOffline() {
		$this->updateDataSet(new ArrayDataSet(array(
				'ZSD_MESSAGES' => array(
						array('NODE_ID' => '0', 'CONTEXT' => MessageMapper::CONTEXT_DAEMON, 'MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'TYPE' => MessageMapper::TYPE_OFFLINE),
				)
		)));
		
		self::assertTrue($this->mapper->isDaemonOffline('jqd', 0));
		self::assertTrue($this->mapper->isDaemonOffline('jqd'));
		self::assertFalse($this->mapper->isDaemonOffline('jqd', 1));
		self::assertFalse($this->mapper->isDaemonOffline('monitor', 0));
		self::assertFalse($this->mapper->isDaemonOffline('monitor', 1));
	}
	
	public function testFindAllDirectivesMessages() {
		$this->updateDataSet(new ArrayDataSet(array(
			'ZSD_MESSAGES' => array(
				array('NODE_ID' => '0', 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE, 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED),
				array('NODE_ID' => '1', 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE, 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED),
			)
		)));
		
		$result = $this->mapper->findAllDirectivesMessages();
		self::assertEquals(2, $result->count());
		
		$result = $this->mapper->findAllDirectivesMessages(0);
		self::assertEquals(1, $result->count());
		
		$result = $this->mapper->findAllDirectivesMessages(1);
		self::assertEquals(1, $result->count());
		
		$result = $this->mapper->findAllDirectivesMessages(2);
		self::assertEquals(0, $result->count());
	}
	
	public function testIsDirectivesAwaitingRestart() {
		
		self::assertFalse($this->mapper->isDirectivesAwaitingRestart(array()));
		
		$this->updateDataSet(new ArrayDataSet(array(
			'ZSD_MESSAGES' => array(
				array('NODE_ID' => '0', 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE, 'MSG_KEY' => 'directive1', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED),
				array('NODE_ID' => '1', 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE, 'MSG_KEY' => 'directive1', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED),
			)
		)));
		
		self::assertFalse($this->mapper->isDirectivesAwaitingRestart(array('directive2')));
		self::assertFalse($this->mapper->isDirectivesAwaitingRestart(array('directive1'), 2));
		self::assertFalse($this->mapper->isDirectivesAwaitingRestart(array(), 2));/// wrong nodeid
		self::assertFalse($this->mapper->isDirectivesAwaitingRestart(array(), 1));/// correct nodeid, no directives specified
		self::assertFalse($this->mapper->isDirectivesAwaitingRestart(array(), 0));/// correct nodeid, no directives specified
		
		self::assertTrue($this->mapper->isDirectivesAwaitingRestart(array('directive1')));
		self::assertTrue($this->mapper->isDirectivesAwaitingRestart(array('directive1'), 1));
		self::assertTrue($this->mapper->isDirectivesAwaitingRestart(array('directive1'), 0));
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new MessageMapper(new TableGateway('ZSD_MESSAGES', $this->getAdapter()));
	}
	
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'ZSD_MESSAGES' => array()
		));
	}
}