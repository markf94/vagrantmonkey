<?php

namespace Users\Db;

use Zend\Db\Sql\Predicate\Predicate;

use Zend\Db\Sql\Where;

use Zend\Db\Sql\Delete;

use ZendServer\Exception;

use ZendServer\Log\Log;
use Zend\Crypt\Hash;
use ZendServer\Set;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Exception\ExceptionInterface;

class Mapper {
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $tableGateway;

	public function __construct(TableGateway $tableGateway = null) {
		$this->tableGateway = $tableGateway;
	}
	
	/**
	 * @return Set
	 */
	public function getActiveUsers() {
		$where = new Where();
		$where->notEqualTo('PASSWORD', '');
		return $this->tableGateway->select($where);
	}
	
	/**
	 * @return Set
	 */
	public function getUsers() {
		return $this->tableGateway->select();
	}
	/**
	 * @param string $name
	 * @return array
	 */
	public function findUserByName($name) {
		$user = $this->tableGateway->select(array('NAME' => $name))->current();
		if ($user instanceof \ArrayObject) {
			return $user->getArrayCopy();
		}
		throw new Exception(_t('User \'%s\' was not found', array($name)));
	}
	
	/**
	 * 
	 * @param string $username
	 * @param string $password
	 * @param string $role
	 * @throws \ZendServer\Exception
	 */
	public function setUser($username, $password, $role = null) {
		
		if ($password == '') {
			$updateFields = array('PASSWORD' => '');
		} else {
			//Insert or Update if exists
			$updateFields = array('PASSWORD' => Hash::compute('sha256', $password));
		}
		
		if ($role) {
			$updateFields['ROLE'] = $role;
		}
		
		try {
			$user = $this->findUserByName($username);
			if ($user['PASSWORD'] != Hash::compute('sha256', $password)) {
				/// only store the password if it is actually different
				$this->tableGateway->update($updateFields, array('NAME' => $username));
			}
		} catch (Exception $ex) {
			/// no user found, insert a new user
			$this->tableGateway->insert(array('NAME' => $username) + $updateFields);
		} catch (ExceptionInterface $ex) {
			throw new Exception(_t("Could not write to the database: users table. Please check permissions."));
		}
		
		if ($password == '') {
			Log::info("Add user $username (with no password) and role $role");
		} else {
			Log::info("Add user $username (with password) and role $role");
		}
	}
	
	/**
	 * Remove all users' records, except for the administrator user
	 * @return integer
	 */
	public function deleteAllButAdmin() {
		$predicate = new Predicate();
		$predicate->notEqualTo('NAME', 'admin');
		return $this->tableGateway->update(array('PASSWORD' => ''), new Where(array($predicate)));
	}

}
