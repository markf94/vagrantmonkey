<?php
namespace Servers\Controller;

use Audit\AuditTypeInterface,
	WebAPI\Mvc\View\Http\ExceptionStrategy,
	Zend\Json\Json,
	Zend\Uri\UriFactory,
	WebAPI\SignatureGenerator,
	ZendServer\Edition,
	WebAPI\Exception,
	Application\Db\Creator,
	Audit\Db\ProgressMapper,
	Audit\Db\Mapper,
	Application\Module,
	ZendServer\Mvc\Controller\WebAPIActionController,
	ZendServer\Set,
	ZendServer\Log\Log,
	Zend\View\Model\ViewModel,
	Servers\View\Helper\ServerStatus,
	\Servers\Container,
	Zend\Http\Client as httpClient,
	ZendServer\Ini\IniReader,
	WebAPI,
	Zsd\Db\TasksMapper,
	Notifications\NotificationContainer,
	\Bootstrap\Mapper as BootstrapModel;
use Zend\Http\Client\Exception\ExceptionInterface as HttpException;
use Servers\Configuration\Mapper as ServersConfigurationMapper;
use Application\Db\AbstractFactoryConnector;
use Application\Db\Connector;
use Zend\Config\Reader\Ini;
use Zend\Config\Config;
use Application\Db\DirectivesFileConnector;
use Zend\Db\TableGateway\TableGateway;

class WebAPIController extends WebAPIActionController
{
	public function clusterAddServerAction() {
		$this->isMethodPost();
		
		$params = $this->getParameters(array('serverPort' => Module::config('installation', 'defaultPort')));
		$this->validateMandatoryParameters($params, array('serverName', 'serverIp'));
		
		$this->validateString($params['serverName'], 'serverName');
		$this->validateString($params['serverIp'], 'serverIp');
		$serverPort = $this->validatePositiveInteger($params['serverPort'], 'serverPort');
		
		if ($this->getServersMapper()->isNodeNameExists($params['serverName'])) {
			throw new WebAPI\Exception(_t('This server name already exists in the cluster'), WebAPI\Exception::INVALID_SERVER_RESPONSE);
		}
		
		$config = new IniReader();
		$iniFile = getCfgVar('zend.conf_dir').DIRECTORY_SEPARATOR.'zend_database.ini';
		$dbDirectives = $config->fromFile($iniFile, false);// flat reading, important for windows
		
		$client = new httpClient();
		
		$webAPIKey = $this->getWebapiMapper()->findKeyByName(\WebAPI\Db\Mapper::SYSTEM_KEY_NAME);
		$signatureGenrator = new SignatureGenerator();
		$baseUrl = Module::config('baseUrl');
		
		$uri = UriFactory::factory("http://{$params['serverIp']}:{$serverPort}{$baseUrl}/Api/serverAddToCluster");
		
		$agent = 'Zend ZSM1';
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		$signature = $signatureGenrator
						->setDate($date)
						->setHost("{$uri->getHost()}:{$uri->getPort()}")
						->setRequestUri($uri->getPath())
						->setUserAgent($agent)
					->generate($webAPIKey->getHash());
		
		$client->setMethod('POST')
			->setUri($uri->toString())
			->setHeaders(array(
					'User-Agent'        => $agent,
					'Date'              => $date,
					'X-Zend-Signature'  => "{$webAPIKey->getName()}; $signature",
					'Accept'			=> 'application/vnd.zend.serverapi+json;version=1.3',
			))
			->setParameterPost(array (
				'serverName' => $params['serverName'],
				'nodeIp' => $params['serverIp'],
				'dbHost' => $dbDirectives['zend.database.host_name'],
				'dbUsername' => $dbDirectives['zend.database.user'],
				'dbPassword' => $dbDirectives['zend.database.password'],
				'dbName' => $dbDirectives['zend.database.name'],
				'failIfConnected' => 'TRUE'
			));
		
		try {
			$audit = $this->auditMessage(Mapper::AUDIT_SERVER_ADD, ProgressMapper::AUDIT_NO_PROGRESS, array(array(
				'uri' => $uri,
				'serverName' => $params['serverName'],
				'nodeIp' => $params['serverIp'],
				'dbHost' => $dbDirectives['zend.database.host_name'],
				'dbUsername' => $dbDirectives['zend.database.user'],
				'dbName' => $dbDirectives['zend.database.name'],
			))); /* @var $audit \Audit\Container */
			$response = $client->send();
		} catch (HttpException $ex) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err($ex->getMessage());
			throw new WebAPI\Exception("Communications error - failed to connect to the target server", WebAPI\Exception::INVALID_SERVER_RESPONSE, $ex);
		} catch (\Exception $ex) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err($ex->getMessage());
			throw new WebAPI\Exception($ex->getMessage(), WebAPI\Exception::INVALID_SERVER_RESPONSE, $ex);
		}

		try {
			$response = Json::decode($response->getBody(), Json::TYPE_ARRAY);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED,
					array('message' => $e->getMessage(), 'response' => $response->getBody()));
			throw new WebAPI\Exception('Remote server returned an invalid response', WebAPI\Exception::INVALID_SERVER_RESPONSE, $e);
		}
		
		if (isset($response['errorData'])) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED,
					$response['errorData']);
			
			if ($response['errorData']['errorCode'] == ExceptionStrategy::ERRORCODE_AUTH_ERROR) {
				throw new WebAPI\Exception('Failed to add server. Only servers that have not yet been launched can be added to a cluster.', WebAPI\Exception::INVALID_SERVER_RESPONSE);
			} else {
				throw new WebAPI\Exception($response['errorData']['errorMessage'], WebAPI\Exception::INVALID_SERVER_RESPONSE);
			}
			
		}
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
			
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$serverStatus = $serversMapper->findServerByName($params['serverName']);
		while(empty($serverStatus)) {
			$serverStatus = $serversMapper->findServerByName($params['serverName']);
		}
		$viewModel = new ViewModel(array('server' => $serverStatus));
		$viewModel->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $viewModel;
	}
	
	/**
	 * Re-enable a cluster member. This process may be asynchronous
	 * if Session Clustering is used - if this is the case, the initial operation will return an HTTP 202 response
	 */
	public function clusterEnableServerAction() {
		$params = $this->getParameters();
		$this->validateEnableDisableServerActions($params);
		$serverName = $this->getServersMapper()->findServerNameById($params['serverId']);

		$audit = $this->auditMessage(Mapper::AUDIT_SERVER_ENABLE, ProgressMapper::AUDIT_NO_PROGRESS, array(array('serverName'=>$serverName))); /* @var $audit \Audit\Container */
		try {
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			$tasks->serverEnable($params['serverId']);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to enable the server on cluster: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to enable the server on cluster: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		
		return $this->getServerInfo($params['serverId']);
	}
	
	/**
	 * Disable the cluster member
	 */
	public function clusterDisableServerAction() {
		$params = $this->getParameters();
		$this->validateEnableDisableServerActions($params);
		$serverName = $this->getServersMapper()->findServerNameById($params['serverId']);

		$audit = $this->auditMessage(Mapper::AUDIT_SERVER_DISABLE, ProgressMapper::AUDIT_NO_PROGRESS, array(array('serverName'=>$serverName)));/* @var $audit \Audit\Container */
		
		// check if disabling last active node in the cluster
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$serversSet = $serversMapper->findRespondingServers();
		if ($serversSet->count() == 1) {
			// only check if the server disabled is the current active server
			$currentServer = $serversSet->current();
			if ($currentServer->getNodeId() == $params['serverId']) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
				Log::err("Last active server in the cluster cannot be disabled"); 
				throw new WebAPI\Exception(
						_t('Last active server in the cluster cannot be disabled'), 
						WebAPI\Exception::INTERNAL_SERVER_ERROR
				);
			}
		}
		
		
		try {
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			$tasks->serverDisable($params['serverId']);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to disable the server on cluster: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to disable the server on cluster: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$this->getDeploymentMapper()->serverDisabled($params['serverId'], $this->getServersMapper()->findRespondingServersIds());
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		// use the same view as enable server action uses to avoid the dublication
		$viewModel = new ViewModel($this->getServerInfo($params['serverId']));
		$viewModel->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $viewModel;
	}
	
	/**
	 * Remove a server from the cluster
	 */
	public function clusterForceRemoveServerAction() {
		$params = $this->getParameters();
		$this->validateEnableDisableServerActions($params);		
		$serverName = $this->getServersMapper()->findServerNameById($params['serverId']);

		$audit = $this->auditMessage(Mapper::AUDIT_SERVER_REMOVE_FORCE, ProgressMapper::AUDIT_NO_PROGRESS, array(array('serverName'=>$serverName))); /* @var $audit \Audit\Container */
		try {
			$this->getServersMapper()->setIsDeleted($params['serverId'], 1);
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			$tasks->serverForceRemove($params['serverId']);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to force remove the server on cluster: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to force remove the server on cluster: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$this->getDeploymentMapper()->serverRemoved($params['serverId'], $this->getServersMapper()->findRespondingServersIds());
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		// use the same view as enable server action uses to avoid the dublication
		$viewModel = new ViewModel($this->getServerInfo($params['serverId']));
		$viewModel->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $viewModel;
	}
	
	/**
	 * Remove a server from the cluster
	 */
	public function clusterRemoveServerAction() {
		$params = $this->getParameters();
		$this->validateEnableDisableServerActions($params);
		$serverName = $this->getServersMapper()->findServerNameById($params['serverId']);

		$audit = $this->auditMessage(Mapper::AUDIT_SERVER_REMOVE, ProgressMapper::AUDIT_NO_PROGRESS, array(array('serverName'=>$serverName))); /* @var $audit \Audit\Container */
		
		// check if removing last active node in the cluster (if it have more nodes than one)
		$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
		$serversSet = $serversMapper->findRespondingServers();
		$allServersSet = $serversMapper->findAllServers();
		if ($serversSet->count() == 1 && $allServersSet->count() > 1) {
			// only check if the server disabled is the current active server
			$currentServer = $serversSet->current();
			if ($currentServer->getNodeId() == $params['serverId']) {
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
				Log::err("Last active server in the cluster cannot be removed"); 
				throw new WebAPI\Exception(
						_t('Last active server in the cluster cannot be removed'), 
						WebAPI\Exception::INTERNAL_SERVER_ERROR
				);
			}
		}
		
		try {
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			$tasks->serverRemove($params['serverId']);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to remove the server on cluster: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to remove the server on cluster: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$this->getDeploymentMapper()->serverRemoved($params['serverId'], $this->getServersMapper()->findRespondingServersIds());
		
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		// use the same view as enable server action uses to avoid the dublication
		$viewModel = new ViewModel($this->getServerInfo($params['serverId']));
		$viewModel->setTemplate('servers/web-api/1x3/cluster-enable-server');
		return $viewModel;
	}
	
	/**
	 * 
	 * @throws WebAPI\Exception
	 */
	public function serverAddToClusterAction() {		
		$mapper = new ServersConfigurationMapper();
		if (! $mapper->isClusterSupport()) {
			throw new WebAPI\Exception(_t('The cluster is not supported'), WebAPI\Exception::CLUSTER_NOT_ALLOWED);
		}
		
		$this->isMethodPost();
		$params = $this->getParameters(array('failIfConnected' => 'FALSE'));
		
		$this->validateMandatoryParameters($params, array('serverName', 'dbHost', 'dbUsername', 'dbPassword', 'dbName', 'nodeIp'));
		
		$serverName = $this->validateString($params['serverName'], 'serverName');
		$dbHost 	= $this->validateString($params['dbHost'], 'dbHost');
		$dbUsername = $this->validateString($params['dbUsername'], 'dbUsername');
		$dbPassword = $this->validateString($params['dbPassword'], 'dbPassword');
		$nodeIp 	= $this->validateString($params['nodeIp'], 'nodeIp');
		$dbName 	= $this->validateString($params['dbName'], 'dbName');
		$failIfConnected = $this->validateBoolean($params['failIfConnected'], $params);
		
		$userCredentials = array('new' => false, 'username' => $dbUsername, 'password' => $dbPassword);
		
		if (Module::isClusterServer()) {
			if ($failIfConnected) {
				Log::err("server {$serverName} already connected to a cluster DB at {$dbHost}");
				throw new Exception(_t('Server is already connected to a cluster'), Exception::SERVER_ALREADY_CONNECTED);
			} else {
				Log::warn("server {$serverName} already connected to a cluster DB at {$dbHost}");
				$bootstrapModel = $this->getLocator()->get('Bootstrap\Mapper');
				$bootstrapModel->setBootStrapCompleted();
				$bootstrapModel->setServerUniqueId();
				$bootstrapModel->setServerTimezone();
				
				return $this->waitToGetServerInfoByName($serverName) + array('newCredentials' => array('new' => $userCredentials['new']), 'adminKey'=>$this->getAdminWebApiKey());
			}
		}
		
		$dbHostParts = explode(':', $dbHost);
		if (count($dbHostParts) == 2) {
			$dbHost = $dbHostParts[0];
			$dbPort = $dbHostParts[1];
			$dsn = "mysql:host={$dbHostParts[0]};port={$dbHostParts[1]}";
		} else {
			$dbPort = 3306;
			$dbHost = $dbHostParts[0];
			$dsn = "mysql:host={$dbHostParts[0]}";
		}
		
		// we're writing an audit entry to the local db. ZSD, using the audit data we pass (auditId not required), will write a new audit entry to the cluster db
		$auditEntryData = array(array(
				'serverName' => $serverName,
				'nodeIp' => $nodeIp,
				'dbHost' => $dbHost,
				'dbPort' => $dbPort,
				'dbUsername' => $userCredentials['username'],
				'dbName' => $dbName,
		));
		$audit = $this->auditMessage(Mapper::AUDIT_SERVER_JOIN, ProgressMapper::AUDIT_PROGRESS_REQUESTED, $auditEntryData); /* @var $audit \Audit\Container */
		
		
		try {
			$dbCreator = new Creator($dsn, $dbUsername, $dbPassword, $dbName);
			$this->getDbLock($dbCreator);
			
			try { //@todo - as grantPermissions() is executed only on newly created user, would probably be a good idea to verify that existing user which was passed has all grants
				if (! $dbCreator->zendUserExists()) {
					$userCredentials = $dbCreator->createZendUser();
					$dbCreator->grantPermissions($dbName, $userCredentials['username']);
					$userCredentials['new'] = true;
					Log::info("New user {$userCredentials['username']} created");
				} else {
					Log::notice("User {$userCredentials['username']} already exists, will use provided credentials");
				}				
			} catch (\Exception $e) {
				Log::notice("User {$userCredentials['username']} assumed to be created (mysql.user query cannot be executed as of insufficient privileges)");
			}
			
			if (! $dbCreator->schemaExists()) {
				$dbCreator->createSchema();
				Log::info('Database schema for the cluster created');
			}
			$dbCreator->releaseLock();
		} catch (\Exception $e) {
			if(isset($dbCreator) && $dbCreator instanceof Creator && $dbCreator->hasLock()) {
				$dbCreator->releaseLock();
				$dbCreator->cleanUpZend($dbName);
			}
			
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('messages' => array($e->getMessage())));
			
			if ($e instanceof Exception) {
				throw $e; // simply throw on the webAPI exception
			}

			Log::err("Database schema management failed: {$e->getMessage()}");
			Log::debug($e);
			$this->handleDbCreatorException($e);
		}
		
		$dbAdapter = $dbCreator->getAdapter();
		if ((! Module::config('debugMode', 'zend_gui', 'debugModeEnabled')) && $dbAdapter) {
		    $currentProfile = $this->getNodesProfileMapper()->getProfile();
			$profileValidator = new \ZendServer\Validator\ServerProfileValidator(array('adapter' => $dbAdapter, 'currProfile' => $currentProfile));
			if (!$profileValidator->isValid($serverName)) {
				Log::err("Server $serverName failed profile matching!");
				$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED, array('messages' => $profileValidator->getMessages()));
				throw new WebAPI\Exception(current($profileValidator->getMessages()), WebAPI\Exception::SERVER_NON_MATCHING_PROFILE);
			}
		}

		$serversMapper = $this->getServersMapper();
		$serversMapper->setTableGateway(new TableGateway('ZSD_NODES', $dbAdapter));
		$existingServer = $serversMapper->findServerByName($serverName);
		if ($existingServer->getNodeId() !== '') {
			throw new Exception('Server name is already in use in the cluster', Exception::INVALID_PARAMETER);
		}
		
		$extraAuditDetails = $audit->toArray();
		unset($extraAuditDetails['AUDIT_ID'], $extraAuditDetails['CREATION_TIME']); // not required by ZSD
		try {
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			$tasks->serverAddToCluster(
					$serverName, $nodeIp, $dbHost, $dbPort, $userCredentials['username'], $userCredentials['password'], $dbName, $extraAuditDetails);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to add server to cluster: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to add server to cluster: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		if (! isset($params['defaultServer']) || empty($params['defaultServer'])) {
			$params['defaultServer'] = '<default-server>';
		}
    	$serverData = $this->waitToGetServerInfoByName($serverName);
    	
    	////// Post process audit message - write audit to the cluster db, besides the earlier entry written into the local sqlite db
    	$this->auditMessageClusterLeap(Mapper::AUDIT_SERVER_JOIN, ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY, $auditEntryData);
    	
    	$adminKeyData = $this->getAdminWebApiKey();
    	
    	try {
    		$package = $this->getLocator('Configuration\Task\ConfigurationPackageFreshDb'); /* @var $package \Configuration\Task\ConfigurationPackage */
    		$package->exportConfiguration(\Snapshots\Db\Mapper::SNAPSHOT_SYSTEM_BOOT);
    	} catch (\Exception $e) {
    		// most probably snapshot already exists
    		Log::warn($e->getMessage());
    		Log::debug($e);
    	}
    			
    	return $serverData + array('newCredentials' => array('new' => $userCredentials['new']), 'adminKey'=>$adminKeyData);
	}
	
	
	/**
	 * checks wether the user is going to be the first node in a cluster
	 */
	public function clusterIsInitializedAction() {
		$this->isMethodPost();
		$params = $this->getParameters();
		$this->validateMandatoryParameters($params, array('serverName', 'dbHost', 'dbUsername', 'dbPassword', 'nodeIp'));
		
		$this->validateString($params['serverName'], 'serverName');
		$this->validateString($params['dbHost'], 'dbHost');
		$this->validateString($params['dbUsername'], 'dbUsername');
		$this->validateString($params['dbPassword'], 'dbPassword');
		$this->validateString($params['nodeIp'], 'nodeIp');
		$this->validateString($params['dbName'], 'dbName');
		
		$dbHostParts = explode(':', $params['dbHost']);
		if (count($dbHostParts) == 2) {
			$dsn = "mysql:host={$dbHostParts[0]};port={$dbHostParts[1]}";
		} else {
			$dsn = "mysql:host={$params['dbHost']}";
		}
		
		try {
			$dbCreator = new Creator($dsn, $params['dbUsername'], $params['dbPassword'], $params['dbName']);
			$userCredentials = array('new' => false);
				
			if ( !$dbCreator->schemaExists()) {
				//You are creating a new database
				$userCredentials['new'] = true;
			}
			
		} catch (\Exception $e) {
			Log::debug($e);
			$this->handleDbCreatorException($e);
		}
			
		return array('serverInfo' => array('name' => $params['serverName']), 'newCredentials' => $userCredentials);
	}
	
	public function changeServerNameByIdAction() {
		$this->isMethodPost();		
		$params = $this->getParameters();
		
		$this->validateString($params['serverName'], 'serverName');
		$this->validateInteger($params['serverId'], 'serverId');		

		$server = $this->getServersMapper()->findServerById($params['serverId']);//CHANGE THE SERVER IN DB
		$audit = $this->auditMessage(Mapper::AUDIT_SERVER_RENAME, ProgressMapper::AUDIT_NO_PROGRESS, array(array(
			'oldName' => $server->getNodeName(),
			'serverName' => $params['serverName']
		))); /* @var $audit \Audit\Container */
		try {
			$serversSet = $this->getServersMapper()->changeServerNameById($params['serverId'], $params['serverName']);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to change server name: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to change server name: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		$serversSet->setHydrateClass('\Servers\Container');
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		return array('servers' => $serversSet);
	}
	
	public function clusterGetServersCountAction() {
		$this->isMethodGet();
		$edition = new Edition();
		if ($edition->isSingleServer()) {
			$serversCount = 0;
		} else {
			$serversMapper = $this->getLocator()->get('Servers\Db\Mapper'); /* @var $serversMapper \Servers\Db\Mapper */
			$serversCount = $serversMapper->countAllServers();
		}
		
		return array('serversCount' => $serversCount);
	}

	/**
	 * Restart PHP on all servers or on specified servers in the cluster.
	 * A 202 response in this case does not always indicate a 
	 * successful restart of all servers, and the user is advised to check the server(s) status again
	 * after a few seconds using the clusterGetServerStatus command.
	 * 
	 * 
	 * @throws WebAPI\Exception
	 */
	public function restartPhpAction() {		
		$this->isMethodPost();
		$params = $this->getParameters(
				array(
						'servers' => array(),
						'force'	=> 'FALSE',
				)
		);
		
		$force = $this->validateBoolean($params['force'], 'force');
		
		$serversIds = $this->getServersIds($params['servers']);	

		
	
		try {
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			if ($force) {
				$audit = $this->auditMessage(Mapper::AUDIT_RESTART_PHP, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array('Restart Type' => 'Full','Servers IDs' => $serversIds))); /* @var $audit \Audit\Container */
				$tasks->restartPhpFull($serversIds);
			} else {
				$audit = $this->auditMessage(Mapper::AUDIT_RESTART_PHP, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array('Restart Type' => 'Selective','Servers IDs' => $serversIds))); /* @var $audit \Audit\Container */
				$tasks->restartPhpSelective($serversIds);
			}
			$this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_RESTART_REQUIRED);
			$this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_SERVER_RESTARTING);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to restart PHP: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to restart PHP: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		
		return $this->processServersOutput($serversIds, $audit->getAuditId());
	}

	/**
	 * Restart a zend server daemon on all servers or on specified servers in the cluster.
	 *
	 * @throws WebAPI\Exception
	 */
	public function restartDaemonAction() {	
		$this->isMethodPost();
		$params = $this->getParameters(array('servers' => array()));
		$this->validateMandatoryParameters($params, array('daemon'));
		$daemon = $this->validateString($params['daemon'], 'daemon');
		$serversIds = $this->getServersIds($params['servers']);		
		
		$audit = $this->auditMessage(Mapper::AUDIT_RESTART_DAEMON, ProgressMapper::AUDIT_PROGRESS_REQUESTED, array(array('Daemon' => $daemon))); /* @var $audit \Audit\Container */
		try {
			$tasks = $this->getLocator()->get('Servers\Db\Tasks'); /* @var $tasks \Servers\Db\Tasks */
			$tasks->restartDaemon($serversIds, $daemon);
		} catch (\Exception $e) {
			$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_FAILED);
			Log::err("Failed to restart daemon '{$daemon}: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to restart daemon %s: %s', array($daemon, $e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
	
		return $this->processServersOutput($serversIds, $audit->getAuditId());
	}
	
	
	
	/**
	 *
	 * @param array $serversIds
	 * @throws WebAPI\Exception
	 */
	protected function validateServersIds($serversIds) {
		$serversIds = $this->validateArray($serversIds, 'servers');
		foreach($serversIds as $key => $serverId) {
			$this->validateInteger($serverId, "servers[$key]");
		}
	
		return $serversIds;
	}
	
	/**
	 * 
	 * @param array $serversIds
	 * @return array
	 */
	protected function getServersIds(array $serversIds) {
		$serversIds = $this->validateServersIds($serversIds);
		if ($serversIds) {
			return $serversIds;
		}
			
		$servers = $this->getServersMapper()->findAllServers();
		
		return array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
	}

	protected function processServersOutput($serversIds, $auditId) {// return the serversList with the status of all servers to which the restart command was requested
		$serversSet = $this->getServersMapper()->findServersById($serversIds);
		$this->auditMessageProgress(ProgressMapper::AUDIT_PROGRESS_ENDED_SUCCESFULLY);
		$this->getEvent()->getRouteMatch()->setParam('action', 'cluster-get-server-status');
		return array('servers' => $serversSet);
	}

	protected function addMessageData($servers) {
		$newServers = array();
		$messages = $this->getMessagesMapper()->findServersMessages(array_keys($servers->toArray()));
		$messagesPerServer = array();
		foreach ($messages as $message) { /* @var $message \Messages\MessageContainer */
			$serverId = $message->getMessageNodeId();
			$messagesPerServer[$serverId][] = $message;
		}
	
		foreach ($servers as $idx=>$server) { /* @var $server \Servers\Container */
			if ($server->isStatusError() && !$this->isServerWithErrorMessages($messages)) {
				$server->setStatusCode(ServerStatus::STATUS_WARNING); // node has error status, but there aren't any error messages (usually implies server is mismatched)
			}
				
			$newServers[] = $server->toArray() + array('MESSAGES' => isset($messagesPerServer[$idx]) ? $messagesPerServer[$idx] : array());
		}
	
		return new Set($newServers, '\Servers\Container');
	}
		
	protected function isServerWithErrorMessages($messages) {
		foreach($messages as $message) {/* @var $message \Messages\MessageContainer */
			if ($message->isError()) {
				return true;
			}
		}		
		
		return false;
	}
	
	protected function getGuiTempDir() {
		return \ZendServer\FS\FS::getGuiTempDir();
	}
	
	/**
	 * Trim variables and array (incl. multi-dimensional ones)
	 *
	 * @param mixed $value
	 * @return mixed; null if the param didn't exist
	 */
	protected function trimParam($value) {
		if (is_array($value)) {
			return array_map(array('self', __FUNCTION__), $value);
		}
	
		return trim($value);
	}
	
	/**
	 *
	 * @param array $params
	 * @return array
	 * @throws WebAPI\Exception
	 */
	protected function validateUserParams($userParams)
	{
		$userParams = $this->trimParam($userParams);
		if (! is_array($userParams)) {
			throw new WebAPI\Exception(
					_t("Parameter 'userParams' must be an array of values for the uploaded package"),
					WebAPI\Exception::INVALID_PARAMETER
			);
		}
	}
	
	protected function setFileTransfer() {
		$fileTransfer = new \Zend\File\Transfer\Adapter\Http();
		$uploaddir = $this->getGuiTempDir();
		$fileTransfer->setDestination($uploaddir);
		if (! $fileTransfer->receive()) {
			throw new WebAPI\Exception(
					_t("Failed to upload package file"),
					WebAPI\Exception::INVALID_PARAMETER
			);
		}
		
		Log::debug('File is uploaded to ' . $uploaddir);
		return $fileTransfer;
	}

	protected function generatePackage($fileTransfer) {
		try {
			$deploymentPackage = \Deployment\Application\Package::generate($fileTransfer->getFilename());
		} catch (\Exception $e) {
			Log::err("Failed to validate application package: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t("Failed to validate application package: %s", array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
		 
		return $deploymentPackage;
	}

	
	protected function validateParams($userParams, $baseUrl, $deploymentModel, $deploymentPackage) {
		$validator = $this->getFormValidator($baseUrl, $deploymentModel, $deploymentPackage);
		
		if (! $validator->isValid($userParams)) {
			$deploymentModel->cancelPendingDeployment($baseUrl);			
			throw new WebAPI\Exception(
					_t('Failed to validate user supplied parameters according to the package\'s definitions: %s', current($validator->getMessages())), // @todo - multi errors. using current, as structure is:  ["db_type"]=>["notDigits"]=>'msg'
					WebAPI\Exception::INVALID_PARAMETER);
		}
		
		return $validator;
	}  


	protected function getFormValidator($baseUrl, $deploymentModel, $deploymentPackage) {
		try {
			$validator = $deploymentModel->getUserParamsForm($deploymentPackage->getRequiredParams());
		} catch (\Exception $e) {
			$deploymentModel->cancelPendingDeployment($baseUrl);
			Log::err("Failed to validate user parameters: " . $e->getMessage());
			throw new WebAPI\Exception(
					_t('Failed to validate user parameters: %s', array($e->getMessage())),
					WebAPI\Exception::INTERNAL_SERVER_ERROR
			);
		}
	
		return $validator;
	}

	/**
	 *
	 * @param \Application\Db\Creator $dbCreator
	 */
	protected function getDbLock($dbCreator) {
		$max_time = ini_get('max_execution_time');
		$initTime = $_SERVER["REQUEST_TIME"];
		$sleep=1;
	
		while (($timePassed = round(time() - $initTime)) < $max_time - ($sleep+3)) { // will stop the check, once we're about to reach the max_execution_time limit
			if ($dbCreator->getLock()) {
				Log::info("dbLock obtained");
				return true;
			}
	
			Log::debug("waiting for dbLock ({$timePassed} seconds passed)");
			sleep($sleep);
		}
	
		Log::warn("DB is locked as ZS schema is being created at the moment - try to reissue the command shortly in order to join the cluster");
		throw new Exception(_t('DB is locked as ZS schema is being created at the moment - try to reissue the command shortly in order to join the cluster'), Exception::DB_CREATION_LOCKED);
	}
		
	/**
	 * @param array $params
	 */
	private function validateEnableDisableServerActions($params) {
		$this->isMethodPost();
		$this->validateMandatoryParameters($params, array('serverId'));
		$this->validateExistingServerId($params['serverId']);
	}
	
	/**
	 * @param int $id
	 */
	private function getServerInfo($id) {
		$serversSet = $this->getServersMapper()->findServersById(array($id));
		
		$serverData = $serversSet->current();
		if ($serversSet->count() == 0) {
			$serverData = new Container(array(),'');
			$serverData->setNodeId($id);
			$serverData->setStatusCode(ServerStatus::STATUS_NOT_EXIST);
		}
		
		return array('server' => $serverData);
	}
	
	private function waitToGetServerInfoByName($serverName) {
		$max_time = ini_get('max_execution_time');
		$initTime = $_SERVER["REQUEST_TIME"];
		$sleep=1;
		
		while (($timePassed = round(time() - $initTime)) < $max_time - ($sleep+3)) { // will stop the check, once we're about to reach the max_execution_time limit
			if (($serverData = $this->getServerInfoByName($serverName)) !== false) {
				if (is_numeric($serverData->getNodeId()) && $serverData->getNodeId() > 0) {
					Log::debug("serverData for $serverName was obtained");
					return array('server' => $serverData);					
				}
			}
		
			Log::debug("waiting for serverData for server {$serverName} - ({$timePassed} seconds passed)");
			sleep($sleep);
		}	

		$msg = "Failed retrieving server data for server {$serverName} - the server may have not been added to the cluster";
		Log::err($msg);
		throw new Exception(_t($msg), Exception::INTERNAL_SERVER_ERROR);
	}
	
	/**
	 * @param string $serverName
	 * @return \Servers\Container|false
	 */
	private function getServerInfoByName($serverName) {
		
		$dbConnector = new DirectivesFileConnector();
		if ($dbConnector->isSqlite()) {
			return false;
		}		
        
        $adapter = $dbConnector->createDbAdapter(Connector::DB_CONTEXT_ZSD);
		
		try {
			$res = $adapter->query("select * from ZSD_NODES where NODE_NAME='{$serverName}'"); /* @var $res Zend\Db\Adapter\Driver\Pdo\Statement */
			if (!($serverInfo = $res->execute()->current())) {
				throw new \ZendServer\Exception("No server data yet");
			}
		
		} catch (\Exception $e) {
			return false;		
		}		

		return new \Servers\Container($serverInfo);
	}

	private function arrayToObject($d) {
		if (is_array($d)) {
			return (object) $d;
		}
		else {
			return $d;
		}
	}
	
	/**
	 * @param \Exception $ex
	 * @throws \Exception
	 */
	private function handleDbCreatorException(\Exception $ex) {
		if (($ex instanceof \ZendServer\Exception) && ($ex->getCode() == \ZendServer\Exception::DATABASE_MISSING_DRIVER)) {
			throw new Exception(_t('Cluster connection failed, could not connect (%s)', array($ex->getMessage())), Exception::INTERNAL_SERVER_ERROR, $ex);
		} elseif ($ex->getCode() == 1045) {
			throw new Exception(_t('Cluster connection failed, database authentication failed'), Exception::INTERNAL_SERVER_ERROR, $ex);
		}
		throw new Exception(_t('Cluster connection failed, could not create the database or user', array()), Exception::INTERNAL_SERVER_ERROR, $ex);
	}
	
	/**
	 * We return a MySQL admin key. We assume that when this function is called, we should already have a mysql connection (waitToGetServerInfoByName was already called).
	 * 
	 * @return \WebAPI\Db\ApiKeyContainer
	 */
	private function getAdminWebApiKey() {
		$dbConnector = new DirectivesFileConnector();
		
		// need to work against mysql only
		if ($dbConnector->isSqlite()) {
			return new \WebAPI\Db\ApiKeyContainer(array());
		}
		
		$adapter = $dbConnector->createDbAdapter(Connector::DB_CONTEXT_GUI);
		
		try {
			$res = $adapter->query("select * from GUI_WEBAPI_KEYS where USERNAME='" . Module::config('user', 'adminUser') . "'");
			if (!($keyInfo = $res->execute()->current())) {
				return new \WebAPI\Db\ApiKeyContainer(array());
			}
		} catch (\Exception $e) {
			return new \WebAPI\Db\ApiKeyContainer(array());
		}

		return new \WebAPI\Db\ApiKeyContainer($keyInfo);
	}
}
