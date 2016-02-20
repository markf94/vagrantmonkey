<?php
namespace Vhost\Validator;

use ZendServer\PHPUnit\TestCase;
use Vhost\Entity\Vhost;

require_once 'tests/bootstrap.php';

class VhostValidForRedeployTest extends TestCase
{
	public function testIsValid() {
		$validator = new VhostValidForRedeploy();
		$vhostMapper = $this->getMock('Vhost\Mapper\Vhost');
		
		$vhostContainer = $this->getMock('Vhost\Entity\Vhost', array(), array(), '', false);
		$vhostContainer->expects($this->any())->method('isZendDefined')->will($this->returnValue(true));
		$vhostContainer->expects($this->any())->method('getId')->will($this->returnValue(1));
		
		$vhostNodeEntity = $this->getMock('Vhost\Entity\VhostNode');
		$vhostNodeEntity->expects($this->any())->method('getStatus')->will($this->returnValue(Vhost::STATUS_OK));
		
		$vhostMapper->expects($this->once())->method('getVhostByName')->with('vhost:80')
			->will($this->returnValue($vhostContainer));
		
		$validator->setVhostMapper($vhostMapper);
		
		self::assertTrue($validator->isValid('vhost:80'));
		self::assertEquals(0, count($validator->getMessages()));
	}
	
	public function testIsValidVhostNotFound() {
		$validator = new VhostValidForRedeploy();
		$vhostMapper = $this->getMock('Vhost\Mapper\Vhost');
		
		
		$vhostNodeEntity = $this->getMock('Vhost\Entity\VhostNode');
		$vhostNodeEntity->expects($this->never())->method('getStatus');
		
		$vhostMapper->expects($this->once())->method('getVhostByName')->with('vhost:80')
			->will($this->returnValue(false));
		
		$vhostMapper->expects($this->never())->method('getSingleVhostNodes');
		
		$validator->setVhostMapper($vhostMapper);
		
		self::assertFalse($validator->isValid('vhost:80'));
		$messages = $validator->getMessages();
		self::assertEquals(1, count($messages));
		self::assertArrayHasKey('virtualHostMissing', $messages);
	}
	
	public function testIsValidVhostNotFoundNull() {
		$validator = new VhostValidForRedeploy();
		$vhostMapper = $this->getMock('Vhost\Mapper\Vhost');
		
		
		$vhostNodeEntity = $this->getMock('Vhost\Entity\VhostNode');
		$vhostNodeEntity->expects($this->never())->method('getStatus');
		
		$vhostMapper->expects($this->once())->method('getVhostByName')->with('vhost:80')
			->will($this->returnValue(null));
		
		$vhostMapper->expects($this->never())->method('getSingleVhostNodes');
		
		$validator->setVhostMapper($vhostMapper);
		
		self::assertFalse($validator->isValid('vhost:80'));
		$messages = $validator->getMessages();
		self::assertEquals(1, count($messages));
		self::assertArrayHasKey('virtualHostMissing', $messages);
	}
	
	public function testIsValidNotZendDefined() {
		$validator = new VhostValidForRedeploy();
		$vhostMapper = $this->getMock('Vhost\Mapper\Vhost');
		
		$vhostContainer = $this->getMock('Vhost\Entity\Vhost', array(), array(), '', false);
		$vhostContainer->expects($this->any())->method('isZendDefined')->will($this->returnValue(false));
		
		$vhostNodeEntity = $this->getMock('Vhost\Entity\VhostNode');
		$vhostNodeEntity->expects($this->never())->method('getStatus');
		
		$vhostMapper->expects($this->once())->method('getVhostByName')->with('vhost:80')
			->will($this->returnValue($vhostContainer));
		
		$vhostMapper->expects($this->never())->method('getSingleVhostNodes');
		
		$validator->setVhostMapper($vhostMapper);
		
		self::assertFalse($validator->isValid('vhost:80'));
		$messages = $validator->getMessages();
		self::assertEquals(1, count($messages));
		self::assertArrayHasKey('virtualHostNotZendDefined', $messages);
	}
	
	public function testIsValidHostInErrorIsValid() {
		$validator = new VhostValidForRedeploy();
		$vhostMapper = $this->getMock('Vhost\Mapper\Vhost');
	
		$vhostContainer = $this->getMock('Vhost\Entity\Vhost', array(), array(), '', false);
		$vhostContainer->expects($this->any())->method('isZendDefined')->will($this->returnValue(true));
		$vhostContainer->expects($this->any())->method('getId')->will($this->returnValue(1));
	
		$vhostNodeEntity = $this->getMock('Vhost\Entity\VhostNode');
		$vhostNodeEntity->expects($this->any())->method('getStatus')->will($this->returnValue(Vhost::STATUS_ERROR));
	
		$vhostMapper->expects($this->once())->method('getVhostByName')->with('vhost:80')
		->will($this->returnValue($vhostContainer));
	
		$validator->setVhostMapper($vhostMapper);
	
		self::assertTrue($validator->isValid('vhost:80'));
	}
}