<?php
namespace Configuration\Audit\ExtraData;

use ZendServer\PHPUnit\TestCase;
require_once 'tests/bootstrap.php';

class DirectivesParserTest extends TestCase {
	public function testToArray() {
		$parser = new DirectivesParser();
		$original = array('gui_directive' => '1');
		$directivesMapper = $this->getMock('Configuration\MapperDirectives');
		$directivesMapper->expects($this->once())->method('getDirectivesValues')
		->withAnyParameters()->will($this->returnValue(array('zend_gui.gui_directive' => '-1')));
		$parser->setDirectivesMapper($directivesMapper);
		$parser->setExtraData($original);
		
		$result = $parser->toArray();
		
		self::assertInternalType('array', $result);
		self::assertEquals(1, count($result));
		
		self::assertInternalType('array', current($result));
		self::assertEquals('GUI Setting: zend_gui.gui_directive, Old value: -1, New value: 1', current(current($result)));
	}
	
	public function testToArrayPartialName() {
		$parser = new DirectivesParser();
		$original = array('gui_directive' => '1');
		$directivesMapper = $this->getMock('Configuration\MapperDirectives');
		$directivesMapper->expects($this->once())->method('getDirectivesValues')
		->withAnyParameters()->will($this->returnValue(array('zend_gui.gui_directive' => '-1')));
		/// note different (full, absoluate) name is returned from the mapper
		$parser->setDirectivesMapper($directivesMapper);
		$parser->setExtraData($original);
		
		$result = $parser->toArray();
		
		self::assertInternalType('array', $result);
		self::assertEquals(1, count($result));
		
		self::assertInternalType('array', current($result));
		self::assertEquals('GUI Setting: zend_gui.gui_directive, Old value: -1, New value: 1', current(current($result)));
	}
	
	public function testToArrayNoOldValueFound() {
		$parser = new DirectivesParser();
		$original = array('gui_directive' => '1');
		$directivesMapper = $this->getMock('Configuration\MapperDirectives');
		$directivesMapper->expects($this->once())->method('getDirectivesValues')
		->withAnyParameters()->will($this->returnValue(array()));

		$parser->setDirectivesMapper($directivesMapper);
		$parser->setExtraData($original);
		
		$result = $parser->toArray();
		
		self::assertInternalType('array', $result);
		self::assertEquals(1, count($result));

		self::assertInternalType('array', current($result));
		self::assertEquals('GUI Setting: gui_directive, New value: 1', current(current($result)));
	}
}
