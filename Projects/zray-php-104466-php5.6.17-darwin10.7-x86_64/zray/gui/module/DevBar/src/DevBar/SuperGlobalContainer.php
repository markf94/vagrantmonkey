<?php
namespace DevBar;

class SuperGlobalContainer {
	/**
	 * @var array
	 */
	protected $superglobal;
	
	/**
	 * @param array $superglobal
	 */
	public function __construct(array $superglobal, $key=null) {
		$this->superglobal = $superglobal;
	}
	
	public function toArray() {
		return $this->superglobal;
	}	
	
	public function getRequestId() {
		return $this->superglobal['request_id'];
	}
	
	public function getName() {
		return $this->superglobal['sg_name'];
	}
	
	public function getData() {
		return unserialize($this->superglobal['data']);
	}
	
	public function getRawData() {
		return $this->superglobal['data'];
	}
	
	public function getSampleType() {
		return $this->superglobal['sample_type'];
	}
}