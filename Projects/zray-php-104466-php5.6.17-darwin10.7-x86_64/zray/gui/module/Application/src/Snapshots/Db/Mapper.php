<?php

namespace Snapshots\Db;

use ZendServer\Log\Log;
use ZendServer\Set;
use Configuration\MapperAbstract;

class Mapper extends MapperAbstract {
	const SNAPSHOT_TYPE_USER = 0;
	const SNAPSHOT_TYPE_SYSTEM = 1;
	
	const SNAPSHOT_SYSTEM_BOOT = 'SystemBoot';
	
	const SYSTEM_KEY_NAME = 'QAWebAPITestKey';
	protected $setClass = '\Snapshots\Db\SnapshotContainer';
	
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
	
	public function addSnapshot($name, $data, $type = self::SNAPSHOT_TYPE_USER) {
		$this->tableGateway->insert(
				array(	'NAME'	=> $name,
						'TYPE' => $type,
						'DATA' 	=> $data
				));
	}

	/**
	 * 
	 * @param array $id
	 */
	public function deleteKeysById($ids) {
		$searchIdArray = implode(',', $ids);
		return $this->tableGateway->delete(array('ID in (' . $searchIdArray . ')'));
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findAllKeys() {
		return $this->select();
	}
	
	
	/**
	 * Find key by id.
	 * 
	 * @param string $id
	 * @return Ambigous <multitype:, \Zend\Db\ResultSet\RowObjectInterface>
	 */
	public function findKeyById($id) {
		$result = $this->select(array("ID = $id"));
		if ($result instanceof Set) {
			return $result->current();
		}
		return current($result);
	}
	
	/**
	 * Find key by hash.
	 * 
	 * @param string $hash
	 * @return Ambigous <multitype:, \Zend\Db\ResultSet\RowObjectInterface>
	 */
	public function findKeyByHash($hash) {
		$result = $this->select(array("HASH = '$hash'"));
		return current($result);
	}
	
	/**
	 * Find key by name.
	 *
	 * @param string $name
	 * @return \Snapshots\Db\SnapshotContainer
	 */
	public function findSnapshotByName($name) {
		return $this->select(array("NAME = '$name'"))->current();
	}

	/**
	 * Find the System Snapshot
	 * @return \Snapshots\Db\SnapshotContainer
	 */
	public function findSystemSnapshot() {
		return $this->findSnapshotByName(self::SNAPSHOT_SYSTEM_BOOT);
	}
		
	/**
	 * @return string
	 */
	private function generateHash() {
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		mt_srand($seed);
		
		$min = 0;
		$max = mt_getrandmax();
		
		return hash('sha256', mt_rand($min, $max));
	}
}
