<?php

namespace Zsd\Db;

use ZendServer\Log\Log,
Zend\Db\TableGateway\TableGateway,
ZendServer\Exception;
use Zend\Db\Sql\Select;

class NodesProfileMapper {
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $tableGateway;
	
	public function __construct(TableGateway $tableGateway = null) {
		$this->tableGateway = $tableGateway;
	}
	
	/**
	 * 
	 * @param integer $id
	 * @param integer $auditId
	 * @return array
	 */
	public function getProfile() {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		// suppose that all nodes have the same profile
		$select->limit(1);
		$profile = $this->tableGateway->selectWith($select)->toArray();
		if (isset($profile[0])) {
			return $profile[0];
		} else {
			return array();
		}
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $tableGateway
	 */
	public function getTableGateway() {
		return $this->tableGateway;
	}

	/**
	 * @param \Zend\Db\TableGateway\TableGateway $tableGateway
	 * @return NodesProfileMapper
	 */
	public function setTableGateway($tableGateway) {
		$this->tableGateway = $tableGateway;
		return $this;
	}

}
