<?php

namespace ZendServer\Validator;

use Zend\Uri\Http,
	Zend\Validator\AbstractValidator;

class Url extends AbstractValidator {
	
	const INVALID_URL = 'invalidUrl';
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::INVALID_URL  => "'%value%' is not a valid HTTP URL",
	);
	
	public function __construct($options = array()) {
			
		parent::__construct($options);
	}
	
	public function isValid($value) {
		$this->setValue($value);
	
		$url = new Http($value);
		if (!$url->isValid()) {
			$this->error(self::INVALID_PATH);
			return false;
			
		}
		return true;
	}
}

?>