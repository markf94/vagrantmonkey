<?php

namespace ZendServer\Validator;

use ZendServer\Validator\AbstractZendServerValidator;

class FloatValidator extends AbstractZendServerValidator {
	
	const INVALID  = 'invalid';
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID  => "'%value%' is not float value",
	);
		
	public function __construct($options = array()) {
		parent::__construct($options);
	}
	
	/**
	 * @param string $value
	 * @return Boolean
	 */
	public function isValid($value) {
		$this->setValue($value);
		
		// check if the value is numeric (integer or float) 
		if (false === filter_var($value, FILTER_VALIDATE_FLOAT | FILTER_VALIDATE_INT)) {
			$this->error(self::INVALID, $value);
			return false;
		}
	
		return true;
	}
	
}