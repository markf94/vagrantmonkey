<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use ZendServer\PHPUnit\TestCase;
use DeploymentLibrary\Container;
use DeploymentLibrary\Prerequisites\Validator\Library\Max;

require_once 'tests/bootstrap.php';

class MaxTest extends TestCase
{
	public function testIsValid() {
		$deployedValidator = new Max('1.0.0');

		$container = new Container(array(
			'libraryName' => 'Library1',
			'versions' => array(array('version' => '0.9.0')),
		), 1);
		self::assertTrue($deployedValidator->isValid($container));
		
		$container = new Container(array(
			'libraryName' => 'Library1',
			'versions' => array(array('version' => '1.0.1'), array('version' => '1.1.0')),
		), 1);
		self::assertFalse($deployedValidator->isValid($container));
		
		$container = new Container(array(
			'libraryName' => 'Library1',
			'versions' => array(array('version' => '1.0.0')),
		), 1);
		self::assertTrue($deployedValidator->isValid($container));
	}
}