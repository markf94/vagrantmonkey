<?php
namespace Deployment\Application;

use ZendServer\Container\Structure,
	ZendDeployment_Application,
	ZendDeployment_PackageMetaData,
	ZendDeployment_Application_Interface,
	Deployment\Model;
;

class Container implements ZendDeployment_Application_Interface {
	
	/**
	 * @var \ZendDeployment_Application
	 */
	private $application;
	
	/**
	 * @param \ZendDeployment_Application $application
	 */
	public function __construct($application) {
		if ($application instanceof ZendDeployment_Application) {
			$this->application = $application;
		} else {
			$this->application = new ZendDeployment_Application();
		}
	}
	
	/**
	 * @return integer
	 */
	public function getVhostId() {
		return $this->application->getVhostId();
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::isRollbackable()
	*/
	public function isRollbackable() {
	    return $this->application->isRollbackable();
	}
	
	public function getRunOnceNode() {
		return $this->application->getRunOnceNode();
	}
	
	public function getApplicationId() {
		return $this->application->getApplicationId();
	}
	
	public function getAppVersionId() {
	    return $this->application->getAppVersionId();
	}
	
	public function getApplicationName() {
		return $this->application->getApplicationName();
	}
	
	public function getAppStatus() {
		return $this->application->getStatus();
	}
	
	public function getStatus() {
	    return  Model::convertApplicationStatus($this->application->getStatus());
	}
	
	public function getHealthStatus() {
	    return Model::convertApplicationHealthStatus($this->application->getHealthStatus());
	}
	
	public function setStatus($status) {
		return $this->application->setStatus($status);
	}
	
	public function setHealthStatus($status) {
		return $this->application->setHealthStatus($status);
	}
	
	/**
	 * @return int
	 */
	public function getConvertedHealthStatus() {
	    return Model::convertApplicationHealthStatus($this->application->getHealthStatus());
	}
	
	public function getHealthMessage() {
		// Added htmlentities to prevent xss attack
	    return htmlentities($this->application->getHealthMessage());
	}
	
	public function getBaseUrl() {
	    return $this->application->getBaseUrl();
	}
		
	public function getCreationTime() {
	    return $this->application->getCreationTime();
	}
	
	public function getErrors() {
	    return $this->application->getErrors();
	}
	
	public function getInstallPath() {
	    return $this->application->getInstallPath();
	}
	
	public function getLastUsed() {
	    return $this->application->getLastUsed();
	}
	
	public function getNextAppStatusId() {
	    return $this->application->getNextAppStatusId();
	}
	
	public function getNodeId() {
	    return $this->application->getNodeId();
	}
	
	public function getRollbackToVersion() {
	    return $this->application->getRollbackToVersion();
	}
	
	public function getUserApplicationName() {
	    return $this->application->getUserApplicationName();
	}
	
	public function getVersion() {
	    return $this->application->getVersion();
	}
	
	public function getUserParams() {
	    return $this->application->getUserParams();
	}
	
    /**
	 * @return \ZendDeployment_PackageMetaData_Interface
	 */
	public function getPackageMetaData() {
	    return $this->application->getPackageMetaData();
	}
	
	public function getLogo() {
	    return $this->getPackageMetaData()->getLogo();
	}
	
	public function cannotRedeploy($status=null) {
		if ($status === null) $status = $this->getStatus();
		
	    if (in_array($status, Model::getNoRedeployStatuses())) {
	        return true;
	    }
	    
	    return false;
	}
	/* (non-PHPdoc)
	 * @see ZendDeployment_Application_Interface::isDefinedApplication()
	 */
	public function isDefinedApplication() {
		return $this->application->isDefinedApplication();
	}
}
