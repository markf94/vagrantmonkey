<?php

namespace ZendServer\Permissions\Assertion;

use ZendServer\Log\Log;

use Configuration\License\License;

use Zend\Permissions\Acl\Resource\ResourceInterface;

use Zend\Permissions\Acl\Role\RoleInterface;

use Zend\Permissions\Acl\Acl;

use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Configuration\License\LicenseAwareInterface;

class Edition implements AssertionInterface, LicenseAwareInterface {
	/**
	 * @var License
	 */
	private $license;
	
	/**
	 * @var Acl
	 */
	private $acl;
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Permissions\Acl\Assertion\AssertionInterface::assert()
	 */
	public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null) {
		$licenseRole = strtoupper($this->license->getEdition());
		if ($this->acl->hasResource($resource)) {
			$result = $this->acl->isAllowed("edition:{$licenseRole}", $resource, $privilege);
			Log::debug("Edition assertion: isAllowed 'edition:{$licenseRole}', '$resource', '$privilege': " . ($result ? 'true' : 'false'));
			return $result;
		} else {
			return true;
		}
	}
	/*
	 * (non-PHPdoc)
	 * @see \ZendServer\License\LicenseAwareInterface::setLicense()
	 */
	public function setLicense(License $license) {
		$this->license = $license;
		return $this;
	}
	
	/**
	 * @param Acl $acl
	 * @return \ZendServer\Permissions\Assertion\Edition
	 */
	public function setAcl(Acl $acl) {
		$this->acl = $acl;
		return $this;
	}
}

