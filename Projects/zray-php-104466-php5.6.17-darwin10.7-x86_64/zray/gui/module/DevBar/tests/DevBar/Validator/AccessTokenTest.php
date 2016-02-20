<?php
namespace DevBar\Validator;

use ZendServer\PHPUnit\TestCase;
use Zend\Crypt\Hash;

require_once 'tests/bootstrap.php';

class AccessTokenTest extends TestCase
{
	public function testIsValidAccessTokenEmpty() {
		$validator = new AccessToken();
		self::assertFalse($validator->isValid(''));
		self::assertFalse($validator->isValid(0));
		self::assertFalse($validator->isValid('0'));
		self::assertFalse($validator->isValid(null));
		
		self::assertEquals(AccessToken::INVALID_TOKEN, key($validator->getMessages()));
	}
	
	public function testIsValidAccessTokenCorrect() {
		$validator = new AccessToken();
		self::assertTrue($validator->isValid(Hash::compute('sha256','')));
		self::assertTrue($validator->isValid(Hash::compute('sha256','1234567890')));
		self::assertTrue($validator->isValid(Hash::compute('sha256','abcdefghijk')));
	}
	
	public function testIsValidAccessTokenInvalid() {
		$validator = new AccessToken();
		self::assertFalse($validator->isValid(md5('')));
		self::assertFalse($validator->isValid(base64_encode('1234567890')));
		self::assertFalse($validator->isValid(crc32('abcdefghijk')));
		
		self::assertEquals(AccessToken::INVALID_TOKEN, key($validator->getMessages()));
	}
	
	public function testIsValidAccessTokenHTMLInjection() {
		$validator = new AccessToken();
		self::assertFalse($validator->isValid(Hash::compute('sha256','').'<script>alert(\'asd\')</script>'));
		self::assertFalse($validator->isValid('da39a3ee5<script>alert(\'asd\')</script>'));
		
		self::assertEquals(AccessToken::INVALID_TOKEN, key($validator->getMessages()));
	}
}