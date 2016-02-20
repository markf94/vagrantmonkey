<?php

namespace DeploymentLibrary\Db;

use Zend\Db\Sql\Predicate\Predicate;

use Zend\Db\Sql\Where;

use Zend\Db\Sql\Delete;

use ZendServer\Exception;

use ZendServer\Log\Log;
use Zend\Crypt\Hash;
use ZendServer\Set;
use Zend\Db\TableGateway\TableGateway;

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
	public function getUpdates() {
		return $this->tableGateway->select();
	}
	
	public function getUpdate($name) {
		return $this->tableGateway->select(array('name' => $name));
	}
	
	public function deleteUpdate($name) {
		$this->tableGateway->delete(array('name' => $name));
	}
	
	public function addUpdate($name, $version, $extraData) {
		$this->tableGateway->insert(
				array(	'NAME'	=> $name,
						'VERSION' => $version,
						'EXTRA_DATA' => $extraData,
				));
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
	
	public function deleteByName($name) {
		$predicate = new Predicate();
		$predicate->equalTo('NAME', $name);
		return $this->tableGateway->delete(new Where(array($predicate)));
	}
}
