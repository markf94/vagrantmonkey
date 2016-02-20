<?php
namespace Application\Validators;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	ZendServer\Validator\HostWithPort;

class DefaultServer extends HostWithPort {
	
	public function __construct($options = array()) {
		parent::__construct($options);
	}
	
	public function isValid($value) {
		if ($value == '<default-server>') {
			return true;
		}
		
		return parent::isValid($value);
	}
}
