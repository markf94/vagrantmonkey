<?php

namespace ZendServer\Validator;

use Zend\Validator\AbstractValidator;

class NullValidator extends AbstractValidator {
	
	/* (non-PHPdoc)
	 * @see \Zend\Session\Validator::isValid()
	 */
	public function isValid($value) {
		return true;
	}
}