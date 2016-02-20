<?php
namespace Prerequisites\Validator\Library;

use ZendServer\PHPUnit\TestCase;
use DeploymentLibrary\Container;
use DeploymentLibrary\Prerequisites\Validator\Library\Min;

require_once 'tests/bootstrap.php';

class MinTest extends TestCase
{
	public function testIsValid() {
		$validator = new Min('1.1.1');
		
		$library = new Container(array('versions' => array(array('version' => '1.1.1'))), 1);
		
		self::assertTrue($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at least 1.1.1 (is 1.1.1)', current($validator->getMessages()));
	}
	
	public function testIsValidMultiple() {
		$validator = new Min('1.1.1');
		
		$library = new Container(array('versions' => array(array('version' => '1.1.2'), array('version' => '1.1.1'), array('version' => '1.2.1'))), 1);
		self::assertTrue($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at least 1.1.1 (is 1.1.2)', current($validator->getMessages()));
	}
	
	public function testIsInvalid() {
		$validator = new Min('1.1.1');
		
		$library = new Container(array('versions' => array(array('version' => '1.1.0'), array('version' => '1.0.1'), array('version' => '0.9.0'))), 1);
		
		self::assertFalse($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at least 1.1.1 (is 1.1.0)', current($validator->getMessages()));
	}
	

	public function testIsInvalidNoLibraryVersion() {
		$validator = new Min('1.1.1');
	
		$library = false;
	
		self::assertFalse($validator->isValid($library));
		self::assertEquals(1, count($validator->getMessages()));
		self::assertEquals('Version should be at least 1.1.1 (none found)', current($validator->getMessages()));
	}
}