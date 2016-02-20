<?php

namespace WebAPI\Db;

use ZendServer\Log\Log;
use ZendServer\Set;
use WebAPI\Db\ApiKeyContainer;
use Zend\Db\TableGateway\TableGateway;
use Configuration\MapperAbstract;

class Mapper extends MapperAbstract {
	
	const SYSTEM_KEY_NAME = 'zend-zsd';
	const ADMIN_KEY_NAME = 'admin';
	protected $setClass = 'WebAPI\Db\ApiKeyContainer';
	/**
	 * @var string
	 */
	private $generatedHash;
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
	 * @param string $name
	 * @param string $username
	 * @return ApiKeyContainer
	 */
	public function addKey($name, $username, $hash = '') {
		if (! $hash) {
			$hash = $this->generateHash();
		}
		
		$keyFields = array(	'NAME'	=> $name,
						'HASH' 	=> $hash,
						'USERNAME' => $username,
						'CREATION_TIME' => time(),
				);
		$this->tableGateway->insert($keyFields);
		$keyFields['ID'] = $this->tableGateway->getLastInsertValue();
		return new ApiKeyContainer($keyFields, $keyFields['ID']);
	}

	/**
	 * @param string $name
	 * @return ApiKeyContainer
	 */
	public function addAdminKey($name) {
		return $this->addKey($name, \Application\Module::config('user', 'adminUser'));
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
	public function findKeys($order, $direction) {
		$table = $this->getTableGateway()->getTable();
		$select = new \Zend\Db\Sql\Select();
		$select->from ( $this->getTableGateway ()->getTable () );
		$select->order($order . ' ' . $direction);
		return $this->selectWith($select);
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
	 * @return WebAPI\Db\ApiKeyContainer
	 */
	public function findKeyByName($name) {
		return $this->select(array("NAME = '$name'"))->current();
	}
		
	/**
	 * @param string $generatedHash
	 */
	public function setGeneratedHash($generatedHash) {
		$this->generatedHash = $generatedHash;
	}

	/**
	 * @return string
	 */
	private function generateHash() {
		if (is_null($this->generatedHash)) {
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);
			mt_srand($seed);
			
			$min = 0;
			$max = mt_getrandmax();
			$this->generatedHash = hash('sha256', mt_rand($min, $max));
		}
		
		return $this->generatedHash;
	}
}
