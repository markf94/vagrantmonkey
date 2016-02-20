<?php

namespace Application\Controller\Plugin;
use Acl\Db\MapperGroups;

use ZendServer\Authentication\Adapter\IdentityGroupsProvider;

use Users\Identity;

use ZendServer\FS\FS;

use Zend\Authentication\Adapter\AdapterInterface;


use Zend\Mvc\Controller\Plugin\AbstractPlugin,
Application\Module,
ZendServer\Log\Log,
Zend\Authentication\AuthenticationService,
Zend\Crypt\Hash,
ZendServer\Exception;


use Zend\Authentication\Adapter as Adapter;

class Authentication extends AbstractPlugin
{
  	/**
	 * @var AuthAdapter
	 */
	private $authAdapter;

	/**
	 * @var AuthenticationSerivce
	 */
	private $authService;
	
	/**
	 * @var RoleData
	 */
	private $RoleData;	
	
	/**
	 * @var MapperGroups
	 */
	private $groupsMapper;
	
	/**
	 * Proxy convenience method
	 *
	 * @return bool
	 */	
	public function hasIdentity()
	{
		return $this->getAuthService()->hasIdentity();
	}
	
	/**
	 * Proxy convenience method 
	 * 
	 * @return \Users\Identity
	 * @throws \ZendServer\Exception
	 */
	public function getIdentity()
	{
		if (! $this->hasIdentity()) {
			throw new \ZendServer\Exception(_t('No authenticated identity available'));
		}
		return $this->getAuthService()->getIdentity();
	}
	
	/**
	 * @return string 
	 */
	public function getRole() {
		return $this->getIdentity()->getRole(); 
	}
	
	public function getUser() {
		return $this->getIdentity()->getIdentity();
	}	
	
	/**
	 * Only authenticate the credentials, do not actually make session changes
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function authenticateOnly($username, $password) {
		$adapter = $this->getAuthAdapter();
		$adapter->setIdentity($username);
		$adapter->setCredential($password);
		
		$result = $adapter->authenticate();
		return $result->isValid();
	}
	
	/**
	 * 
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function authenticate($username, $password) {		
		$adapter = $this->getAuthAdapter();
		$adapter->setIdentity($username);
		$adapter->setCredential($password);
		
		$result = $this->getAuthService()->authenticate($adapter);
		if (! $result->isValid()) {
			Log::err('authentication failed: ' . implode(', ', $result->getMessages()));
			return false;
		}
		
		if ($adapter instanceof IdentityGroupsProvider) {

			/// lower case both lists for case insensitivity
			$groups = array_map('strtolower', $adapter->getIdentityGroups());
			$mappedGroups = array_map('strtolower', $this->getGroupsMapper()->findAllMappedRoles());
			
			$role = 'guest';
			$roles = array_intersect($mappedGroups, $groups);
			$roles = array_keys($roles);
			if (0 < count($roles)) {
				$role = current($roles);
			}
			
			if ($role == 'guest' || $role == 'developerLimited') {
				// implictly grant developerLimited role to a user which
				// has an application group
				$mappedApps = $this->getGroupsMapper()->findAllMappedApplications();
				$apps = array_intersect($mappedApps, $groups);
				$apps = array_keys($apps);
				if (0 < count($apps)) {
					$role = 'developerLimited';
				}
			}
			
			$result->getIdentity()->setRole($role);
		}
		$result->getIdentity()->setLoggedIn();
		return true;
		
	}
   
	/**
	 * Set authService.
	 *
	 * @param AuthenticationService $authService
	 */
	public function setAuthService(AuthenticationService $authService)
	{
		$this->authService = $authService;
		return $this;
	}
	
	/**
	 * Set authAdapter.
	 *
	 * @param  AdapterInterface $authAdapter
	 */
	public function setAuthAdapter(AdapterInterface $authAdapter)
	{
		$this->authAdapter = $authAdapter;
		return $this;
	}

	/**
	 * @return \Acl\Db\MapperGroups $groupsMapper
	 */
	public function getGroupsMapper() {
		return $this->groupsMapper;
	}

	/**
	 * @param \Acl\Db\MapperGroups $groupsMapper
	 * @return Authentication
	 */
	public function setGroupsMapper($groupsMapper) {
		$this->groupsMapper = $groupsMapper;
		return $this;
	}

	/**
	 * Set RoleData.
	 *
	 * @param array $data
	 */
	public function setRoleData(array $data)
	{
		$this->RoleData = $data;
		return $this;
	}
	   
	/**
	 * Get authService.
	 *
	 * @return AuthenticationService
	 */
	public function getAuthService()
	{
		return $this->authService;
	}

	/**
	 * 
	 * @throws \ZendServer\Exception
	 * @return Zend\Authentication\Adapter\AbstractAdapter
	 */
	private function getAuthAdapter()
	{
		return $this->authAdapter;
	}
}