<?php

interface ZendDeployment_Manager_Interface {

	const DAEMON_STATUS_OK = "DAEMON_STATUS_OK";
	const DAEMON_STATUS_OFF = "DAEMON_STATUS_OFF";
	
	
	/**
	 * @param integer $appId
	 * @param integer $libId
	 * @param string $url
	 * @param string $extraData
	 */
	public function downloadFile($server, $appId, $libId, $url, $extraData);
	
	
	/**
	 * 
	 * @param integer $downloadId
	 */
	public function cancelDownloadFile($server, $downloadId);
		
	/**
	 * Deploy an application on given servers
	 * 
	 * @param array $servers - servers IDs where the action is needed
	 * @param ZendDeployment_PackageMetaData_Interface $package
	 * @param array $userParams
	 * @param array $zendParams
	 */
	public function deployApplication(array $servers, ZendDeployment_PackageMetaData_Interface $package, array $userParams, array $zendParams);
	
	/**
	 * Deploy a library on given servers
	 *
	 * @param array $servers - servers IDs where the action is needed
	 * @param string $packagePath
	 * @param array $userParams
	 * @param array $zendParams
	 */
	public function deployLibrary(array $servers, $packagePath, array $userParams, array $zendParams);
	
	/**
	 * Define an existing application
	 * 
	 * @param string $baseUrl application base url
	 * @param string $name application name
	 * @param string $version application version
	 * @param string $healthCheck health check script
	 * @param string $logo logo contents	 
	 */
	public function defineApplication($servers, $baseUrl, $name, $version, $healthCheck, $logo);

	/**
	 * Get a list of paths for defineable applications
	 * @param unknown_type $servers
	 * @return array (server id => array of paths)
	 */
	public function getDefineableApplications();
	
	/**
	 * @param array $servers - servers IDs where the action is needed
	 * @param string $applicationId
	 * @param array $zendParams
	 */
	public function redeployApplication(array $servers, $applicationId, array $zendParams);

	/**
	 * @param array $servers - servers IDs where the action is needed
	 * @param string $applicationVersionId
	 * @param array $zendParams
	 */
	public function removeApplication(array $servers, $applicationId, $zendParams);
	
	/**
	 * @param array $servers - servers IDs where the action is needed
	 * @param string $applicationVersionId
	 */
	public function cancelApplicationAction(array $servers, $applicationId);

	/**
	 * Upgrade an application on given servers 
	 * 
	 * @param array $servers - servers IDs where the action is needed
	 * @param ZendDeployment_PackageMetaData_Interface $package
	 * @param string $applicationId 
	 * @param array $userParams
	 * @param array $zendParams
	 * 
	 */
	public function upgradeApplication(array $servers, ZendDeployment_PackageMetaData_Interface $package, $applicationId, array $userParams, array $zendParams);
	
	/**
	 * Rollback an application 
	 * 
	 * @param array $servers - servers IDs where the action is needed
	 * @param string $applicationId 
	 * @param array $zendParams
	 * 
	 * @throws ZendDeployment_Exception_Interface if $applicationId is invalid
	 * 
	 */
	public function rollbackApplication(array $servers, $applicationId, array $zendParams);
	
	/**
	 * @param array $servers - servers IDs where the action is needed
	 * @param array $zendParams
	 */
	public function redeployAllApplications(array $servers, array $zendParams);

	/**
	 * @param array $servers - servers IDs where the action is needed
	 * @param array $zendParams
	 *  
	 */
	public function removeAllApplications(array $servers, array $zendParams);

	/**
	 * @param array $servers - servers IDs where the action is needed
	 * @return array -
	 *	appId to array of (server ID => ZendDeployment_Application_Interface elements)
	 */
	public function getApplications(array $servers);
	
	/**
	 * @param array $servers - servers IDs where the action is needed
	 *
	 * @return array -
	 *       app ID => array() 
	 */
	public function getAllApplicationsInfo($servers);
	
	/**
	 * Return applications that match an array of application ids
	 * 
	 * @param array $applicationIds 
	 *  
	 * @return array -
	 *	appId to array of (server ID => ZendDeployment_Application_Interface elements)
	 */
	public function getApplicationsByIds(array $applicationIds);
	
	/**
	 * Return applications that match an array of vhosts ids
	 * @param array $vhostIds
	 */
	public function getApplicationsByVhostId(array $vhostIds);

	/**
	 * @param string $baseUrl
	 * @return array of (server ID => ZendDeployment_Application_Interface elements)
	 */
	public function getApplicationByBaseUrl($baseUrl);

	/**
	 * Add a pending deployment to the DB so that if the user stopped in the middle
	 *		of the wizard, he can return to the latest step he was in
	 *
	 * @param array $userParams parameters given by user
	 * @param array $zendParams parameters given by zend deployment process
	 * @param ZendDeployment_PackageFile_Interface $packageFile
	 */
	public function storePendingDeployment(ZendDeployment_PackageFile $packageFile, $userParams = array(), $zendParams = array());

	/**
	 * Undo storePendingDeployment, returns only an application which is not
	 *	already deployed or in the process of being deployed
	 * @param string $baseUrl
	 * @throws ZendDeployment_Exception
	 */
	public function cancelPendingDeployment($baseUrl);

	/**
	 * @param string $baseUrl
	 * @return ZendDeployment_PendingDeployment_Interface
	 * @throws ZendDeployment_Exception if the package can not be found
	 */
	public function getPendingDeploymentByBaseUrl($baseUrl);
	
	/**
	 * @param string $id
	 * @return ZendDeployment_PendingDeployment_Interface
	 * @throws ZendDeployment_Exception if the package can not be found
	 */
	public function getPendingDeploymentById($id);

	/**
	 * @return string
	 */
	public function getDaemonStatus();
	
	/**
	 * @return string
	 */
	public function getDaemonStatusWithRetries();
	
	/**
	 * Call a reload configuration command on the servers 
	 * 
	 * @param array @servers
	 */
	public function reloadConfiguration(array $servers);
	
	
    /**
     * @param array $servers
     * @return array of virtual hosts, e.g. array('www.zend.com', 'shop.zend.com')
     * @throws ZendDeployment_Exception
     */
    public function getVirtualHosts(array $servers);
	
    /**
     * @param array $servers
     * Deleted all deployment data for provided servers
     */
    public function purgeApplicationsData(array $servers);
    

    /**
     * Set a path for a health check request
     * 
     * @param string $applicationId
     * @param string $path
     */
    public function setHealthCheckScript($applicationId, $path);
    
    
    /**
     * Returns the master application version of an application 
     * 
     * @param string $applicationId
     * @return ZendDeployment_Application
     */
    public function getMasterApplication($applicationId);
    
    
    /**
     * Marks an application status as hidden so it is not displayed in UI
     * @param string $applicationId
     * @param array $servers
     * @throws ZendDeployment_Exception on bad parameters or DB error
     */
    public function hideApplicationStatus($applicationId, $servers);
    
        
     /**
     * Checks whether an application with the given id exists
     * 
     * @param string $applicationId
     * @return bool
     */
     public function applicationExists($applicationId);
     
     /**
      * Return an array with libraries data
      * @param array $ids
      * 
      * @return array 
      */
     public function getLibrariesByIds(array $ids = array());
     
     /**
      * Checks whether a library version is already deployed
      * @param string $name
      * @param string $version
      */
     public function isLibraryVersionExists($name, $version);
     
     /**
      * 
      * @param string $libraryId
      * @param array $zendParams
      */
     public function removeLibrary($servers, $libraryId, $zendParams);
     
     /**
      *
      * @param string $libraryVersionId
      * @param array $zendParams
      */
     public function removeLibraryVersion($servers, $libraryVersionId, $zendParams);
     
     /**
      *
      * @param string $libraryVersionId
      * @param array $zendParams
      */
     public function redeployLibraryVersion($servers, $libraryVersionId, $zendParams);     
     
         
     /**
      * 
      * @param string $libraryVersionId
      * @return ZendDeployment_PackageMetaData
      */
     public function getLibraryVersionPackageMetaData($libraryVersionId);
     
     /**
      * Set the default library version of a library
      * @param string $libraryVersionId
      */
     
     public function setDefaultLibrary($servers, $libraryVersionId);
     
}
