<?php
namespace Messages;

use Zend\Json\Json;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class MessageContainerTest extends TestCase
{
	public function testGetMessageContext() {
		$message = new MessageContainer(array());
		self::assertEquals('', $message->getMessageContext());
		$message = new MessageContainer(array('CONTEXT' => ''));
		self::assertEquals('', $message->getMessageContext());
		$message = new MessageContainer(array('CONTEXT' => 'unknown'));
		self::assertEquals('', $message->getMessageContext());
		$message = new MessageContainer(array('CONTEXT' => 0));
		self::assertEquals(0, $message->getMessageContext());
		$message = new MessageContainer(array('CONTEXT' => 1));
		self::assertEquals(1, $message->getMessageContext());
		$message = new MessageContainer(array('CONTEXT' => 10));
		self::assertEquals(10, $message->getMessageContext());
	}
	
	public function testGetMessageType() {
		$message = new MessageContainer(array());
		self::assertEquals('', $message->getMessageType());
		$message = new MessageContainer(array('TYPE' => ''));
		self::assertEquals('', $message->getMessageType());
		$message = new MessageContainer(array('TYPE' => 'unknown'));
		self::assertEquals('', $message->getMessageType());
		$message = new MessageContainer(array('TYPE' => 0));
		self::assertEquals(0, $message->getMessageType());
		$message = new MessageContainer(array('TYPE' => 1));
		self::assertEquals(1, $message->getMessageType());
		$message = new MessageContainer(array('TYPE' => 10));
		self::assertEquals(10, $message->getMessageType());
	}
	
	public function testGetMessageDetails() {
		$message = new MessageContainer(array());
		self::assertEquals('', $message->getMessageDetails());
		$message = new MessageContainer(array('DETAILS' => ''));
		self::assertEquals('', $message->getMessageDetails());
		$message = new MessageContainer(array('DETAILS' => 'bad json'));
		self::assertEquals(array(), $message->getMessageDetails());
		$message = new MessageContainer(array('DETAILS' => 0));
		self::assertEquals('', $message->getMessageDetails());
		$message = new MessageContainer(array('DETAILS' => Json::encode('unknown')));
		self::assertEquals(array('unknown'), $message->getMessageDetails());
		$message = new MessageContainer(array('DETAILS' => Json::encode(array('unknown'))));
		self::assertEquals(array('unknown'), $message->getMessageDetails());
		
	}
	
	public function testGetMessageSeverity() {
		$message = new MessageContainer(array());
		self::assertEquals('', $message->getMessageSeverity());
		$message = new MessageContainer(array('MSG_SEVERITY' => ''));
		self::assertEquals('', $message->getMessageSeverity());
		$message = new MessageContainer(array('MSG_SEVERITY' => 'unknown'));
		self::assertEquals('', $message->getMessageSeverity());
		$message = new MessageContainer(array('MSG_SEVERITY' => 0));
		self::assertEquals(0, $message->getMessageSeverity());
		$message = new MessageContainer(array('MSG_SEVERITY' => 1));
		self::assertEquals(1, $message->getMessageSeverity());
		$message = new MessageContainer(array('MSG_SEVERITY' => 10));
		self::assertEquals(10, $message->getMessageSeverity());
	}
}