<?php

namespace ZendServer\Validator;

use ZendServer\Validator\AbstractZendServerValidator;

class Integer extends AbstractZendServerValidator {
	
	const INVALID  = 'invalid';
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID  => "'%value%' is not integer value",
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
		
		// check if the value is integer: numeric and not float
		if (!is_numeric($value) || ($value != round($value))) {
			$this->error(self::INVALID, $value);
			return false;
		}
		
		return true;
	}
	
}