<?php
namespace DevBar\Validator;

use ZendServer\PHPUnit\TestCase;
use ZendServer\Exception;

require_once 'tests/bootstrap.php';

class PageIdTest extends TestCase
{
	public function testIsValidEmpty() {
		$validator = new PageId();
		self::assertFalse($validator->isValid(''));
	}
	
	public function testIsValidValid() {
		$validator = new PageId();
		self::assertTrue($validator->isValid('0@0@0@0'));
		self::assertTrue($validator->isValid('499@30301@1406455690@0'));
		self::assertTrue($validator->isValid('499@30301@1406455690@3'));
	}
	
	public function testIsValidInValid() {
		$validator = new PageId();
		self::assertFalse($validator->isValid('0'));
		self::assertFalse($validator->isValid('0@'));
		self::assertFalse($validator->isValid('0@0'));
		self::assertFalse($validator->isValid('0@0@'));
		self::assertFalse($validator->isValid('0@0@0'));
		self::assertFalse($validator->isValid('0@0@0@'));
		self::assertFalse($validator->isValid('a@a@a@a'));
		self::assertFalse($validator->isValid('0@a@a@a'));
		self::assertFalse($validator->isValid('0@0@a@a'));
		self::assertFalse($validator->isValid('0@0@0@a'));
		self::assertFalse($validator->isValid('0@ @0@0'));
	}
	
	public function testIsValidInjection() {
		$validator = new PageId();
		self::assertFalse($validator->isValid('0<script>alert(\'78\')</script>'));
	}
}