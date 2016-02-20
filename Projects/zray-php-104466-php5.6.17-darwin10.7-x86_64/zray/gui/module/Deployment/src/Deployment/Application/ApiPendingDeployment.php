<?php
namespace Deployment\Application;

class ApiPendingDeployment {

	/**
	 * @var \ZendDeployment_PendingDeployment_Interface
	 */
	private $pendingDeployment = null;
	
	public function __construct(\ZendDeployment_PendingDeployment_Interface $pendingDeployment) {
		$this->pendingDeployment = $pendingDeployment;
	}
	
	/**
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->pendingDeployment->getBaseUrl();
	}
	
	/**
	 * @return \ZendDeployment_PackageMetaData_Interface
	 */
	public function getDeploymentPackage() {
		return $this->pendingDeployment->getDeploymentPackage();
	}
	
	/**
	 * @return array
	 */
	public function getUserParams() {
		return $this->pendingDeployment->getUserParams();
	}
	
	/**
	 * @param array
	 */
	public function getZendParams() {
		return $this->pendingDeployment->getZendParams();
	}	

	
	/**
	 * 
	 * @param string $packageFilepath
	 * @throws \Exception
	 * @return \Deployment\Application\ApiPendingDeployment
	 */
	public static function generate($packageFilepath) {
		
		$packageFile = new \ZendDeployment_PackageFile();
		$packageFile->loadFile($packageFilepath);
		
		return new self($packageFile);
	}


}

