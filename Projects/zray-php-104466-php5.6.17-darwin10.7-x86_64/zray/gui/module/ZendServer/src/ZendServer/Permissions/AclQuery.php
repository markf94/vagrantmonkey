<?php

namespace ZendServer\Permissions;

use Users\Identity;

use Zend\Permissions\Acl\Acl;
use Configuration\License\License;
use ZendServer\Log\Log;
use Configuration\License\LicenseAwareInterface;
use Zend\Authentication\AuthenticationService;

class AclQuery implements LicenseAwareInterface {
	/**
	 * @var License
	 */
	private $license;
	
	/**
	 * @var Acl
	 */
	private $acl;
	
	/**
	 * 
	 * @var AuthenticationService
	 */
	private $authService;
	
	/**
	 * @var Acl
	 */
	private $editionAcl;
	
	/**
	 * @var boolean
	 */
	private $enabled = true;
	
	/**
	 * @var string
	 */
	private $overrideRole;
	
	/**
	 * @param ResourceInterface|string $resource
	 * @return boolean
	 */
	public function hasResource($resource) {
		return $this->acl->hasResource($resource);
	}
	
	/**
	 * @param string $resource
	 * @param string $privilege
	 */
	public function isAllowed ($resource = null, $privilege = null) {
		if ($this->enabled) {
			return $this->isAllowedEdition($resource, $privilege) && $this->isAllowedIdentity($resource, $privilege);
		}
		return true;
	}
	
	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return boolean
	 */
	public function isAllowedIdentity($resource = null, $privilege = null) {
		if ($this->enabled) {
			if (! $this->hasResource($resource)) {
				return false;
			}
			
			$licenseRole = $this->overrideRole ? $this->overrideRole : $this->getIdentity()->getRole();
			$result = $this->acl->isAllowed($licenseRole, $resource, $privilege);
			Log::debug("Role assertion: isAllowed '{$licenseRole}', '$resource', '$privilege': " . ($result ? 'true' : 'false'));
			return $result;
		}
		return true;
	}
	
	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return boolean
	 */
	public function isAllowedEdition($resource = null, $privilege = null) {
		if ($this->enabled) {
			$licenseRole = strtoupper($this->license->getEdition());
			if ($this->editionAcl->hasResource($resource)) {
				$result = $this->editionAcl->isAllowed("edition:{$licenseRole}", $resource, $privilege);
				Log::debug("Edition assertion: isAllowed 'edition:{$licenseRole}', '$resource', '$privilege': " . ($result ? 'true' : 'false'));
				return $result;
			} else {
				return true;
			}
		}
		return true;
	}
	/**
	 * @param Acl $acl
	 * @return AclQuery
	 */
	public function setAcl($acl) {
		$this->acl = $acl;
		return $this;
	}

	/**
	 * @param AuthenticationService $authService
	 * @return \ZendServer\Permissions\AclQuery
	 */
	public function setAuthService($authService) {
		$this->authService = $authService;
		return $this;
	}
	
	/**
	 * @return \Users\Identity
	 */
	public function getIdentity() {
		if ($this->authService->hasIdentity()) {
			return $this->authService->getIdentity();
		}
		return new Identity('Unknown', 'guest');
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
	public function setEditionAcl(Acl $acl) {
		$this->editionAcl = $acl;
		return $this;
	}
	
	/**
	 * @param boolean $enabled
	 * @return \ZendServer\Permissions\AclQuery
	 */
	public function setEnabled($enabled = true) {
		$this->enabled = $enabled;
		return $this;
	}
	
	/**
	 * @param string $overrideRole
	 * @return \ZendServer\Permissions\AclQuery
	 */
	public function setOverrideRole($overrideRole) {
		$this->overrideRole = $overrideRole;
		return $this;
	}
}

