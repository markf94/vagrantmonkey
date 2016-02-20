<?php

namespace ZendServer\Validator;

use Zend\Uri\Exception\ExceptionInterface;

use Zend\Uri\Http,
	ZendServer\Log\Log,
	Zend\Validator\AbstractValidator,
	Zend\Uri\Exception;

class UriPath extends AbstractValidator {
	const INVALID_PATH  = 'invalidPath';
    
	/**
	 * @var string
	 */
	protected $value;
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
				self::INVALID_PATH  => "'%value%' is not a valid HTTP path",
			);
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);

		$result = false;
		try {
			$uri = new Http();
			$result = $uri->validatePath($value);
		} catch (ExceptionInterface $e) {
			$this->error(self::INVALID_PATH);
			$result = false;
		}
		
		if (false === $result) {
			$this->error(self::INVALID_PATH);
			return false;
		}
		return true;
	}
	
}