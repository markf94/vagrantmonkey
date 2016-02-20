<?php

class TaskDescriptor {
	
	private $_packageId;
	private $_userParams;
	private $_zendParams;
	private $_id;
	
	
	public function setPackageId($packageId) {
		$this->_packageId = $packageId;
	}
	
	public function setUserParams($params) {
		$this->_userParams = $params;
	}
	
	public function setZendParams($params) {
		$this->_zendParams = $params;
	}
	
	public function setId($id) {
		$this->_id = $id;
	}
	
	public function getId() {
		return $this->_id;
	}
	
	public function getPackageId() {
		return $this->_packageId;
	}
	
	public function getUserParams() {
		return $this->_userParams;
	}
	
	public function getZendParams() {
		return $this->_zendParams;
		}
		
}

?>
