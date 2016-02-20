<?php

namespace WebAPI\Authentication\Adapter;

use ZendServer\Exception;

use ZendServer\Log\Log;

use Users\Identity;

use Zend\Authentication\Adapter\AdapterInterface;
class SignatureSimple extends SignatureAbstract {
	/**
	 * @var \Users\Db\Mapper
	 */
	private $usersMapper;
	
	/**
	 * 
	 * @param Identity $identity
	 * @return Identity
	 */
	protected function collectGroups(Identity $identity) {
		try {
			$username = $identity->getUsername();
			$user = $this->getUsersMapper()->findUserByName($username);
			$identity->setRole($user['ROLE']);
		} catch (Exception $ex) {
			Log::warn("Bound user '{$username}' was not found");
			Log::debug($ex);
			$identity->setRole('guest');
		}
		return $identity;
	}
	

	/**
	 * @return \Users\Db\Mapper $usersMapper
	 */
	public function getUsersMapper() {
		return $this->usersMapper;
	}

	/**
	 * @param \Users\Db\Mapper $usersMapper
	 * @return Signature
	 */
	public function setUsersMapper($usersMapper) {
		$this->usersMapper = $usersMapper;
		return $this;
	}
}
