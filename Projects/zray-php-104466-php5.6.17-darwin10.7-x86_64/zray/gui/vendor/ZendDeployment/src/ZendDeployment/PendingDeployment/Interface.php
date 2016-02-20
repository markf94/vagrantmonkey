<?php

interface ZendDeployment_PendingDeployment_Interface {
	
	/**
	 * Returns the deployment package that is associated with the pending deployment
	 * @return ZendDeployment_PackageMetaData_Interface 
	 */
	public function getDeploymentPackage();
	
	/**
	 * Returns the base url that is associated with the pending deployment
	 * @return string 
	 */
	public function getBaseUrl();
	
	/**
	 * Returns the user parameters that are associated with the pending deployment
	 * @return array 
	 */
	public function getUserParams();
	
	/**
	 * Returns the zend parameters that are associated with the pending deployment
	 * @return array 
	 */
	public function getZendParams();
	
	/**
	 * Returns true if the object is null; otherwise returns false.
	 * 
	 * @return boolean
	 */
	public function isNull();
	
	/**
	 * Returns the id 
	 * 
	 * @return integer
	 */
	public function getId();
}
