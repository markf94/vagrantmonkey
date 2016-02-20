<?php
namespace Prerequisites\Validator\Library;

use ZendServer\PHPUnit\TestCase;
use DeploymentLibrary\Prerequisites\Validator\Library\Max;
use DeploymentLibrary\Container;

require_once 'tests/bootstrap.php';

class MaxTest extends TestCase
{
	public function testIsValid() {
		$validator = new Max('1.1.1');
		
		$library = new Container(array('versions' => array(array('version' => '1.1.0'))), 1);
		
		self::assertTrue($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at most 1.1.1 (is 1.1.0)', current($validator->getMessages()));
	}
	
	public function testIsValidMultiple() {
		$validator = new Max('1.1.1');
		
		$library = new Container(array('versions' => array(array('version' => '1.1.0'), array('version' => '1.1.1'), array('version' => '1.0.1'))), 1);
		self::assertTrue($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at most 1.1.1 (is 1.0.1)', current($validator->getMessages()));
	}
	
	public function testIsInvalid() {
		$validator = new Max('1.1.1');
		
		$library = new Container(array('versions' => array(array('version' => '1.1.2'), array('version' => '1.3.1'), array('version' => '1.2.1'))), 1);
		
		self::assertFalse($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at most 1.1.1 (is 1.1.2)', current($validator->getMessages()));
	}
	
	public function testIsInvalidNoLibraryVersion() {
		$validator = new Max('1.1.1');
		
		$library = false;
		
		self::assertFalse($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at most 1.1.1 (none found)', current($validator->getMessages()));
	}
}