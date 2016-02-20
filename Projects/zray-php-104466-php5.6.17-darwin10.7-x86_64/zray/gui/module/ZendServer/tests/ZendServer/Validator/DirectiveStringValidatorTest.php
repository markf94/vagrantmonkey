<?php

namespace ZendServer\Validator;
use ZendServer\Exception;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class LicenseStringValidatorTest extends TestCase
{

	public function testIsValid() {
		$validator = new DirectiveStringValidator();
		self::assertTrue($validator->isValid(''));
		self::assertTrue($validator->isValid('123abcAB'));
		self::assertTrue($validator->isValid('123abc'));
		self::assertTrue($validator->isValid('123:abc'));
		self::assertTrue($validator->isValid('123;abc'));
		self::assertTrue($validator->isValid('123-abc'));
		self::assertTrue($validator->isValid('123_abc'));
		self::assertTrue($validator->isValid('123/abc'));
		self::assertTrue($validator->isValid('123+abcA'));
		
		
		self::assertTrue($validator->isValid('-1'));
		self::assertTrue($validator->isValid('#000000'));
		self::assertTrue($validator->isValid('104@127.0.0.1@zs6@1359989078.42'));
		self::assertTrue($validator->isValid('%Y-%m-%d %H:%M:%S'));
		self::assertTrue($validator->isValid('E_ALL & ~E_DEPRECATED & ~E_STRICT'));
		self::assertTrue($validator->isValid('.:/usr/local/zend/share/ZendFramework/library:/usr/local/zend/share/pear'));
		self::assertTrue($validator->isValid('"C:\Program Files (x86)\Zend\ZendServer\lib\codetracing"'));
		self::assertTrue($validator->isValid('127.0.0.1'));
		self::assertTrue($validator->isValid('codetracing/dump'));
		self::assertTrue($validator->isValid('a=href,area=href,frame=src,input=src,form=fakeentry'));
		self::assertTrue($validator->isValid('N;MODE;/path'));
		self::assertTrue($validator->isValid('@/var/www/blacklist.txt'));
		
		
		self::assertTrue($validator->isValid('127.0.0.0/8,10.0.0.0/8,192.168.0.0/16,172.16.0.0/12'));
		self::assertTrue($validator->isValid('10.9.183.85,10.9.183.83,10.9.183.84'));
		self::assertFalse($validator->isValid('10.9.183.83,"10.9.183.84,10.9.183.85"'));
		self::assertTrue($validator->isValid('"10.9.183.83,10.9.183.84,10.9.183.85"'));
		
	}
	
	
	
}

