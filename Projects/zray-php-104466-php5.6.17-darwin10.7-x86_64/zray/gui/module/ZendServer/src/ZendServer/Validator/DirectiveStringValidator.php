<?php

namespace ZendServer\Validator;

use Zend\Validator\AbstractValidator;

class DirectiveStringValidator extends AbstractValidator {
	
	const INVALID_STRING  = 'invalid string';
	
	/**
	 * @var string
	 */
	protected $value;
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID_STRING  => "'%value%' is not a valid string value for a directive. Directive string values may be alpha numeric, punctuation or printable characters except for quotes.",
	);
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	*/
	public function isValid($value) {
		$this->setValue($value);
	
		if ((preg_match('/^"?[[:print:]]*"?$/', $value) > 0) && (preg_match('/^"?[^"]*"?$/', $value) > 0)) {
			return true;
		}
		
		$this->error(self::INVALID_STRING);
		return false;
	}
	
}