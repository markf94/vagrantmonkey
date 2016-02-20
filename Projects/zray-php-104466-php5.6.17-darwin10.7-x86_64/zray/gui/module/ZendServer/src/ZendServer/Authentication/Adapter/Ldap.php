<?php

namespace ZendServer\Authentication\Adapter;

use ZendServer\Log\Log;

use Zend\Authentication\Result;

use Users\Identity;

use Zend\Authentication\Adapter\Ldap as baseLdap;
use Zend\Ldap\Ldap as ldapCon;

class Ldap extends baseLdap implements IdentityGroupsProvider {
	
	/**
	 * @var \Acl\Db\MapperGroups
	 */
	private $mapperGroups;
	/**
	 * @var array
	 */
	private $groups;
	
	/**
	 * @var string
	 */
	private $groupsAttribute;
	
	/* (non-PHPdoc)
	 * @see \ZendServer\Authentication\Adapter\IdentityGroupsProvider::isGroupsProvider()
	 */
	public function getIdentityGroups() {
		return $this->groups;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Authentication\Adapter\Ldap::authenticate()
	 */
	public function authenticate() {
	
		$result = parent::authenticate();
	
		$identity = new Identity($result->getIdentity()); /* @var $identity Identity */
		if ($result->isValid()) {
			$this->groups = $this->getAccountGroups();
			$identity->setGroups($this->groups);
			$identity->setUsername($result->getIdentity());
		}
		$result = new Result($result->getCode(), $identity, $result->getMessages());
		return $result;
	}
	
	/**
	 * @param \Acl\Db\MapperGroups $mapperGroups
	 * @return Ldap
	 */
	public function setMapperGroups($mapperGroups) {
		$this->mapperGroups = $mapperGroups;
		return $this;
	}
	
	/**
	 * Clean up CN formatted group information to fit our group names
	 * 
	 * @param array $memberof
	 * @return array
	 */
	public static function cleanGroupNames($memberof) {
		$groups = array();
		foreach ($memberof as $groupLdap) {
			if (0 < preg_match('#^CN=(?P<group>[^,]+),?#i', $groupLdap, $matches)) {
				$groups[] = $matches['group'];
			} else {
				$groups[] = $groupLdap;
			}
		}
		return $groups;
	}
	
	/**
	 * @return array
	 */
	private function getAccountGroups() {
		$identityObject = $this->getAccountObject();
		$groupsDn = array();
		
		if ((isset($identityObject->{$this->getGroupsAttribute()}))
			&& (is_array($identityObject->{$this->getGroupsAttribute()}) || $identityObject->{$this->getGroupsAttribute()} instanceof \Traversable)) {
			
			/// Active directory expected response
			$groupsDn = $identityObject->{$this->getGroupsAttribute()};
		} elseif (isset($identityObject->{$this->getGroupsAttribute()})) {
			/// Posix LDAP expected response, single group value
			$groupsDn = array($identityObject->{$this->getGroupsAttribute()});
		} else {
			/// fail-over and try to retrieve operational attributes
			$results = $this->getLdap()->search("(uid={$this->getUsername()})", $this->authenticatedDn, ldapCon::SEARCH_SCOPE_BASE, array($this->getGroupsAttribute()));
			$results = $results->current();
			if (isset($results[$this->getGroupsAttribute()])) {
				$groupsDn = $results[$this->getGroupsAttribute()];
			} else {
				Log::warn("Required \"{$this->getGroupsAttribute()}\" object was not found in ldap account information");
				Log::warn('Available attributes: '. implode(',',array_keys(get_object_vars($identityObject))));
			}
		}
		
		return self::cleanGroupNames($groupsDn);
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

