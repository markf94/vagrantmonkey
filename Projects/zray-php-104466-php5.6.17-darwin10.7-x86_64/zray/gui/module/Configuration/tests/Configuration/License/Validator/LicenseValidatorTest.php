<?php

namespace Configuration\License\Validator;
use ZendServer\Exception;

use Configuration\License\License;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class LicenseValidatorTest extends TestCase
{

	public function testIsValid() {
		$validator = new LicenseValidator('user');
		$wrapper = $this->getMock('Configuration\License\Wrapper');
		$wrapper->expects($this->once())->method('getSerialNumberInfo')
				->with('12345678901234567890123456789012', 'user')
				->will($this->returnValue(new License(array(
							'user_name' => 'user',
							'signature_invalid' => false,
							'license_expired' => false,
							'license_ok' => true,
							'edition' => '6', // basic
						))));
		$validator->setUtilsWrapper($wrapper);
				
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())->method('isCluster')->withAnyParameters()->will($this->returnValue(false));
		$validator->setEdition($edition);
		
		self::assertTrue($validator->isValid('12345678901234567890123456789012'));
	}
	
	public function testIsValidFailLicenseString() {
		$validator = new LicenseValidator('user');
		$wrapper = $this->getMock('Configuration\License\Wrapper');
		$wrapper->expects($this->never())->method('getSerialNumberInfo');
		$validator->setUtilsWrapper($wrapper);

		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())->method('isCluster')->withAnyParameters()->will($this->returnValue(false));
		$validator->setEdition($edition);
		
		self::assertFalse($validator->isValid('1234567890123456789012345678901'), 'Too short license key');
		$errors = $validator->getMessages();
		self::assertArrayHasKey(LicenseValidator::INVALID_STRING_LENGTH, $errors);

		self::assertFalse($validator->isValid('1234567890123456789012345678901!'), 'Illegal characters');
		$errors = $validator->getMessages();
		self::assertArrayHasKey(LicenseValidator::INVALID_STRING_CHARACTERS, $errors);
	}
	
	public function testIsValidFailUtilsOutputExpired() {
		$validator = new LicenseValidator('user');
		$wrapper = $this->getMock('Configuration\License\Wrapper');
		$wrapper->expects($this->once())->method('getSerialNumberInfo')
				->will($this->returnValue(new License(array(
							'user_name' => 'user',
							'signature_invalid' => false,
							'license_expired' => true,
							'license_ok' => false,
						))));
		$validator->setUtilsWrapper($wrapper);

		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())->method('isCluster')->withAnyParameters()->will($this->returnValue(false));
		$validator->setEdition($edition);
		
		self::assertFalse($validator->isValid('12345678901234567890123456789012'));
		$errors = $validator->getMessages();
		self::assertArrayHasKey(LicenseValidator::LICENSE_EXPIRED, $errors);

	}
	
	public function testIsValidFailUtilsOutputSignature() {
		$validator = new LicenseValidator('user');
		$wrapper = $this->getMock('Configuration\License\Wrapper');
		$wrapper->expects($this->once())->method('getSerialNumberInfo')
				->will($this->returnValue(new License(array(
							'user_name' => 'user',
							'signature_invalid' => true,
							'license_expired' => false,
							'license_ok' => false,
						))));
		$validator->setUtilsWrapper($wrapper);

		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())->method('isCluster')->withAnyParameters()->will($this->returnValue(false));
		$validator->setEdition($edition);
		
		self::assertFalse($validator->isValid('12345678901234567890123456789012'));
		$errors = $validator->getMessages();
		self::assertArrayHasKey(LicenseValidator::INVALID_SIGNATURE, $errors);

	}
	
	public function testIsValidFailUtilsOutputNotok() {
		$validator = new LicenseValidator('user');
		$wrapper = $this->getMock('Configuration\License\Wrapper');
		$wrapper->expects($this->once())->method('getSerialNumberInfo')
				->will($this->returnValue(new License(array(
							'user_name' => 'user',
							'signature_invalid' => false,
							'license_expired' => false,
							'license_ok' => false,
						))));
		$validator->setUtilsWrapper($wrapper);

		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())->method('isCluster')->withAnyParameters()->will($this->returnValue(false));
		$validator->setEdition($edition);
		
		self::assertFalse($validator->isValid('12345678901234567890123456789012'));
		$errors = $validator->getMessages();
		self::assertArrayHasKey(LicenseValidator::LICENSE_NOT_OK, $errors);

	}
	
	public function testIsValidFailUtilsPropagateException() {
		$validator = new LicenseValidator('user');
		$wrapper = $this->getMock('Configuration\License\Wrapper');
		$wrapper->expects($this->once())->method('getSerialNumberInfo')
				->will($this->throwException(new Exception()));
		$validator->setUtilsWrapper($wrapper);

		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())->method('isCluster')->withAnyParameters()->will($this->returnValue(false));
		$validator->setEdition($edition);
		
		self::setExpectedException('ZendServer\Exception');
		$validator->isValid('12345678901234567890123456789012');

	}
	
}

