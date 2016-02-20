<?php

namespace ZendServer\Validator;

use ZendServer\Validator\AbstractZendServerValidator;

class Boolean extends AbstractZendServerValidator {
	
	const INVALID  = 'invalid';
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID  => "'%value%' is not a \"boolean\" value: pass either '0' or '1'", // better to narrow the user to 0 & 1 only
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
		
		if (! in_array($value, array('0', '1', 'On', 'Off', ''))) { // being fairly flexible
			$this->error(self::INVALID);
			return false;
		}
		
		return true;
	}
	
}