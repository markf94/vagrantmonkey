<?php

namespace DevBar\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Users\Identity;
use Application\Module as appModule;
use DevBar\Module;
use Zend\Authentication\AuthenticationService;
use ZendServer\Log\Log;
use Users\IdentityAwareInterface;

class DemoteRole extends AbstractPlugin implements IdentityAwareInterface {
	
	/**
	 * @var Identity
	 */
	private $identity;
	
	/**
	 * @var AuthenticationService
	 */
	private $authService;
	
	/**
	 * @return Identity
	 */
	public function __invoke() {
		$identity = $this->identity;
		if ($identity->getRole() == Module::ACL_ROLE_DEVBAR) {
			$identity->setRole(appModule::ACL_ROLE_GUEST);
			$this->authService->getStorage()->write($identity);
		}
		return $identity;
	}
	
	/**
	 * @return AuthenticationService
	 */
	public function getAuthService() {
		return $this->authService;
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