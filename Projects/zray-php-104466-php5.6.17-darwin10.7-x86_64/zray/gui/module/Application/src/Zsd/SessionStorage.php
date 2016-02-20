<?php
namespace Zsd;

use \Zend\Session\SessionManager,
	\ZendServer\Exception;

class SessionStorage {
	
	/**
	 * @var string
	 */
	const NS = 'ZEND_SERVER_ZSD';
	
	/**
	 * @var SessionManager
	 */
	private $manager = null;
	
	/**
	 * @param string $timestamp
	 */
	public function setZsdTimeStamp($timestamp) {
		$this->setValue('ZsdTimeStamp', $timestamp);
	}
	
	/**
	 * @return string 
	 */
	public function getZsdTimeStamp() {
		return $this->getValue('ZsdTimeStamp');
	}

	/**
	 * @param string $timestamp
	 */
	public function setPhpTimeStamp($timestamp) {
		$this->setValue('PhpTimeStamp', $timestamp);
	}
	
	/**
	 * @return string
	 */
	public function getPhpTimeStamp() {
		return $this->getValue('PhpTimeStamp');
	}
	
	public function isExists() {
		return ($this->manager->getStorage()->offsetExists(self::NS) &&	! is_null($this->getStorage()));
	}
	
	public function clear() {
		$this->manager->getStorage()->clear(self::NS);
	}
	
	/**
	 * @param string $key
	 * @param string $value
	 */
	private function setValue($key, $value) {
		$data = $this->getStorage();
		$data[$key] = $value;
		$this->setData($data);
	}
	
	/**
	 * @param string $key
	 * @return string
	 * @throws Exception
	 */
	private function getValue($key) {
		$data = $this->getStorage();
		
		if (! isset($data[$key])) {
			return null;
		}
	
		return $data[$key];
	}
	
	/**
	 * @param string $key
	 * @return string
	 * @throws Zwas_Exception
	 */
	private function hasValue($key) {
		$data = $this->getStorage();
		
		return isset($data[$key]);
	}
	
	/**
	 * @param array $data
	 */
	private function setData($data) {
		$namespace = self::NS;
		$this->manager->getStorage()->$namespace = $data;
	}
	
	/**
	 * @return array
	 */
	public function getStorage() {
		$namespace = self::NS;
		$data = isset($this->getManager()->getStorage()->$namespace) ? $this->getManager()->getStorage()->$namespace : null;
		
		if (! is_array($data)) {
			return array();
		}
		
		return $data;
	}
	
	/**
	 * @return SessionManager
	 */
	public function getManager() {
		if ($this->manager) return $this->manager;
		
		$this->manager = new SessionManager();
		$this->manager->start();
		return $this->manager;
	}

	/**
	 * @param \Zend\Session\SessionManager $manager
	 */
	public function setManager($manager) {
		$this->manager = $manager;
		return $this;
	}

}