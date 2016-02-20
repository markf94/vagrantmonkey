<?php
namespace Deployment;

use ZendServer\Edition;

use Zend\EventManager\EventManager;

use Zend\EventManager\EventsCapableInterface;

use Zend\InputFilter\InputFilter;

use Zend\Form\Factory;

use Servers\Db\Mapper;

use ZendServer\Log\Log;
use ZendServer\Exception;
use Deployment\Application\ApiPendingDeployment;

use ZendDeployment_Manager,
ZendDeployment_PackageFile,
Deployment\Application\Package,
Deployment\Application\Container,
ZendServer\Set;
use ZendServer\EditionAwareInterface;
use Vhost\Mapper\Vhost;
use Servers\Db\ServersAwareInterface;
use Zsd\Db\TasksMapper;
use Audit\Controller\Plugin\InjectAuditMessageInterface;
use Audit\Controller\Plugin\AuditMessage;

class Model implements EventsCapableInterface, EditionAwareInterface, ServersAwareInterface, InjectAuditMessageInterface {

	const PREREQUISITES_PHP_ELEMENT = 'php';
	const PREREQUISITES_ZEND_SERVER_ELEMENT = 'zendserver';
	const PREREQUISITES_ZEND_FRAMEWORK_ELEMENT = 'zendframework';
	const PREREQUISITES_ZEND_FRAMEWORK2_ELEMENT = 'zendframework2';
	const PREREQUISITES_PLUGIN_ELEMENT = 'plugin';
	
	const PREREQUISITES_DIRECTIVE_ELEMENT = 'directive';
	const PREREQUISITES_COMPONENT_ELEMENT = 'zendservercomponent';
	const PREREQUISITES_VERSION_ELEMENT = 'version';
	const PREREQUISITES_EXTENSION_ELEMENT = 'extension';
	
	
	const DEFAULT_SERVER = '<default-server>';
	const STATUS_NOT_EXISTS 		= 1;
	
	const STATUS_UPLOADING 			= 11;
	const STATUS_UPLOADING_ERROR 	= 12;
	
	const STATUS_STAGING 			= 21;
	const STATUS_STAGING_ERROR 		= 22;
	
	//added also plugin statuses 
	const STATUS_STAGED 		    = 'STAGED';
	const STATUS_UNSTAGED 		    = 'UNSTAGED';
	const STATUS_DISABLED 		    = 'DISABLED';
	
	const STATUS_ACTIVATING 		= 31;
	const STATUS_ACTIVE 			= 32;
	const STATUS_ACTIVATING_ERROR 	= 33;
	
	const STATUS_DEACTIVATING 		= 41;
	const STATUS_DEACTIVATING_ERROR = 42;
	
	const STATUS_UNSTAGING 			= 51;
	const STATUS_UNSTAGING_ERROR 	= 52;
	
	const STATUS_WAITING_FOR_DEPLOY = 61;
	const STATUS_WAITING_FOR_REMOVE = 62;
	const STATUS_WAITING_FOR_REDEPLOY = 63;
	const STATUS_WAITING_FOR_UPGRADE = 64;
	const STATUS_WAITING_FOR_ROLLBACK = 65;
	
	const STATUS_TIMEOUT_WAITING_FOR_DEPLOY = 71;
	const STATUS_TIMEOUT_WAITING_FOR_REMOVE = 72;
	const STATUS_TIMEOUT_WAITING_FOR_REDEPLOY = 73;
	const STATUS_TIMEOUT_WAITING_FOR_UPGRADE = 74;
	const STATUS_TIMEOUT_WAITING_FOR_ROLLBACK = 75;
	
	const HEALTH_OK = 81;
	const HEALTH_ERROR = 82;
	const HEALTH_UNKNOWN = 83;
	
	const STATUS_UNKNOWN = 90;
	
	/**
	 * @var \Servers\Db\Mapper
	 */
	private $serversMapper;
	
	private $manager = null;
	
	/**
	 * @var EventManager
	 */
	private $events;
	
	/**
	 * @var boolean
	 */
	private $deploySupportedByWebserver;
	
	/**
	 * @var Edition
	 */
	private $edition;
	
	/**
	 * @var Vhost
	 */
	private $vhostsMapper;
	/**
	 * @var AuditMessage
	 */
	private $auditMessage;
	
	public function getDefineableApplications() {
		return $this->getManager()->getDefineableApplications();
	}
	
	public function getDeployedApplicationNames() {
		return $this->getManager()->getDeployedApplicationNames();		
	}
	
	public function getDeployedBaseUrls() {
		return $this->getManager()->getDeployedBaseUrls();
	}
	
	/**
	 * Redeploy application
	 * @param \Deployment\Application\Container $applicationId
	 * @param boolean $ignoreFailures
	 * @param array $servers
	 */
	public function redeployApplication(\Deployment\Application\Container $application, $ignoreFailures=false, $servers = null, $serviceManager = null) {
		if (is_null($servers)) {
			$servers = $this->getRespondingServers();
		}
		Log::debug("Redeploy app {$application->getApplicationId()} on servers ".implode(',', $servers));
		$zendParams = $this->createZendParams($application->getUserApplicationName(), $ignoreFailures, $application->getBaseUrl(), true);
		$zendParams = $this->addAuditIdToZendParams($zendParams);
		
		
		if ($application->isDefinedApplication()) {
			$this->getManager()->redefineApplication($servers, $application->getApplicationId(), $application->getAppVersionId(), $application->getInstallPath(), $application->getAppStatus());
		} else {
			
			if (in_array($application->getRunOnceNode(), $servers)) {
				Log::debug("Run once node " . $application->getRunOnceNode() . " will redeploy - removing exising rules ");
				$serviceManager->get('PageCache\Model\Mapper')->deleteRulesByApplicationId($this->getManager()->getApplicationIdByBaseUrl($application->getBaseUrl()));
				$serviceManager->get('MonitorRules\Model\Mapper')->removeApplicationRules($this->getManager()->getApplicationIdByBaseUrl($application->getBaseUrl()));
			} else {
				Log::debug("Run once node " . $application->getRunOnceNode() . " will not redeploy - not removing exising rules ");
			}
			
			$this->getManager()->redeployApplication($servers, $application->getApplicationId(), $zendParams);
		}
	}
	
	/**
	 * Remove Application
	 * @param \Deployment\Application\Container $application
	 * @param boolean $ignoreFailures
	 * @param bool $removeJobs
	 */
	public function removeApplication(\Deployment\Application\Container $application, $ignoreFailures=false, $removeApplicationData=false) {
		$servers = $this->getRespondingServers();
		$zendParams = $this->createZendParams($application->getUserApplicationName(), $ignoreFailures, $application->getBaseUrl(), true, null, $removeApplicationData);
		$zendParams = $this->addAuditIdToZendParams($zendParams);
		$this->getEventManager()->trigger('preRemove', $application, $zendParams);	   
		$this->getManager()->removeApplication($servers, $application->getApplicationId(), $zendParams);
		$this->getEventManager()->trigger('postRemove', $application, $zendParams);	   
	}

	
	public function serverRemoved($serverId, $servers) {
		
		//same deal as in disabled
		$this->getManager()->serverDisabled($serverId, $servers);
	}
	
	public function serverDisabled($serverId, $servers) {
		$this->getManager()->serverDisabled($serverId, $servers);	
	}
	
	/**
	 * define Application
	  * @param string $baseUrl
	  * @param string $name
	  * @param string $version
	  * @param string $healthCheck
	  * @param binary $logo
	  */
	public function defineApplication($baseUrl, $name, $version, $healthCheck, $logo=null) {
		$servers = $this->getRespondingServers();
		Log::debug("Define app {$baseUrl} on servers ".implode(',', $servers));
		$this->getManager()->defineApplication($servers, $baseUrl, $name, $version, $healthCheck, $logo);
	}
	
	/**
	 * Rollback application
	 * @param \Deployment\Application\Container $application
	 * @param boolean $ignoreFailures
	 */
	public function applicationRollback(\Deployment\Application\Container $application, $ignoreFailures=false) {
		$servers = $this->getRespondingServers();
		$zendParams = $this->createZendParams($application->getUserApplicationName(), $ignoreFailures, $application->getBaseUrl(), true);
		$zendParams = $this->addAuditIdToZendParams($zendParams);
		$this->getManager()->rollbackApplication($servers, $application->getApplicationId(), $zendParams);
	}

	/**
	 * @param array $servers
	 * @param array $zendParams
	 * @return boolean
	 * @throws \ZendServer\Exception
	 */
	public function redeployAllApplications($servers = null, array $zendParams = array()) {
		if (is_null($servers)) {
			$servers = $this->getRespondingServers();
			if (1 < count($servers)) {
				throw new Exception(_t('A server must be specified when connected to a cluster'));
			}
		}
		Log::debug("Redeploy all applications on servers ".implode(',', $servers));
		$this->getManager()->redeployAllApplications($servers, $this->addAuditIdToZendParams($zendParams));
		return true;
	}
		
	/**
	 * @param string $baseUrl
	 * @return boolean
	 */
	public function updateApplication($baseUrl, $appId, $serviceManager ) {
		if (! $this->isDeploySupportedByWebserver()) {
			throw new Exception(_t('Deployment is not supported on this Web server'));
		}
		$servers = $this->getRespondingServers();
		$pendingDeployment = $this->getPendingDeploymentByBaseUrl($baseUrl); /* @var $package \Deployment\Application\ApiPendingDeployment */
		$zendParams = $this->addAuditIdToZendParams($pendingDeployment->getZendParams());
		try {
			$application = $this->getApplicationById($appId);			
			if (in_array($application->getRunOnceNode(), $servers)) {
				Log::debug("Run once node " . $application->getRunOnceNode() . " will update - removing exising rules ");
				if ($pendingDeployment->getDeploymentPackage()->isPageCacheRulesFileExists()) {
					$serviceManager->get('PageCache\Model\Mapper')->deleteRulesByApplicationId($this->getManager()->getApplicationIdByBaseUrl($application->getBaseUrl()));
				}
				if ($pendingDeployment->getDeploymentPackage()->isMonitorRulesFileExists()) {
					$serviceManager->get('MonitorRules\Model\Mapper')->removeApplicationRules($this->getManager()->getApplicationIdByBaseUrl($application->getBaseUrl()));
				}
			} else {
				Log::debug("Run once node " . $application->getRunOnceNode() . " will not redeploy - not removing exising rules ");
			}
			
			$this->getManager()->upgradeApplication($servers, $pendingDeployment->getDeploymentPackage(), $appId, $pendingDeployment->getUserParams(), $zendParams);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
		return true;
	}
			
	/**
	 * @return \ZendServer\Set
	 */
	public function getAllApplicationsInfo() {
		$manager = $this->getManager();
		$applications = $manager->getAllApplicationsInfo($this->getDefaultServers());
		return new Set($applications);
	}
	
	/**
	 * @param integer $appId
	 * @param string $baseUrl
	 * @param string $userAppName
	 */
	public function setApplicationName($appId, $baseUrl, $userAppName) {
		$manager = $this->getManager();
		$manager->setApplicationName($appId, $baseUrl, $userAppName);
	}
		
	/**
	 * @return array
	 */
	public function getAllApplicationIds() {
		$manager = $this->getManager();
		$applications = $manager->getAllApplicationsInfo($this->getDefaultServers());
		return array_keys($applications);
	}

	/**
	 * @return boolean
	 */
	public function isApplicationIdExists($id) {
		return $this->getManager()->applicationExists($id);
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function getApplicationsInfo(array $ids = array()) {
		$manager = $this->getManager();
		$applications = $manager->getAllApplicationsInfo($this->getDefaultServers());
		
		$intersect = array();
		foreach ($ids as $id) {
			if (isset($applications[$id])) {
				$intersect[$id] = $applications[$id];
			}
		}
		
		return new Set($intersect);
	}
	
	/**
	 * @param integer $id
	 * @return \Deployment\Application\Container
	 */
	public function getApplicationById($id) {
		if (!$this->isApplicationIdExists($id)) {
			return false;
		}
		
		return new Container($this->getManager()->getMasterApplication($id));
	}
	/**
	 * @param array $ids
	 * @return array
	 */
	public function getServersStatusByAppIds(array $ids) {
		
		$manager = $this->getManager();
		$apps = $manager->getApplicationsByIds($ids);
		
		$servers = array();
		$serversMapper = $this->getServersMapper(); /* @var $serversMapper \Servers\Db\Mapper */
		
		$serversData = $serversMapper->findAllServers();
		foreach ($apps as $appId => $applicationServers) {
			foreach ($applicationServers as $serverId => $application) {
				// only if the server data was found in the ZSD_NODES we attach the server info,
				// otherwise the node that the app was deployed is not in the system (not in the ZSD_NODES)
				if (isset($serversData[$serverId])) {
					$servers[$appId][$serverId] = $this->mapApplicationServerData($serverId, $application, $serversData);
				}
			}
		}
		
		return $servers;
	}
	
	/**
	 * @param integer $id
	 * @return array
	 */
	public function getServersStatusByAppId($id) {
		if (!is_numeric($id) || !$this->isApplicationIdExists($id)) {
			return array();
		}
		
		$manager = $this->getManager();
		$apps = $manager->getApplicationsByIds(array($id));
		
		$servers = array();
		$serversMapper = $this->getServersMapper(); /* @var $serversMapper \Servers\Db\Mapper */
		
		$serverIds = array_keys($apps[$id]);
		$serversData = $serversMapper->findServersById($serverIds);
		foreach ($apps[$id] as $serverId => $application) {
			// only if the server data was found in the ZSD_NODES we attach the server info,
			// otherwise the node that the app was deployed is not in the system (not in the ZSD_NODES)
			if (isset($serversData[$serverId])) {
				$servers[$serverId] = $this->mapApplicationServerData($serverId, $application, $serversData);
			}
		}
		
		return $servers;
	}

	/**
	 * @param integer $appId
	 * @param integer $serverId
	 * @return integer
	 */
	public function getServerStatusByAppId($appId, $serverId) {
		$serversStatus = $this->getServersStatusByAppId($appId);
		if (!isset($serversStatus[$serverId])) {
			log::info("Could find app status for server '{$serverId}' and appId '{$appId}'"); // might be when changing from single to cluster for instance
			return self::STATUS_NOT_EXISTS;
		}

		return $serversStatus[$serverId];
	}
		
	/**
	 * @param integer $serverId
	 * @param array $application
	 * @param array $serversData
	 * @return array
	 */
	private function mapApplicationServerData($serverId, $application, $serversData) {
		$appContainer = new \Deployment\Application\Container($application);
		$server = array();
		$server = $serversData[$serverId]->toArray();
		$server['appId'] = $appContainer->getApplicationId();
		$server['status'] = $appContainer->getStatus();
		$server['healthStatus'] = $appContainer->getHealthStatus();
			
		if(array_key_exists( 'NODE_NAME' , $server)){
			$server['serverName'] = $server['NODE_NAME'];
		}
			
		$server['version'] = $appContainer->getVersion();
		// temprorally put together also the errors and health messages
		$server['messages'] = implode('. ', $appContainer->getErrors()) . ' ' . $appContainer->getHealthMessage();
		return $server;
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function getApplicationsByIds(array $ids) {
		$manager = $this->getManager();
		return new Set($manager->getApplicationsByIds($ids));
	}
	
	/**
	 * @return array
	 */
	public function getApplicationsByVhostIds(array $ids) {
		$manager = $this->getManager();
		return $manager->getApplicationsByVhostId($ids);
	}
	
	/**
	 * @param string $baseUrl
	 * @return \Deployment\Application\Container
	 */
	public function getApplicationByBaseUrl($baseUrl) {
		$manager = $this->getManager();
		$application = $manager->getApplicationByBaseUrl($baseUrl);
		if (! $application) {
			return null;
		}
		//TODO improve application selection - this simply picks the first off the collection. Master application by baseurl?
		$application = array_shift($application);
		return new Container($manager->getMasterApplication($application->getApplicationId()));
	}
	
	
	public function removeIntegrationCandidateApplication($appId) {
		$this->getManager()->removeIntegrationCandidateApplication($appId);
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function getMasterApplication($applicationId) {
		$manager = $this->getManager();
		return new Set(array($manager->getMasterApplication($applicationId)));
	}
	
	/**
	 * @param array $ids
	 * @param string $orderDirection
	 * @return \ZendServer\Set
	 */
	public function getMasterApplicationsByIds(array $ids = array(), $orderDirection = 'ASC') {
		$manager = $this->getManager();
		
		if (0 < count($ids)) {
			$result = array();
			$apps = $manager->getApplicationsByIds($ids);
			$masterApps = array();
			foreach ($ids as $id) {
				if (isset($apps[$id])) {
					$app = $manager->getMasterAppFromAppsList(array($apps[$id]));
				} else {
					// Return Empty application with status NOT EXISTS
					$app = new \ZendDeployment_Application();
					$app->setAppId($id);
					$app->setStatus(\ZendDeployment_Application_Interface::STATUS_NOT_EXISTS);
				}
				$masterApps[$id] = $app;
			}
			$result = $masterApps;
		} else {
			$result = $manager->getMasterApplications($this->getDefaultServers());
		}
		
		if ($result) {
			usort($result, function(\ZendDeployment_Application $a, \ZendDeployment_Application $b) use ($orderDirection) {
				// strcmp returns 1|0|-1. Direction will flip the sign but do nothing else
				return strcasecmp($a->getUserApplicationName(), $b->getUserApplicationName()) * ($orderDirection == 'ASC' ? 1 : -1);// non case sensitive
			});			
		}		

		return new Set($result);
	}
	

	/**
	 * @return integer
	 */
	public function getDefaultServerPort() {
		$name = $this->getManager()->getDefaultServerName();
		if (! preg_match('/:([\d]{1,5})/', $name, $res)) {
			Log::notice("Could not determine default deployment port");
			return 80;
		}
		
		$port = $res[1];
		Log::debug("determined default deployment port: {$port}");
		return $port;
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function getMasterApplications() {
		$manager = $this->getManager();
		
		return new Set($manager->getMasterApplications($this->getDefaultServers()));
	} 
	
	/**
	 * @return array
	 */
	public function getAllApplicationsPrerequisited(array $sections = null) {
		$applications = $this->getMasterApplications();
		$applications->setHydrateClass('\Deployment\Application\Container');
		$configuration = array();
		foreach ($applications as $app) {
			$metadata = $app->getPackageMetaData();
			if (is_object($metadata)) {
				$prerequisites = $metadata->getPrerequisites();
				$configuration[] = \Prerequisites\Validator\Generator::getConfiguration($prerequisites, $sections);
			} else {
				
				throw new \ZendServer\Exception('Deployment package may be corrupted. Check package details and try to redeploy.');
			}
		}
		
		return $configuration;
	}
	
	/**
	 * @param ZendDeployment_Manager $manager
	 * @return \Deployment\Model
	 */
	public function setManager(ZendDeployment_Manager $manager) {
		$this->manager = $manager;
		return $this;
	}
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function convertUriToDefaultServer($url) {
		$uri = new \Zend\Uri\Http($url); 
		$port = $uri->getPort();
		$scheme = $uri->getScheme();
		$requestedDefaultPort = (preg_match('#:(80|443)#', $url, $matches) > 0);
		if (! empty($port) && ($requestedDefaultPort || (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443)))) {
			$port = ':' . $port;
		} else {
			$port = '';
		}
		$path = $uri->getPath();
	
		return $scheme . '://' . self::DEFAULT_SERVER . $port . $path;
	}
	
	/**
	 * @param string $baseUrl
	 */
	public function cancelPendingDeployment($baseUrl) {		
		$this->getManager()->cancelPendingDeployment($baseUrl);
	}
	
	/**
	 * @param string $userApplicationName
	 * @param boolean $ignoreFailures
	 * @param string $baseUrl
	 * @param boolean $createVhost
	 * @param boolean $defaultServer
	 * @throws \ZendServer\Exception
	 * @return array
	 */
	public function createZendParams($userApplicationName, $ignoreFailures, $baseUrl, $createVhost=null, $defaultServer=null, $removeApplicationData = false, $vhostId = 0) {
		if (! is_null($userApplicationName)) {
			$this->validateString($userApplicationName, 'userApplicationName');
		}
		
		if (! is_null($baseUrl)) {
			$this->validateString($baseUrl, 'baseUrl');		
		}
		
		if (! is_null($ignoreFailures)) {
			$this->validateBoolean($ignoreFailures, 'ignoreFailures');
		}
	
		$result = array(
				'baseUrl' 				=> 	(string)$baseUrl,
				'userApplicationName'	=> 	(string)$userApplicationName,
				'ignoreFailures' 		=> 	(string)intval($ignoreFailures),
				'removeApplicationData'			=>	(bool) $removeApplicationData,
                'vhostId'               => (integer)$vhostId
		);
	
		if (! is_null($createVhost)) {			
			$this->validateBoolean($createVhost, 'createVhost');
			$result['createVhost'] = (string)intval($createVhost);
		}
	
		if (! is_null($defaultServer)) {
			$this->validateBoolean($defaultServer, 'defaultServer');
			$result['defaultServer'] = (string)intval($defaultServer);
		}
	
		Log::debug('Zend Params: ' . var_export($result, true));
		return $result;
	}	
	
	/**
	 * @param string $path
	 * @param array $userParams parameters given by user
	 * @param array $zendParams parameters given by zend deployment process
	 */
	public function storePendingDeployment($path, $userParams, $zendParams) {
		
		$package = new ZendDeployment_PackageFile();
		$package->loadFile($path);	
		$this->getManager()->storePendingDeployment($package, $userParams, $zendParams);
		
		if (! isset($zendParams['baseUrl'])) {
			$zendParams['baseUrl'] = '';
		}
		$pendingDeployment = $this->getManager()->getPendingDeploymentByBaseUrl($zendParams['baseUrl']);
		return new Package($pendingDeployment->getDeploymentPackage());
	}
	

	/**
	 * @param array $formFields
	 * @param array $userParams
	 * @return \Zend\Form\Form
	 * @throws \ZendServer\Exception
	 */
	public function getUserParamsForm($formFields = array(), array $userParams = array()) {
		try {
			$factory = new Factory();
			$form = $factory->createForm($formFields);
			$form->setAttribute('id', 'deployment-user-params');
			
			if ($userParams) { 
				$form->setData($userParams);
			}
		} catch (\Exception $e) {
			throw new \ZendServer\Exception('Deployment package file may be corrupted. Check file description and try again', null, $e);
		}
		
		return $form;
	}		
	
	/**
	 * @param string $baseUrl
	 * @return \Deployment\Application\ApiPendingDeployment
	 */
	public function getPendingDeploymentByBaseUrl($baseUrl) {
		return new ApiPendingDeployment($this->getManager()->getPendingDeploymentByBaseUrl($baseUrl));
	}

	/**
	 * @see ZendDeployment_Manager_Interface::getVirtualHosts()
	 */
	public function getVirtualHosts() {
		$manager = $this->getManager();
		
		return $manager->getVirtualHosts($this->getDefaultServers());
	}
	
	/**
	 * Convert application status string into constant
	 * @param string $applicationStatus
	 * @return int
	 */
	public static function convertApplicationStatus($applicationStatus)
	{
		switch($applicationStatus) {
			case \ZendDeployment_Application_Interface::STATUS_NOT_EXISTS:
				return self::STATUS_NOT_EXISTS;
				break;
			case \ZendDeployment_Application_Interface::STATUS_UPLOADING:
				return self::STATUS_UPLOADING;
				break;
			case \ZendDeployment_Application_Interface::STATUS_UPLOADING_ERROR:
				return self::STATUS_UPLOADING_ERROR;
				break;
			case \ZendDeployment_Application_Interface::STATUS_STAGING:
				return self::STATUS_STAGING;
				break;
			case \ZendDeployment_Application_Interface::STATUS_STAGING_ERROR:
				return self::STATUS_STAGING_ERROR;
				break;
			case \ZendDeployment_Application_Interface::STATUS_ACTIVE:
				return self::STATUS_ACTIVE;
				break;
			case \ZendDeployment_Application_Interface::STATUS_ACTIVATING:
				return self::STATUS_ACTIVATING;
				break;
			case \ZendDeployment_Application_Interface::STATUS_ACTIVATING_ERROR:
				return self::STATUS_ACTIVATING_ERROR;
				break;
			case \ZendDeployment_Application_Interface::STATUS_DEACTIVATING:
				return self::STATUS_DEACTIVATING;
				break;
			case \ZendDeployment_Application_Interface::STATUS_DEACTIVATING_ERROR:
				return self::STATUS_DEACTIVATING_ERROR;
				break;
			case \ZendDeployment_Application_Interface::STATUS_UNSTAGING:
				return self::STATUS_UNSTAGING;
				break;
			case \ZendDeployment_Application_Interface::STATUS_UNSTAGING_ERROR:
				return self::STATUS_UNSTAGING_ERROR;
				break;
			case \ZendDeployment_Application_Interface::STATUS_WAITING_FOR_DEPLOY:
				return self::STATUS_WAITING_FOR_DEPLOY;
				break;
			case \ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REMOVE:
				return self::STATUS_WAITING_FOR_REMOVE;
				break;
			case \ZendDeployment_Application_Interface::STATUS_WAITING_FOR_REDEPLOY:
				return self::STATUS_WAITING_FOR_REDEPLOY;
				break;
			case \ZendDeployment_Application_Interface::STATUS_WAITING_FOR_UPGRADE:
				return self::STATUS_WAITING_FOR_UPGRADE;
				break;
			case \ZendDeployment_Application_Interface::STATUS_WAITING_FOR_ROLLBACK:
				return self::STATUS_WAITING_FOR_ROLLBACK;
				break;
			case \ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_DEPLOY:
				return self::STATUS_TIMEOUT_WAITING_FOR_DEPLOY;
				break;
			case \ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REMOVE:
				return self::STATUS_TIMEOUT_WAITING_FOR_REMOVE;
				break;
			case \ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY:
				return self::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY;
				break;
			case \ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_UPGRADE:
				return self::STATUS_TIMEOUT_WAITING_FOR_UPGRADE;
				break;
			case \ZendDeployment_Application_Interface::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK:
				return self::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK;
				break;
			default:
				return self::STATUS_UNKNOWN;
		}
	}
	
	/**
	 * Convert application health status string into constant
	 * @param string $applicationHealthStatus
	 * @return int
	 */
	public static function convertApplicationHealthStatus($applicationHealthStatus)
	{
		switch($applicationHealthStatus) {
			case \ZendDeployment_Application_Interface::HEALTH_OK:
				return self::HEALTH_OK;
				break;
			case \ZendDeployment_Application_Interface::HEALTH_ERROR:
				return self::HEALTH_ERROR;
				break;
			case \ZendDeployment_Application_Interface::HEALTH_UNKNOWN:
			default:
				return self::HEALTH_UNKNOWN;
		}
	}
	
	/**
	 * @return array
	 */
	public static function getNoRedeployStatuses() {
		return array(
				self::STATUS_ACTIVATING,
				self::STATUS_DEACTIVATING,
				self::STATUS_STAGING,
				self::STATUS_UNSTAGING,
				self::STATUS_UPLOADING,
				self::STATUS_WAITING_FOR_DEPLOY,
				self::STATUS_WAITING_FOR_REDEPLOY,
				self::STATUS_WAITING_FOR_REMOVE,
				self::STATUS_WAITING_FOR_ROLLBACK,
				self::STATUS_WAITING_FOR_UPGRADE,
		);
	}

	/**
	 * @param Package $packagePath
	 * @throws \Deployment\Exception
	 * @return boolean
	 */
	public function validatePackage($packagePath) {
		$package = Package::generate($packagePath);
	
		if (! $package->isApplication()) {
			Log::err('Uploaded package file is not an application, it may be a library');
			throw new \Deployment\Exception(_t('The uploaded package file is not an application'), \Deployment\Exception::WRONG_TYPE); 
		}
		return true;
	}
	
	/**
	 * @return \Servers\Db\Mapper $serversMapper
	 */
	public function getServersMapper() {
		return $this->serversMapper;
	}
	
	/**
	 * @param \Servers\Db\Mapper $serversMapper
	 * @return Model
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
		return $this;
	}
	
	/**
	 * @return boolean $deploySupportedByWebserver
	 */
	public function isDeploySupportedByWebserver() {
		return $this->deploySupportedByWebserver;
	}

	/**
	 * @param boolean $deploySupportedByWebserver
	 * @return Model
	 */
	public function setDeploySupportedByWebserver($deploySupportedByWebserver) {
		$this->deploySupportedByWebserver = $deploySupportedByWebserver;
		return $this;
	}

	/* (non-PHPdoc)
	 * @see \Zend\EventManager\EventsCapableInterface::events()
	*/
	public function getEventManager() {
		if (is_null($this->events)) {
			$this->events = new EventManager();
		}
		return $this->events;
	}

	public function addAuditIdToZendParams($zendParams) {
		$auditId = null;
		if ($this->getAuditMessage() instanceof AuditMessage) {
			$auditId = $this->getAuditMessage()->getMessage()->getAuditId();
		}
		$auditId = is_null($auditId) ? TasksMapper::DUMMY_AUDIT_ID : $auditId;
		$zendParams['auditId'] = $auditId;
		return $zendParams;
	}
	
	// **** PRIVATE FUNCTIONS FROM HERE ****
		
	/**
	 * @return array
	 * @throws \ZendServer\Exception
	 */
	private function getDefaultServers() {
		try {
			return $this->getRespondingServers();
		} catch (Exception $ex) {
			if (! $this->getEdition()->isClusterServer()) {
				return array(0);
			}
			return $this->getServersMapper()->findAllServersIds();
		}
	}
		
	/**
	 * @return array
	 * @throws \ZendServer\Exception
	 */
	public function getRespondingServers() {
		$serverIds = array();
		$servers = $this->getServersMapper()->findRespondingServers();
		if (0 == $servers->count()) {
			if ($this->getEdition()->isClusterServer()) {
				throw new Exception(_t('Failed to find responding servers'));
			}

			return array(0);// cluster manager with no nodes
		}
		$serverIds = array_map(function($server) {return $server['NODE_ID'];}, $servers->toArray());
		return $serverIds;
	}
	
	private function validateBoolean($value, $name) {
		if (! is_bool($value)) throw new \ZendServer\Exception($name . " should be a boolean");
	
		return true;
	}	
	
	private function validateString($value, $name) {
		if (! is_string($value)) throw new \ZendServer\Exception($name . " should be a string");
	
		return true;
	}		
	
	/**
	 * @return \ZendDeployment_Manager
	 */
	private function getManager() {
		if (is_null($this->manager)) {
			$this->manager = new ZendDeployment_Manager();
		}
		
		return $this->manager;
	}
	

	/**
	 * Get directive prerequisites
	 * @param \SimpleXMLElement $required
		 * @return array
	 */
	private function getPrerequisitesDirective(\SimpleXMLElement $required) {
		$ret = array();
		if(!$required) {
			return $ret;
		}
		foreach ($required->children() as $element) {
			if ($element->getName() == self::PREREQUISITES_DIRECTIVE_ELEMENT) {
					$children = (array)$element->children();
					if (count($children) == 1) {
						$ret[$children['name']] = array('operator' => 'exists', 'operand' => '');
					} else {
						// find the name of the 2nd element
						$operator = current(array_diff(array_keys($children), array('name')));
						$ret[$children['name']] = array('operator' => $operator, 'operand' => $children[$operator]);
					}
			}
		}
		return $ret;
	}
	
	/**
	 * Get component prerequisites
	 * @param \SimpleXMLElement $required
		 * @return array
	 */
	private function getPrerequisitesComponent(\SimpleXMLElement $required) {
		$ret = array();
		if(!$required) {
			return $ret;
		}
		foreach ($required->children() as $element) {
			if ($element->getName() == self::PREREQUISITES_COMPONENT_ELEMENT) {
					$children = (array)$element->children();
					if (count($children) == 1) {
						$ret[$children['name']] = array('operator' => 'exists', 'operand' => '');
					} else {
						// find the name of the 2nd element
						$operator = current(array_diff(array_keys($children), array('name')));
						$ret[$children['name']] = array('operator' => $operator, 'operand' => $children[$operator]);
					}
			}
		}
		return $ret;
	}
	
	/**
	 * Get extension prerequisites
	 * @param \SimpleXMLElement $required
		 * @return array
	 */
	private function getPrerequisitesExtension(\SimpleXMLElement $required) {
		$ret = array();
		if(!$required) {
			return $ret;
		}
		foreach ($required->children() as $element) {
			if ($element->getName() == self::PREREQUISITES_EXTENSION_ELEMENT) {
					$children = (array)$element->children();
					if (count($children) == 1) {
						$ret[$children['name']] = array('operator' => 'exists', 'operand' => '');
					} else {
					   // find the name of the 2nd element
						$operator = current(array_diff(array_keys($children), array('name')));
						$ret[$children['name']] = array('operator' => $operator, 'operand' => $children[$operator]);
					}
			}
		}
		return $ret;
	}
	
	/**
	 * Get system prerequisites
	 * @param \SimpleXMLElement $system
		 * @return array
	 */
	private function getPrerequisitesSystem(\SimpleXMLElement $system) {
		
		$ret = array();
		if(!$system) {
		   return $ret;
		}
		foreach ($system->children() as $element) {
				
				$name = null;
				$code = trim( (string) $element->getName());
				if (!in_array($code, array( self::PREREQUISITES_PHP_ELEMENT, 
				                            self::PREREQUISITES_ZEND_SERVER_ELEMENT,
				                            self::PREREQUISITES_ZEND_FRAMEWORK_ELEMENT,
				                            self::PREREQUISITES_ZEND_FRAMEWORK2_ELEMENT,
				                            self::PREREQUISITES_PLUGIN_ELEMENT))) {
					continue;
				}
				
				$children = (array)$element->children();
				
				switch ($code) {
					case self::PREREQUISITES_PHP_ELEMENT:
						$name = 'PHP';
						break;
					case self::PREREQUISITES_ZEND_SERVER_ELEMENT:
						$name = 'Zend Server';
						break;
					case self::PREREQUISITES_ZEND_FRAMEWORK_ELEMENT:
						$name = 'Zend Framework';
						break;
					case self::PREREQUISITES_ZEND_FRAMEWORK2_ELEMENT:
						$name = 'Zend Framework 2';
						break;
					case self::PREREQUISITES_PLUGIN_ELEMENT:
					    $name = 'Plugin';
					    break;
				}
				
				// find the name of the 2nd element
				$operator = current(array_diff(array_keys($children), array('name')));
				$ret[$name] = array('operator' => $operator, 'operand' => $children[$operator]);
		}
	
		return $ret;
	}
	/* (non-PHPdoc)
	 * @see \ZendServer\EditionAwareInterface::setEdition()
	 */
	public function setEdition($edition) {
		$this->edition = $edition;
	}

	/**
	 * @return Edition
	 */
	public function getEdition() {
		if (! $this->edition instanceof Edition) {
			$this->edition = new Edition();
		}
		return $this->edition;
	}
	/**
	 * @return Vhost
	 */
	public function getVhostsMapper() {
		return $this->vhostsMapper;
	}

	/**
	 * @param \Vhost\Mapper\Vhost $vhostsMapper
	 */
	public function setVhostsMapper($vhostsMapper) {
		$this->vhostsMapper = $vhostsMapper;
	}
	
	/* (non-PHPdoc)
	 * @see \Audit\Controller\Plugin\InjectAuditMessageInterface::setAuditMessage()
	 */
	public function setAuditMessage($auditMessage) {
		$this->auditMessage = $auditMessage;
	}
	/**
	 * @return AuditMessage
	 */
	private function getAuditMessage() {
		return $this->auditMessage;
	}
}

