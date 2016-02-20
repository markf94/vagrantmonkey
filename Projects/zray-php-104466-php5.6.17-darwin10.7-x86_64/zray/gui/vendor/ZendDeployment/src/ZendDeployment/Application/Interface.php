<?php

interface ZendDeployment_Application_Interface {

	const STATUS_NOT_EXISTS 		= "NOT_EXISTS";
	
	const STATUS_UPLOADING 			= "UPLOADING";
	const STATUS_UPLOADING_ERROR 	= "UPLOADING_ERROR";
	
	const STATUS_STAGING 			= "STAGING";
	const STATUS_STAGING_ERROR 		= "STAGING_ERROR";
	
	const STATUS_STAGED 			= "STAGED";
	const STATUS_UNSTAGED 			= "UNSTAGED";
	
	const STATUS_ACTIVATING 		= "ACTIVATING";
	const STATUS_ACTIVE 			= "ACTIVE";
	const STATUS_ACTIVATING_ERROR 	= "ACTIVATING_ERROR";
	
	const STATUS_DEACTIVATING 		= "DEACTIVATING";
	const STATUS_DEACTIVATING_ERROR = "DEACTIVATING_ERROR";
	
	const STATUS_UNSTAGING 			= "UNSTAGING";
	const STATUS_UNSTAGING_ERROR 	= "UNSTAGING_ERROR";	
	
	const STATUS_WAITING_FOR_DEPLOY = "WAITING_FOR_DEPLOY";
	const STATUS_WAITING_FOR_REMOVE = "WAITING_FOR_REMOVE";
	const STATUS_WAITING_FOR_ENABLE = "WAITING_FOR_ENABLE";
	const STATUS_WAITING_FOR_DISABLE = "WAITING_FOR_DISABLE";
	const STATUS_DISABLED            = "DISABLED";
	const STATUS_WAITING_FOR_REDEPLOY = "WAITING_FOR_REDEPLOY";
	const STATUS_WAITING_FOR_UPGRADE = "WAITING_FOR_UPGRADE";
	const STATUS_WAITING_FOR_ROLLBACK = "WAITING_FOR_ROLLBACK";
	
	const STATUS_TIMEOUT_WAITING_FOR_DEPLOY = "TIMEOUT_WAITING_FOR_DEPLOY";
	const STATUS_TIMEOUT_WAITING_FOR_REMOVE = "TIMEOUT_WAITING_FOR_REMOVE";
	const STATUS_TIMEOUT_WAITING_FOR_REDEPLOY = "TIMEOUT_WAITING_FOR_REDEPLOY";
	const STATUS_TIMEOUT_WAITING_FOR_UPGRADE = "TIMEOUT_WAITING_FOR_UPGRADE";
	const STATUS_TIMEOUT_WAITING_FOR_ROLLBACK = "TIMEOUT_WAITING_FOR_ROLLBACK";
	
	const STATUS_INTEGRATION_CANDIDATE = "INTEGRATION_CANDIDATE";
	const STATUS_WAITING_FOR_INTEGRATION = "STATUS_WAITING_FOR_INTEGRATION";
	
	const HEALTH_OK = "HEALTH_OK";
	const HEALTH_ERROR = "HEALTH_ERROR";
	const HEALTH_UNKNOWN = "HEALTH_UNKNOWN";

	/**
	 * @return string
	 */
	public function getStatus();

	/**
	 * Returns the health status for an application
	 * (HEALTH_OK/HEALTH_ERROR/HEALTH_UNKNOWN)
	 * 
	 * @return string
	 */
	public function getHealthStatus();

	/**
	 * If the health check script returned a content as its error response
	 * @return string
	 */
	public function getHealthMessage();

	/**
	 * @return string
	 */
	public function getApplicationId();

	/**
	 * @return string
	 */
	public function getBaseUrl();

	/**
	 * @return string
	 */
	public function getApplicationName();

	/**
	 * @return array of error constants => error message
	 */
	public function getErrors();

	/**
	 * 
	 * @return string Application version
	 */
	public function getVersion();
	
	/**
	 * Returns the time in which the application was deployed
	 * 
	 * @return integer
	 */
	public function getCreationTime();
	
	/**
	 * Returns the user given name for the application
	 * 
	 * @return string
	 */
	public function getUserApplicationName();
	
	
	/**
	 * Returns the user given name for the application
	 * 
	 * @return string
	 */
	public function getInstallPath();
	
	
	/**
	 * Returns the meta data of the pacakge used to deploy the application 
	 * 
	 * @return ZendDeployment_PackageMetaData_Interface
	 */
	public function getPackageMetaData();
	
	
	/**
	 * Returns the user parameters that are associated with the pending deployment
	 * @return array 
	 */
	public function getUserParams();

	
	/**
	 * Indicates whether the application can be rollbacked
	 * 
	 * @return boolean
	 */
	public function isRollbackable();

	
	/**
	 * Return the roolback version of the application
	 * 
	 * @return ZendDeployment_Application_Interface
	 * 
	 * @throws ZendDeployment_Exception_Interface if no rollback version is available (exception type - APPLICATION_NOT_ROLLBACKABLE)
	 */
	public function getRollbackToVersion();
	
	/**
	 * If the application was an existing one that was integrated into Zend Deployment
	 * 
	 * @return boolean
	 */
	public function isDefinedApplication();
}

