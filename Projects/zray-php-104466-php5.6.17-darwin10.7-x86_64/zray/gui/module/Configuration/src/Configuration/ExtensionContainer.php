<?php
namespace Configuration;
use Configuration\DaemonContainer;

class ExtensionContainer extends DaemonContainer {
	
	/**
	 * @return string
	 */
	public function getName() {
		if (isset($this->data['NAME'])) {
			return $this->data['NAME'];
		}
		return '';
	}
	
	/**
	 * @return string
	 */
	public function getVersion() {
		return preg_replace('@\$[\D]*@', '', $this->data['EXT_VERSION']); // removing such stuff as in Reflection: $Revision: 321634 $, or in exif: 1.4 $Id: exif.c 321634..
	}

	/**
	 * @return string
	 */
	public function getType() {
		if (! isset($this->data['IS_ZEND_EXTENSION']) || ($this->data['IS_ZEND_EXTENSION'] === '') || is_null($this->data['IS_ZEND_EXTENSION'])) {
			$this->data['IS_ZEND_EXTENSION'] = $this->data['IS_ZEND_COMPONENT'];
		}
		$this->data['IS_ZEND_EXTENSION'] === 'true' ? $type = 'zend': $type = 'php';
		return $type;
	}
	
	/**
	 * @return string
	 */
	public function getStatus() {
		if (! $this->isLoaded()) {
			return 'Off';
		}
		
		if (($status = $this->getErrorStatus()) !== false) {
			return $status;
		}
		
		return 'Loaded';
	}
	
	/**
	 * @return string
	 */
	public function setIsLoaded($status) {
		return $this->data['IS_LOADED'] = $status;
	}	
	
	/**
	 * @return string
	 */
	public function getIsLoaded() {
		return isset($this->data['IS_LOADED']) && $this->data['IS_LOADED'] ? 'true' : 'false';
	}
	
	/**
	 * @return string
	 */
	public function getIsInstalled() {
		return isset($this->data['IS_INSTALLED']) && $this->data['IS_INSTALLED'] ? 'true' : 'false';
	}

	/**
	 * @return string
	 */
	public function getBuiltIn() {
		if ($this->isLoaded() && !$this->isInstalled()) {
			return 'true';
		}
		
		return 'false';
	}

	/**
	 * @return ExtensionContainer
	 */
	public function setDummy($value) {
		$this->data['dummy'] = $value;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDummy() {
		if (isset($this->data['dummy'])){ // could be "true" or "1" for instance
			return 'true';
		}
		
		return 'false';
	}
	
	/**
	 * @return string
	 */
	public function getShortDescription() {
		return isset($this->data['shortDescription']) ? trim($this->data['shortDescription']) : '';
	}
	
	/**
	 * @return string
	 */
	public function getLongDescription() {
		return isset($this->data['longDescription']) ? trim($this->data['longDescription']) : '';
	}
		
	public function isInstalled() {
		return $this->isTrue($this->getIsInstalled());
	}

	public function isLoaded() {
		return $this->isTrue($this->getIsLoaded());
	}

	public function isBuiltIn() {
		return $this->isTrue($this->getBuiltIn());
	}	

	public function isDummy() {
		return $this->isTrue($this->getDummy());
	}

	public function isInType($type) {
		if ($type === 'all' || $type === $this->getType()) {
			return true;
		}
		
		return false;
	}
			
	private function isTrue($value) {
		return $value && $value !== 'false';
	}
}