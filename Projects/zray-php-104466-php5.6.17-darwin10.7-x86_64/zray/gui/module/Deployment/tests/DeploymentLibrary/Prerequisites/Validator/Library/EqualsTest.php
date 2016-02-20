<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use ZendServer\PHPUnit\TestCase;
use DeploymentLibrary\Container;

require_once 'tests/bootstrap.php';

class EqualsTest extends TestCase
{
	public function testIsValid() {
		$deployedValidator = new Equals('1.0.0');
		
		$container = new Container(array(
			'versions' => array(array('version' => '1.1.0')),
		), 1);
		self::assertFalse($deployedValidator->isValid($container));
		
		$container = new Container(array(
			'versions' => array(array('version' => '1.0.0'), array('version' => '1.1.0')),
		), 1);
		self::assertTrue($deployedValidator->isValid($container));
	}
}