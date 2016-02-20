<?php

namespace WebAPI\Authentication\Adapter;

use ZendServer\Authentication\Adapter\Ldap;

use ZendServer\Exception;

use ZendServer\Log\Log;

use Zend\Authentication\Result;

use Users\Identity;
use Zend\Ldap as ZendLdap;
use Zend\Ldap\Ldap as ldapCon;

class SignatureGroups extends SignatureAbstract {
	
	/**
	 * @var \Zend\Config\Config
	 */
	private $ldapConfig;
	
	/**
	 * @var \Acl\Db\MapperGroups
	 */
	private $mapperGroups;
	/**
	 * @var string
	 */
	private $groupsAttribute;
	
	/* (non-PHPdoc)
	 * @see \WebAPI\Authentication\Adapter\Signature::collectGroups()
	 */
	protected function collectGroups(Identity $identity) {
		/// collect groups from ldap
		try {
			$groups = $this->getAccountGroups($identity);
			$identity->setGroups($groups);
				
			$mapperGroups = $this->getMapperGroups(); /* @var $mapperGroups \Acl\Db\MapperGroups */
			$mappedGroups = $mapperGroups->findAllMappedRoles();
			$roles = array_intersect($mappedGroups, $groups);
			$roles = array_keys($roles);
			if (0 < count($roles)) {
				$identity->setRole(current($roles));
			} else {
				/// check if the user has any mapped applications, he may get developerLimited implicitly
				$mappedApps = $mapperGroups->findAllMappedApplications();
				$apps = array_intersect($mappedApps, $groups);
				$apps = array_keys($apps);
				if (0 < count($apps)) {
					$identity->setRole('developerLimited');
				}
			}
		} catch (Exception $ex) {
			$identity->setRole('guest');
			Log::warn("Could not connect to ldap server, user may have no application permissions: {$ex->getMessage()}");
			Log::debug($ex);
		} catch (ZendLdap\Exception\LdapException $ex) {
			$identity->setRole('guest');
			Log::warn("Could not retrieve ldap groups, user may have no application permissions: {$ex->getMessage()}");
			Log::debug($ex);
		}
		return $identity;
	}

	/**
	 * @param Identity $identity
	 * @return array
	 */
	private function getAccountGroups(Identity $identity) {
		
		$ldap = new ZendLdap\Ldap($this->getLdapConfig()->toArray());
		$ldap->connect();
		$ldapCredentials = $this->getLdapConfig();
		$ldap->bind($ldapCredentials->username, $ldapCredentials->password);
		
		$username = $identity->getUsername();
		$dn = $ldap->getCanonicalAccountName($username, ZendLdap\Ldap::ACCTNAME_FORM_DN);
		$groupsData = $ldap->getEntry($dn, array($this->getGroupsAttribute()), false);
		$groupsDn = array();
		
		if (isset($groupsData[$this->getGroupsAttribute()])) {
			if (is_array($groupsData[$this->getGroupsAttribute()]) || $groupsData[$this->getGroupsAttribute()] instanceof \Traversable) {
				/// Active directory expected response
				$groupsDn = $groupsData[$this->getGroupsAttribute()];
			} else {
				/// Posix LDAP expected response, single group value
				$groupsDn = array($groupsData[$this->getGroupsAttribute()]);
			}
		} else {
			/// fail-over and try to retrieve operational attributes
			$results = $ldap->search("(uid={$this->getUsername()})", $this->authenticatedDn, ldapCon::SEARCH_SCOPE_BASE, array($this->getGroupsAttribute()));
			$results = $results->current();
			if (isset($results[$this->getGroupsAttribute()])) {
				$groupsDn = $results[$this->getGroupsAttribute()];
			} else {
				Log::warn("Required \"{$this->getGroupsAttribute()}\" object was not found in ldap account information");
				Log::warn('Available attributes: '. implode(',',array_keys(get_object_vars($groupsData))));
			}
		}
	
		return Ldap::cleanGroupNames($groupsDn);
	}
	
	/**
	 * @return \Zend\Config\Config $ldapConfig
	 */
	public function getLdapConfig() {
		return $this->ldapConfig;
	}

	/**
	 * @return \Acl\Db\MapperGroups $mapperGroups
	 */
	public function getMapperGroups() {
		return $this->mapperGroups;
	}

	/**
	 * @param \Acl\Db\MapperGroups $mapperGroups
	 * @return Signature
	 */
	public function setMapperGroups($mapperGroups) {
		$this->mapperGroups = $mapperGroups;
		return $this;
	}

	/**
	 * @param \Zend\Config\Config $ldapConfig
	 * @return Signature
	 */
	public function setLdapConfig($ldapConfig) {
		$this->ldapConfig = $ldapConfig;
		return $this;
	}

	/**
	 * @return string $groupsAttribute
	 */
	public function getGroupsAttribute() {
		return $this->groupsAttribute;
	}
	
	/**
	 * @param string $groupsAttribute
	 * @return Ldap
	 */
	public function setGroupsAttribute($groupsAttribute) {
		$this->groupsAttribute = $groupsAttribute;
		return $this;
	}

}
