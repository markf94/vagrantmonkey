<?php

namespace LibraryUpdates\Db;

use Configuration\MapperAbstract;

class Mapper extends MapperAbstract {
	protected $setClass = '';
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $tableGateway
	 */
	public function getTableGateway() {
		return $this->tableGateway;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $tableGateway
	 * @return Mapper
	 */
	public function setTableGateway($tableGateway) {
		$this->tableGateway = $tableGateway;
		return $this;
	}
	
	/**
	 * 
	 * @param array $name
	 */
	public function deleteByName($name) {
		return $this->tableGateway->delete(array('NAME = "' . $name . '"'));
	}
}
