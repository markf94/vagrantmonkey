<?php

require_once dirname(__FILE__) . '/Logger.php';
require_once dirname(__FILE__) . '/Exception/Interface.php';

class ZendDeployment_Exception extends ZendDeployment_Exception_Interface {
	
	public function __construct($msg, $code = 0) {
		parent::__construct($msg, $code);
		
		ZERROR("Zend Deployment Exception: " . $msg);
		ZERROR($this->getTraceAsString());	
	}
	
}

