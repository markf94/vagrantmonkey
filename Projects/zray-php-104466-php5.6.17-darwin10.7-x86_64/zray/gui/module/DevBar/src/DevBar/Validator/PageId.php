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

class PageId extends AbstractValidator {
	
	const INVALID_PAGEID = 'INVALID_PAGEID';
	
	protected $messageTemplates = array(
		self::INVALID_PAGEID => '\'%value%\' is not a valid Z-Ray Page id',
	);
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		if (preg_match('#^\d+@\d+@\d+@\d+$#', $value) == 0) {
			$this->error(self::INVALID_PAGEID);
			return false;
		}
		return true;
	}
}