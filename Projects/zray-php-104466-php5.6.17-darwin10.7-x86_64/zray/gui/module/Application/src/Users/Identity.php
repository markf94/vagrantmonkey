<?php

namespace Users;

class Identity {
	/**
	 * @var string
	 */
	private $identity;
	/**
	 * @var string
	 */
	private $role;
	
	/**
	 * @var array
	 */
	private $groups = array();
	
	/**
	 * @var string
	 */
	private $username;
	
	/**
	 * @var boolean
	 */
	private $loggedIn = false;
	
	public function __construct($identity = null, $role = null) {
		$this->identity = $identity;
		$this->role = $role;
		$this->username = $identity;
	}
	/**
	 * @return string $identity
	 */
	public function getIdentity() {
		return $this->identity;
	}

	/**
	 * @return string $role
	 */
	public function getRole() {
		return $this->role;
	}

	/**
	 * @return array $groups
	 */
	public function getGroups() {
		return $this->groups;
	}

	/**
	 * @return string $username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @return boolean
	 */
	public function isLoggedIn() {
		return $this->loggedIn;
	}

	/**
	 * @param boolean $loggedIn
	 */
	public function setLoggedIn($loggedIn = true) {
		$this->loggedIn = $loggedIn;
	}

	/**
	 * @param string $username
	 * @return Identity
	 */
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}

	/**
	 * @param array $groups
	 * @return Identity
	 */
	public function setGroups($groups) {
		$this->groups = $groups;
		return $this;
	}

	/**
	 * @param string $identity
	 * @return Identity
	 */
	public function setIdentity($identity) {
		$this->identity = $identity;
		return $this;
	}

	/**
	 * @param string $role
	 * @return Identity
	 */
	public function setRole($role) {
		$this->role = $role;
		return $this;
	}

	public function __toString() {
		return "{$this->getIdentity()}:{$this->getRole()}";
	}
	
}

