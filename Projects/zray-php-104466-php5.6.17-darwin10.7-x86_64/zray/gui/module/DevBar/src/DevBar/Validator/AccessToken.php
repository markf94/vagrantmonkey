<?php

namespace DevBar\Validator;

use Zend\Validator\AbstractValidator;
use ZendServer\Validator\IpRange;
use ZendServer\Log\Log;
use Zend\Uri\UriFactory;
use ZendServer\Exception;
use Zend\Uri\Exception\InvalidArgumentException;
use Zend\Validator\Uri;
use Zend\Uri\Http;
use Zend\Crypt\Hash;

class AccessToken extends AbstractValidator {
	const INVALID_TOKEN = 'invalidToken';
	
	protected $messageTemplates = array(
		self::INVALID_TOKEN => 'Provided token \'%value%\' is invalid'
	);
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		
		$this->setValue($value);
		if (preg_match('#^[[:digit:]a-fA-F]{'. Hash::getOutputSize('sha256') .'}$#', $value) > 0) {
			return true;
		}
		$this->error(self::INVALID_TOKEN);
		return false;
	}
}