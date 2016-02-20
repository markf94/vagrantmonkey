<?php
namespace DevBar\Validator;

use ZendServer\PHPUnit\TestCase;
use ZendServer\Exception;

require_once 'tests/bootstrap.php';

class BaseUrlAccessTest extends TestCase
{
	public function testIsValidEmptyBaseUrl() {
		self::setExpectedException('ZendServer\Exception');
		$validator = new BaseUrlAccess();
	}
	
	public function testIsValidBaseUrlBadInit() {
		self::setExpectedException('ZendServer\Exception');
		new BaseUrlAccess(array('baseUrl' => 'not a url'));
	}
	
	public function testIsValidBaseUrlBadInput() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com'));
		self::assertFalse($validator->isValid('/relative/url'));
		self::assertFalse($validator->isValid('deny anything'));
	}
	
	public function testIsValidBaseUrlBasic() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com'));
		self::assertTrue($validator->isValid('http://test.com'));
		self::assertFalse($validator->isValid('http://test2.com'));
	}
	
	public function testIsValidBaseUrlScheme() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com'));
		self::assertTrue($validator->isValid('http://test.com'));
		self::assertTrue($validator->isValid('https://test.com'));
	}
	
	public function testIsValidBaseUrlBasicVhost() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com'));
		self::assertTrue($validator->isValid('http://test.com'));
		self::assertFalse($validator->isValid('http://www.test.com'));
	}
	
	public function testIsValidBaseUrlPath() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path2'));
		/// no path
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com'));
		self::assertTrue($validator->isValid('http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path2'));
		self::assertFalse($validator->isValid('http://test.cox'));
	}
	
	public function testIsValidBaseUrlPathChildren() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path/2'));
		self::assertTrue($validator->isValid('http://test.com/path/to/a/file'));
		self::assertTrue($validator->isValid('http://test.com/path2/to/a/file'));
		self::assertFalse($validator->isValid('http://test.com/pax'));
	}
	
	public function testIsValidBaseUrlFilepathChildren() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com/path/f'));
		self::assertFalse($validator->isValid('http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path/f'));
		self::assertTrue($validator->isValid('http://test.com/path/file'));
	}
	
	public function testIsValidBaseUrlDontCareAboutQuery() {
		$validator = new BaseUrlAccess(array('baseUrl' => 'http://test.com/path'));
		self::assertTrue($validator->isValid('http://test.com/path?asdasd=asd'));
		self::assertFalse($validator->isValid('http://test2.com/path?asdasd=asd'));
		self::assertTrue($validator->isValid('http://test.com/path/2?asdasd=asd'));
		self::assertTrue($validator->isValid('http://test.com/path/to/a/file?asdasd=asd'));
	}
}