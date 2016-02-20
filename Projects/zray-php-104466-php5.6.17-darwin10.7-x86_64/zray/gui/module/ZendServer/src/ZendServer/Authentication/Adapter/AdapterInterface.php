<?php

namespace ZendServer\Authentication\Adapter;

use Users\Identity;

use Zend\Authentication\Adapter\AdapterInterface as baseAdapterInterface;
 
interface AdapterInterface extends baseAdapterInterface {
	/**
	 * @param Identity $identity
	 */
	public function setIdentity(Identity $identity);
	
	/**
	 * @param string $credential
	 */
	public function setCredential($credential);
}

