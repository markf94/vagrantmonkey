<?php

namespace ZendServer\Authentication\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Users\Identity;

class Azure implements AdapterInterface {
	
	/* (non-PHPdoc)
	 * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
	 */
	public function authenticate() {
    	/// generate identity
    	return new Result(Result::SUCCESS, new Identity('admin', 'administrator'));
	}
}