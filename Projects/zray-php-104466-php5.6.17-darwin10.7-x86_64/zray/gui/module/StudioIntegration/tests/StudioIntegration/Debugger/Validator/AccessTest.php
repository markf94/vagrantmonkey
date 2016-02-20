<?php
namespace StudioIntegration\Debugger\Validator;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class AccessTest extends TestCase
{
	public function testIsValidEmptyHosts() {
		$validator = new Access();
		self::assertFalse($validator->isValid('10.1.1.1'));
		self::assertEquals('Host 10.1.1.1 is not in any allowed ip range ()', current($validator->getMessages()));
		self::assertTrue($validator->isValid('127.0.0.1'));
	}
	
	public function testIsValidInvalidHosts() {
		$validator = new Access(array('allow_hosts' => 'definitely not a CIDR range'));
		self::assertFalse($validator->isValid('10.1.1.1'));
		self::assertEquals('Host 10.1.1.1 is not in any allowed ip range (definitely not a CIDR range)', current($validator->getMessages()));
		self::assertTrue($validator->isValid('127.0.0.1'));
	}

	public function testIsValidAllowHostsFullRange() {
		$validator = new Access(array('allow_hosts' => '10.0.0.0/8'));
		self::assertTrue($validator->isValid('10.1.1.1'));
		self::assertFalse($validator->isValid('11.1.1.1'));
		self::assertEquals('Host 11.1.1.1 is not in any allowed ip range (10.0.0.0/8)', current($validator->getMessages()));
		self::assertTrue($validator->isValid('127.0.0.1'));
	}

	public function testIsValidAllowHostsFullRanges() {
		$validator = new Access(array('allow_hosts' => '10.0.0.0/8,11.0.0.0/8'));
		self::assertTrue($validator->isValid('10.1.1.1'));
		self::assertTrue($validator->isValid('11.1.1.1'));
		self::assertFalse($validator->isValid('12.1.1.1'));
		self::assertEquals('Host 12.1.1.1 is not in any allowed ip range (10.0.0.0/8,11.0.0.0/8)', current($validator->getMessages()));
		self::assertTrue($validator->isValid('127.0.0.1'));
	}

	public function testIsValidAllowHostsFullRangeDeniedException() {
		$validator = new Access(array('allow_hosts' => '10.0.0.0/8', 'deny_hosts' => '10.1.1.1/32'));
		self::assertFalse($validator->isValid('10.1.1.1'));
		self::assertEquals('Host 10.1.1.1 is denied access (10.1.1.1/32)', current($validator->getMessages()));
		self::assertTrue($validator->isValid('10.1.1.2'));
		self::assertFalse($validator->isValid('11.1.1.1'));
		self::assertEquals('Host 11.1.1.1 is not in any allowed ip range (10.0.0.0/8)', current($validator->getMessages()));
		self::assertTrue($validator->isValid('127.0.0.1'));
	}
}