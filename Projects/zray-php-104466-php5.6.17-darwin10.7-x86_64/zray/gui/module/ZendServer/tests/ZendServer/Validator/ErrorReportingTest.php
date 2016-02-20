<?php

namespace ZendServer\Validator;


use ZendServer\PHPUnit\TestCase;
require_once 'tests/bootstrap.php';

class ErrorReportingTest extends TestCase {
    public function testIsValid() {
        $validator = new ErrorReporting();
        self::assertTrue($validator->isValid('E_ALL & ~E_DEPRECATED & ~E_STRICT'));
        self::assertTrue($validator->isValid('1024'));
        self::assertFalse($validator->isValid('This is not a valid code string'));
    }
}
