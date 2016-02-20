<?php
namespace WebAPI\Db;

use ZendServer\Log\Log;

class ApiKeyContainer {
	
	/**
	 * @var array
	 */
	protected $apiKeyData = array();

	/**
	 * @param array $apiKeyData
	 */
	public function __construct(array $apiKeyData) {
		if (!$apiKeyData) {
			$this->setId(0);
			$this->setName('');
			$this->setHash('');
			$this->setUsername('');
			$this->setCreationTime(0);
			return;
		}
		
		$this->setId($apiKeyData['ID']);
		$this->setName($apiKeyData['NAME']);
		$this->setHash($apiKeyData['HASH']);
		$this->setUsername($apiKeyData['USERNAME']);
		$this->setCreationTime($apiKeyData['CREATION_TIME']);
		
	}
	
	public function toArray() {
		return $this->apiKeyData;
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->apiKeyData['id'];
	}
	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->apiKeyData['id'] = $id;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->apiKeyData['name'];
	}
	/**
	 * @param string $name
	 */
	protected function setName($name) {
		$this->apiKeyData['name'] = $name;
	}

	/**
	 * @return the $hash
	 */
	public function getHash() {
		return $this->apiKeyData['hash'];
	}
	/**
	 * @param string $hash
	 */
	protected function setHash($hash) {
		$this->apiKeyData['hash'] = $hash;
	}

	/**
	 * @return the $role
	 */
	public function getUsername() {
		return $this->apiKeyData['username'];
	}
	/**
	 * @param string $role
	 */
	protected function setUsername($username) {
		$this->apiKeyData['username'] = $username;
	}
	
	
	/**
	 * @return the $creationTime
	 */
	public function getCreationTime() {
		return $this->apiKeyData['creationTime'];
	}
	/**
	 * @param string $creationTime
	 */
	protected function setCreationTime($creationTime) {
		$this->apiKeyData['creationTime'] = $creationTime;
	}

}