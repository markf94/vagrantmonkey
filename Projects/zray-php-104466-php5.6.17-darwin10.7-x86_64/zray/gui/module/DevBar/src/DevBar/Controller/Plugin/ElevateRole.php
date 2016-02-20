<?php

namespace DevBar\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Users\Identity;
use Application\Module as appModule;
use DevBar\Module;
use Zend\Authentication\AuthenticationService;
use ZendServer\Log\Log;
use Users\IdentityAwareInterface;

class ElevateRole extends AbstractPlugin implements IdentityAwareInterface {
	
	/**
	 * @var Identity
	 */
	private $identity;
	
	/**
	 * @var AuthenticationService
	 */
	private $authService;
	
	/**
	 * Elevate the current identity if needed. Returns true if elevation was performed or false if elevation was not needed.
	 * @return boolean
	 */
	public function __invoke() {
		$identity = $this->identity;
		if ($identity->getRole() == appModule::ACL_ROLE_GUEST) {
			$identity->setRole(Module::ACL_ROLE_DEVBAR);
			$this->authService->getStorage()->write($identity);
			return true;
		}
		return false;
	}
	
	/**
	 * @return AuthenticationService
	 */
	public function getAuthService() {
		return $this->authService;
	}

	/**
	 * @return Identity
	 */
	public function getIdentity() {
		return $this->identity;
	}

	/**
	 * @param AuthenticationService $authService
	 * @return \Zend\Authentication\AuthenticationService
	 */
	public function setAuthService($authService) {
		$this->authService = $authService;
		return $this->authService;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Users\IdentityAwareInterface::setIdentity()
	 */
	public function setIdentity(Identity $identity) {
		$this->identity = $identity;
		return $this;
	}
}