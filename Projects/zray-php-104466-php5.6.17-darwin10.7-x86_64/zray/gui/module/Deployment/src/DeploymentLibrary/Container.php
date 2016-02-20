<?php
namespace DeploymentLibrary;

use ZendServer\Container\Structure;

class Container implements Structure {
	
	/**
	 * @var array
	 */
	private $library;
	/**
	 * 
	 * @var string
	 */
	private $libraryId;
	
	/**
	 * @param array $library
	 */
	public function __construct(array $library, $libraryId) {
		$this->library = $library;
		$this->libraryId = $libraryId;
	}
	
	public function getLibraryId() {
		return $this->library['libraryId'];
	}
	
	public function getLibraryName() {
		return $this->library['libraryName'];
	}
	
	public function getUpdateUrl() {
		if (isset($this->library['updateUrl'])) {
			return $this->library['updateUrl'];
		}
	
		return '';
	}
	
	public function getLibStatus() {
		if (isset($this->library['status'])) {
			return $this->library['status'];
		} else {
			return 'unknown';
		}
	}
	
	public function getVersions() {
	    return $this->library['versions'];
	}
	
	public function setVersions($versions) {
		$this->library['versions'] = $versions;
	}
	
	public function toArray() {
		return $this->library;
	}
	
}
