<?php

require_once dirname ( __FILE__ ) . '/Application/Interface.php';
require_once dirname ( __FILE__ ) . '/Logger.php';

class ZendDeployment_Application implements ZendDeployment_Application_Interface {
	
	private $_status;
	private $_healthStatus;
	private $_healthMessage;
	private $_appId;
	private $_baseUrl;
	private $_appName;
	private $_errors;
	private $_appVersion;
	private $_creationTime;
	private $_userAppName;
	private $_installPath;
	private $_packageMetaData;
	private $_userParams;
	private $_runOnceNode;
	private $_appStatusId;
	private $_nextAppStatusId;
	private $_rollbackToApp;
	private $_nodeId;
	private $_appVersionId;
	private $_lastUsed;
	private $_isDefinedApp;
	private $_vhostId;
	
	/**
	 * @return the $_vhostId
	 */
	public function getVhostId() {
		return $this->_vhostId;
	}

	/**
	 * @param field_type $_vhostId
	 */
	public function setVhostId($_vhostId) {
		$this->_vhostId = $_vhostId;
	}

	/**
	 * @return the $_lastUsed
	 */
	public function getLastUsed() {
		return $this->_lastUsed;
	}

	/**
	 * @param field_type $_lastUsed
	 */
	public function setLastUsed($_lastUsed) {
		$this->_lastUsed = $_lastUsed;
	}
	
	public function setRunOnceNode($id) {
		$this->_runOnceNode = $id;
	}

	/**
	 * @return the $_appVersionId
	 */
	public function getAppVersionId() {
		return $this->_appVersionId;
	}

	/**
	 * @param field_type $_appVersionId
	 */
	public function setAppVersionId($_appVersionId) {
		$this->_appVersionId = $_appVersionId;
	}

	public function __construct() {
		
		$this->_errors = array ();
		$this->_rollbackToApp = NULL;
			
	}
	
	/**
	 * @param string $healthMessage
	 */
	public function setHealthMessage($healthMessage) {
		$this->_healthMessage = $healthMessage?$healthMessage:"";
	}
	
	/**
	 * @param string $appId
	 */
	public function setAppId($appId) {
		$this->_appId = $appId;
	}
	
	/**
	 * @param field_type $_baseUrl
	 */
	public function setBaseUrl($baseUrl) {
		$this->_baseUrl = $baseUrl;
	}
	
	/**
	 * @param string $appName
	 */
	public function setAppName($appName) {
		$this->_appName = $appName;
	}
	
	/**
	 * @param array $errors
	 */
	public function setErrors($errors) {
		$this->_errors = $errors;
	}
	
	/**
	 * @param string $appVersion
	 */
	public function setVersion($appVersion) {
		$this->_appVersion = $appVersion;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getStatus()
	 */
	public function getStatus() {
		return $this->_status;
	}
	
	function setStatus($status) {
		$this->_status = $status;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getHealthStatus()
	 */
	public function getHealthStatus() {
		return $this->_healthStatus;
	}
	
	function setHealthStatus($status) {
		$this->_healthStatus = $status;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getHealthMessage()
	 */
	public function getHealthMessage() {
		return $this->_healthMessage;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getApplicationId()
	 */
	public function getApplicationId() {
		return $this->_appId;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getBaseUrl()
	 */
	public function getBaseUrl() {
		return $this->_baseUrl;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getApplicationName()
	 */
	public function getApplicationName() {
		return $this->_appName;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getErrors()
	 */
	public function getErrors() {
		return $this->_errors;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getVersion()
	 */
	public function getVersion() {
		return $this->_appVersion;
	}
	
	/**
	 * @param integer $creationTime
	 */
	public function setCreationTime($creationTime) {
		$this->_creationTime = $creationTime;
	}
	
	/**
	 * @param string $userAppName
	 */
	public function setUserAppName($userAppName) {
		$this->_userAppName = $userAppName;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getCreationTime()
	 */
	public function getCreationTime() {
		return $this->_creationTime;
	
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getUserApplicationName()
	 */
	public function getUserApplicationName() {
		return $this->_userAppName;
	}
	
	public function setInstallPath($path) {
		$this->_installPath = $path;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getInstallPath()
	 */
	public function getInstallPath() {
		return $this->_installPath;
	}
	
	public function setPackageMetaData($packageMetaData) {
		$this->_packageMetaData = $packageMetaData;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getPackageMetaData()
	 */
	public function getPackageMetaData() {
		return $this->_packageMetaData;
	}
	
	public function setUserParams($params) {
		$this->_userParams = $params;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getUserParams()
	 */
	public function getUserParams() {
		return $this->_userParams;		
	}
	
	public function getRunOnceNode() {
		return $this->_runOnceNode;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::isRollbackable()
	 */
	public function isRollbackable() {
		return ($this->_rollbackToApp != NULL);		
	}

	public function setRollbackToVersion($app) {
		$this->_rollbackToApp = $app;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::getRollbackToVersion()
	 */
	public function getRollbackToVersion() {
		if (!$this->_rollbackToApp) {
			throw new ZendDeployment_Exception("", ZendDeployment_Exception_Interface::APPLICATION_NOT_ROLLBACKABLE);
		}
		return $this->_rollbackToApp;
	}
	
	/**
	 * @return the app status Id
	 */
	public function getAppStatusId() {
		return $this->_appStatusId;
	}

	/**
	 * @return the next app status id
	 */
	public function getNextAppStatusId() {
		return $this->_nextAppStatusId;
	}

	/**
	 * @param integer $_appStatusId
	 */
	public function setAppStatusId($_appStatusId) {
		$this->_appStatusId = $_appStatusId;
	}

	/**
	 * @param integer $_nextAppStatusId
	 */
	public function setNextAppStatusId($_nextAppStatusId) {
		$this->_nextAppStatusId = $_nextAppStatusId;
	}

	/**
	 * @return the node id
	 */
	public function getNodeId() {
		return $this->_nodeId;
	}

	/**
	 * @param integer _nodeId
	 */
	public function setNodeId($_nodeId) {
		$this->_nodeId = $_nodeId;
	}
	
	
	public function setIsDefinedApp($isDefinedApp) {
		$this->_isDefinedApp = $isDefinedApp;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::isDefinedApplication()
	 */
	public function isDefinedApplication() {
		return $this->_isDefinedApp;
	}	
	
}
