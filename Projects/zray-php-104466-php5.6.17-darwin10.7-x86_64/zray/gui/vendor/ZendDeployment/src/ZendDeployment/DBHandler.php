<?php

require_once dirname ( __FILE__ ) . '/Application.php';
require_once dirname ( __FILE__ ) . '/TaskDescriptor.php';
require_once dirname ( __FILE__ ) . '/PackageMetaData.php';
use ZendServer\Log\Log;

class ZendDeployment_DB_Config {
	
	const SQLITE_DB_NAME = "deployment.db";
	
	const DB_HOST_DIRECTIVE = "zend_deployment.mysql_host";
	const DB_PORT_DIRECTIVE = "zend_deployment.mysql_port";
	const DB_NAME_DIRECTIVE = "zend_deployment.mysql_name";
	const DB_USER_DIRECTIVE = "zend_deployment.mysql_user";
	const DB_PASSWORD_DIRECTIVE = "zend_deployment.mysql_password";
	
	private $_dbHost;
	private $_dbPort;
	private $_dbName;
	private $_dbUser;
	private $_dbPassword;
	private $_dbType;
	
	public function __construct() {
	
	}
	
	public function setDbHost($host) {
		$this->_dbHost = $host;
	}
	
	public function setDbName($name) {
		$this->_dbName = $name;
	}
	
	public function setDbUser($user) {
		$this->_dbUser = $user;
	}
	
	public function setDbPassword($password) {
		$this->_dbPassword = $password;
	}
	
	public function setDbPort($port) {
		$this->_dbPort = $port;
	}
	
	public function setDbType($type) {
		$this->_dbType = $type;
	}
	
	public function getDbHost() {
		return $this->_dbHost;
	}
	
	public function getDbName() {
		return $this->_dbName;
	}
	
	public function getDbUser() {
		return $this->_dbUser;
	}
	
	public function getDbPassword() {
		return $this->_dbPassword;
	}
	
	public function getDbPort() {
		return $this->_dbPort;
	}
	
	public function getDbType() {
		return $this->_dbType;
	}

}

class ZendDeployment_Vhost {
	public $name;
	public $path;
	public $node_id;
}

class ZendDeployment_DB_Handler {
	
	const QUERIES_FILE = "DBHandler/db_queries.ini";
	
	const TASK_TYPE_DEPLOY = "DEPLOY";
	const TASK_TYPE_REDEPLOY = "REDEPLOY";
	const TASK_TYPE_REMOVE = "REMOVE";
	const TASK_TYPE_ENABLE = "ENABLE";
	const TASK_TYPE_DISABLE = "DISABLE";
	const TASK_TYPE_UPGRADE = "UPGRADE";
	const TASK_TYPE_ROLLBACK = "ROLLBACK";
	const TASK_TYPE_RELOAD_CONFIGURATION = "RELOAD_CONFIGURATION";
	const TASK_TYPE_DELETE_APPLICATIONS_DATA = "DELETE_APPLICATIONS_DATA";
	const TASK_TYPE_UPDATE_DEFAULT_LIBRARY = "UPDATE_DEFAULT_LIBRARY";
	const TASK_TYPE_UPDATE_DEFAULT_PLUGIN = "UPDATE_DEFAULT_PLUGIN";
	const TASK_TYPE_DOWNLOAD_FILE = "DOWNLOAD_FILE";
	const TASK_TYPE_CANCEL_DOWNLOAD_FILE = "CANCEL_DOWNLOAD_FILE";
	const TASK_TYPE_APP_DEFINED = "APPLICATION_DEFINED";
	const TASK_TYPE_APP_UNDEFINED = "APPLICATION_UNDEFINED";
	
	const TASK_TYPE_START_MONITOR = "START_MONITOR";
	const TASK_TYPE_START_JOBQUEUE = "START_JOBQUEUE";
	const TASK_TYPE_START_SCD = "START_SCD";
	const TASK_TYPE_STOP_MONITOR = "STOP_MONITOR";
	const TASK_TYPE_STOP_JOBQUEUE = "STOP_JOBQUEUE";
	const TASK_TYPE_STOP_SCD = "STOP_SCD";
	
	const TASK_STATUS_ACTIVE = "ACTIVE";
	const TASK_STATUS_PENDING = "PENDING";
	
	const PARAMS_DELIMITER = ";#*";
	
	const BUSY_RETRIES = 5;
	
	const DOWNLOAD_STATUS_PENDING = 0;
	
	private $_queries;
	
	// handler to DB
	private $_dbh = NULL;
	
	/**
	 * 
	 * @param ZendDeployment_DB_Config $config
	 * @param string $handlerType (sqlite/mysql)
	 * @throws ZendDeployment_Exception
	 */
	public function __construct(ZendDeployment_DB_Config $config) {
		
		// parse the possible queries from ini file
		$handlerType = strtolower ( $config->getDbType () );
		
		$this->_queries = parse_ini_file ( dirname ( __FILE__ ) . "/" . self::QUERIES_FILE );
		if (! $this->_queries) {
			throw new ZendDeployment_Exception ( "Unable to find DB queries file at " . self::QUERIES_FILE, ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
		}
		
		try {
			
			if ($handlerType == "sqlite") {
				if (isZrayStandaloneEnv()) {
					$zendDir = getCfgVar('zend.data_dir');
					$sqliteDbName = $zendDir . "/db/" . ZendDeployment_DB_Config::SQLITE_DB_NAME;
					
				} elseif (get_cfg_var ( "zend.data_dir" )) {
					$sqliteDbName = get_cfg_var ( "zend.data_dir" ) . "/db/" . ZendDeployment_DB_Config::SQLITE_DB_NAME;
				} else {
					$sqliteDbName = ZendDeployment_DB_Config::SQLITE_DB_NAME;
				}
				if (! file_exists ( $sqliteDbName )) {
					throw new ZendDeployment_Exception ( "Unable to locate sqlite db at $sqliteDbName", ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
				}
				$this->_dbh = new PDO ( "sqlite:$sqliteDbName" );
				
				// 15 seconds timeout on locked db
				$this->_dbh->setAttribute ( PDO::ATTR_TIMEOUT, 15);
				
				ZDBG2 ( "Using SQLITE db at $sqliteDbName" );
			} elseif ($handlerType == 'memory') {
				$this->_dbh = new PDO ( "sqlite::memory:" );
				ZDBG2 ( "Using SQLITE db at memory" );
			} else { //assumed mysql
				$dbHost = $config->getDbHost ();
				$dbPort = $config->getDbPort ();
				$dbName = $config->getDbName ();
				$dbUser = $config->getDbUser ();
				$dbPassword = $config->getDbPassword ();
				
				$this->_dbh = new PDO ( "mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword );
				ZDBG2 ( "Connected to mysql:host=$dbHost;port=$dbPort;dbname=$dbName" );
			}
			
			$this->_dbh->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
		} catch ( PDOException $ex ) {
			$err = "Unable to connect to MYSQL server at $dbHost (database $dbName). " . $ex->getMessage ();
			throw new ZendDeployment_Exception ( $err, ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	private function getQuery($query) {
		if (! isset ( $this->_queries [$query] )) {
			throw new ZendDeployment_Exception ( "Database: cannot find query - " . $query, ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
		}
		ZDBG3 ( "DB: preparing query: " . $this->_queries [$query] );
		return $this->_queries [$query];
	}
	
	private function beginTransaction() {
		$this->execWithRetries ( "beginTransaction" );
	}
	
	private function rollback() {
		$this->execWithRetries ( "rollback" );
	}
	
	private function commit() {
		$this->execWithRetries ( "commit" );
	}
	
	private function execWithRetries($operation) {
		for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
			try {
				
				$this->_dbh->$operation ();
				
				return;
			
			} catch ( PDOException $ex ) {
				
				if ($i + 1 == self::BUSY_RETRIES) {
					throw $ex;
				}
				
				if (stripos ( $ex->getMessage (), "database is locked" ) !== false) {
					ZDBG2 ( "Database was locked while performing $operation - retrying..." );
					sleep ( 1 );
					continue;
				} else {
					
					ZWARNING ( "Deployment database exception while performing $operation: " . $ex->getMessage () );
					
					throw $ex;
				}
			}
		}
	}
	
	private function updateAppHealthCheck($appId, $health_check) {
		
		$query = $this->getQuery ( "update_app_health_check" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId);
		$stmt->bindValue ( ":health_check_path", $health_check);
		$res = $this->executeStatement($stmt);		
	}
	
	private function createDefinedApp($baseUrl, $appName, $appVersion, $healthCheck, $logo)
	{
		$package = new ZendDeployment_PackageFile();
		$package->setName($appName);
		$package->setVersion($appVersion);
		$package->setLogo($logo);
		$packageId = $this->insertNewPackage($package);
				
		$taskDescriptorId = $this->insertNewTaskDescriptor($packageId, array(), array("baseUrl" => $baseUrl), 0, time(), self::TASK_STATUS_ACTIVE);
		ZDBG3("Created descriptor $taskDescriptorId");
				
		$appId = $this->insertNewApp($appName, $appVersion, $baseUrl, $taskDescriptorId, $appName, -1, true);
		ZDBG3("Created app $appId");
		
		$appVersionId = $this->insertNewAppVersion($appId, $taskDescriptorId, $healthCheck);
		ZDBG3("Created app version $appVersionId");
		
		return $appId;			
	}

	public function updateApp($appId, $baseUrl, $userAppName) {
	
		$query = $this->getQuery ( "update_app" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId);
		$stmt->bindValue ( ":base_url", $baseUrl);
		$stmt->bindValue ( ":user_app_name", $userAppName);
		$res = $this->executeStatement($stmt);
	}
	
		
	public function updateRunOnceNodeId($origServerId, $newServerId) {
		try {
				
			ZDBG1 ( "ZendDeployment_DB_Handler updateRunOnceNodeId $origServerId => $newServerId");
			$this->beginTransaction ();
				
			$query = $this->getQuery ( "update_run_once_node_id" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":new_node_id", $newServerId);
			$stmt->bindValue ( ":orig_node_id", $origServerId);
			$res = $this->executeStatement($stmt);
			$this->commit ();
			
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}	
	
	public function redefineApplication($servers, $appId, $appVersionId, $installPath, $status) {
		try {
				
			ZDBG1 ( "ZendDeployment_DB_Handler redefineApplication " . $appVersionId );
				
			$this->beginTransaction ();
			
			$deploymentTime = time ();
			
			foreach ($servers as $server) {
				$currentStatus = $this->getAppActiveStatus($server, $appId);
				if (!$currentStatus) {
					$this->insertNewAppStatus($server, $appId, $appVersionId, $deploymentTime, $status, $installPath);
				} else {
					$this->updateAppStatus($currentStatus['app_status_id'], $status);
				}	
			}
							
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
	}
	
	public function defineApplication($servers, $baseUrl, $name, $installPath, $version, $healthCheck, $logo) {
	try {
			
			ZDBG1 ( "ZendDeployment_DB_Handler integrating application " . $baseUrl . " on servers " . implode(",", $servers));
			
			$this->beginTransaction ();
			$candidates = $this->getDefineableApplications();
			$theCandidate = NULL;
			
			foreach ($candidates as $candidate) {
				if ($candidate['base_url'] == $baseUrl ) {
					$theCandidate = $candidate;
					break;					
				}
			}
			$deploymentTime = time ();
			
			if (!$theCandidate) {

				ZDBG2("Creating new defined app");
				$appId = $this->createDefinedApp($baseUrl, $name, $version, $healthCheck, $logo);
											
				ZDBG2("Creating new defined app version");
				$appVersion = $this->getAppVersionByAppId($appId);
				
				ZDBG2("Creating new defined app task dec");
				$taskDesc = $this->getTaskDescriptorByApplicationId($appId);
				
				foreach ($servers as $server) {
					$this->insertNewAppStatus($server, $appId, $appVersion['app_version_id'], $deploymentTime, ZendDeployment_Application::STATUS_ACTIVE, $installPath);					
				}
							
			} else {
												
				$this->updateApp($theCandidate['app_id'], $baseUrl, $name);
				$this->updateAppHealthCheck($theCandidate['app_version_id'], $healthCheck);
				$this->updateAppVersion($theCandidate['app_id'], $version);
				$this->updateAppLogo($theCandidate['app_id'], $logo);
				
				$this->activateDefinedApp($theCandidate['app_id']);
				
				/* @var $app ZendDeployment_Application_Interface */
				$app = current($this->getApplications(
										array('appIds'=>array($theCandidate['app_id'])
						)));
				
				$serversWithApp = array_keys($app);
				$serversWithoutApp = array_diff($servers, $serversWithApp);
				ZDBG3("Creating new statuses for servers " . implode(",", $serversWithoutApp));
				if ($serversWithoutApp) {					
					foreach ($serversWithoutApp as $server) {
						$this->insertNewAppStatus($server, $theCandidate['app_id'], $theCandidate['app_version_id'], $deploymentTime, ZendDeployment_Application::STATUS_ACTIVE, $installPath);						
					}
				}				
			}
			
			
			$taskDescriptorId = $this->insertNewTaskDescriptor(-1, array(), array("baseUrl" => $baseUrl), 0, time(), self::TASK_STATUS_ACTIVE);
			foreach ($servers as $server) {
				$this->insertNewTask ( self::TASK_TYPE_APP_DEFINED, -1, $server, $taskDescriptorId, -1 );
			}
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
	}	

	private function updateAppVersion($appId, $version) {
		ZDBG2 ( "ZendDeployment_DB_Handler updateAppVersion for app $appId");
		
		$query = $this->getQuery ( "update_package_version_by_app_id");
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId);
		$stmt->bindValue ( ":version", $version);
		$res = $this->executeStatement($stmt);
	}
	
	private function updateAppLogo($appId, $logo) {
		ZDBG2 ( "ZendDeployment_DB_Handler updateAppLogo for app $appId");
		
		$query = $this->getQuery ( "update_package_logo_by_app_id");
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId);
		$stmt->bindValue ( ":logo", base64_encode($logo));
		$res = $this->executeStatement($stmt);
	}
	
	private function updateAppStatus($appStatusId, $status) {
		ZDBG2 ( "ZendDeployment_DB_Handler updateAppStatus for app status $appStatusId");
				
		$query = $this->getQuery ( "update_app_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_status_id", $appStatusId);
		$stmt->bindValue ( ":status", $status);
		$res = $this->executeStatement($stmt);
		
	}
		
	private function deleteAppVersionStatuses($appVersionId) {
		ZDBG2 ( "ZendDeployment_DB_Handler deleteAppStatuses for app version $appVersionId");
				
		$query = $this->getQuery ( "delete_app_version_statuses" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_version_id", $appVersionId);
		$res = $this->executeStatement($stmt);		
	}
	
	public function removeDefinedApp(array $servers, $appId) {
		
		try {
			ZDBG2 ( "ZendDeployment_DB_Handler removeDefinedApp for appId $appId");
			
			$this->beginTransaction ();

			$query = $this->getQuery ( "change_defined_app_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $appId);
			$stmt->bindValue ( ":new_status", ZendDeployment_Application::STATUS_INTEGRATION_CANDIDATE);
			$res = $this->executeStatement($stmt);		
			
			$taskDescId = $this->insertNewTaskDescriptor(-1, array(), array('appId' => $appId), -1, time(), self::TASK_STATUS_ACTIVE);
			foreach ($servers as $server) {
				$taskId = $this->insertNewTask ( self::TASK_TYPE_APP_UNDEFINED, -1, $server, $taskDescId , -1 );
			}
			
			$this->commit();
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
			
	}
	
	public function removeApp($appId) {
	
		try {
			ZDBG2 ( "ZendDeployment_DB_Handler removeApp for appId $appId");
				
			$this->beginTransaction ();
			
			$appVersion = $this->getAppVersionByAppId($appId);
			$appVersionId = $appVersion['app_version_id'];
			$this->deleteAppVersionStatuses($appVersionId);
			
			$this->commit();		
			
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
		$this->cleanupObsoleteEntries();
			
	}
	
	private function activateDefinedApp($appId) {
		ZDBG2 ( "ZendDeployment_DB_Handler activateDefinedApp for appId $appId");
				
		$query = $this->getQuery ( "change_defined_app_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId);
		$stmt->bindValue ( ":new_status", ZendDeployment_Application::STATUS_ACTIVE);
		$res = $this->executeStatement($stmt);		
	}
	
	public function getDeployedApplicationNames() {
		ZDBG2 ( "ZendDeployment_DB_Handler getDeployedApplicationNames");
		
		$list = array();
		
		$query = $this->getQuery ( "get_deployed_app_names" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue(":status", ZendDeployment_Application::STATUS_INTEGRATION_CANDIDATE);
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$list[] = $row['user_app_name'];		
			}
		}
		
		return $list;
		
	}
	
	public function getDeployedBaseUrls() {
		ZDBG2 ( "ZendDeployment_DB_Handler getDeployedBaseUrls");
	
		$list = array();
	
		$query = $this->getQuery ( "get_deployed_base_urls" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue(":status", ZendDeployment_Application::STATUS_INTEGRATION_CANDIDATE);
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$list[] = $row['base_url'];
			}
		}
	
		return $list;
	
	}
	
	public function getDefaultServerName() {
		ZDBG2 ( "ZendDeployment_DB_Handler getDefaultServerName");
	
		$query = $this->getQuery ( "get_default_server_name" );
		$stmt = $this->_dbh->prepare ( $query );
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				return $row['name'];
			}
		}
	
		return "";
	
	}
	
	public function getLibraryIdByName($name) {
		ZDBG2 ( "ZendDeployment_DB_Handler getLibraryIdByName by $name");
		
		$query = $this->getQuery ( "get_library_id_by_name" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue(":name", $name);
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				return $row['lib_id'];
			}
		}
		
		return -1;
		
	}
	
	public function getDefineableApplications() {
		
		ZDBG2 ( "ZendDeployment_DB_Handler getDefineableApplications");
		
		$list = array();
		
		$query = $this->getQuery ( "get_integration_candidates" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":status", ZendDeployment_Application::STATUS_INTEGRATION_CANDIDATE);
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$baseUrl = $row['base_url'];
				$appName = $row['name'];
				$healthCheck = $row['health_check_path'];
				$version = "1.0.0";
				
				$details = array();
				$details['install_path'] = $row['install_path'];
				//$details['document_root'] = $path;
				$details['base_url'] = $baseUrl;
				$details['name'] = $appName;
				$details['version'] = $version;
				$details['health_check_path'] = $healthCheck;
				
				$details['app_version_id'] = $row['app_version_id'];
				$details['app_id'] = $row['app_id'];
				$details['app_status_id'] = $row['app_status_id'];

				$details['task_descriptor_id'] = $row['task_descriptor_id'];
				
				$list[] = $details;				
			}
		}
		
				
		return $list;
	}
	
	/**
	 * Deploy a package in given servers
	 * @param array $servers list of servers
	 * @param ZendDeployment_PackageMetaData_Interface $package package to deploy
	 * @param array $userParams user provided params 
	 * @param array $zendParams internal deployment related params
	 * 
	 * @return string application id
	 */
	public function insertDeployApplicationTask(array $servers, $package, array $userParams, array $zendParams, $runOnceServer) {
		try {
			
			ZDBG1 ( "ZendDeployment_DB_Handler deploying application " . $package->getPackagePath () . " on servers (" . implode ( " ", $servers ) . ")" );
			
			$this->beginTransaction ();
			
			$deploymentTime = time ();
			
			$packageId = $package->getPersistentId ();
			$isPendingDeployment = true;
			if ($packageId == ZendDeployment_PackageFile::ID_NONE) {
				$isPendingDeployment = false;
				// insert the new package to the DB
				$packageId = $this->insertNewPackage ( $package );
				ZDBG2 ( "DB: new package id is $packageId" );
				$package->setPersistentId ( $packageId );
			}
			ZDBG2 ( "DB: Package id is " . $packageId );
			
			if (! $isPendingDeployment) {
				$taskDescriptorId = $this->insertNewTaskDescriptor ( $packageId, $userParams, $zendParams, $runOnceServer, $deploymentTime, self::TASK_STATUS_ACTIVE );
			} else {
				$taskDescriptorId = $this->updatePendingTask ( $zendParams ['baseUrl'], $userParams, $zendParams );
			}
			
			$appId = $this->insertNewApp ( $package->getName (), $package->getVersion (), $zendParams ['baseUrl'], $taskDescriptorId, $zendParams ['userApplicationName'], $zendParams['vhostId'] );
			
			$groupId = $this->insertNewSequence ();
			
			$appVersionId = $this->insertNewAppVersion ( $appId, $taskDescriptorId, $package->getHealthCheckPath() );
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewAppStatus ( $server, $appId, $appVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
				$taskId = $this->insertNewTask ( self::TASK_TYPE_DEPLOY, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );				
			}
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	/**
	 * Deploy a package in given servers
	 * @param array $servers list of servers
	 * @param ZendDeployment_PackageMetaData_Interface $package package to deploy
	 * @param array $userParams user provided params
	 * @param array $zendParams internal deployment related params
	 *
	 * @return string application id
	 */
	public function insertDeployLibraryTask(array $servers, $package, $userParams, array $zendParams) {
		try {
				
			ZDBG1 ( "ZendDeployment_DB_Handler deploying application " . $package->getPackagePath () . " on servers (" . implode ( " ", $servers ) . ")" );
				
			$this->beginTransaction ();
				
			$deploymentTime = time ();
				
			$packageId = $this->insertNewPackage ( $package );
			ZDBG2 ( "DB: new package id is $packageId" );
			$package->setPersistentId ( $packageId );
							
			$taskDescriptorId = $this->insertNewTaskDescriptor ( $packageId, $userParams, $zendParams, -1, $deploymentTime, self::TASK_STATUS_ACTIVE );

			$libId = $this->getLibraryIdByName($package->getName ());
			$isDefault = false;
			if ($libId < 0) {
				$libId = $this->insertNewLibrary ( $package->getName (), $package->getVersion (), $taskDescriptorId);
				$isDefault = true;
			} else {
				$isDefault = (isset($zendParams['isDefault']) && $zendParams['isDefault']);
			}
				
			$groupId = $this->insertNewSequence ();
			
			$libVersionId = $this->insertNewLibraryVersion ( $libId, $taskDescriptorId, $isDefault);
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewLibraryStatus ( $server, $libId, $libVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
				$taskId = $this->insertNewTask ( self::TASK_TYPE_DEPLOY, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
			}
				
			$this->commit ();
	
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	/**
	 * Resume a pending deployment task (fill in apps and tasks)
	 * @param array $server
	 * @param string $baseUrl
	 * @param integer $taskDescriptorId
	 * @param ZendDeployment_PackageMetaData_Interface
	 * @param array $zendParams internal deployment related params
	 * 
	 * @return string application id
	 */
	public function resumePendingDeployment($servers, $baseUrl, $taskDescriptorId, $package, $zendParams) {
		try {
			
			$this->beginTransaction ();
			
			$appId = $this->insertNewApp ( $package->getName (), $package->getVersion (), $zendParams ['baseUrl'], $taskDescriptorId, $zendParams ['userApplicationName'], $zendParams['vhostId'] );
			
			$groupId = $this->insertNewSequence ();
			
			$deploymentTime = time ();
			
			$appVersionId = $this->insertNewAppVersion ( $appId, $taskDescriptorId, $package->getHealthCheckPath() );
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$taskId = $this->insertNewTask ( self::TASK_TYPE_DEPLOY, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
				$this->insertNewAppStatus ( $server, $appId, $appVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
			}
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function insertRollbackTask(array $servers, $applicationId, array $zendParams) {
		
		try {
			
			ZDBG1 ( "ZendDeployment_DB_Handler rollbacking application " . $applicationId . " on servers (" . implode ( " ", $servers ) . ")" );
			
			$runOnceServer = $this->chooseRunOnceServer($servers, $applicationId);
			
			$taskTime = time ();
			
			// retrieve the package id 
			$taskDescriptor = $this->getTaskDescriptorByApplicationId ( $applicationId );
			
			//override the zend params with the new ones (if exist)
			$zendParams = array_merge ( $taskDescriptor->getZendParams (), $zendParams );
			
			//add createVhost for non default server urls
			if (! strstr ( $zendParams ['baseUrl'], "<default" )) {
				$zendParams ['createVhost'] = "1";
			}
				
			$this->beginTransaction ();
			
			$taskDescriptorId = $this->insertNewTaskDescriptor ( $taskDescriptor->getPackageId (), $taskDescriptor->getUserParams (), $zendParams, $runOnceServer, $taskTime, self::TASK_STATUS_ACTIVE );
			
			$groupId = $this->insertNewSequence ();
			
			$appVersions = $this->getAppVersionsListByAppId ( $applicationId );
			
			$appVersion = array_shift($appVersions);
			if ($appVersions) {
				// set the previous version as master version
				$olderAppVersion = array_shift($appVersions);
				$this->updateAppVersionLastUsed($olderAppVersion['app_version_id']);
			}
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewAppStatus ( $server, $applicationId, $appVersion ['app_version_id'], $taskTime, ZendDeployment_Application::STATUS_WAITING_FOR_ROLLBACK );
				$taskId = $this->insertNewTask ( self::TASK_TYPE_ROLLBACK, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );				
			}
			
			$this->commit ();
			
			return $this->_dbh->lastInsertId ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	private function updateAppVersionLastUsed($appVersionId) {
		ZDBG3 ( "DB: updating app version last run for app version $appVersionId" );
		
		$query = $this->getQuery ( "update_app_version_last_used" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":last_used", time());
		$stmt->bindValue ( ":app_version_id", $appVersionId);
		
		$res = $this->executeStatement($stmt);		
	}
	
	private function getMasterAppVersion($applicationId) {
		$appVersions = $this->getAppVersionsListByAppId( $applicationId );
		$masterAppVersion = array_shift($appVersions);
		
		return $masterAppVersion;
	}
	
	private function chooseRunOnceServer($servers, $applicationId) {
		ZDBG3("DB: Choosing run once server for app $applicationId from servers " . implode(",", $servers));
		$apps = $this->getApplications(array("appIds" => array($applicationId)));
		
		$appByServers = array_pop($apps);
		$masterAppVersion = $this->getMasterAppVersion($applicationId);
		
		foreach ($appByServers as $serverKey => $app) {
			if ($app->getAppVersionId() != $masterAppVersion['app_version_id']) {
				unset($appByServers[$serverKey]);
			}
		}
		if (!$appByServers) {
			throw new ZendDeployment_Exception("Unable to locate an enabled server with master version application $applicationId", ZendDeployment_Exception::INTERNAL_SERVER_ERROR);
		}
		
		$servesWithApp = array_keys($appByServers);
		$sharedServers = array_intersect($servers, $servesWithApp);
		if (!$appByServers) {
			throw new ZendDeployment_Exception("Unable to locate an enabled server with application $applicationId from given servers", ZendDeployment_Exception::INTERNAL_SERVER_ERROR);
		}
			
		$runOnceServer = array_shift($sharedServers);
		ZDBG2("DB: Chose server $runOnceServer as run once server for app $applicationId");
		return $runOnceServer;
	}
	
	public function insertRedeployTask(array $servers, $applicationId, array $zendParams) {
		
		try {
			
			ZDBG1 ( "ZendDeployment_DB_Handler redeploying application " . $applicationId . " on servers (" . implode ( " ", $servers ) . ")" );
			
			$runOnceServer = $this->chooseRunOnceServer($servers, $applicationId);
			
			$this->beginTransaction ();
			
			$taskTime = time ();
			
			// retrieve the package id 
			$taskDescriptor = $this->getTaskDescriptorByApplicationId ( $applicationId );
			
			//override the zend params with the new ones (if exist)
			$zendParams = array_merge ( $taskDescriptor->getZendParams (), $zendParams );
			
			//add createVhost for non default server urls
			if (! strstr ( $zendParams ['baseUrl'], "<default" )) {
				$zendParams ['createVhost'] = "1";
			}
			
			$taskDescriptorId = $this->insertNewTaskDescriptor ( $taskDescriptor->getPackageId (), $taskDescriptor->getUserParams (), $zendParams, $runOnceServer, $taskTime, self::TASK_STATUS_ACTIVE );
			
			$groupId = $this->insertNewSequence ();
			
			$appVersions = $this->getAppVersionsListByAppId( $applicationId );
			$masterAppVersion = array_shift($appVersions);
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewAppStatus ( $server, $applicationId, $masterAppVersion ['app_version_id'], $taskTime, ZendDeployment_Application::STATUS_WAITING_FOR_REDEPLOY );
				$taskId = $this->insertNewTask ( self::TASK_TYPE_REDEPLOY, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
			}
			
			$this->commit ();
			
			return $this->_dbh->lastInsertId ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function getBaseUrlByAppId($appId) {
		ZDBG3 ( "DB: looking for base url of app id " . $appId );
		
		$query = $this->getQuery ( "get_base_url_by_app_id" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId );
		$res = $this->executeStatement($stmt);
		$baseUrl = NULL;
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$baseUrl = $row ['base_url'];
			}
		}
		
		ZDBG3 ( "DB: found base url " . $baseUrl );
		return $baseUrl;
	}
	
	public function getAppIdByBaseUrl($baseUrl) {
		ZDBG3 ( "DB: looking for app id of base url" . $baseUrl );
	
		$query = $this->getQuery ( "get_app_id_by_base_url" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":base_url", $baseUrl );
		$res = $this->executeStatement($stmt);
		$appId = NULL;
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$appId = $row ['app_id'];
			}
		}
	
		ZDBG3 ( "DB: found appId " . $appId );
		return $appId;
	}
	
	public function cleanupObsoleteEntries() {
		
		ZDBG2 ( "Cleaning obsolete entries" );
		
		try {
			$this->_dbh->beginTransaction();
		
			$query = $this->getQuery ( "delete_obsolete_apps_version" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$query = $this->getQuery ( "delete_obsolete_apps" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$query = $this->getQuery ( "delete_obsolete_descriptors" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue(":status", $stmt->bindValue ( ":status", self::TASK_STATUS_ACTIVE ));
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$query = $this->getQuery ( "delete_obsolete_packages" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$query = $this->getQuery ( "delete_obsolete_package_data" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$this->_dbh->commit();
			
		} catch ( Exception $ex ) {
			$this->rollback ();
		}
	
	}
	
	private function deleteAppStatus($appStatusId) {
		$query = $this->getQuery ( "delete_app_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_status_id", $appStatusId );
		$res = $this->executeStatement($stmt);
	}
	
	public function rollbackAppStatus($appStatusId, $applicationId, $server) {
		ZDBG2 ( "Rolling back app status $appStatusId on node $server" );
		
		try {
			$this->beginTransaction ();
			
			$taskDescId = $this->getTaskDescriptorByApplicationStatusId ( $appStatusId );
			
			ZDBG2 ( "deleting task by task descriptor  " . $taskDescId->getId () . " for server $server" );
			$query = $this->getQuery ( "delete_task" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":node_ids", $server );
			$stmt->bindValue ( ":task_descriptor_id", $taskDescId->getId () );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$query = $this->getQuery ( "delete_app_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_status_id", $appStatusId );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$query = $this->getQuery ( "reactivate_app_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":next_status", $appStatusId );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$this->commit ();
		
		} catch ( Exception $ex ) {
			$this->rollback ();
		}
	}
	
	public function insertRemoveAllTask(array $servers) {
		ZDBG1 ( "DB: insertRemoveAllTask called with servers (" . implode ( " ", $servers ) . ")" );
		
		try {
			$this->beginTransaction ();
			
			$groupId = $this->insertNewSequence ();
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewTask ( self::TASK_TYPE_DELETE_APPLICATIONS_DATA, $groupId, $server, - 1 );
			}
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function insertRemoveTask(array $servers, $applicationId, array $zendParams) {
		
		for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
			
			try {
				
				ZDBG1 ( "ZendDeployment_DB_Handler remove application " . $applicationId . " on servers (" . implode ( " ", $servers ) . ")" );
				
				$runOnceServer = $this->chooseRunOnceServer($servers, $applicationId);
				
				$taskTime = time ();
				
				$baseUrl = $this->getBaseUrlByAppId ( $applicationId );
				if (! $baseUrl) {
					throw new ZendDeployment_Exception ( "No such application id " . $applicationId, ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
				}
				
				$zendParams ['baseUrl'] = $baseUrl;
				
				$this->beginTransaction ();
												
				$taskDescriptorId = $this->insertNewTaskDescriptor ( - 1, array (), $zendParams, $runOnceServer, $taskTime, self::TASK_STATUS_ACTIVE );
				
				$groupId = $this->insertNewSequence ();
				
				$appVersion = $this->getAppVersionByAppId ( $applicationId );
				
				foreach ( $servers as $server ) {
					// create new app status for each server
					$this->insertNewAppStatus ( $server, $applicationId, $appVersion ['app_version_id'], $taskTime, ZendDeployment_Application::STATUS_WAITING_FOR_REMOVE );
				}									
				$this->commit ();
				
				$this->beginTransaction();
				foreach ( $servers as $server ) {
					$taskId = $this->insertNewTask ( self::TASK_TYPE_REMOVE, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
				}
				$this->commit();
				
				return $this->_dbh->lastInsertId ();
			
			} catch ( PDOException $ex ) {
				$this->rollback ();
				
				if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
					ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
					sleep ( 1 );
					continue;
				}
				
				throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
			}
		}
	}
	
	private function updateLibraryStatus($libraryId, $newStatus) {
		ZDBG2 ( "updating all statuses of library $libraryId to $newStatus");
		$query = $this->getQuery ( "update_library_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":lib_id", $libraryId );
		$stmt->bindValue ( ":status", $newStatus);
		$res = $this->executeStatement($stmt);
		unset($stmt);
	}
	
	private function updatePluginStatus($pluginId, $newStatus) {
	    ZDBG2 ( "updating all statuses of plugin $pluginId to $newStatus");
	    $query = $this->getQuery ( "update_plugin_status" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_id", $pluginId );
	    $stmt->bindValue ( ":status", $newStatus);
	    $res = $this->executeStatement($stmt);
	    unset($stmt);
	}
	
	private function updateLibraryVersionStatus($libraryVersionId, $newStatus) {
		ZDBG2 ( "updating library version $libraryVersionId status to $newStatus");
		$query = $this->getQuery ( "update_library_version_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":lib_version_id", $libraryVersionId );
		$stmt->bindValue ( ":status", $newStatus);
		$res = $this->executeStatement($stmt);
		unset($stmt);
	}
	
	public function insertRemoveLibraryTask(array $servers, $libraryId, array $zendParams) {
	
		for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
				
			try {
	
				ZDBG1 ( "ZendDeployment_DB_Handler remove library " . $libraryId . " on servers (" . implode ( " ", $servers ) . ")" );
	
				$taskTime = time ();
	
				$zendParams ['libraryId'] = $libraryId;
	
				$this->beginTransaction ();

				$this->updateLibraryStatus($libraryId, ZendDeployment_Application::STATUS_WAITING_FOR_REMOVE);
				
				$taskDescs = $this->getTaskDescriptorsByLibraryId($libraryId);
				foreach($taskDescs as $taskDesc) { /* @var $taskDesc TaskDescriptor */
				
					$taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
		
					$groupId = $this->insertNewSequence ();
		
					foreach ( $servers as $server ) {
						$taskId = $this->insertNewTask ( self::TASK_TYPE_REMOVE, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
					}
				}
	
				$this->commit ();
	
				return $this->_dbh->lastInsertId ();
					
			} catch ( PDOException $ex ) {
				$this->rollback ();
	
				if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
					ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
					sleep ( 1 );
					continue;
				}
	
				throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
			}
		}
	}
	
	public function insertRemoveLibraryVersionTask(array $servers, $libraryVersionId, array $zendParams) {
	
		for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
	
			try {
	
				ZDBG1 ( "ZendDeployment_DB_Handler remove library version" . $libraryVersionId . " on servers (" . implode ( " ", $servers ) . ")" );
	
				$taskTime = time ();
	
				$zendParams ['libraryVersionId'] = $libraryVersionId;
	
				$this->beginTransaction ();
	
				$this->updateLibraryVersionStatus($libraryVersionId, ZendDeployment_Application::STATUS_WAITING_FOR_REMOVE);
	
				$taskDesc = $this->getTaskDescriptorByLibraryVersionId($libraryVersionId);
				
				$taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
	
				$groupId = $this->insertNewSequence ();
				foreach ( $servers as $server ) {
					$taskId = $this->insertNewTask ( self::TASK_TYPE_REMOVE, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
				}		
	
				$this->commit ();
	
				return $this->_dbh->lastInsertId ();
					
			} catch ( PDOException $ex ) {
				$this->rollback ();
	
				if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
					ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
					sleep ( 1 );
					continue;
				}
	
				throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
			}
		}
	}
	
	public function insertRedeployLibraryVersionTask(array $servers, $libraryVersionId, array $zendParams) {
	
		for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
	
			try {
	
				ZDBG1 ( "ZendDeployment_DB_Handler redeploy library version" . $libraryVersionId . " on servers (" . implode ( " ", $servers ) . ")" );
	
				$taskTime = time ();
	
				$zendParams ['libraryVersionId'] = $libraryVersionId;
	
				$this->beginTransaction ();
	
				$this->updateLibraryVersionStatus($libraryVersionId, ZendDeployment_Application::STATUS_WAITING_FOR_REDEPLOY);
	
				$taskDesc = $this->getTaskDescriptorByLibraryVersionId($libraryVersionId);
	
				$taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
	
				$groupId = $this->insertNewSequence ();
				foreach ( $servers as $server ) {
					$taskId = $this->insertNewTask ( self::TASK_TYPE_REDEPLOY, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
				}
	
				$this->commit ();
	
				return $this->_dbh->lastInsertId ();
					
			} catch ( PDOException $ex ) {
				$this->rollback ();
	
				if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
					ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
					sleep ( 1 );
					continue;
				}
	
				throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
			}
		}
	}
	
	/**
	 * Upgrade a package in given servers
	 * @param array $servers list of servers
	 * @param ZendDeployment_PackageMetaData_Interface $package package to deploy
	 * @param string $applicationId
	 * @param array $userParams user provided params 
	 * @param array $zendParams internal deployment related params
	 * 
	 * @return string application id
	 */
	public function insertUpgradeTask(array $servers, ZendDeployment_PackageMetaData_Interface $package, $applicationId, array $userParams, array $zendParams) {
		try {
			
			ZDBG1 ( "ZendDeployment_DB_Handler upgrade application " . $package->getPackagePath () . " on servers (" . implode ( " ", $servers ) . ")" );
			
			$runOnceServer = $this->chooseRunOnceServer($servers, $applicationId);
			
			$deploymentTime = time ();
			
			$baseUrl = $this->getBaseUrlByAppId ( $applicationId );
			if (! $baseUrl) {
				throw new ZendDeployment_Exception ( "No such application id " . $applicationId, ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
			}
			$zendParams ['baseUrl'] = $baseUrl;
			
			$this->beginTransaction ();
			
			$this->updatePendingTask ( $baseUrl, $userParams, $zendParams );
			$pending = $this->getPendingTasks ( $baseUrl );
			$taskDescriptorId = $pending [$baseUrl] ['task_descriptor_id'];
			if (! isset ( $zendParams ['userApplicationName'] )) {
				$zendParams ['userApplicationName'] = "";
			}
			
			$groupId = $this->insertNewSequence ();
			
			$appVersionId = $this->insertNewAppVersion ( $applicationId, $taskDescriptorId, $package->getHealthCheckPath() );
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewAppStatus ( $server, $applicationId, $appVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_UPGRADE );
				$taskId = $this->insertNewTask ( self::TASK_TYPE_UPGRADE, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );				
			}
			
			$this->commit ();
			
			return $this->_dbh->lastInsertId ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function insertUpgradePluginTask(array $servers, ZendDeployment_PackageMetaData_Interface $package, $pluginId, $auditId, $taskDescriptorId) {
	    try {
	        	
	        ZDBG1 ( "ZendDeployment_DB_Handler upgrade plugin " . $package->getPackagePath () . " on servers (" . implode ( " ", $servers ) . ")" );
	        	
	        $deploymentTime = time ();
	        	
	        $this->beginTransaction ();
	        
	        $groupId = $this->insertNewSequence ();
	        	
	        $pluginVersionId = $this->insertNewPluginVersion($pluginId, $taskDescriptorId);
	        	
	        foreach ( $servers as $server ) {
	            // create the task for each server
	            $newId = $this->insertNewPluginStatus ( $server, $pluginId, $pluginVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
	            
	            // get the current app status id and save it
	            $activeStatus = $this->getPluginActiveStatus ( $server, $pluginId );
	            $this->connectPluginsStatuses($newId, $activeStatus ['plugin_status_id']);
	            $taskId = $this->insertNewTask ( self::TASK_TYPE_UPGRADE, $groupId, $server, $taskDescriptorId, $auditId );
	        }
	
	        $this->commit ();
	        	
	        return $this->_dbh->lastInsertId ();
	
	    } catch ( PDOException $ex ) {
	        $this->rollback ();
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	/**
	 * 
	 * Return the task descriptor of an application 
	 * @param string $applicationId
	 * @return TaskDescriptor 
	 */
	private function getTaskDescriptorByApplicationStatusId($applicationStatusId) {
		$desc = array ();
		$query = $this->getQuery ( "get_task_descriptor_by_application_status_id" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_status_id", $applicationStatusId );
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$res = new TaskDescriptor ();
				$res->setPackageId ( $row ['package_id'] );
				$res->setUserParams ( $this->unserializeParams ( $row ['user_params'] ) );
				$res->setZendParams ( $this->unserializeParams ( $row ['zend_params'] ) );
				$res->setId ( $row ['task_descriptor_id'] );
				
				return $res;
			}
		}
		return array ();
	}
	
	/**
	 *
	 * Return the task descriptors of a library
	 * @param string $libraryId
	 * @return array
	 */
	private function getTaskDescriptorsByLibraryId($libraryId) {
		
		$descs = array ();
		$query = $this->getQuery ( "get_task_descriptors_by_library_id" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":lib_id", $libraryId );
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$res = new TaskDescriptor ();
				$res->setPackageId ( $row ['package_id'] );
				$res->setUserParams ( $this->unserializeParams ( $row ['user_params'] ) );
				$res->setZendParams ( $this->unserializeParams ( $row ['zend_params'] ) );
				$res->setId ( $row ['task_descriptor_id'] );
	
				$descs[] = $res;
			}
		}
		return $descs;
	}
	
	/**
	 *
	 * Return the task descriptor of a library version
	 * @param string $libraryVersionId
	 * @return array
	 */
	public function getTaskDescriptorByLibraryVersionId($libraryVersionId) {
	
		$query = $this->getQuery ( "get_task_descriptors_by_library_version_id" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":lib_version_id", $libraryVersionId );
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$res = new TaskDescriptor ();
				$res->setPackageId ( $row ['package_id'] );
				$res->setUserParams ( $this->unserializeParams ( $row ['user_params'] ) );
				$res->setZendParams ( $this->unserializeParams ( $row ['zend_params'] ) );
				$res->setId ( $row ['task_descriptor_id'] );
	
				return $res;
			}
		}
		return null;
	}
	
	/**
	 * 
	 * Return the task descriptor of an application 
	 * @param string $applicationId
	 * @return TaskDescriptor 
	 */
	private function getTaskDescriptorByApplicationId($applicationId) {
		static $descs = array ();
		
		if (isset($descs[$applicationId])) {
			return $descs[$applicationId];
		}
		$query = $this->getQuery ( "get_task_descriptor_by_application_id" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $applicationId );
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				$res = new TaskDescriptor ();
				$res->setPackageId ( $row ['package_id'] );
				$res->setUserParams ( $this->unserializeParams ( $row ['user_params'] ) );
				$res->setZendParams ( $this->unserializeParams ( $row ['zend_params'] ) );
				$res->setId ( $row ['task_descriptor_id'] );
				
				$descs[$applicationId] = $res;
				return $res;
			}
		}
		return array ();
	}
	
	/**
	 * 
	 * Add a new app to the DB
	 * @param string $name
	 * @param string $version
	 * @param string $baseUrl
	 * @param string $taskDescriptorId
	 * @param string $userAppName
	 */
	private function insertNewApp($name, $version, $baseUrl, $taskDescriptorId, $userAppName, $vhostId, $isDefined = false) {
		
		ZDBG2 ( "DB: Creating new application - " . $name );
		
		$query = $this->getQuery ( "insert_application" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":base_url", $baseUrl );
		$stmt->bindValue ( ":user_app_name", $userAppName );
		$stmt->bindValue ( ":is_defined", $isDefined?1:0 );
		$stmt->bindValue ( ":vhost_id", $vhostId );
		$this->executeStatement($stmt);
		return $this->_dbh->lastInsertId ();
	}
	
	
	/**
	 *
	 * Add a new app to the DB
	 * @param string $name
	 * @param string $version
	 * @param string $baseUrl
	 * @param string $taskDescriptorId
	 * @param string $userAppName
	 */
	private function insertNewLibrary($name, $version, $taskDescriptorId, $isDefined = false) {
	
		ZDBG2 ( "DB: Creating new library - " . $name );
	
		$query = $this->getQuery ( "insert_library" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":is_defined", $isDefined?1:0 );
		$this->executeStatement($stmt);
		return $this->_dbh->lastInsertId ();
	}
	private function insertNewAppVersion($appId, $taskDescriptorId, $healthCheckPath) {
		
		ZDBG1 ( "DB: creating new app version for app $appId with task descriptor $taskDescriptorId " );
		
		// insert the new app version
		$now = time();
		
		$query = $this->getQuery ( "insert_application_version" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_id", $appId );
		$stmt->bindValue ( ":task_descriptor_id", $taskDescriptorId );
		$stmt->bindValue ( ":health_check_path", $healthCheckPath );
		$stmt->bindValue ( ":last_used", $now);
		$stmt->bindValue ( ":creation_time", $now);		
		$this->executeStatement($stmt);
		$newId = $this->_dbh->lastInsertId ();
		
		return $newId;
	}
	
	private function insertNewLibraryVersion($libId, $taskDescriptorId, $isDefault) {
	
		ZDBG1 ( "DB: creating new app version for lib $libId with task descriptor $taskDescriptorId " );
	
		if ($isDefault) {
			$this->resetLibraryDefaults($libId);
		}
		// insert the new app version
		$now = time();
	
		$query = $this->getQuery ( "insert_library_version" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":lib_id", $libId );
		$stmt->bindValue ( ":task_descriptor_id", $taskDescriptorId );
		$stmt->bindValue ( ":creation_time", $now);
		$stmt->bindValue ( ":is_default", $isDefault?1:0);
		$this->executeStatement($stmt);
		$newId = $this->_dbh->lastInsertId ();
	
		return $newId;
	}
	
	/**
	 * 
	 * Add a app status to a new added application
	 * @param string $nodeId
	 * @param string $appId
	 * @param integer $time
	 */
	private function insertNewAppStatus($nodeId, $appId, $appVersionId, $time, $status, $installPath = "") {
		
		ZDBG1 ( "DB: creating new app status for node $nodeId. (app version $appVersionId)" );
		
		// get the current app status id and save it
		$activeStatus = $this->getAppActiveStatus ( $nodeId, $appId );
		$currentId = $activeStatus ['app_status_id'];
		
		$previousStatus = $this->getStatusByNextStatus ($currentId);
		if ($previousStatus && ($previousStatus['app_version_id'] == $activeStatus['app_version_id']) ) {
			ZDBG3("The active status is a stale command status from before - removing it");
			$this->deleteAppStatus($currentId);
			$activeStatus = $previousStatus;
			$currentId = $activeStatus ['app_status_id'];
		}
		
		if (!$installPath) {
			$installPath = $activeStatus ? $activeStatus ['install_path'] : "";
		}
		
		// insert the new app status
		$query = $this->getQuery ( "insert_application_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":app_version_id", $appVersionId );
		$stmt->bindValue ( ":node_id", $nodeId );
		$stmt->bindValue ( ":status", $status );
		$stmt->bindValue ( ":last_updated", $time );
		$stmt->bindValue ( ":install_path", $installPath );
		$stmt->bindValue ( ":health_status", ZendDeployment_Application::HEALTH_OK);
		$this->executeStatement($stmt);
		$newId = $this->_dbh->lastInsertId ();
		unset($stmt);
		
		if ($activeStatus) {
			// connect the statuses
			ZDBG3 ( "Connecting old status $currentId with new status $newId" );
			$query = $this->getQuery ( "update_application_status_next_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_status_id", intval($currentId) );
			$stmt->bindValue ( ":next_status", intval($newId) );
			$this->executeStatement($stmt);
		}
		
		return $newId;
	}
	
	/**
	 *
	 * Add a lib status to a new added library
	 * @param string $nodeId
	 * @param string $libId
	 * @param integer $time
	 */
	private function insertNewLibraryStatus($nodeId, $libId, $libVersionId, $time, $status, $installPath = "") {
	
		ZDBG1 ( "DB: creating new lib status for node $nodeId. (lib version $libVersionId)" );
	
		// insert the new lib status
		$query = $this->getQuery ( "insert_library_status" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":lib_version_id", $libVersionId );
		$stmt->bindValue ( ":node_id", $nodeId );
		$stmt->bindValue ( ":status", $status );
		$stmt->bindValue ( ":last_updated", $time );
		$stmt->bindValue ( ":install_path", $installPath );
		$this->executeStatement($stmt);
		$newId = $this->_dbh->lastInsertId ();
			
		return $newId;
	}
	
	/**
	 * 
	 * Return an application status 
	 * @param integer $nodeId
	 * @param string $appId
	 * @return string status
	 * @throws ZendDeployment_Exception
	 */
	public function getAppActiveStatus($nodeId, $appId) {
		try {
			ZDBG2 ( "DB: getting app $appId status for node $nodeId" );
			
			$query = $this->getQuery ( "get_application_active_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $appId );
			$stmt->bindValue ( ":node_id", $nodeId );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					ZDBG2 ( "DB: found status " . $row ['status'] );
					return $row;
				}
			}
			
			ZDBG2 ( "DB: did not find status" );
			return false;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function getPluginActiveStatus($nodeId, $pluginId) {
	    try {
	        ZDBG2 ( "DB: getting plugin $pluginId status for node $nodeId for pluginId: $pluginId" );
	        	
	        $query = $this->getQuery ( "get_plugin_active_status" );
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue ( ":plugin_id", $pluginId );
	        $stmt->bindValue ( ":node_id", $nodeId );
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                ZDBG2 ( "DB: found status " . $row ['status'] );
	                return $row;
	            }
	        }
	        	
	        ZDBG2 ( "DB: did not find status" );
	        return false;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	public function getAppDetails($appId) {
	ZDBG2 ( "DB: getAppDetails appId $appId" );
			
			$query = $this->getQuery ( "get_app_details" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $appId );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					return $row;
				}
			}
	}
	
	private function getStatusByNextStatus($nextStatus) {
		try {
			ZDBG2 ( "DB: getStatusByNextStatus by $nextStatus" );
			
			$query = $this->getQuery ( "get_status_by_next_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":next_status", $nextStatus );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					ZDBG2 ( "DB: found status " . $row ['status'] );
					return $row;
				}
			}
			
			ZDBG2 ( "DB: did not find status" );
			return false;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	
	private function getAppVersionByAppId($appId) {
		try {
			ZDBG2 ( "DB: getting app $appId version" );
			
			$query = $this->getQuery ( "get_application_version_by_app_id" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $appId );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					return $row;
				}
			}
			
			return false;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	/**
	 * 
	 * Returns array of app versions according to 'last used' order
	 * @param string $appId
	 * @return array
	 * @throws ZendDeployment_Exception
	 */
	private function getAppVersionsListByAppId($appId) {
		$list = array();
		try {
			ZDBG2 ( "DB: getting app versions list of app $appId" );
			
			$query = $this->getQuery ( "get_application_version_by_app_id" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $appId );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					$list[] = $row;
				}
			}
			
			ZDBG2 ( "DB: getAppVersionsListByAppId returned " . count($list) . " apps" );
			return $list;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	private function insertNewSequence() {
		$query = $this->getQuery ( "insert_sequence" );
		$this->_dbh->exec ( $query );
		return $this->_dbh->lastInsertId ();
	}
	
	/**
	 * Insert a new task to the DB 
	 * $param integer $type
	 * @param integer $groupId
	 * @param integer $serverId
	 * @param integer $taskDescriptorId
	 * @param integer $auditId
	 * 
	 * @return integer task id
	 */
	private function insertNewTask($type, $groupId, $serverId, $taskDescriptorId, $auditId) {
		
		ZDBG2 ( "DB: Creating new task for server " . $serverId . " type: $type groupId: $groupId taskDescriptorId:$taskDescriptorId auditId:$auditId");
		
		$query = $this->getQuery ( "insert_task" );
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue ( ":group_id", $groupId );
		$stmt->bindValue ( ":node_id", $serverId );
		$stmt->bindValue ( ":type", $type );
		$stmt->bindValue ( ":task_descriptor_id", $taskDescriptorId );
		$stmt->bindValue ( ":audit_id", $auditId );
		$this->executeStatement($stmt);
		
		return $this->_dbh->lastInsertId ();
	}
	
	private function findPluginStatusForNodeId($serverId, $pluginVersion) {
	
	    ZDBG2 ( "DB: Serach plugin status for plugin version: $pluginVersion for server $serverId");
	
	    $query = $this->getQuery ( "find_plugin_for_node" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_version_id", $pluginVersion );
	    $stmt->bindValue ( ":node_id", $serverId );
	    $res = $this->executeStatement($stmt);
	    $list = array();
	    if ($res) {
	        foreach ( $stmt->fetchAll () as $row ) {
	            $list[] = $row;
	        }
	    }
	    return $list;
	}
	
	/**
	 * 
	 * @param PDOStatement $stmt
	 * @throws PDOException
	 */
	private function executeStatement(&$stmt) {

		for ($i = 0 ; $i < 10 ; $i++) {
			try {
				return $stmt->execute();
			} catch (PDOException $e) {
				if($i < 9 && stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
					ZDBG2("DB: Statement execution found a locked DB. Retry $i in 1 sec...");
					$stmt->closeCursor();
	            	sleep(1);
	        	} else {
		           	throw $e;
	        	}
			}			
		}
	}
	
	/**
	 * Insert a new task descriptor to the DB 
	 * @param integer $packageId
	 * @param array $userParams
	 * @param array $zendParams
	 * @param integer $runOnceServer
	 * 
	 * @return integer task descriptor id
	 */
	private function insertNewTaskDescriptor($packageId, $userParams, $zendParams, $runOnceServer, $creationTime, $status) {
		
		ZDBG2 ( "DB: Creating task descriptor for package " . $packageId . " with zend params " . var_export($zendParams, true) );
		
		// insert into "deployment_tasks_descriptors" table
		$query = $this->getQuery ( "insert_task_descriptor" );
		$stmt = $this->_dbh->prepare ( $query );
		
		if (isset ( $zendParams ['baseUrl'] )) {
			$stmt->bindValue ( ":base_url", $zendParams ['baseUrl'] );
		} else {
			$stmt->bindValue ( ":base_url", NULL );
		}
		
		if (count($userParams) == 0) {
		   $stmt->bindValue ( ":user_params", NULL );
		} else {
		   $stmt->bindValue ( ":user_params", $this->serializeParams ( $userParams ) );
		}
		
		
		if (empty($zendParams)) {
		    $stmt->bindValue ( ":zend_params", NULL );
		} else {
		    $stmt->bindValue ( ":zend_params", $this->serializeParams ( $zendParams ) );
		}
		
		$stmt->bindValue ( ":package_id", $packageId );
		$stmt->bindValue ( ":run_once_node_id", $runOnceServer );
		$stmt->bindValue ( ":creation_time", $creationTime );
		$stmt->bindValue ( ":status", $status );
		$this->executeStatement($stmt);
		return $this->_dbh->lastInsertId ();
	}
	
	private function updateTaskDescriptor($descId, $userParams, $zendParams) {
		
		ZDBG2 ( "DB: updating task descriptor " . $descId );
		
		$query = $this->getQuery ( "update_task_descriptor" );
		$stmt = $this->_dbh->prepare ( $query );
		
		$stmt->bindValue ( ":task_descriptor_id", $descId);
		$stmt->bindValue ( ":user_params", $this->serializeParams ( $userParams ) );
		$stmt->bindValue ( ":zend_params", $this->serializeParams ( $zendParams ) );
		
		$this->executeStatement($stmt);		
	}
	
	
	
	public function updatePendingTask($baseUrl, $userParams, $zendParams) {
		
		try {
			ZDBG2 ( "DB: Updating pending task for base url '$baseUrl'" );
			
			$query = $this->getQuery ( "update_pending_task_descriptor" );
			$stmt = $this->_dbh->prepare ( $query );
			
			$stmt->bindValue ( ":user_params", $this->serializeParams ( $userParams ) );
			$stmt->bindValue ( ":zend_params", $this->serializeParams ( $zendParams ) );
			
			$stmt->bindValue ( ":base_url", $baseUrl );
			$stmt->bindValue ( ":status", self::TASK_STATUS_PENDING );
			
			$this->executeStatement($stmt);
			return $this->_dbh->lastInsertId ();
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	/**
	 * 
	 * Mark a pending task as ACTIVE
	 * @param string $baseUrl
	 * @param integer $runOnceServer
	 */
	public function activatePendingTask($baseUrl, $runOnceServer) {
		
		try {
			ZDBG2 ( "DB: activating pending task for base url '$baseUrl'" );
			
			$query = $this->getQuery ( "activate_pending_task_descriptor" );
			$stmt = $this->_dbh->prepare ( $query );
			
			// update
			$stmt->bindValue ( ":run_once_node_id", $runOnceServer );
			$stmt->bindValue ( ":newstatus", self::TASK_STATUS_ACTIVE );
			$stmt->bindValue ( ":creation_time", time () );
			
			// where
			$stmt->bindValue ( ":base_url", $baseUrl );
			$stmt->bindValue ( ":oldstatus", self::TASK_STATUS_PENDING );
			
			$this->executeStatement($stmt);
			return $this->_dbh->lastInsertId ();
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	/**
	 *
	 * Mark a pending task as ACTIVE
	 * @param string $pendingTaskId
	 * @param integer $runOnceServer
	 * @param string $name
	 */
	public function activatePendingPluginTask($pendingTaskId, $runOnceServer, $name) {
	
	    try {
	        ZDBG2 ( "DB: activating pending task for name " . $name . "pendingTaskId: " .  $pendingTaskId  . "runOnceServer: " .   $runOnceServer);
	        	
	        $query = $this->getQuery ( "activate_pending_plugin_task_descriptor" );
	        $stmt = $this->_dbh->prepare ( $query );
	        	
	        // update
	        $stmt->bindValue ( ":run_once_node_id", $runOnceServer );
	        $stmt->bindValue ( ":newstatus", self::TASK_STATUS_ACTIVE );
	        $stmt->bindValue ( ":creation_time", time () );
	        	
	        // where
	        $stmt->bindValue ( ":taskid", $pendingTaskId );
	        $stmt->bindValue ( ":oldstatus", self::TASK_STATUS_PENDING );
	        	
	        $this->executeStatement($stmt);
	        return $this->_dbh->lastInsertId ();
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	private function updatePackage($packageId, $version) {
		
		$query = $this->getQuery ( "update_package" );
		$stmt = $this->_dbh->prepare ( $query );
		
		$stmt->bindValue ( ":package_id", $packageId );
		$stmt->bindValue ( ":version", $version);
		
		$this->executeStatement($stmt);
	}
	
	/**
	 * Insert a new package to the DB 
	 * 1. store package in "deployment_packages"
	 * 2. store package contents in "deployment_package_data"
	 * @param ZendDeployment_PackageFile $package
	 * 
	 * @return integer package id
	 */
	private function insertNewPackage($package) {
		
		ZDBG1 ( "DB: inserting package data for " . $package->getName () . " package" );
		$storeFileLocally = ((strpos ( PHP_VERSION, "5.2" ) === 0) && ($this->_dbh->getAttribute ( PDO::ATTR_DRIVER_NAME ) != "mysql"));
		
		$isSQLite = ($this->_dbh->getAttribute ( PDO::ATTR_DRIVER_NAME ) != "mysql");
		
		$pkgPath = NULL;
		if ($storeFileLocally) {
			
			// on PHP 5.2 we save the file locally and save the path
			$packagesDir = get_cfg_var ( "zend.data_dir" ) . "/apps/packages";
			$pkgPath = $packagesDir . "/" . basename ( $package->getPackagePath () ) . "." . time ();
			ZDBG1 ( "Storing package at " . $pkgPath );
			if (! file_exists ( $packagesDir ) && ! mkdir ( $packagesDir )) {
				throw new ZendDeployment_Exception ( "Unable to create package directory $packagesDir", ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
			}
			if (! copy ( $package->getPackagePath (), $pkgPath )) {
				throw new ZendDeployment_Exception ( "Unable to copy package " . $package->getPackagePath () . " to " . $pkgPath, ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
			}
		}
		
		// Store the package meta data (into "deployment_packages" table)
		$query = $this->getQuery ( "insert_package" );
		$stmt = $this->_dbh->prepare ( $query );
		
		$stmt->bindValue ( ":path", $pkgPath );
		$stmt->bindValue ( ":eula", $package->getEula () );
		$stmt->bindValue ( ":readme", substr($package->getReadme(), 0, 65535));
		$stmt->bindValue ( ":logo", base64_encode ( $package->getLogo () ) );
		$stmt->bindValue ( ":package_descriptor", $package->getPackageDescriptor () );
		$stmt->bindValue ( ":name", $package->getName () );
		$stmt->bindValue ( ":version", $package->getVersion () );
		$stmt->bindValue ( ":monitor_rules", $package->getMonitorRules () );
		$stmt->bindValue ( ":pagecache_rules", $package->getPageCacheRules());
		$this->executeStatement($stmt);
		unset($stmt);
		
		// Insert the package data
		$pkgId = $this->_dbh->lastInsertId ();
		
		// insert into "deployment_package_data" table (the contents of the package will be stored in this table)
		$query_pkg_data = $this->getQuery ( "insert_package_data" );
		
		// default chunk size is set to 1MB
		// IMPORTANT: any change to this code, requires adjustments on the ZDD side!!!
		$chunk_size = 1024 * 1024;
		if (! $isSQLite) {
			ZDBG1 ( "determining chunk size..." );
			$varRes = $this->_dbh->query ( "SHOW VARIABLES LIKE 'max_allowed_packet'" );
			foreach ( $varRes as $varResLine ) {
				$chunk_size = $varResLine ['Value'];
				break;
			}
			ZDBG1 ( "max_allowed_packet is set to: " . $chunk_size );
		}
		
		$chunk_size = min(array ( 10 * 1024 * 1024 , 
								  $chunk_size)); // 10MB maximum
			
		$chunk_size = ceil ( $chunk_size * 0.7 ); // we use 70% of the packet size as our chunk
		ZDBG1 ( "Using chunk size of: " . $chunk_size );
		// END OF IMPORTANT section

		if ($package->getPackagePath () && ! $storeFileLocally) {
			$fp = NULL;
			$data_size = filesize ( $package->getPackagePath () );
			$size_left = $data_size;
			$fp = fopen ( $package->getPackagePath (), "rb" );
			
			if (! $fp) {
				throw new ZendDeployment_Exception ( "Unable to read package " . $package->getPackagePath (), ZendDeployment_Exception_Interface::ERR_FILE_SYSTEM );
			}
			
			while ( $size_left > 0 ) {
				ZDBG1 ( "DB: inserting chunk" );
				
				$stmt2 = $this->_dbh->prepare ( $query_pkg_data );
				
				// determine the chunk size
				$size_left > $chunk_size ? $tmp_chunk_size = $chunk_size : $tmp_chunk_size = $size_left;
				$size_left -= $tmp_chunk_size;
				
				$chunk_data = fread ( $fp, $tmp_chunk_size );
				$stmt2 = $this->_dbh->prepare ( $query_pkg_data );
				$stmt2->bindValue ( ":package_id", $pkgId );
				$stmt2->bindValue ( ":data", $chunk_data, PDO::PARAM_LOB );
				$smtmResult = $stmt2->execute ();
				unset($stmt2);
			}
			
			fclose ( $fp );
		
		} else {
			// PHP 5.2 - there is no need to write data content - we set it to NULL
			$stmt2 = $this->_dbh->prepare ( $query_pkg_data );
			$stmt2->bindValue ( ":package_id", $pkgId );
			$stmt2->bindValue ( ":data", NULL, PDO::PARAM_LOB );
			$stmt2->execute ();
			unset($stmt2);
		}
		
		return $pkgId;
	}
	
	/**
	 * 
	 * @param array $row
	 * @return ZendDeployment_Application_Interface
	 */
	private function appFromRow($row) {
		
		$app = new ZendDeployment_Application ();
		$app->setStatus ( $row ['status'] );
		$app->setHealthMessage ( $row ['health_message'] );
		$app->setHealthStatus ( $row ['health_status'] );
		$app->setAppId ( $row ['app_id'] );
		$app->setBaseUrl ( $row ['base_url'] );
		$app->setAppName ( $row ['name'] );
		$app->setVhostId ( $row ['vhost_id'] );
		
		if (! $row ['last_message']) {
			$app->setErrors ( array () );
		} else {
			$app->setErrors ( array ($row ['last_message'] ) );
		}
		$app->setVersion ( $row ['version'] );
		$app->setUserAppName ( $row ['user_app_name'] );
		$app->setCreationTime ( $row ['creation_time'] );
		$app->setInstallPath ( $row ['install_path'] );
		$app->setIsDefinedApp((int) $row['is_defined']);
		
			$taskDesc = $this->getTaskDescriptorByApplicationId ( $row ['app_id'] );
		$packageId = -1;
		if (!$taskDesc) {
			//throw new ZendDeployment_Exception ( "Unable to find task descriptor for appId " . $row ['app_id'], ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
		} else {
			$userParams = $taskDesc->getUserParams ();
			$packageId = $taskDesc->getPackageId ();
			$zendParams = $taskDesc->getZendParams ();
			$app->setUserParams ( $userParams );
		}
		
		if ($packageId != -1) {
			// create the package meta data object
			$packageMetaData = $this->getPackageMetaData ( $packageId );
			if (! $packageMetaData) {
				//throw new ZendDeployment_Exception ( "Cannot find package meta data for base url " . $row ['base_url'], ZendDeployment_Exception_Interface::INTERNAL_SERVER_ERROR );
			} else {
				$app->setPackageMetaData ( $packageMetaData );
			}
		} else {
			$app->setPackageMetaData ( new  ZendDeployment_PackageMetaData());
		}
		
		$app->setAppStatusId ( $row ['app_status_id'] );
		$app->setNextAppStatusId ( $row ['next_status'] );
		$app->setNodeId ( $row ['node_id'] );
		$app->setAppVersionId ( $row ['app_version_id'] );
		$app->setLastUsed($row['last_used']);
		$app->setRunOnceNode($row ['run_once_node_id']);
		
		return $app;
	}
	
	/**
	 * Return the list of known applications 
	 * $return array of (appId => array of ("userApplicationName" => name))
	 */
	public function getAllApplicationsInfo($servers) {
		ZDBG2 ( "DB: Creating list of applications info" );
				
		try {
			
			$appsListToRet = array ();
			$query = $this->getQuery ( "get_all_applications_info" );
			$query = str_replace(":node_ids", implode(",", $servers), $query);
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					$info = array();
					$info['userApplicationName'] = $row['user_app_name'];
					$info['applicationName'] = $row['name'];
					$info['applicationStatus'] = $row['status'];
					$info['applicationHealthStatus'] = $row['health_status'];
					$info['applicationId'] = $row['app_id'];
					$info['baseUrl'] = $row['base_url'];
					
					if ($info['applicationStatus'] == "INTEGRATION_CANDIDATE") {
						continue;
					}
					
					$appsListToRet[$row['app_id']] = $info;
				}
			}
			
			return $appsListToRet;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}		
	}	
	
	/**
	 * Return the list of deployed applications 
	 * $return array of (serverId => (array of applicationId => ZendDeployment_Application_Interface))
	 */
	public function getApplications(array $filter = array(), $includeCandidates = true) {
		
		ZDBG2 ( "DB: Creating list of applications" );
		if ($filter) {
			ZDBG3 ( "DB: Using filter " . var_export ( $filter, true ) );
		}
		
		try {
			
			$appsListToRet = array ();
				$query = $this->getQuery ( "get_applications" );
				$res = $this->_dbh->query ( $query );
			
			$apps = array ();
			foreach ( $res as $row ) {
				
				if (isset ( $filter ['baseUrl'] )) {
					if ($row ['base_url'] != $filter ['baseUrl']) {
						continue;
					}
				}
				
				if (isset ( $filter ['appIds'] )) {
					if (! in_array ( $row ['app_id'], $filter ['appIds'] )) {
						continue;
					}
				}
				
				if (isset ( $filter ['servers'] )) {
					if (! in_array ( $row ['node_id'], $filter ['servers'] )) {
						continue;
					}
				}
				
				if (isset($filter['vhostIds'])) {
					if (! in_array ( $row ['vhost_id'], $filter ['vhostIds'] )) {
						continue;
					}
				}
				
				$appId = $row ['app_id'];
				
				$newApp = $this->appFromRow ( $row );
				
				if (!$includeCandidates && $newApp->getStatus() == ZendDeployment_Application_Interface::STATUS_INTEGRATION_CANDIDATE) {
					continue;
				}
				
				$nodeId = $row ['node_id'];
				
				ZDBG2 ( "Found app status " . $newApp->getAppStatusId () . " with base url " . $row ['base_url'] . " in node $nodeId" );
				
				foreach ( $apps as $id => $app ) {
					if ($app->getNextAppStatusId () == $newApp->getAppStatusId ()) {
						
						if (strstr ( $newApp->getStatus (), "WAITING" )) {
							ZDBG2 ( "DB: App is a a waiting-to-be-executed version" );
							break;
						}
						
						ZDBG2 ( "DB: App is the next status of version " . $app->getVersion () . " for base url " . $app->getBaseUrl () );
						$newApp->setRollbackToVersion ( $app );
						$apps [$newApp->getAppStatusId ()] = $newApp;
					}
				}
				
				$apps [$newApp->getAppStatusId ()] = $newApp;
			}
			
			// now create the list by active apps
			foreach ( $apps as $app ) {
				if (! isset ( $appsListToRet [$app->getApplicationId ()] )) {
					$appsListToRet [$app->getApplicationId ()] = array ();
				}
				$appsListToRet [$app->getApplicationId ()] [$app->getNodeId ()] = $app;
			}
			
			ZDBG2 ( "DB: found " . count ( $appsListToRet ) . " applications" );
						
			return $appsListToRet;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	
	}
	
	/**
	 * Returns a package meta data
	 * 
	 * @return ZendDeployment_PackageMetaData
	 */
	public function getPackageMetaData($packageId) {
		
		static $packagesMetaData = array();
		
		if (isset($packagesMetaData[$packageId])) {
			return $packagesMetaData[$packageId];
		}
		
		ZDBG2 ( "DB: Getting package meta data for package $packageId" );
		
		try {
			$query = $this->getQuery ( "get_package_meta_data" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":package_id", $packageId );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					$data = new ZendDeployment_PackageMetaData ();
					$data->setAppEula ( $row ['eula'] );
					$data->setAppLogo ( base64_decode ( $row ['logo'] ) );
					$data->setPackageDescriptor ( $row ['package_descriptor'] );
					$data->setAppVersion ( $row ['version'] );
					$data->setAppName ( $row ['name'] );
					$data->setMonitorRulesFileExists(strlen($row ['monitor_rules']) > 0);
					$data->setMonitorRules($row ['monitor_rules'] );
					$data->setPageCacheRulesFileExists(strlen($row ['pagecache_rules']) > 0);
					$data->setPageCacheRules($row ['pagecache_rules'] );
					
					$desc = simplexml_load_string($row ['package_descriptor']);
					if (isset($desc->updateurl)) {
						$data->setUpdateUrl((string) $desc->updateurl);
					}
					if (isset($desc->releasedate)) {
						$data->setReleaseDate((string) $desc->releasedate);
					}
					
					$data->setPackageId ( $packageId );
					$packagesMetaData[$packageId] = $data;
					return $data;
				}
			}
			
			return NULL;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	/**
	 * Returns a package meta data
	 *
	 * @return ZendDeployment_PackageMetaData
	 */
	public function getPluginPackageMetaData($packageId) {
	
	    static $packagesMetaData = array();
	
	    if (isset($packagesMetaData[$packageId])) {
	        return $packagesMetaData[$packageId];
	    }
	
	    ZDBG2 ( "DB: Getting json package meta data for package $packageId" );
	
	    try {
	        $query = $this->getQuery ( "get_package_meta_data" );
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue ( ":package_id", $packageId );
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                $data = new ZendDeployment_PackageMetaData ();
	                $data->setAppEula ( $row ['eula'] );
	                $data->setAppLogo ( base64_decode ( $row ['logo'] ) );
	                $data->setPackageDescriptor ( $row ['package_descriptor'] );
	                $data->setAppVersion ( $row ['version'] );
	                $data->setAppName ( $row ['name'] );
	                
	                $desc = json_decode($row ['package_descriptor']);
	                $data->setPackageId ( $packageId );
	                $packagesMetaData[$packageId] = $data;
	                return $data;
	            }
	        }
	        	
	        return NULL;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	/**
	 * Serialize a parameters array so it can be inserted to the DB
	 * @param array $params
	 * 
	 * @return string serialized parameters 
	 */
	private function serializeParams(array $params) {
		if (! $params) {
			return "";
		}
		
		//ZDBG3 ( "DB: serializing params " . var_export ( $params, true ) );
		$res = "";
		foreach ( $params as $key => $value ) {
			if ($res) {
				$res .= self::PARAMS_DELIMITER;
			}
			$res .= $key . self::PARAMS_DELIMITER . $value;
		}
		
		return $res;
	}
	
	/**
	 * Unserialize parameters from the DB to an array
	 * @param string $paramsStr 
	 * 
	 * @return array 
	 */
	private function unserializeParams($paramsStr) {
		
		if (! $paramsStr) {
			return array ();
		}
		
		$res = array ();
		$exploded = explode ( self::PARAMS_DELIMITER, $paramsStr );
		for($i = 0; $i < count ( $exploded ); $i ++) {
			$key = $exploded [$i];
			$i ++;
			$value = $exploded [$i];
			$res [$key] = $value;
		}
		
		return $res;
	}
	
	/**
	 * 
	 * Insert a pending task details
	 * @param ZendDeplyment_Package $packageFile
	 * @param string $baseUrl
	 * @param array $userParams
	 * @param array $zendParams
	 */
	public function insertPendingTask($packageFile, $userParams, $zendParams) {
		try {
			
			ZDBG1 ( "ZendDeployment_DB_Handler insertPendingTask " . $packageFile->getPackagePath () );
			
			$this->beginTransaction ();
			
			$taskTime = time ();
			
			// 1. store the package in "deployment_packages"
			// 2. store the package contents in "deployment_package_data"
			// retrieve the package id 
			$packageId = $this->insertNewPackage ( $packageFile );
			
			// insert new row into "deployment_tasks_descriptors"
			$taskDescriptorId = $this->insertNewTaskDescriptor ( $packageId, $userParams, $zendParams, - 1, $taskTime, self::TASK_STATUS_PENDING );
			
			$this->commit ();

			return $taskDescriptorId;
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function getPendingTaskById($id) {
		try {
			ZDBG3 ( "DB: getting pending task by id" );
				
			$query = $this->getQuery ( "get_pending_deployment_by_task_id" );
			
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":task_descriptor_id", $id );
			$stmt->bindValue ( ":status", self::TASK_STATUS_PENDING );
			
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					$desc = array ();
					$desc ['user_params'] = $this->unserializeParams ( $row ['user_params'] );
					$desc ['zend_params'] = $this->unserializeParams ( $row ['zend_params'] );
					$desc ['package_id'] = $row ['package_id'];
					$desc ['task_descriptor_id'] = $row ['task_descriptor_id'];
					
					return $desc;
				}
			}
			return array();
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function getPendingTasks($baseUrl = NULL) {
		try {
			ZDBG3 ( "DB: getting pending tasks" );
			
			$tasks = array ();
			if ($baseUrl) {
				$query = $this->getQuery ( "get_pending_deployment_by_base_url" );
			} else {
				$query = $this->getQuery ( "get_pending_deployments" );
			}
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":status", self::TASK_STATUS_PENDING );
			if ($baseUrl) {
				$stmt->bindValue ( ":base_url", $baseUrl );
			}
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					$desc = array ();
					$baseUrl = $row ['base_url'];
					$desc ['base_url'] = $baseUrl;
					$desc ['user_params'] = $this->unserializeParams ( $row ['user_params'] );
					$desc ['zend_params'] = $this->unserializeParams ( $row ['zend_params'] );
					$desc ['package_id'] = $row ['package_id'];
					$desc ['task_descriptor_id'] = $row ['task_descriptor_id'];
					$tasks [$baseUrl] = $desc;
				}
			}
			return $tasks;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}

	public function getPluginPendingTasks($name = NULL) {
	    try {
	        ZDBG3 ( "DB: getting plugin pending tasks" );
	        	
	        $tasks = array ();
	        if ($name) {
	           // ORDER BY  deployment_tasks_descriptors.task_descriptor_id: to take the last pending task to avoid bugs that can take the previous pending task
	            $query = $this->getQuery ( "get_pending_deployment_by_plugin_name" );
	        } else {
	            $query = $this->getQuery ( "get_pending_deployments" );
	        }
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue ( ":status", self::TASK_STATUS_PENDING );
	        if ($name) {
	            $stmt->bindValue (":name", $name);
	        }
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                $desc = array ();
	                $pluginName = $row ['name'];
	                if (isset($row ['base_url']) && $row ['base_url']) {
	                   continue;
	                }
	                $desc ['name'] = $pluginName;
	                $desc ['package_id'] = $row ['package_id'];
	                $desc ['task_descriptor_id'] = $row ['task_descriptor_id'];
	                $tasks [$pluginName] = $desc;
	            }
	        }
	        return $tasks;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	public function deletePendingTask($baseUrl) {
		try {
			ZDBG3 ( "DB: deleting pending task for $baseUrl" );
			
			$this->beginTransaction ();
			
			$desc = array ();
			$query = $this->getQuery ( "delete_pending_task_by_base_url" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":base_url", $baseUrl );
			$stmt->bindValue ( ":status", self::TASK_STATUS_PENDING );
			$res = $this->executeStatement($stmt);
			ZDBG3("DB: deleted " . $stmt->rowCount() . " rows");
			unset($stmt);
			
			$query = $this->getQuery ( "delete_obsolete_packages" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			ZDBG3("DB: deleted " . $stmt->rowCount() . " rows");
			unset($stmt);
			
			$query = $this->getQuery ( "delete_obsolete_package_data" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			ZDBG3("DB: deleted " . $stmt->rowCount() . " rows");
			unset($stmt);
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function deletePendingPluginTask($name) {
	    try {
	        ZDBG3 ( "DB: deleting pending plugin task for $name" );
	        	
	        $this->beginTransaction ();
	        	
	        $desc = array ();
			// delete from "deployment_tasks_descriptors" table
	        $query = $this->getQuery ( "delete_pending_plugin_task_by_name" );
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue ( ":name", $name );
	        $stmt->bindValue ( ":status", self::TASK_STATUS_PENDING );
	        $res = $this->executeStatement($stmt);
	        ZDBG3("DB: deleted " . $stmt->rowCount() . " rows");
	        unset($stmt);
	        	
			// delete from "deployment_packages" table
	        $query = $this->getQuery ( "delete_obsolete_packages" );
	        $stmt = $this->_dbh->prepare ( $query );
	        $res = $this->executeStatement($stmt);
	        ZDBG3("DB: deleted " . $stmt->rowCount() . " rows");
	        unset($stmt);
	        	
			// delete from "deployment_package_data" table
	        $query = $this->getQuery ( "delete_obsolete_package_data" );
	        $stmt = $this->_dbh->prepare ( $query );
	        $res = $this->executeStatement($stmt);
	        ZDBG3("DB: deleted " . $stmt->rowCount() . " rows");
	        unset($stmt);
	        	
	        $this->commit ();
	
	    } catch ( PDOException $ex ) {
	        $this->rollback ();
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	public function insertReloadConfigurationTask($servers) {
		
		ZDBG3 ( "DB: insertReloadConfigurationTask" );
		
		try {
			$this->beginTransaction ();
			
			$groupId = $this->insertNewSequence ();
			
			foreach ( $servers as $server ) {
				// create the task for each server
				$this->insertNewTask ( self::TASK_TYPE_RELOAD_CONFIGURATION, $groupId, $server, - 1 );
			}
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	
	}
	
	public function getNodeStatus($nodeId) {
		ZDBG3 ( "DB: getNodeStatus on node $nodeId" );
		
		$status = array ();
		try {
			$query = $this->getQuery ( "get_node_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":node_id", $nodeId );
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					$status ['status'] = $row ['status'];
					$status ['last_updated'] = $row ['last_updated'];
				}
			}
		
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
		return $status;
	}
	
	public function getVirtualHosts() {
		
		ZDBG3 ( "DB: getVirtualHosts" );
		
		$vhosts = array ();
		try {
			$query = $this->getQuery ( "get_virtual_hosts" );
			$stmt = $this->_dbh->prepare ( $query );
			$res = $this->executeStatement($stmt);
			$vhosts = $stmt->fetchAll ( PDO::FETCH_CLASS, "ZendDeployment_Vhost" );
			return $vhosts;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_Manager_Interface::forceRemoveApplications()
	 */
	public function purgeApplications(array $servers) {
		ZDBG1 ( "Manager: purgeApplications called with servers (" . implode ( " ", $servers ) . ")" );
		
		try {
			$this->beginTransaction ();
			$query = $this->getQuery ( "purge_applications" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":node_ids", implode ( ",", $servers ) );
			$res = $this->executeStatement($stmt);
			unset($stmt);
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
		$this->cleanupObsoleteEntries ();
	}
	
	public function failTimedOutTasks($oldStatus, $newStatus, $timeLimit) {
		ZDBG2 ( "DB: failTimedOutTasks called with old status $oldStatus with time limit of $timeLimit" );
		
		try {
			$this->beginTransaction ();
			$query = $this->getQuery ( "fail_timedout_tasks" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":old_status", $oldStatus );
			$stmt->bindValue ( ":new_status", $newStatus );
			$stmt->bindValue ( ":time_limit", $timeLimit );
			$res = $this->executeStatement($stmt);
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function setHealthCheckScript($applicationId, $path) {
		ZDBG1 ( "DB: setHealthCheckScript called for app $applicationId - $path" );
		
		try {
			$this->beginTransaction ();
			$query = $this->getQuery ( "set_health_check_path" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $applicationId );
			$stmt->bindValue ( ":health_check_path", $path );
			$res = $this->executeStatement($stmt);
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	public function hideApplicationStatus($applicationId, $servers) {
		ZDBG2("DB: hideApplicationStatus called with servers (" . implode(" ", $servers) . ") and appId " . $applicationId);
		
		try {
			$this->beginTransaction ();
			$query = $this->getQuery ( "hide_app_status" );
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue ( ":app_id", $applicationId );
			$stmt->bindValue ( ":node_ids",  implode ( ",", $servers ) );
			$res = $this->executeStatement($stmt);
			
			$this->commit ();
		
		} catch ( PDOException $ex ) {
			$this->rollback ();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
	}
	
	public function getLibrariesByIds(array $ids = array()) {
		
		/*
		 * 'libraryVersionId' => 1,
						'version' => '1.12.1',
						'status' => 'OK',
						'installedLocation' => '/path/to/lib',
						'isDefinedLibrary' => false,
						'creationTime' => time(),
						'servers' => array(
							0 => array('serverId' => 0, 'status' => 'OK') // serverId
						)
		 */
		
		try {
			ZDBG3 ( "DB: getLibrariesByIds with ids " . implode(",", $ids) );
				
			$libs = array ();
			$query = $this->getQuery ( "get_libraries_status" );
			
			$stmt = $this->_dbh->prepare ( $query );
			
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					
					$row = (object) $row;
					
					
					if ($ids) {
						if (!in_array($row->lib_id, $ids)) {
							continue;
						}
					}
					
					$packageDescriptor = $row->package_descriptor;
					$desc = simplexml_load_string($packageDescriptor);
					$updateUrl = "";
					if (isset($desc->updateurl)) {
						$updateUrl = (string) $desc->updateurl;
					}
					$releaseDate = "";
					if (isset($desc->releasedate)) {
						$releaseDate = (string) $desc->releasedate;
					}
					
					$libVersion = array(
							"libraryVersionId" => $row->lib_version_id,
							"version" => $row->version,
							"default" => true,
							"installedLocation" => $row->install_path,
							"isDefinedLibrary" => $row->is_defined?true:false,
							"default" => $row->is_default?true:false,
							"creationTime" => $row->creation_time,
							"releaseDate" => $releaseDate,
							"updateUrl" => $updateUrl,
							"serversStatus" => array(
									$row->node_id => array (
										'id' => $row->node_id,
										'status' => $row->status,
										'lastMessage' => $row->last_message,
										'lastUpdated' => $row->last_updated
											),
									)
					);
					
					$lib = array (
							'libraryId' => $row->lib_id,
							'libraryName' => $row->name,
							'versions' => array	(
									$row->lib_version_id => $libVersion,
								),
							);		
									
					
					if (!isset($libs[$row->lib_id])) {
												
						$libs[$row->lib_id] = $lib;
						
					} else {
						
						if (!isset($libs[$row->lib_id]['versions'][$row->lib_version_id])) {
							
							$libs[$row->lib_id]['versions'][$row->lib_version_id] = $libVersion;
														
						} else {
							
							$libs[$row->lib_id]['versions'][$row->lib_version_id]['serversStatus'][$row->node_id] = array (
									'id' => $row->node_id,
									'status' => $row->status,
									'lastMessage' => $row->last_message,
									'lastUpdated' => $row->last_updated
									);							
						}						
					
					}					
				}
			}
			
			
			return $libs;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
	}	

	
	public function isLibraryVersionExists($name, $version) {

		try {
			$query = $this->getQuery ( "is_library_version_exists" );
				
			$stmt = $this->_dbh->prepare ( $query );
			$stmt->bindValue(":name", $name);
			$stmt->bindValue(":version", $version);
				
			$res = $this->executeStatement($stmt);
			if ($res) {
				foreach ( $stmt->fetchAll () as $row ) {
					return true;
				}
			}
			
			return false;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
	}
	
	private function resetLibraryDefaults($libId) {
		$query = $this->getQuery ( "reset_library_default" );
		
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue(":lib_id", $libId);
		
		return $this->executeStatement($stmt);
		
	} 
	
	public function getLibraryIdByLibraryVersion($libVersionId) {
		$query = $this->getQuery ( "get_library_id_by_library_version" );
		
		$stmt = $this->_dbh->prepare ( $query );
		$stmt->bindValue(":lib_version_id", $libVersionId);
		
		$res = $this->executeStatement($stmt);
		if ($res) {
			foreach ( $stmt->fetchAll () as $row ) {
				return $row['lib_id'];
			}
		}
		
		return "";
	}
	
	public function setDefaultLibrary($libraryVersionId) {
		
		try {
			
			$libId = $this->getLibraryIdByLibraryVersion($libraryVersionId);
			
			if ($this->resetLibraryDefaults($libId)) {
						
				$query2 = $this->getQuery("set_library_default_by_lib_version");
				$stmt2 = $this->_dbh->prepare ( $query2 );
				$stmt2->bindValue(":lib_version_id", $libraryVersionId);
				
				$res = $this->executeStatement($stmt2);
				if ($res) {
					return true;
				} else {
					return false;
				}
			}
				
			return false;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
	}
	
	public function insertUpdateDefaultLibraryTask($servers, $libVersionId) {
		
		try {
				
			$descId = $this->insertNewTaskDescriptor(-1, array(), array("libVersionId"=>$libVersionId), -1, time(), self::TASK_STATUS_ACTIVE);
			
			foreach ($servers as $server) {
						
				$this->insertNewTask ( self::TASK_TYPE_UPDATE_DEFAULT_LIBRARY, -1, $server, $descId, -1);		
								
			}
		
			return true;
		} catch ( PDOException $ex ) {
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}		
	}
	
	
	public function insertNewDownloadStatus($appId, $libId, $url, $extraData) {
		
		$query = $this->getQuery("insert_new_download_status");
		$stmt = $this->_dbh->prepare ( $query );
		
		$stmt->bindValue(":lib_id", $libId);
		$stmt->bindValue(":app_id", $appId);
		$stmt->bindValue(":url", $url);
		$stmt->bindValue(":path", "");
		$stmt->bindValue(":status", self::DOWNLOAD_STATUS_PENDING);
		$stmt->bindValue(":extra_data", json_encode($extraData));
		$stmt->bindValue(":message", "");
		$stmt->bindValue(":total", 0);
		$stmt->bindValue(":downloaded", 0);
		$stmt->bindValue(":start_time", time());
		
		$res = $this->executeStatement($stmt);
		if ($res) {
			return $this->_dbh->lastInsertId();
		} else {
			return false;
		}		
		
	}
	
	public function insertCancelDownloadFileTask($server, $downloadId) {
		
		ZDBG2("Inserting cancel download file $downloadId");
		$this->_dbh->beginTransaction();
		
		try {
				
			$deploymentTime = time ();
				
			$extraData['downloadId'] = $downloadId;
				
			$taskDescriptorId = $this->insertNewTaskDescriptor ( -1, array(), $extraData, -1, $deploymentTime, self::TASK_STATUS_ACTIVE );
				
			$taskId = $this->insertNewTask ( self::TASK_TYPE_CANCEL_DOWNLOAD_FILE, -1, $server, $taskDescriptorId, -1 );
		
			$this->_dbh->commit();
				
		} catch ( PDOException $ex ) {
			$this->_dbh->rollBack();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}
		
	}
	
	public function insertDownloadFileTask($server, $appId, $libId, $url, $extraData) {
		
		ZDBG2("Inserting download file task appId $appId libId $libId url $url " . var_export($extraData, true));
		$this->_dbh->beginTransaction();
		
		try {
			
			$statusId = $this->insertNewDownloadStatus($appId, $libId, $url, $extraData);
			
			$deploymentTime = time ();
			
			$extraData['url'] = $url;
			$extraData['libId'] = $libId;
			$extraData['appId'] = $appId;
			$extraData['downloadId'] = $statusId;
			
			$taskDescriptorId = $this->insertNewTaskDescriptor ( -1, array(), $extraData, -1, $deploymentTime, self::TASK_STATUS_ACTIVE );
			
			$taskId = $this->insertNewTask ( self::TASK_TYPE_DOWNLOAD_FILE, -1, $server, $taskDescriptorId, -1 );
												
			$this->_dbh->commit();
			
		} catch ( PDOException $ex ) {
			$this->_dbh->rollBack();
			throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
		}	
	}
		
	///////////// PLUGINS ////////////////////////////////////
	
	/**
	 * Return the list of known plugins
	 * $return array of (pluginId => array of (name => name))
	 */
	public function getAllPluginsInfo($servers, $orderDirection = 'ASC', $order = "name") {
	    ZDBG2 ( "DB: Creating list of plugins info" );
	
	    try {
	        	
	        $pluginsListToRet = array ();
	        $query = $this->getQuery ( "get_all_plugins_info" );
	        $query = str_replace(":node_ids", implode(",", $servers), $query);
	        
	        // kind of escaping
	        if ($order == 'id') {
	            $order = 'deployment_packages.package_id';
	        } elseif ($order == 'name') {
	            $order = 'deployment_packages.name';
	        } elseif ($order == 'version') {
	            $order = 'deployment_packages.version';
	        }  elseif ($order == 'creation_time') {
	            $order = 'deployment_plugins_status.last_updated';
	        } else {
	            throw new ZendDeployment_Exception ('Order string in get all plugins is incorrect', ZendDeployment_Exception_Interface::DATABASE_ERROR );
	        }
	        
	        if(strcasecmp($orderDirection,'ASC') != 0 && strcasecmp($orderDirection,'DESC') != 0) {
	            throw new ZendDeployment_Exception ('Order direction string in get all plugins is incorrect: ' . $orderDirection, ZendDeployment_Exception_Interface::DATABASE_ERROR );
	        }
	        
	        $stmt = $this->_dbh->prepare ( $query . ' ORDER BY ' . $order . " $orderDirection");
	        ZDBG2 ( "DB: Creating list of plugins info statement: " . $query . ' ORDER BY ' . $order . " $orderDirection");
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                $info = array();
	                $info['name'] = $row['name'];
	                $info['status'] = $row['status'];
	                $info['plugin_id'] = $row['plugin_id'];
	                	
	                if ($info['status'] == "INTEGRATION_CANDIDATE") {
	                    continue;
	                }
	                	
	                $pluginsListToRet[$row['plugin_id']] = $info;
	            }
	        }
	        	
	        return $pluginsListToRet;
	    } catch ( PDOException $ex ) {
	        
	        
	       // throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	
	public function getPluginsByIds(array $ids = array(), $orderDirection = 'ASC') {
	
	    /*
	    plugin_status_id INTEGER PRIMARY KEY AUTOINCREMENT,
	    plugin_version_id INTEGER,
	    status VARCHAR(32),
	    node_id INTEGER,
	    install_path VARCHAR(4096),
	    last_message VARCHAR(1024),
	    last_updated INTEGER,
	    next_status INTEGER DEFAULT -1
	     */
	
	    try {
	        ZDBG3 ( "DB: getPluginsByIds  with ids " . implode(",", $ids) );
	
	        $plugins = array ();
	        $query = $this->getQuery ( "get_plugins_status" );
	        	
	        $stmt = $this->_dbh->prepare ( $query . " $orderDirection" );
	        
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                	
	                $row = (object) $row;
	                	
	                	
	                if ($ids) {
	                    if (!in_array($row->plugin_id, $ids)) {
	                        continue;
	                    }
	                }
	                	
	                $packageDescriptor = $row->package_descriptor;
	                $desc = json_decode($packageDescriptor);
	              /* desc
                  * {
                      "name" : "Magento",
                      "version" : "1.0.0",
                      "type": ["zray", "mvc"],
                      "eula": "EULA.txt",
                      "logo": "logo.gif"
                    }
	               */
	                $pluginVersion = array(
	                    "pluginVersionId" => $row->plugin_version_id,
	                    "version" => $row->version,
	                    "default" => true,
	                    "creationTime" => $row->creation_time,
	                    "serversStatus" => array(
	                        $row->node_id => array (
	                            'id' => $row->node_id,
	                            'status' => $row->status,
	                            'lastMessage' => $row->last_message,
	                            'lastUpdated' => $row->last_updated
	                        ),
	                    )
	                );
	                	
	                $plugin = array (
	                    'pluginId' => $row->plugin_id,
	                    'pluginName' => $row->name,
	                    'unique_plugin_id' => $row->unique_plugin_id,
	                    "install_path" => $row->install_path,
	                    "logo" => $row->logo,
	                    'packageMetadata' => $desc,
	                    'packageMetadataJson' => $packageDescriptor,
	                    'versions' => array	(
	                        $row->plugin_version_id => $pluginVersion,
	                    ),
	                );
	                	
	                	
	                if (!isset($plugins[$row->plugin_id])) {
	
	                    $plugins[$row->plugin_id] = $plugin;
	
	                } else {
	
	                    if (!isset($plugins[$row->plugin_id]['versions'][$row->plugin_version_id])) {
	                        	
	                        $plugins[$row->plugin_id]['versions'][$row->plugin_version_id] = $pluginVersion;
	
	                    } else {
	                        	
	                        $plugins[$row->plugin_id]['versions'][$row->plugin_version_id]['serversStatus'][$row->node_id] = array (
	                            'id' => $row->node_id,
	                            'status' => $row->status,
	                            'lastMessage' => $row->last_message,
	                            'lastUpdated' => $row->last_updated
	                        );
	                    }
	                    	
	                }
	            }
	        }
	        	
	        	
	        return $plugins;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	
	}

	/**
	 *
	 * Return the task descriptor of a plugin version
	 * @param string $pluginVersionId
	 * @return array
	 */
	public function getTaskDescriptorByPluginVersionId($pluginVersionId) {
	
	    ZDBG1 ( "ZendDeployment_DB_Handler get task descriptor by plugin version" . $pluginVersionId);
	    
	    $query = $this->getQuery ( "get_task_descriptors_by_plugin_version_id" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_version_id", $pluginVersionId );
	    $res = $this->executeStatement($stmt);
	    if ($res) {
	        foreach ( $stmt->fetchAll () as $row ) {
	            $res = new TaskDescriptor ();
	            $res->setPackageId ( $row ['package_id'] );
	            $res->setUserParams ( $this->unserializeParams ( $row ['user_params'] ) );
	            $res->setZendParams ( $this->unserializeParams ( $row ['zend_params'] ) );
	            $res->setId ( $row ['task_descriptor_id'] );
	
	            return $res;
	        }
	    }
	    return null;
	}
	
	public function insertRemovePluginVersionTask(array $servers, $pluginVersionId, array $zendParams) {
	
	    for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
	
	        try {
	
	            ZDBG1 ( "ZendDeployment_DB_Handler remove plugin version" . $pluginVersionId . " on servers (" . implode ( " ", $servers ) . ")" );
	
	            $taskTime = time ();
	
	            $zendParams ['pluginVersionId'] = $pluginVersionId;
	
	            $this->beginTransaction ();
	
	            $this->updatePluginVersionStatus($pluginVersionId, ZendDeployment_Application::STATUS_WAITING_FOR_REMOVE);
	
	            $taskDesc = $this->getTaskDescriptorByPluginVersionId($pluginVersionId);
	
	            $taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
	
	            $groupId = $this->insertNewSequence ();
	            foreach ( $servers as $server ) {
	                $taskId = $this->insertNewTask ( self::TASK_TYPE_REMOVE, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
	            }
	
	            $this->commit ();
	
	            return $this->_dbh->lastInsertId ();
	            	
	        } catch ( PDOException $ex ) {
	            $this->rollback ();
	
	            if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
	                ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
	                sleep ( 1 );
	                continue;
	            }
	
	            throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	        }
	    }
	}
	
	public function insertRemovePluginTask(array $servers, $pluginId, array $zendParams) {
	
	    for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
	
	        try {
	
	            ZDBG1 ( "ZendDeployment_DB_Handler remove plugin " . $pluginId . " on servers (" . implode ( " ", $servers ) . ")" );
	
	            $taskTime = time ();
	
	            $zendParams ['pluginId'] = $pluginId;
	
	            $this->beginTransaction ();
	
	            $this->updatePluginStatus($pluginId, ZendDeployment_Application::STATUS_WAITING_FOR_REMOVE);
	
	            $taskDescs = $this->getTaskDescriptorsByPluginId($pluginId);
	            foreach($taskDescs as $taskDesc) { /* @var $taskDesc TaskDescriptor */
	
	                $taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
	
	                $groupId = $this->insertNewSequence ();
	
	                foreach ( $servers as $server ) {
	                    $taskId = $this->insertNewTask ( self::TASK_TYPE_REMOVE, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
	                }
	            }
	
	            $this->commit ();
	
	            return $this->_dbh->lastInsertId ();
	
	        } catch ( PDOException $ex ) {
	            $this->rollback ();
	
	            if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
	                ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
	                sleep ( 1 );
	                continue;
	            }
	
	            throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	        }
	    }
	}
	
	public function insertEnableDisablePluginTask($isEnable, array $servers, $pluginId, array $zendParams) {
	
	    for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
	
	        try {
	
	            $action = ($isEnable) ? "enable" : "disable";
	            ZDBG1 ( "ZendDeployment_DB_Handler $action plugin " . $pluginId . " on servers (" . implode ( " ", $servers ) . ")" );
	
	            $taskTime = time ();
	
	            $zendParams ['pluginId'] = $pluginId;
	
	            $this->beginTransaction ();
	
	            $status = ($isEnable) ?    ZendDeployment_Application::STATUS_WAITING_FOR_ENABLE :
	                                       ZendDeployment_Application::STATUS_WAITING_FOR_DISABLE;
										   
				// update deployment_plugin_status table
	            $this->updatePluginStatus($pluginId, $status);
		
	            $taskDescs = $this->getTaskDescriptorsByPluginId($pluginId);
	            foreach($taskDescs as $pluginVersionId => $taskDesc) { /* @var $taskDesc TaskDescriptor */
	
	                $taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
	
	                $groupId = $this->insertNewSequence ();
	
	                foreach ( $servers as $server ) {
	                    $taskType = ($isEnable) ?  self::TASK_TYPE_ENABLE :
	                                               self::TASK_TYPE_DISABLE;
	                    
	                    // check if the $server down't exist in plugins_status with plugin $pluginId - add task deploy plugin
	                    if ($taskType == self::TASK_TYPE_ENABLE && !$this->findPluginStatusForNodeId($server, $pluginVersionId)) {
	                        $deploymentTime = time();
	                        // create the task for each server
	                        $this->insertNewPluginStatus ( $server, $pluginId, $pluginVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
	                        $taskType = self::TASK_TYPE_DEPLOY;
	                    }
	                    $taskId = $this->insertNewTask ($taskType, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
	                }
	            }
	
	            $this->commit ();
	
	            return $this->_dbh->lastInsertId ();
	
	        } catch ( PDOException $ex ) {
	            $this->rollback ();
	
	            if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
	                ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
	                sleep ( 1 );
	                continue;
	            }
	
	            throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	        }
	    }
	}
	
	public function insertRedeployPluginTask(array $servers, $pluginId, array $zendParams) {
	
	    for($i = 0; $i < self::BUSY_RETRIES; $i ++) {
	
	        try {
	
	            ZDBG1 ( "ZendDeployment_DB_Handler redeploy plugin version " . $pluginId . " on servers (" . implode ( " ", $servers ) . ")" );
	
	            $taskTime = time ();
	
	            $zendParams ['pluginId'] = $pluginId;
	
	            $this->beginTransaction ();
	
	            $pluginVersions = $this->getPluginVersionByPluginId($pluginId);
	            $this->updatePluginVersionStatus($pluginVersions['plugin_version_id'], ZendDeployment_Application::STATUS_WAITING_FOR_REDEPLOY);
	            $taskDesc = $this->getTaskDescriptorByPluginVersionId($pluginVersions['plugin_version_id']);
	
	            $taskDescriptorId = $this->insertNewTaskDescriptor ($taskDesc->getPackageId(), array (), $zendParams, -1, $taskTime, self::TASK_STATUS_ACTIVE );
	
	            $groupId = $this->insertNewSequence ();
	            foreach ( $servers as $server ) {
	               $taskId = $this->insertNewTask ( self::TASK_TYPE_REDEPLOY, $groupId, $server, $taskDescriptorId, $zendParams['auditId'] );
	            }
	
	            $this->commit ();
	
	            return $this->_dbh->lastInsertId ();
	            	
	        } catch ( PDOException $ex ) {
	            $this->rollback ();
	
	            if ($i != self::BUSY_RETRIES - 1 && strstr ( $ex->getMessage (), "locked" )) {
	                ZDBG1 ( "Database was locked in " . __FUNCTION__ . " - retrying..." );
	                sleep ( 1 );
	                continue;
	            }
	
	            throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	        }
	    }
	}
	
	public function isPluginVersionExists($name, $version) {
	
	    try {
	        $query = $this->getQuery ( "is_plugin_version_exists" );
	
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue(":name", $name);
	        $stmt->bindValue(":version", $version);
	
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                return true;
	            }
	        }
	        	
	        return false;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	
	public function isPluginExists($name) {
	
	    try {
	        $query = $this->getQuery ( "is_plugin_exists" );
	
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue(":name", $name);
	
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                return true;
	            }
	        }
	        	
	        return false;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	/**
	 * Deploy a package in given servers
	 * @param array $servers list of servers
	 * @param ZendDeployment_PackageMetaData_Interface $package package to deploy
	 * @param integer $auditId audit id
	 *
	 * @return string plugin id
	 */
	public function insertDeployPluginTask(array $servers, $package, $auditId) {
	    try {
	
	        ZDBG1 ( "ZendDeployment_DB_Handler deploying plugin " . $package->getPackagePath () . " on servers (" . implode ( " ", $servers ) . ")" );
	
	        $this->beginTransaction ();
	
	        $deploymentTime = time ();
	
	        $packageId = $this->insertNewPackage ( $package );
	        ZDBG2 ( "DB: new package id is $packageId" );
	        $package->setPersistentId ( $packageId );
	        	
	        $taskDescriptorId = $this->insertNewTaskDescriptor ( $packageId, array(), array(), -1, $deploymentTime, self::TASK_STATUS_ACTIVE );
	
	        $pluginId = $this->getPluginIdByName($package->getName ());
	        if ($pluginId < 0) {
	            $pluginId = $this->insertNewPlugin ( $package->getName (), $package->getVersion (), $taskDescriptorId);
	        }
	        
	        $groupId = $this->insertNewSequence ();
	        	
	        $pluginVersionId = $this->insertNewPluginVersion ( $pluginId, $taskDescriptorId, $isDefault);
	        	
	        foreach ( $servers as $server ) {
	            // create the task for each server
	            $this->insertNewPluginStatus ( $server, $pluginId, $pluginVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
	            $taskId = $this->insertNewTask ( self::TASK_TYPE_DEPLOY, $groupId, $server, $taskDescriptorId, $auditId );
	        }
	
	        $this->commit ();
	
	    } catch ( PDOException $ex ) {
	        $this->rollback ();
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}


	public function setDefaultPlugin($pluginVersionId) {
	
	    try {
	        	
	        $pluginId = $this->getPluginIdByPluginVersion($pluginVersionId);
	        	
	        if ($this->resetPluginDefaults($pluginId)) {
	
	            $query2 = $this->getQuery("set_plugin_default_by_plugin_version");
	            $stmt2 = $this->_dbh->prepare ( $query2 );
	            $stmt2->bindValue(":plugin_version_id", $pluginVersionId);
	
	            $res = $this->executeStatement($stmt2);
	            if ($res) {
	                return true;
	            } else {
	                return false;
	            }
	        }
	
	        return false;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	
	}
	
	public function getPluginIdByPluginVersion($pluginVersionId) {
	    $query = $this->getQuery ( "get_plugin_id_by_plugin_version" );
	
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue(":plugin_version_id", $pluginVersionId);
	
	    $res = $this->executeStatement($stmt);
	    if ($res) {
	        foreach ( $stmt->fetchAll () as $row ) {
	            return $row['plugin_id'];
	        }
	    }
	
	    return "";
	}
	
	public function insertUpdateDefaultPluginTask($servers, $pluginVersionId) {
	
	    try {
	
	        $descId = $this->insertNewTaskDescriptor(-1, array(), array("pluginVersionId"=>$pluginVersionId), -1, time(), self::TASK_STATUS_ACTIVE);
	        	
	        foreach ($servers as $server) {
	
	            $this->insertNewTask ( self::TASK_TYPE_UPDATE_DEFAULT_PLUGIN, -1, $server, $descId, -1);
	
	        }
	
	        return true;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}

	/**
	 * Resume a pending deployment task (fill in apps and tasks)
	 * @param array $server
	 * @param string $name
	 * @param integer $taskDescriptorId
	 * @param ZendDeployment_PackageMetaData_Interface
	 *
	 * @return string application id
	 */
	public function resumePendingPluginDeployment($servers, $name, $taskDescriptorId, $package, $auditId) {
	    try {
	        	
	        $this->beginTransaction ();
	        	
	        $pluginId = $this->insertNewPlugin( $package->getName (), $package->getVersion (), $taskDescriptorId );
	        	
	        $groupId = $this->insertNewSequence ();
	        	
	        $deploymentTime = time ();
	        $pluginVersionId = $this->insertNewPluginVersion ( $pluginId, $taskDescriptorId );
	        foreach ( $servers as $server ) {
	            // create the task for each server
	            $taskId = $this->insertNewTask ( self::TASK_TYPE_DEPLOY, $groupId, $server, $taskDescriptorId, $auditId);
	            $this->insertNewPluginStatus ( $server, $pluginId, $pluginVersionId, $deploymentTime, ZendDeployment_Application::STATUS_WAITING_FOR_DEPLOY );
	        }
	        	
	        $this->commit ();
	
	    } catch ( PDOException $ex ) {
	        	
	        $this->rollback ();
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	/**
	 *
	 * Add a plugin status to a new added plugin
	 * 
	 * @param string $nodeId
	 * @param string $pluginId
	 * @param string $pluginVersionId
	 * @param string $time
	 * @param string $status
	 * @param string $installPath
	 * @return string
	 */
	private function insertNewPluginStatus($nodeId, $pluginId, $pluginVersionId, $time, $status, $installPath = "") {
	
	    ZDBG1 ( "DB: creating new plugin status for node $nodeId. (plugin version $pluginVersionId)" );
	
	    // insert the new plugin status
	    $query = $this->getQuery ( "insert_plugin_status" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_version_id", $pluginVersionId );
	    $stmt->bindValue ( ":node_id", $nodeId );
	    $stmt->bindValue ( ":status", $status );
	    $stmt->bindValue ( ":last_updated", $time );
	    $stmt->bindValue ( ":install_path", $installPath );
	    $this->executeStatement($stmt);
	    $newId = $this->_dbh->lastInsertId ();
	    	
	    return $newId;
	}
	
	private function connectPluginsStatuses($newId, $currentId) {
	    // connect the statuses
	    ZDBG3 ( "Connecting old status $currentId with new status $newId" );
	    $query = $this->getQuery ( "update_plugin_status_next_status" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_status_id", intval($currentId) );
	    $stmt->bindValue ( ":next_status", intval($newId) );
	    $this->executeStatement($stmt);
	}
	
	private function insertNewPluginVersion($pluginId, $taskDescriptorId) {
	
	    ZDBG1 ( "DB: creating new plugin version for plugin $pluginId with task descriptor $taskDescriptorId " );
	
	    // insert the new plugin version
	    $now = time();
	
	    $query = $this->getQuery ( "insert_plugin_version" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_id", $pluginId );
	    $stmt->bindValue ( ":task_descriptor_id", $taskDescriptorId );
	    $stmt->bindValue ( ":creation_time", $now);
	    $this->executeStatement($stmt);
	    $newId = $this->_dbh->lastInsertId ();
	
	    return $newId;
	}
	
	private function resetPluginDefaults($pluginId) {
	    $query = $this->getQuery ( "reset_plugin_default" );
	
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue(":plugin_id", $pluginId);
	
	    return $this->executeStatement($stmt);
	
	}
	
	/**
	 *
	 * Add a new app to the DB
	 * @param string $name
	 * @param string $version
	 * @param string $baseUrl
	 * @param string $taskDescriptorId
	 * @param string $userAppName
	 */
	private function insertNewPlugin($name, $version, $taskDescriptorId) {
	
	    ZDBG2 ( "DB: Creating new plugin - " . $name );
	
	    $query = $this->getQuery ( "insert_plugin" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $this->executeStatement($stmt);
	    return $this->_dbh->lastInsertId ();
	}
	
	public function getPluginIdByName($name) {
	    ZDBG2 ( "ZendDeployment_DB_Handler getPluginIdByName by $name");
		
		// get from the list of installed plugins
	    $query = $this->getQuery ( "get_plugin_id_by_name" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue(":name", $name);
	    $res = $this->executeStatement($stmt);
	    if ($res) {
	        foreach ( $stmt->fetchAll () as $row ) {
	            return $row['plugin_id'];
	        }
	    }
	
	    return -1;
	
	}
	
	public function getPluginByName($name) {
	    ZDBG2 ( "ZendDeployment_DB_Handler getPluginByName by $name");
	
	    $query = $this->getQuery ( "get_plugin_by_name" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue(":name", $name);
	    $res = $this->executeStatement($stmt);
	    if ($res) {
	        foreach ( $stmt->fetchAll () as $row ) {
	            return $row;
	        }
	    }
	
	    return -1;
	
	}
	
	private function getPluginVersionByPluginId($pluginId) {
	    try {
	        ZDBG2 ( "DB: getting plugin $pluginId version" );
	        	
	        $query = $this->getQuery ( "get_plugin_version_by_plugin_id" );
	        $stmt = $this->_dbh->prepare ( $query );
	        $stmt->bindValue ( ":plugin_id", $pluginId );
	        $res = $this->executeStatement($stmt);
	        if ($res) {
	            foreach ( $stmt->fetchAll () as $row ) {
	                return $row;
	            }
	        }
	        	
	        return false;
	    } catch ( PDOException $ex ) {
	        throw new ZendDeployment_Exception ( $ex->getMessage (), ZendDeployment_Exception_Interface::DATABASE_ERROR );
	    }
	}
	
	/**
	 *
	 * Return the task descriptors of a plugin
	 * @param string $pluginId
	 * @return array
	 */
	private function getTaskDescriptorsByPluginId($pluginId) {
	
	    $descs = array ();
	    $query = $this->getQuery ( "get_task_descriptors_by_plugin_id" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_id", $pluginId );
	    $res = $this->executeStatement($stmt);
	    if ($res) {
	        foreach ( $stmt->fetchAll () as $row ) {
	            $res = new TaskDescriptor ();
	            $res->setPackageId ( $row ['package_id'] );
	            $res->setUserParams ( $this->unserializeParams ( $row ['user_params'] ) );
	            $res->setZendParams ( $this->unserializeParams ( $row ['zend_params'] ) );
	            $res->setId ( $row ['task_descriptor_id'] );
	
	            $descs[$row ['plugin_version_id']] = $res;
	        }
	    }
	    return $descs;
	}
	
	/**
	 * @brief Check if there are rows in `deployment_tasks` table. Used by standalone Z-Ray to complete tasks by the ZDD
	 * @return bool
	 */
	public function hasWaitingTasks() {
		$query = $this->getQuery( 'get_total_tasks' );
		/* @var \PDOStatement */
		$stmt = $this->_dbh->prepare ( $query );
		$res = $this->executeStatement($stmt);
		if ($res) {
			$results = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($results && isset($results['total_rows']) && $results['total_rows'] > 0) {
				return true;
			}
		}
		
		return false;
	}
	
	
	private function updatePluginVersionStatus($pluginVersionId, $newStatus) {
	    ZDBG2 ( "updating plugin version $pluginVersionId status to $newStatus");
	    $query = $this->getQuery ( "update_plugin_version_status" );
	    $stmt = $this->_dbh->prepare ( $query );
	    $stmt->bindValue ( ":plugin_version_id", $pluginVersionId );
	    $stmt->bindValue ( ":status", $newStatus);
	    $res = $this->executeStatement($stmt);
	    unset($stmt);
	}
	
	/**
	 * @param PDO $_dbh
	 */
	public function setDbh($_dbh) {
		$this->_dbh = $_dbh;
	}
	
}
