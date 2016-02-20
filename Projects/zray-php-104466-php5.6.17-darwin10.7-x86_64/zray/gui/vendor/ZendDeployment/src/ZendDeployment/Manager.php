<?php

require_once dirname(__FILE__) . '/Manager/Interface.php';
require_once dirname(__FILE__) . '/Logger.php';
require_once dirname(__FILE__) . '/DBHandler.php';
require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/PackageFile.php';

use ZendServer\Log\Log;
use Plugins\PluginContainer;

class ZendDeployment_Manager implements ZendDeployment_Manager_Interface
{
	
	
	/**
	 * @var ZendDeployment_DB_Handler
	 */
	private $_remoteDbHandler = NULL;
	
	private $_supportedDbTypes = array (
									"mysql", 
									"sqlite"
								);

	private $_tasksTimeout = 120;

	
	public function getMasterAppFromAppsList($apps) {
		if (count($apps) == 0) {
	        return null;
	    }
	    $appInServers = array_shift($apps);
		ZDBG2("Found app in servers " . implode(",", array_keys($appInServers)));
		
		$listToSort = array();
		$masterStatuses = array(); 
		
		$singleServer = (count(array_keys($appInServers)) == 1);
		
		foreach ($appInServers as $server => $app) {
			$listToSort[$app->getLastUsed()] = $app;	
			if ($app->isRollbackable()) {
				$rollback = $app->getRollbackToVersion();
				$listToSort[$rollback->getLastUsed()] = $rollback;
			}
			$masterStatuses[$app->getStatus()] = $app->getStatus();
		}		
		
		krsort($listToSort);
		/** @var $masterApp ZendDeployment_Application */
		
		$masterApp = array_shift($listToSort);
		ZDBG2("Master version is " . $masterApp->getVersion() . " from node " . $masterApp->getNodeId());
		if ($masterApp->isRollbackable()) {
			ZDBG3("Master candidate for rollback version is " . $masterApp->getRollbackToVersion()->getVersion());
		}
		if ($listToSort) {
			$maybeRollback = array_shift($listToSort);
			if ($singleServer && $maybeRollback->getAppStatusId() == $masterApp->getNextAppStatusId()) {
				$masterApp = $maybeRollback; 
				$masterApp->setRollbackToVersion(NULL);
			} else if (!$masterApp->isRollbackable()) {
				ZDBG3("Master rollback taken from node " . $maybeRollback->getNodeId());
				$masterApp->setRollbackToVersion($maybeRollback);
			} else if ($masterApp->getRollbackToVersion()->getLastUsed() < $maybeRollback->getLastUsed()) {
				ZDBG3("Master candidate rollback version is older that rollback in node " . $maybeRollback->getNodeId());
				$masterApp->setRollbackToVersion($maybeRollback);	
			}
		}

		if ($masterApp->isRollbackable()) {
			ZDBG3("Master final rollback version is " . $masterApp->getRollbackToVersion()->getVersion());
		} 
		
		if (count($masterStatuses) > 1) {
			$statusesOrder = array(
					ZendDeployment_Application_Interface::STATUS_NOT_EXISTS,
					ZendDeployment_Application_Interface::STATUS_UPLOADING_ERROR,
					ZendDeployment_Application_Interface::STATUS_STAGING_ERROR,
					ZendDeployment_Application_Interface::STATUS_ACTIVATING_ERROR,
					ZendDeployment_Application_Interface::STATUS_DEACTIVATING_ERROR,
					ZendDeployment_Application_Interface::STATUS_UNSTAGING_ERROR,
					ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_DEPLOY,
					ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REMOVE,
					ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY,
					ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_UPGRADE,
					ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK,
					ZendDeployment_Application_Interface::STATUS_WAITING_FOR_DEPLOY,
					ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REMOVE,
					ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REDEPLOY,
					ZendDeployment_Application_Interface::STATUS_WAITING_FOR_UPGRADE,
					ZendDeployment_Application_Interface::STATUS_WAITING_FOR_ROLLBACK,					
					ZendDeployment_Application_Interface::STATUS_UPLOADING,
					ZendDeployment_Application_Interface::STATUS_STAGING,
					ZendDeployment_Application_Interface::STATUS_ACTIVATING,
					ZendDeployment_Application_Interface::STATUS_ACTIVE,
					ZendDeployment_Application_Interface::STATUS_DEACTIVATING,
					ZendDeployment_Application_Interface::STATUS_UNSTAGING,
					ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE,
					ZendDeployment_Application_Interface::STATUS_WAITING_FOR_INTEGRATION,
			);
			
			foreach ($statusesOrder as $status) {
				if (isset($masterStatuses[$status])) {
					$masterApp->setStatus($status);
					break;
				}
			}
		}
		
		return $masterApp;
	}
	
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getMasterApplication()
	 */
	public function getMasterApplication($applicationId) {
		
		ZDBG2("Manager: getMasterApplication called with id $applicationId");
		
		$apps = $this->getApplicationsByIds(array($applicationId));
		
		return $this->getMasterAppFromAppsList($apps);
		
	}
	
	public function getApplicationsByVhostId(array $vhostIds)
	{
		ZDBG1("Manager: getApplicationsByVhostId called with ids " . implode(",", $vhostIds));
		
		// get all the apps
		
		$filter = array();
		$filter['vhostIds'] = $vhostIds;
		$apps = $this->_remoteDbHandler->getApplications($filter, false);
		
		//filter the ones needed
		foreach ($apps as $appId => $serverApps) {
			foreach ($serverApps as $serverId => $app) {
				if (count($serverApps) == 0) {
					unset($apps[$appId]);
				} else {
					$apps[$appId] = $serverApps;
				}
			}
		}
		
		$appsByVhosts = array();
		foreach ($apps as $appId => $serverApps) {
			$vhostId = current($serverApps)->getVhostId();
			if (!isset($appsByVhosts[$vhostId])) {
				$appsByVhosts[$vhostId] = array();
			}
			
			$appsByVhosts[$vhostId][$appId] = $serverApps;
		}
		
		return $appsByVhosts;
	}
	
	public function getMasterApplications(array $servers)
	{
	    $retApps = array();
	    $apps = $this->getAllApplicationsInfo($servers);
	    foreach ($apps as $appId => $app) {
	        $retApps[] = $this->getMasterApplication($appId);
	    }
	    return $retApps;
	}


	public static function getZendTempDir() {
		$tmp = getCfgVar("zend.temp_dir") . "/deployment";
		if (!file_exists($tmp)) {
			mkdir($tmp);
		}
		
		return $tmp;
	}
	
	private function createActivationDistribution($servers, $distFactor = 2, $distInterval = 10) {
		$distribution = array();
		
		$activationTime = time();
		$groupMaxCount = count($servers) / $distFactor;
		$groupCount = 0;		
		foreach ($servers as $server) {
			if ($groupCount == $groupCount) {
				$activationTime = time();
				$groupCount = 0;				
			}
			$distribution[$server] = $activationTime ;
			$groupCount++;
		}
		
		return $distribution;
	}
		
	/**
	 * 
	 * Initialize the Deployment entry point and the database that supports the deployment operations 
	 * @param ZendDeployment_DB_Config $config
	 * @throws ZendDeployment_Exception on configuration or database errors
	 */
	public function __construct(ZendDeployment_DB_Config $config = NULL) 
	{
		if (!$config) {
			
			$zendDir = get_cfg_var("zend.install_dir");
			if (isZrayStandaloneEnv()) {
				$zendDir = getCfgVar('zend.install_dir');
				$iniFile = "$zendDir/etc/deployment.ini";
			} elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$iniFile = "$zendDir/etc/cfg/deployment.ini";
			} elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'AIX') {
				$iniFile = "$zendDir/etc/conf.d/deployment.ini";
			} else {
				$iniFile = "$zendDir/gui/lighttpd/etc/conf.d/deployment.ini";
			}
            
			// Load DB settings from zend_database.ini file
            $dbIniFile = "$zendDir/etc/zend_database.ini";
			$ini    = parse_ini_file($iniFile);
            
            ZDBG2( "Parsing file " . $dbIniFile );
            $db_ini = parse_ini_file($dbIniFile);
            
			// read db settings from php ini
			$config = new ZendDeployment_DB_Config();
			if (isset($db_ini["zend.database.type"])) {
				$config->setDbType($db_ini["zend.database.type"]);
			} else {
				// default is sqlite
				$config->setDbType("sqlite");
			}
            
			ZDBG2("DBConfig: db type " . $config->getDbType() );
			$dbType = $config->getDbType();
			if (!in_array(strtolower($dbType), $this->_supportedDbTypes)) {
				throw new ZendDeployment_Exception("Unsupported DB type - " . $config->getDbType(), ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR);
			}
		
			if (isset($db_ini["zend.database.host_name"])) {
				$config->setDbHost($db_ini["zend.database.host_name"]);
			}
			ZDBG2("DBConfig: db host " . $config->getDbHost() );
			
			if (isset($db_ini["zend.database.port"])) {
				$config->setDbPort($db_ini["zend.database.port"]);
			}
			ZDBG2("DBConfig: db host " . $config->getDbPort() );
			
			if (isset($db_ini["zend.database.name"])) {
				$config->setDbName($db_ini["zend.database.name"]);
			}
			ZDBG2("DBConfig: db schema " . $config->getDbName() );
			
			if (isset($db_ini["zend.database.user"])) {
				$config->setDbUser($db_ini["zend.database.user"]);
			}
			ZDBG2("DBConfig: db user " . $config->getDbUser() );
			
			if (isset($db_ini["zend.database.password"])) {
				$config->setDbPassword($db_ini["zend.database.password"]);
			}
		} 
		
		$this->_remoteDbHandler = new ZendDeployment_DB_Handler($config);	
		
		if (isset($ini["zend_deployment.tasks_timeout"])) {
			$this->_tasksTimeout = $ini["zend_deployment.tasks_timeout"];
		}
	}
	
	public function getApplicationIdByBaseUrl($baseUrl) {
		return $this->_remoteDbHandler->getAppIdByBaseUrl($baseUrl);
	}
	
	public function getDefaultServerName() {
		return $this->_remoteDbHandler->getDefaultServerName();
	}
	
	private function failTimedOutTasks($timeLimit) {
		try {
			$this->_remoteDbHandler->failTimedOutTasks(ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY, ZendDeployment_Application::STATUS_TIMEOUT_WAITING_FOR_DEPLOY, $timeLimit);
			$this->_remoteDbHandler->failTimedOutTasks(ZendDeployment_Application::STATUS_WAITING_FOR_REDEPLOY, ZendDeployment_Application::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY, $timeLimit);
			$this->_remoteDbHandler->failTimedOutTasks(ZendDeployment_Application::STATUS_WAITING_FOR_REMOVE, ZendDeployment_Application::STATUS_TIMEOUT_WAITING_FOR_REMOVE, $timeLimit);
			$this->_remoteDbHandler->failTimedOutTasks(ZendDeployment_Application::STATUS_WAITING_FOR_UPGRADE, ZendDeployment_Application::STATUS_TIMEOUT_WAITING_FOR_UPGRADE, $timeLimit);
			$this->_remoteDbHandler->failTimedOutTasks(ZendDeployment_Application::STATUS_WAITING_FOR_ROLLBACK, ZendDeployment_Application::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK, $timeLimit);		
		} catch (ZendDeployment_Exception $ex) {
			ZERROR("failTimedOutTasks: " . $ex->getMessage());
		}	 
	}
	
	/**
	 * Verify that a given base url is of an existing vhost in all provided servers
	 * @param string $baseUrl
	 * @param array $servers currently known servers
	 * @throws ZendDeployment_Exception if vhost does not exist
	 */
	private function verifyExistingVhost($baseUrl, $servers) {
        ZDBG2("Skip vhost existence check");
        return ;
		ZDBG2("Verifying existance of $baseUrl vhost");
		
		//extract the host from the base url
		$vhost = parse_url($baseUrl, PHP_URL_HOST);
			
		if ($vhost != "<default-server>") {
		
			$vhost .= ":" . parse_url($baseUrl, PHP_URL_PORT);
			
			ZDBG1("Checking if vhost $vhost exists");
			$knownVhosts = $this->getVirtualHosts($servers);
			ZDBG2("Known hosts are " . implode("," ,$knownVhosts));
			if (!in_array($vhost, $knownVhosts)) {
				throw new ZendDeployment_Exception("$vhost does not exists" , ZendDeployment_Exception::VHOST_NOT_FOUND);
			}
						
		} else {
			ZDBG1("<default-server> used for deploy");
			return;
		}
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::defineApplication()
	 */
	public function defineApplication($servers, $baseUrl, $name, $version, $healthCheck, $logo) {
		try {
			
			$baseUrl = preg_replace('#(\/+)$#', '', $baseUrl); // remove trailing slashes 
			
			ZDBG1("Manager: defineApplication called with app at $baseUrl");
			
			$installPath = "";
			$apps = $this->getDefineableApplications();
			foreach ($apps as $app) {
				if ($app['base_url'] == $baseUrl) {
					$installPath = $app['install_path'];
					break;
				}
			}
		
		
			$this->_remoteDbHandler->defineApplication($servers, $baseUrl, $name, $installPath, $version, $healthCheck, $logo);			
				
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}	
	}
	
	public function removeIntegrationCandidateApplication($appId) {
		ZDBG1("Manager: removeIntegrationCandidateApplication called with app $appId");
				
		$this->_remoteDbHandler->removeApp($appId);
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::defineApplication()
	 */
	public function getDefineableApplications() {
		ZDBG1("Manager: getDefineableApplications called");
		
		return $this->_remoteDbHandler->getDefineableApplications();
	}
		
	public function getDeployedApplicationNames() {
		return $this->_remoteDbHandler->getDeployedApplicationNames();
	}
	
	public function getDeployedBaseUrls() {
		return $this->_remoteDbHandler->getDeployedBaseUrls();
	}
	
	public function downloadFile($server, $appId, $libId, $url, $extraData) {
		ZDBG1("Manager: downloadFile called for library " . $libId . " with url (" . $url . ") and extraData " . var_export($extraData, true));
		$extraData['url'] = str_replace('%20', ' ', $extraData['url']);
	    $url = str_replace('%20', ' ', $url);
		
		try {
			return $this->_remoteDbHandler->insertDownloadFileTask($server, $appId, $libId, $url, $extraData);			
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}
	}
	
	public function cancelDownloadFile($server, $downloadId) {
		ZDBG1("Manager: cancelDownloadFile called for downloadId $downloadId");
	
		try {
			return $this->_remoteDbHandler->insertCancelDownloadFileTask($server, $downloadId);
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::deployApplication()
	 */
	public function deployApplication(array $servers, ZendDeployment_PackageMetaData_Interface $package, array $userParams, array $zendParams) {
		try {
			$baseUrl = $zendParams['baseUrl'];
									
			ZDBG1("Manager: deployApplication called with servers (" . implode(" ", $servers) . ") and base url " . $baseUrl);
			
			// verify that the vhost exists unless 'createVhost' is passed as '1'
			if (!isset($zendParams['createVhost']) || $zendParams['createVhost'] == "0" ) {
				$this->verifyExistingVhost($baseUrl, $servers);
			}
			
			$arr = $this->getApplicationByBaseUrl($baseUrl, true);
			if ($arr) {
				$application = current($arr);
				ZDBG2("Application is in status " . $application->getStatus());
				if ($application->getStatus() == \ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE) {
					
					$this->removeIntegrationCandidateApplication($application->getApplicationId());
					
				} else {
					throw new ZendDeployment_Exception("base url '$baseUrl' is already assigned to an application", ZendDeployment_Exception_Interface::EXISTING_BASE_URL_ERROR);
				}
			}

			// if user did not provide app name - use one from package
			if (!isset($zendParams['userApplicationName'])) {
				$zendParams['userApplicationName'] = $package->getName();
			}
			
			// first server is chosen as single
			$runOnceServer = (int) current($servers);
			ZDBG2("Run once server is " . $runOnceServer);
			
			$pendingDeployment = $this->getPendingDeploymentByBaseUrl($baseUrl);
			if (!$pendingDeployment->getDeploymentPackage()) {
				throw new ZendDeployment_Exception("Cannot find pending deployment for $baseUrl", ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR);
			}
			
			$pendingTaskId = $pendingDeployment->getId();
			
			$this->_remoteDbHandler->updatePendingTask($baseUrl, $userParams, $zendParams);
			$this->_remoteDbHandler->activatePendingTask($baseUrl, $runOnceServer);		
			$this->_remoteDbHandler->resumePendingDeployment($servers, $baseUrl, $pendingTaskId, $pendingDeployment->getDeploymentPackage(), $zendParams);
					
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}	
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::redeployApplication()
	 */
	public function redeployApplication(array $servers, $applicationId, array $zendParams) {
		
	
		ZDBG1("Manager: redeployApplication called with servers (" . implode(" ", $servers) . ") and appId " . $applicationId);
		try {
			return $this->_remoteDbHandler->insertRedeployTask($servers, $applicationId, $zendParams);
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}		
	}
	
	public function redefineApplication(array $servers, $appId, $applicationVersionId, $installPath, $status) {
	
	
		ZDBG1("Manager: redefineApplication called with servers (" . implode(" ", $servers) . ") and app version id " . $applicationVersionId . " with status " . $status);
		try {
			return $this->_remoteDbHandler->redefineApplication($servers, $appId, $applicationVersionId, $installPath, $status);
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}
	}

	public function serverDisabled($disabledServerId, $servers) {
		ZDBG1("Manager: serverDisabled called with server $disabledServerId and cluster servers " . implode(",", $servers));
		foreach ( $servers as $server) {
			if ($server != $disabledServerId) {
				$this->_remoteDbHandler->updateRunOnceNodeId($disabledServerId, $server);
				break;
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::removeApplication()
	 */
	public function removeApplication(array $servers, $applicationId, $zendParams) {
		try {
			
				
			ZDBG1("Manager: removeApplication called with servers (" . implode(",", $servers) . ") and appId " . $applicationId);
			ZDBG1("Manager: with params " . var_export($zendParams, true));
			
			$row = $this->_remoteDbHandler->getAppDetails($applicationId);
			if ((int) $row['is_defined']) {
				$this->_remoteDbHandler->removeDefinedApp($servers, $applicationId);
				return;
			}
			
			$serversToCancel = array();
			foreach ($servers as $server) {
				$activeStatus = $this->_remoteDbHandler->getAppActiveStatus($server, $applicationId);
				if (!$activeStatus) {
					ZDBG2("Application $applicationId does not exist in server $server. Will not removing app there."); 
					continue;
				}
				$status = $activeStatus['status'];
				$appStatusId = $activeStatus['app_status_id'];
				$appVersionId = $activeStatus['app_version_id'];
						
				ZDBG2("Manager: app $applicationId is in status $status in node $server");
				switch ($status) {
					case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REMOVE:
					case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REMOVE:
						continue;
						
					case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_DEPLOY:
					case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REDEPLOY:
					case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_UPGRADE:
					case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_ROLLBACK:
					case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_DEPLOY:
					case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY:
					case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_UPGRADE:
					case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK:
						$serversToCancel[] = $server;
						ZDBG2("Manager: will rollback app status " . $appStatusId);
						$this->_remoteDbHandler->rollbackAppStatus($appStatusId, $applicationId, $server);
						break;
					default:
						break;
				}
			}
			
			$serversToRemove = array_diff($servers, $serversToCancel);
			if ($serversToRemove) {
				ZDBG2("Manager: will add remove tasks on servers " . implode(",", $serversToRemove));
				$this->_remoteDbHandler->insertRemoveTask($serversToRemove, $applicationId, $zendParams);
			}
			
			$this->_remoteDbHandler->cleanupObsoleteEntries();
			
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}		
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::redeployAllApplications()
	 */
	public function redeployAllApplications(array $servers, array $zendParams) {
		ZDBG1("Manager: redeployAllApplications called with servers (" . implode(" ", $servers) . ")");
		
		try {
			
			
			// get the list of application ids deployed
			$appsDeployed = $this->_remoteDbHandler->getApplications();
			$appIds = array_keys($appsDeployed);
			
			ZDBG1("Manager: redeployAllApplications deploying applications (" . implode(" ", $appIds) . ")");
			
			// and add a redeploy task on each one for each server
			foreach ($appIds as $appId) {
				$this->_remoteDbHandler->insertRedeployTask($servers, $appId, $zendParams);
			}
			
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}			
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::removeAllApplications()
	 */
	public function removeAllApplications(array $servers, array $zendParams) {
		ZDBG1("Manager: removeAllApplications called with servers (" . implode(" ", $servers) . ")");
		
		try {
			
			$this->_remoteDbHandler->purgeApplications($servers);
			
			$this->_remoteDbHandler->insertRemoveAllTask($servers);
			
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}			
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getApplications()
	 */
	public function getApplications(array $servers) {
		
		ZDBG1("Manager: getApplications called with servers ids " . implode(",", $servers));
		
		try {
			$timeLimit = time() - $this->_tasksTimeout;
			ZDBG2("Checking timed out tasks with time limit of $this->_tasksTimeout seconds");
			$this->failTimedOutTasks($timeLimit);
		} catch (Exception $ex) {
			// do nothing	
		}
		
		// get all the applications from the DB
		return $this->_remoteDbHandler->getApplications(array("servers" => $servers));
		
	}
	
	/**
	 * @param integer $appId
	 * @param string $baseUrl
	 * @param string $userAppName
	 */
	public function setApplicationName($appId, $baseUrl, $userAppName) {
		ZDBG1("Manager: setApplicationName called with servers id " . $appId . " and baseUrl " . $baseUrl . " change name to " . $userAppName);
		
		$this->_remoteDbHandler->updateApp($appId, $baseUrl, $userAppName);
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getAllApplicationsInfo()
	 */
	public function getAllApplicationsInfo($servers) {
		ZDBG2("Manager: getAllApplicationsInfo called with servers ids " . implode(",", $servers));
		
		try {
			$timeLimit = time() - $this->_tasksTimeout;
			ZDBG2("Checking timed out tasks with time limit of {$this->_tasksTimeout} seconds");
			$this->failTimedOutTasks($timeLimit);
		} catch (Exception $ex) {
			// do nothing
		}
		
		
		$apps = $this->_remoteDbHandler->getAllApplicationsInfo($servers);
		
		ZDBG2("Manager: found " . count($apps) . " apps");
		return $apps;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getApplicationsByIds()
	 */
	public function getApplicationsByIds(array $applicationIds) {
		
	    if (!is_array($applicationIds)) {
	        $applicationIds = array($applicationIds);
	    }
	    
	    ZDBG2("Manager: getApplicationsByIds called with ids " . implode(",", $applicationIds));
		
		// get all the apps
		
		$filter = array();
		$filter['appIds'] = $applicationIds;
		$apps = $this->_remoteDbHandler->getApplications($filter);
		
		//filter the ones needed
		foreach ($apps as $appId => $serverApps) {
			foreach ($serverApps as $serverId => $app) {
				if (count($serverApps) == 0) {
					unset($apps[$appId]);
				} else {
					$apps[$appId] = $serverApps;
				}
			}
		}
		
		return $apps;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getApplicationByBaseUrl()
	 */
	public function getApplicationByBaseUrl($baseUrl, $includeCandidates = false) {
		
		ZDBG1("Manager: getApplicationByBaseUrl called with base url $baseUrl");
		
		// get all the apps
		$filter = array();
		$filter['baseUrl'] = $baseUrl;
		$apps = $this->_remoteDbHandler->getApplications($filter);
		
		//filter the ones needed
		foreach ($apps as $appId => $serverApps) {
			foreach ($serverApps as $serverId => $app) {
				if ($app->getBaseUrl() != $baseUrl) {
					unset($serverApps[$serverId]);
				}
				
				if (!$includeCandidates && $app->getStatus() == ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE) {
					unset($serverApps[$serverId]);
				}
				
				if (count($serverApps) == 0) {
					unset($apps[$appId]);
				} else {
					$apps[$appId] = $serverApps;
				}
			}
		}
		
		if ($apps) {
			return array_shift($apps);
		} else {
			return $apps;
		}
		
	}
	
	private function getNodeId() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$nodeId = get_cfg_var("zend.node_id");	
		} else if (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN') {
			$nodeId = get_cfg_var("zend.node_id");	
		} else {
			$globalsDirectives = get_cfg_var("zend.conf_dir") 
				. "/" 
				. get_cfg_var("zend.ini_scandir")
				. "/ZendGlobalDirectives.ini";
			$ini = parse_ini_file($globalsDirectives);
			$nodeId = $ini["zend.node_id"];	
		}
		
		return $nodeId;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getDaemonStatus()
	 */
	public function getDaemonStatus() {
		
		$nodeId = $this->getNodeId();
		
		ZDBG1("Manager: getDaemonStatus called on node $nodeId");
		
		$status = $this->_remoteDbHandler->getNodeStatus($nodeId);
		if (!$status) {
			ZDBG1("Manager: getDaemonStatus - no daemon status found");
			return self::DAEMON_STATUS_OFF;
		}
		
		$expiration =  time() - 120; // 120 seconds of no reporting means that deamon is down
		if ( $status['last_updated'] < $expiration) {
			ZDBG1("Manager: getDaemonStatus - daemon status " . $status['status'] . " EXPIRED at " . date(ZendDeployment_Logger::TIME_FORMAT, $status['last_updated']));
			return self::DAEMON_STATUS_OFF;
		}
		
		ZDBG1("Manager: getDaemonStatus - daemon status is " . $status['status'] . ". (updated " . date(ZendDeployment_Logger::TIME_FORMAT, $status['last_updated']) .")");
		return $status['status']; 
	}
	
	private function getDaemonStatusByServer($nodeId) {
		
		ZDBG1("Manager: getDaemonStatusByServer called on node $nodeId");
		
		$status = $this->_remoteDbHandler->getNodeStatus($nodeId);
		if (!$status) {
			ZDBG1("Manager: getDaemonStatus - no daemon status found");
			return self::DAEMON_STATUS_OFF;
		}
		
		$expiration =  time() - 120; // 120 seconds of no reporting means that deamon is down
		if ( $status['last_updated'] < $expiration) {
			ZDBG1("Manager: getDaemonStatus - daemon status " . $status['status'] . " EXPIRED at " . date(ZendDeployment_Logger::TIME_FORMAT, $status['last_updated']));
			return self::DAEMON_STATUS_OFF;
		}
		
		ZDBG1("Manager: getDaemonStatus - daemon status is " . $status['status'] . ". (updated " . date(ZendDeployment_Logger::TIME_FORMAT, $status['last_updated']) .")");
		return $status['status']; 
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getDaemonStatusWithRetries()
	 */
	public function getDaemonStatusWithRetries() {
		
		for ($i = 0 ; $i < 10 ; $i++) {

			$nodeId = $this->getNodeId();
			
			ZDBG1("Manager: getDaemonStatusWithRetries called on node $nodeId");
			
			$status = $this->_remoteDbHandler->getNodeStatus($nodeId);
			if (!$status) {
				ZDBG1("Manager: getDaemonStatusWithRetries - no daemon status found. Will retry...");
				sleep(1);
				continue;
			}
			
			$expiration =  time() - 120; // 120 seconds of no reporting means that deamon is down
			if ( $status['last_updated'] < $expiration) {
				ZDBG1("Manager: getDaemonStatusWithRetries - daemon status " . $status['status'] . " EXPIRED at " . date(ZendDeployment_Logger::TIME_FORMAT, $status['last_updated']));
				return self::DAEMON_STATUS_OFF;
			}
			
			ZDBG1("Manager: getDaemonStatusWithRetries - daemon status is " . $status['status'] . ". (updated " . date(ZendDeployment_Logger::TIME_FORMAT, $status['last_updated']) .")");
		}
		
		if (!$status) {
			return self::DAEMON_STATUS_OFF;
		} else {
			return $status['status'];
		} 
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::upgradeApplication()
	 */
	public function upgradeApplication(array $servers, ZendDeployment_PackageMetaData_Interface $package, $applicationId, array $userParams, array $zendParams) {
		ZDBG1("Manager: upgradeApplication called with servers (" . implode(" ", $servers) . ") and appId $applicationId");
		
	
		// first server is chosen as single 
		$runOnceServer = (int) current($servers);
		ZDBG2("Run once server is " . $runOnceServer);
		try {
			
			$baseUrl = $this->_remoteDbHandler->getBaseUrlByAppId($applicationId);
			$zendParams['baseUrl'] = $baseUrl;
			$this->_remoteDbHandler->updatePendingTask($baseUrl, $userParams, $zendParams);
			$this->_remoteDbHandler->insertUpgradeTask($servers, $package, $applicationId, $userParams, $zendParams);
			$this->_remoteDbHandler->activatePendingTask($baseUrl, $runOnceServer);			 
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}	
		
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::upgradePlugin()
	 */
	public function upgradePlugin(array $servers, ZendDeployment_PackageMetaData_Interface $package, $pluginId, $taskDescriptorId, $auditId) {
	    ZDBG1("Manager: upgradePlugin called with servers (" . implode(" ", $servers) . ") and pluginId $pluginId taskDescriptorId: $taskDescriptorId");
	
	    // if the pending task descriptor wasn't set because the wizard separate actions for store pending task and activating it
	    if (! $taskDescriptorId) {
	        $pending = $this->_remoteDbHandler->getPendingTasks();
	        if ($pending) {
	           $taskDescriptorId = array_shift($pending);
	        }
	        if (isset($taskDescriptorId['task_descriptor_id'])) {
	           $taskDescriptorId = $taskDescriptorId['task_descriptor_id'];
	        }
	    }
	
	    // first server is chosen as single
	    $runOnceServer = (int) current($servers);
	    ZDBG2("Run once server is " . $runOnceServer);
	    try {
	        $this->_remoteDbHandler->insertUpgradePluginTask($servers, $package, $pluginId, $auditId, $taskDescriptorId);
	        $this->_remoteDbHandler->activatePendingPluginTask($taskDescriptorId, $runOnceServer, $package->getName());
	    } catch (ZendDeployment_Exception $ex) {
	        throw $ex;
	    }
	
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::storePendingDeployment()
	 */
	public function storePendingDeployment(ZendDeployment_PackageFile $packageFile, $userParams = array(), $zendParams = array()) {
		try {
					
					
			ZDBG1("Manager: storePendingDeployment called on package " . $packageFile->getName());
		
			$baseUrl = $zendParams['baseUrl'];
			
			// if user did not provide app name - use one from package
			if (!isset($zendParams['userApplicationName'])) {
				$zendParams['userApplicationName'] = $packageFile->getName();
			}

			$updatePending = false;
			$pending = $this->getPendingDeploymentByBaseUrl($baseUrl);
			if (!$pending->isNull()) {
				$updatePending = true;
			}			
			
			if (!$updatePending) {
				$appId = $this->getApplicationIdByBaseUrl($baseUrl);
				if ($appId > 0) {
					$apps = $this->getApplicationByBaseUrl($baseUrl);
					if (is_array($apps) && count($apps) > 0) {
						$theApp = array_shift($apps);
						if ($theApp->getStatus() == ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE) {
							$this->removeIntegrationCandidateApplication($appId);
						}
					}					
				}
				
				return $this->_remoteDbHandler->insertPendingTask($packageFile, $userParams, $zendParams);	
			} else {
				return $this->_remoteDbHandler->updatePendingTask($baseUrl, $userParams, $zendParams);
			}			
			
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}		
	}
	
	/* (non-PHPdoc)
	 * @brief insert new record to "deployment_tasks_descriptors" table
	 * @see ZendDeployment_Manager_Interface::storePendingPluginDeployment()
	 */
	public function storePendingPluginDeployment(ZendDeployment_PackageFile $packageFile, $name) {
	    try {
	        ZDBG1("Manager: storePendingPluginDeployment called on package " . $name);
	       
			// get tasks for plugin $name with status "pending" (the line can be removed???)
	        $pending = $this->getPendingPluginDeploymentByName($name);
	    
			// insert new record to "deployment_tasks_descriptors" table. $taskId is $taskDescriptorId
	        $taskId = $this->_remoteDbHandler->insertPendingTask($packageFile, array(), array());
			
			return $taskId;
	        
	    } catch (ZendDeployment_Exception $ex) {
	        throw $ex;
	    }
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::cancelPendingDeployment()
	 */
	public function cancelPendingDeployment($baseUrl) {
		try {
			ZDBG1("Manager: cancelPendingDeployment called on base url $baseUrl ");
			
			$task = $this->_remoteDbHandler->deletePendingTask($baseUrl);
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}		
	}	

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::cancelPendingPluginDeployment()
	 */
	public function cancelPendingPluginDeployment($name) {
	    try {
	        ZDBG1("Manager: cancelPendingPluginDeployment called on name $name ");
	        	
			// delete pending tasks of the plugin $name. Delete not relevant records related to tasks
	        $task = $this->_remoteDbHandler->deletePendingPluginTask($name);
	    } catch (ZendDeployment_Exception $ex) {
	        throw $ex;
	    }
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getPendingDeploymentByBaseUrl()
	 */
	public function getPendingDeploymentByBaseUrl($baseUrl) {
		
		ZDBG1("Manager: getPendingDeploymentByBaseUrl called on base url $baseUrl ");
		
		$tasks = $this->_remoteDbHandler->getPendingTasks($baseUrl);
		ZDBG1("Found " . count($tasks) . " pending deployments by base url $baseUrl");
			
		$res = $this->createPendingDeploymentsFromTasks($tasks);
		if (!$res) {
			return new ZendDeployment_PendingDeployment();
		} else {
			return array_shift($res);
		}
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getPendingPluginDeploymentByName()
	 */
	public function getPendingPluginDeploymentByName($name) {
	
	    ZDBG1("Manager: getPendingPluginDeploymentByName called on name $name ");
		
		// get tasks for plugin $name with status "pending"
	    $tasks = $this->_remoteDbHandler->getPluginPendingTasks($name);
		
	    ZDBG1("Found " . count($tasks) . " pending deployments by name $name");
	    	
		// Create instances of "ZendDeployment_PendingDeployment" object (just a container for a pending task)
	    $res = $this->createPendingPluginDeploymentsFromTasks($tasks);
	    if (!$res) {
	        return new ZendDeployment_PendingDeployment();
	    } else {
	        // the last pending package
	        return array_pop($res);
	    }
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getPendingDeploymentById()
	 */
	public function getPendingDeploymentById($id) {
		ZDBG1("Manager: getPendingDeploymentById called with id $id");
		
		$desc = $this->_remoteDbHandler->getPendingTaskById($id);
					
		$res = $this->createPendingDeploymentsFromTasks(array($desc));
		if (!$res) {
			return new ZendDeployment_PendingDeployment();
		} else {
			return array_shift($res);
		}
	}
	
 	function createPendingDeploymentsFromTasks(array $tasks) {
		$res = array();
		
		foreach ($tasks as $task) {
			$pendingDeployment = new ZendDeployment_PendingDeployment();
			
			if (isset($task['base_url'])) {
				$baseUrl = $task['base_url'];
				$pendingDeployment->setBaseUtl($baseUrl);
			} 
			$pendingDeployment->setUserParams($task['user_params']);
			$pendingDeployment->setZendParams($task['zend_params']);
			$pendingDeployment->setId($task['task_descriptor_id']);
			
			$metaData = $this->_remoteDbHandler->getPackageMetaData($task['package_id']);
			if (!$metaData) {
				throw new ZendDeployment_Exception("Cannot find package meta data for package " . $task['package_id'], ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
			}
			$pendingDeployment->setDeploymentPackage($metaData);			
			
			$res[] = $pendingDeployment;				
		}
		return $res;
	}
	
	/**
	 * @brief Create instances of "ZendDeployment_PendingDeployment" object
	 * @param array $tasks 
	 * @return  
	 */
	function createPendingPluginDeploymentsFromTasks(array $tasks) {
	    $res = array();
	
	    foreach ($tasks as $task) {
	        $pendingDeployment = new ZendDeployment_PendingDeployment();

	        if (isset($task['name'])) {
	            $pendingDeployment->setName($task['name']);
	        }
	        
	        $pendingDeployment->setId($task['task_descriptor_id']);
	        	
	        $metaData = $this->_remoteDbHandler->getPluginPackageMetaData($task['package_id']);
	        if (!$metaData) {
	            throw new ZendDeployment_Exception("Cannot find package meta data for package " . $task['package_id'], ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
	        }
	        $pendingDeployment->setDeploymentPackage($metaData);
	        	
	        $res[] = $pendingDeployment;
	    }
	    return $res;
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::reloadConfiguration()
	 */
	public function reloadConfiguration(array $servers) {
		try {
			
			ZDBG1("Manager: reloadConfiguration called");
			
			$this->_remoteDbHandler->insertReloadConfigurationTask($servers);		
			
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}	
		
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getVirtualHosts()
	 */
	public function getVirtualHosts(array $servers) {
		try {
			ZDBG1("Manager: getVirtualHosts called");
			$vhosts = array();	
			$dbVhosts = $this->_remoteDbHandler->getVirtualHosts();
			ZDBG2("Manager: found " . count($dbVhosts) . " vhosts");
			
			$realZendDir = realpath(get_cfg_var("zend.install_dir"));
			foreach ($dbVhosts as $vhost) { /* @var $vhost ZendDeployment_Vhost */ 
				
				// filter out vhosts not in servers required
				if (!in_array($vhost->node_id, $servers)) {
					ZDBG3("Manager: getVirtualHosts: " . $vhost->name . " is in server " . $vhost->node_id . " which is not included in the required servers");
					continue;
				}
				
				if (strstr($vhost->name, "default-server" )) {
					continue;
				}
				
				// save the count for each vhosts so we can filter out vhosts that don't exists in all servers
				if (isset($vhosts[$vhost->name])) {
					$vhosts[$vhost->name]++;
				} else {
					$vhosts[$vhost->name] = 1;
				} 				
			}	
			
			// filter out vhosts with count less than count of required servers
			foreach ($vhosts as $name => $count) {
				if ($count < count($servers)) {
					ZDBG3("Manager: getVirtualHosts: " . $vhost->name . " was not found in all the servers");
					unset($vhosts[$name]);
				}
			}
			
			return array_keys($vhosts);
					
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}		
	}
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::rollbackApplication()
	 */
	public function rollbackApplication(array $servers, $applicationId, array $zendParams) {
		ZDBG1("Manager: rollbackApplication called with servers (" . implode(" ", $servers) . ") and appId " . $applicationId);
		
	
		try {
			return $this->_remoteDbHandler->insertRollbackTask($servers, $applicationId, $zendParams);
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}	
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::purgeApplicationsData()
	 */
    public function purgeApplicationsData(array $servers) {
    	ZDBG1("Manager: purgeApplicationsData called with servers (" . implode(" ", $servers) . ")");
    	$this->_remoteDbHandler->purgeApplications($servers);
    }
 
    /* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::setHealthCheckScript()
	 */
    public function setHealthCheckScript($applicationId, $path) {
    	$this->_remoteDbHandler->setHealthCheckScript($applicationId, $path);
    }
    
    private function isStatusCancellable($status) {
    	switch ($status) {
			case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REMOVE:
			case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REMOVE:
			case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_DEPLOY:
			case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REDEPLOY:
			case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_UPGRADE:
			case ZendDeployment_Application_Interface::STATUS_WAITING_FOR_ROLLBACK:
			case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_DEPLOY:
			case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY:
			case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_UPGRADE:
			case ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK:
				return true;
			default:
				return false;
		}
    }
    
    /* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::cancelApplicationAction()
	 */
	public function cancelApplicationAction(array $servers, $applicationId) {
		ZDBG1("Manager: rollbackApplication called with servers (" . implode(" ", $servers) . ") and appId " . $applicationId);
		
		foreach ($servers as $server) {
			$activeStatus = $this->_remoteDbHandler->getAppActiveStatus($server, $applicationId);
			if (!$activeStatus) {
				ZDBG2("Application $applicationId does not exist in server $server. Will not removing app there."); 
				continue;
			}
			
			$allCancellable = true;
			 
			$status = $activeStatus['status'];
			$appStatusId = $activeStatus['app_status_id'];
			$appVersionId = $activeStatus['app_version_id'];
			ZDBG2("Manager: app $applicationId is in status $status in node $server");
			if ($this->isStatusCancellable($status)) {
				ZDBG2("Manager: will rollback app status " . $appStatusId);
				$this->_remoteDbHandler->rollbackAppStatus($appStatusId, $applicationId, $server);
			} else {
				$allCancellable = false;
				ZDBG2("Manager: app status is not cancellable");
			}
			
			if ($allCancellable) {
				throw new ZendDeployment_Exception("", ZendDeployment_Exception::ERROR_ALREADY_IN_PROGRESS);
			}
		}
		
		$this->_remoteDbHandler->cleanupObsoleteEntries();
	}
	
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::hideApplicationStatus()
	 */
	public function hideApplicationStatus($applicationId, $servers) {
		
		ZDBG1("Manager: hideApplicationStatus called with servers (" . implode(",", $servers) . ") and appId " . $applicationId);
		
		if (!$servers) {
			throw new ZendDeployment_Exception("Empty servers list", ZendDeployment_Exception::INTERNAL_SERVER_ERROR);
		}
		
		$activeStatus = $this->_remoteDbHandler->hideApplicationStatus($applicationId, $servers);		
		
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::applicationExists()
	 */
	public function applicationExists($applicationId) {
		ZDBG2("Manager: applicationExists called with appId $applicationId");
		
		try {
			return (NULL != $this->_remoteDbHandler->getBaseUrlByAppId($applicationId));
		} catch (ZendDeployment_Exception $ex) {
			throw $ex;
		}	
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::pluginExists()
	 */
	public function pluginExists($pluginId) {
	    ZDBG2("Manager: pluginExists called with pluginId $pluginId");
	
	    try {
	        return ($this->_remoteDbHandler->getPluginsByIds(array($pluginId)) != array());
	    } catch (ZendDeployment_Exception $ex) {
	        throw $ex;
	    }
	}
	
	public function deployLibrary(array $servers, $packagePath, array $userParams, array $zendParams){
		ZDBG1("Manager: deployApplication called with servers (" . implode(" ", $servers) . ") and path " . $packagePath);
		
		$package = new ZendDeployment_PackageFile();
		$package->loadFile($packagePath);
				
		$this->_remoteDbHandler->insertDeployLibraryTask($servers, $package, $userParams, $zendParams);	

		$libId = $this->_remoteDbHandler->getLibraryIdByName($package->getName());
		$libInfo = current($this->_remoteDbHandler->getLibrariesByIds(array($libId)));
		
		$libVersions = $libInfo['versions'];
		foreach ($libVersions as $key => $libVersion) {
			if ($libVersion['version'] != $package->getVersion()) {
				unset($libVersions[$key]);				
			}
		}	
		$libInfo['versions'] = $libVersions;

		ZDBG3("Deployed lib info:");
		ZDBG3(var_export($libInfo, true));
		return $libInfo;
	}

	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getLibrariesByIds()
	 */
	public function getLibrariesByIds(array $ids = array()) {
		$libs = $this->_remoteDbHandler->getLibrariesByIds($ids);
		
		/*
		foreach ($libs as $libId => $lib) {
			foreach ($lib['versions'] as $libVersionId => $libVersion) {
				$taskDesc = $this->_remoteDbHandler->getTaskDescriptorByLibraryVersionId($libVersionId);
				$zendParams = $taskDesc->getZendParams(); 
				if (isset($zendParams['builtin']) && $zendParams['builtin']) {
					$libs[$libId]['versions'][$libVersionId]['builtin'] = true;
				} else {
					$libs[$libId]['versions'][$libVersionId]['builtin'] = false;
				}
			}
		}*/
			
		ZDBG3("Libs returned by getLibrariesByIds:");
		ZDBG3(var_export($libs, true));
		return $libs;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::removeLibrary()
	 */
	public function removeLibrary($servers, $libraryId, $zendParams){
		
		$this->_remoteDbHandler->insertRemoveLibraryTask($servers, $libraryId, $zendParams);
		
		
	}
	 
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::removeLibraryVersion()
	 */
	public function removeLibraryVersion($servers, $libraryVersionId, $zendParams){
		$this->_remoteDbHandler->insertRemoveLibraryVersionTask($servers, $libraryVersionId, $zendParams);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::isLibraryVersionExists()
	 */
	public function isLibraryVersionExists($name, $version) {
		return $this->_remoteDbHandler->isLibraryVersionExists($name, $version);
	}
	
		public function redeployLibraryVersion($servers, $libraryVersionId, $zendParams){
		$this->_remoteDbHandler->insertRedeployLibraryVersionTask($servers, $libraryVersionId, $zendParams);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getLibraryVersionPackageMetaData()
	 */
	public function getLibraryVersionPackageMetaData($libraryVersionId){
		$desc = $this->_remoteDbHandler->getTaskDescriptorByLibraryVersionId($libraryVersionId);
		return $this->_remoteDbHandler->getPackageMetaData($desc->getPackageId());
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::setDefaultLibrary()
	 */
	public function setDefaultLibrary($servers, $libraryVersionId) {
		$this->_remoteDbHandler->setDefaultLibrary($libraryVersionId);
		
		$this->_remoteDbHandler->insertUpdateDefaultLibraryTask($servers, $libraryVersionId);
	}
	/**
	 * @return ZendDeployment_DB_Handler
	 */
	public function getRemoteDbHandler() {
		return $this->_remoteDbHandler;
	}

	//////////////////////////// PLUGINS //////////////////////////////////////////

	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getPluginsByIds()
	 */
	public function getPluginsByIds(array $ids = array(), $orderDirection = 'ASC') {
	    $plugins = $this->_remoteDbHandler->getPluginsByIds($ids, $orderDirection);
	    ZDBG3("Plugins returned by getPluginsByIds:");
	    ZDBG3(var_export($plugins, true));
	    return $plugins;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::getPluginVersionPackageMetaData()
	 */
	public function getPluginVersionPackageMetaData($pluginVersionId){
	    $desc = $this->_remoteDbHandler->getTaskDescriptorByPluginVersionId($pluginVersionId);
	    return $this->_remoteDbHandler->getPackageMetaData($desc->getPackageId());
	}
	

	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::removePluginVersion()
	 */
	public function removePluginVersion($servers, $pluginVersionId, $zendParams){
	    $this->_remoteDbHandler->insertRemovePluginVersionTask($servers, $pluginVersionId, $zendParams);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::removePlugin()
	 */
	public function removePlugin($servers, $pluginId, $zendParams){
	    $this->_remoteDbHandler->insertRemovePluginTask($servers, $pluginId, $zendParams);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::enablePlugins()
	 */
	public function enablePlugins($servers, $plugins, $zendParams){
	    foreach ($plugins as $pluginId) {
	       $this->_remoteDbHandler->insertEnableDisablePluginTask(true, $servers, $pluginId, $zendParams);
	    }
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::disablePlugins()
	 */
	public function disablePlugins($servers, $plugins, $zendParams){
		foreach ($plugins as $pluginId) {
			$this->_remoteDbHandler->insertEnableDisablePluginTask(false, $servers, $pluginId, $zendParams);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::isPluginVersionExists()
	 */
	public function isPluginVersionExists($name, $version) {
	    return $this->_remoteDbHandler->isPluginVersionExists($name, $version);
	}
	
	public function isPluginExists($name) {
	    return $this->_remoteDbHandler->isPluginExists($name);
	}
	
	public function redeployPlugin($servers, $pluginId, $zendParams){
	    $this->_remoteDbHandler->insertRedeployPluginTask($servers, $pluginId, $zendParams);
	}

	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::deployPlugin()
	 */
	public function deployPlugin(array $servers, ZendDeployment_PackageMetaData_Interface $package, $name, $auditId) {
	    try {
	        ZDBG1("Manager: deployPlugin called with servers (" . implode(" ", $servers) . ") and name $name");
	        
			// get plugin ID from the installed plugins
	        $pluginId = $this->getPluginIdByName($name);
	        if ($pluginId > 0) {
    	        $masterPlugin = $this->getMasterPluginByPluginId($pluginId);
    	        ZDBG2("Plugin is in status " . $masterPlugin->getStatus());
	        }
	        	
	        // first server is chosen as single
	        $runOnceServer = (int) current($servers);
	        ZDBG2("Run once server is " . $runOnceServer);
	        
			// Create instances of "ZendDeployment_PendingDeployment" object (just a container for a pending task)
	        $pendingDeployment = $this->getPendingPluginDeploymentByName($name);
	        if (!$pendingDeployment || !$pendingDeployment->getDeploymentPackage()) {
	            throw new ZendDeployment_Exception("Cannot find pending deployment for $name", ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR);
	        }
	        	
	        $pendingTaskId = $pendingDeployment->getId();
			
	        $this->_remoteDbHandler->activatePendingPluginTask($pendingTaskId, $runOnceServer, $name);
	        $this->_remoteDbHandler->resumePendingPluginDeployment($servers, $name, $pendingTaskId, $pendingDeployment->getDeploymentPackage(), $auditId);
	    } catch (ZendDeployment_Exception $ex) {
	        throw $ex;
	    }
	}
	

	/**
	 * (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::setDefaultPlugin()
	 */
	public function setDefaultPlugin($servers, $pluginVersionId) {
	    $this->_remoteDbHandler->setDefaultPlugin($pluginVersionId);
	
	    $this->_remoteDbHandler->insertUpdateDefaultPluginTask($servers, $pluginVersionId);
	}
	

	public function getMasterPluginFromPluginsList($plugins) {
	    if (count($plugins) == 0) {
	        return null;
	    }
	    $pluginInServers = array_shift($plugins);
	    ZDBG2("Found plugin in servers " . implode(",", array_keys($pluginInServers)));
	
	    $listToSort = array();
	    $masterStatuses = array();
	
	    $singleServer = (count(array_keys($pluginInServers)) == 1);
	    foreach ($pluginInServers['serversStatus'] as $server => $plugin) {
	        $listToSort[$plugin['lastUpdated']] = $plugin;
	        $masterStatuses[$plugin['status']] = $plugin['status'];
	    }
	
	    krsort($listToSort);
	   
	    /** @var $masterPlugin Plugins\PluginContainer */
	    $masterPlugin = array_shift($listToSort);
	   
	    if (count($masterStatuses) > 1) {
	        $statusesOrder = array(
	            ZendDeployment_Application_Interface::STATUS_NOT_EXISTS,
	            ZendDeployment_Application_Interface::STATUS_UPLOADING_ERROR,
	            ZendDeployment_Application_Interface::STATUS_STAGING_ERROR,
	            ZendDeployment_Application_Interface::STATUS_ACTIVATING_ERROR,
	            ZendDeployment_Application_Interface::STATUS_DEACTIVATING_ERROR,
	            ZendDeployment_Application_Interface::STATUS_UNSTAGING_ERROR,
	            ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_DEPLOY,
	            ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REMOVE,
	            ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY,
	            ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_UPGRADE,
	            ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK,
	            ZendDeployment_Application_Interface::STATUS_WAITING_FOR_DEPLOY,
	            ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REMOVE,
	            ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REDEPLOY,
	            ZendDeployment_Application_Interface::STATUS_WAITING_FOR_UPGRADE,
	            ZendDeployment_Application_Interface::STATUS_WAITING_FOR_ROLLBACK,
	            ZendDeployment_Application_Interface::STATUS_UPLOADING,
	            ZendDeployment_Application_Interface::STATUS_STAGING,
	            ZendDeployment_Application_Interface::STATUS_ACTIVATING,
	            ZendDeployment_Application_Interface::STATUS_ACTIVE,
	            ZendDeployment_Application_Interface::STATUS_STAGED,
	            ZendDeployment_Application_Interface::STATUS_DEACTIVATING,
	            ZendDeployment_Application_Interface::STATUS_UNSTAGING,
	            ZendDeployment_Application_Interface::STATUS_UNSTAGED,
	            ZendDeployment_Application_Interface::STATUS_DISABLED,
	            ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE,
	            ZendDeployment_Application_Interface::STATUS_WAITING_FOR_INTEGRATION,
	        );
	        	
	        foreach ($statusesOrder as $status) {
	            if (isset($masterStatuses[$status])) {
	                $masterPlugin['masterStatus'] = $status;
	                break;
	            }
	        }
	    } elseif (count($masterStatuses) == 1) {
	        $masterPlugin['masterStatus'] = array_shift($masterStatuses);
	    }
	
	    return $masterPlugin;
	}
	
	public function getMasterPlugins(array $servers, array $ids = array(), $orderDirection = 'ASC', $order = "id") {
	    $retPlugins = array();
	    $pluginsShortInfo = $this->_remoteDbHandler->getAllPluginsInfo($servers, $orderDirection, $order);
	    if ($pluginsShortInfo) {
	       $ids = array_keys($pluginsShortInfo);
	    } else {
	        $ids = array();
	    }
	    $plugins = $this->getPluginsByIds($ids);
	    
	    if (is_array($pluginsShortInfo)) {
    	    foreach ($pluginsShortInfo as $plugId => $pluginInfo) {
    	        if (empty($ids) || (!empty($ids) && in_array($plugId, $ids))) {
        	        $masterPluginData = $this->getMasterPluginFromPluginsList($plugins[$plugId]['versions']);
        	        $masterPlugin = new PluginContainer($plugins[$plugId]);
        	        $masterPlugin->setMasterStatus($masterPluginData['masterStatus']);
        	        $masterPlugin->setPluginMessage($masterPluginData['lastMessage']);
    	            // returned the sorted list by $order parameter, except by Display Name
        	        if ($order == "name") {
        	            $name = property_exists($plugins[$plugId]['packageMetadata'], 'display_name') ? $plugins[$plugId]['packageMetadata']->display_name : $plugins[$plugId]['packageMetadata']->name;
        	            $retPlugins[$name] = $masterPlugin;
        	        } elseif ($order == "id") {
        	            $retPlugins[$plugId] = $masterPlugin;
        	        } else {
        	            $retPlugins[] = $masterPlugin;
        	        }
    	        } 
    	      
    	    }
	    }
	   
	    // returned the sorted list by $order parameter, except by Display Name
	    if ($order == "name") {
	       ksort($retPlugins);
	       
	       if (strcasecmp($orderDirection,'ASC') != 0) {
	           return array_reverse($retPlugins);
	       }
	    }
	    
	    return $retPlugins;
	}
	
	public function getMasterPluginByPluginId($pluginId) {
	    ZDBG1("Manager: getMasterPluginByPluginId called with id $pluginId");
	    $plugins = $this->getPluginsByIds(array($pluginId));
	    ZDBG1("Manager: getMasterPluginByPluginId got plugins list: " . var_export($plugins, true));
	    $masterPluginData = $this->getMasterPluginFromPluginsList($plugins[$pluginId]['versions']);
	    $masterPlugin = new PluginContainer($plugins[$pluginId]);
	    $masterPlugin->setMasterStatus($masterPluginData['masterStatus']);
	    $masterPlugin->setPluginMessage($masterPluginData['lastMessage']);
	    return $masterPlugin;
	}
	
	public function getPluginIdByName($name) {
		
		ZDBG1("Manager: getPluginByName called with name $name");
		
		// get plugin ID from the installed plugins
		return $this->_remoteDbHandler->getPluginIdByName($name);
		
		
		
	}
	
}

