<?php
namespace Plugins;

use \Zend\Session\SessionManager,
	\ZendServer\Exception;

class SessionStorage {
	
	/**
	 * @var string
	 */
	const NS = 'ZEND_SERVER_DEPLOYMENT_UI_INSTALL';
	
	/**
	 * @var SessionManager
	 */
	private $manager = null;
	
	/**
	 * @var integer
	 */
	private $wizardId = 0;
	
	public function __construct($wizardId = 0) {	
		$this->manager = new SessionManager();
		$this->manager->start();
		
		$this->wizardId = $wizardId;
	}
	
	/**
	 * @param integer $id
	 */
	public function setPluginId($id) {
		$this->setValue('pluginId', $id);
	}
	
	/**
	 * @return integer $id
	 */
	public function getPluginId() {
		return $this->getValue('pluginId');
	}
	
	/**
	 * @return integer $id
	 */
	public function hasPluginId() {
		return $this->hasValue('pluginId');
	}
	
	/**
	 * @return integer $id
	 */
	public function hasDownloadId() {
		return $this->hasValue('downloadId');
	}
	
	/**
	 * @param string $path
	 */
	public function setPackageFilePath($path) {
		$this->setValue('packageFilePath', $path);
	}
	
	/**
	 * @param \Deployment\Application\Package $package
	 */
	public function setStoredPackage($package) {
		$this->setValue('storedPackage', $package);
	}
	
	/**
	 * @return string
	 * @throws Zwas_Exception
	 */
	public function getPackageFilePath() {
		return $this->getValue('packageFilePath');
	}
	
	/**
	 * @return string
	 * @throws Zwas_Exception
	 */
	public function getDownloadId() {
		return $this->getValue('downloadId');
	}
	
	/**
	 * @return string
	 * @throws Zwas_Exception
	 */
	public function getName() {
		return $this->getValue('name');
	}
	
	/**
	 * @return \Deployment\Application\Package
	 * @throws Zwas_Exception
	 */
	public function getStoredPackage() {
		return $this->getValue('storedPackage');
	}
	
	/**
	 * @return boolean
	 * @throws Zwas_Exception
	 */
	public function getEulaAccepted() {
		return $this->getValue('eulaAccepted');
	}
	
	/**
	 * @return boolean
	 * @throws Zwas_Exception
	 */
	public function hasPackageFilePath() {
		return $this->hasValue('packageFilePath');
	}
	
	/**
	 * @return boolean
	 * @throws Zwas_Exception
	 */
	public function hasEulaAccepted() {
		return $this->hasValue('eulaAccepted');
	}
	
	/**
	 * @return boolean
	 * @throws Zwas_Exception
	 */
	public function hasStoredPackage() {
		if (! $this->hasValue('storedPackage')) {
			return false;
		}
		
		return ! is_null($this->getStoredPackage());
	}
	
	/**
	 * @return string
	 * @throws Zwas_Exception
	 */
	public function hasName() {
		return $this->hasValue('name');
	}
	
	/**
	 * @param string $downloadId
	 */
	public function setDownloadId($downloadId) {
		$this->setValue('downloadId', $downloadId);
	}
	

	/**
	 * @param string $baseUrl
	 */
	public function setName($name) {
		$this->setValue('name', $name);
	}
	
	/**
	 * @param boolean $eulaAccepted
	 */
	public function setEulaAccepted($eulaAccepted) {
		$this->setValue('eulaAccepted', $eulaAccepted);
	}
	
	
	public function isExists() {
		return ($this->manager->getStorage()->offsetExists($this->getNamespace()) &&	! is_null($this->getStorage()));
	}
	
	public function clear() {
		$this->manager->getStorage()->clear($this->getNamespace());
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
			throw new Exception('Could not retrieve value from key ' . $key);
// 					Zwas_Translate::_('Could not retrieve value from key %s'),
// 					array((string)$key)
// 			));
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
		$namespace = $this->getNamespace();
		$this->manager->getStorage()->$namespace = $data;
	}
	
	/**
	 * @return array
	 */
	public function getStorage() {
		$namespace = $this->getNamespace();
		$data = isset($this->manager->getStorage()->$namespace) ? $this->manager->getStorage()->$namespace : null;
		
		if (! is_array($data)) {
			return array();
		}
		
		return $data;
	}
	
	private function getNamespace() {
		return self::NS . '_' . $this->wizardId;
	}
}