<?php
namespace ZendServer\Http\Header;


use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class SetCookieTest extends TestCase
{
	public function testFromStringDoesNotThrowExceptionOnEmptyValue() {
		SetCookie::fromString('Set-Cookie: empty_cookie=; something=asdf');
		SetCookie::fromString('Set-Cookie: empty_cookie= ; something=asdf');
	}
}