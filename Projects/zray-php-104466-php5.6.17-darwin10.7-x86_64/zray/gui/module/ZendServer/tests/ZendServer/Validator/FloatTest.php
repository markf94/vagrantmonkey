<?php

namespace ZendServer\Validator;


use ZendServer\PHPUnit\TestCase;
require_once 'tests/bootstrap.php';

class FloatTest extends TestCase {
    public function testIsValid() {
        $validator = new FloatValidator();
        self::assertFalse($validator->isValid('E_ALL & ~E_DEPRECATED & ~E_STRICT'));
        self::assertTrue($validator->isValid('1024'));
        self::assertTrue($validator->isValid('1024.2'));
        self::assertTrue($validator->isValid('1024.24'));
        self::assertTrue($validator->isValid('0'));
        self::assertTrue($validator->isValid(1024));
        self::assertTrue($validator->isValid(1024.2));
        self::assertTrue($validator->isValid(1024.24));
        self::assertTrue($validator->isValid(0));
        self::assertFalse($validator->isValid('1024.24.2'));
        self::assertFalse($validator->isValid('This is not a valid code string'));
        self::assertFalse($validator->isValid('ABC'));
    }
}
