<?php
namespace Snapshots\Db;

use ZendServer\Log\Log;

class SnapshotContainer {
	
	/**
	 * @var array
	 */
	protected $snapshotData = array();

	/**
	 * @param array $snapshotData
	 */
	public function __construct(array $snapshotData) {
		if (!$snapshotData) {
			$this->setId(0);
			$this->setName('');
			$this->setType('');
			$this->setData('');
			$this->setCreationTime(0);
			return;
		}
		
		$this->setId($snapshotData['ID']);
		$this->setName($snapshotData['NAME']);
		$this->setType($snapshotData['TYPE']);
		$this->setData($snapshotData['DATA']);
		$this->setCreationTime($snapshotData['CREATION_TIME']);
		
	}
	
	public function toArray() {
		return $this->snapshotData;
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->snapshotData['id'];
	}
	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->snapshotData['id'] = $id;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->snapshotData['name'];
	}
	/**
	 * @param string $name
	 */
	protected function setName($name) {
		$this->snapshotData['name'] = $name;
	}

	/**
	 * @return the $hash
	 */
	public function getType() {
		return $this->snapshotData['type'];
	}
	/**
	 * @param string $type
	 */
	protected function setType($type) {
		$this->snapshotData['type'] = $type;
	}

	/**
	 * @return the $role
	 */
	public function getData() {
		return $this->snapshotData['data'];
	}
	/**
	 * @param string $data
	 */
	protected function setData($data) {
		$this->snapshotData['data'] = $data;
	}
	
	
	/**
	 * @return the $creationTime
	 */
	public function getCreationTime() {
		return $this->snapshotData['creationTime'];
	}
	/**
	 * @param string $creationTime
	 */
	protected function setCreationTime($creationTime) {
		$this->snapshotData['creationTime'] = $creationTime;
	}

}