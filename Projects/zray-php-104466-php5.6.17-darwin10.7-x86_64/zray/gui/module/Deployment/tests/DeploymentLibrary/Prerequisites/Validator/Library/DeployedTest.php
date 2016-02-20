<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use ZendServer\PHPUnit\TestCase;
use DeploymentLibrary\Container;

require_once 'tests/bootstrap.php';

class DeployedTest extends TestCase
{
	public function testIsValid() {
		$deployedValidator = new Deployed('Library1');
		
		self::assertFalse($deployedValidator->isValid(null));
		
		$container = new Container(array(
			'libraryName' => 'Library1',
		), 1);
		self::assertTrue($deployedValidator->isValid($container));
	}
}