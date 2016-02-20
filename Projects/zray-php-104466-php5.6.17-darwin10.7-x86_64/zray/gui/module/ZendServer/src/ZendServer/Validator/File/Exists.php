<?php

namespace ZendServer\Validator\File;

use Zend\Validator\File\Exists as baseExists;

class Exists extends baseExists {
	/* (non-PHPdoc)
	 * @see \Zend\Validator\File\Exists::isValid()
	 */
	public function isValid($value, $file = null) {
		// $file is an aggregated list of all other parameters - this confuses the validator and causes it to fail in a silly way
		// Do not pass $file to avoid this issue, at least in places where we only need to check a local file 
		return parent::isValid($value);
	}
}

