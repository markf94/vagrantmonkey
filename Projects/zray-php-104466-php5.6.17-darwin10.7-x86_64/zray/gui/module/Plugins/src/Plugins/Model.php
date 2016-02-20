<?php
namespace Plugins;

use ZendServer\Log\Log;
use ZendServer\Exception;

use ZendDeployment_Manager,
ZendDeployment_PackageFile,
ZendDeployment_PackageMetaData,
Deployment\Application\Package,
ZendServer\Set;
use Audit\Controller\Plugin\AuditMessage;
use ZendServer\Edition;
use Plugins\PluginContainer;
use Deployment\Application\ApiPendingDeployment;
use ZendServer\EditionAwareInterface;
use Servers\Db\ServersAwareInterface;
use Audit\Controller\Plugin\InjectAuditMessageInterface;
use Zsd\Db\TasksMapper;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;

class Model implements EditionAwareInterface, ServersAwareInterface, InjectAuditMessageInterface {

	const PREREQUISITES_PHP_ELEMENT = 'php';
	const PREREQUISITES_ZEND_SERVER_ELEMENT = 'zendserver';
	const PREREQUISITES_ZEND_FRAMEWORK_ELEMENT = 'zendframework';
	const PREREQUISITES_ZEND_FRAMEWORK2_ELEMENT = 'zendframework2';
	
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
	 * @var Edition
	 */
	private $edition;
	
	private $storedTaskDescriptorId;

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
	 * @param string $name
	 * @return \Deployment\Application\ApiPendingDeployment|null
	 */
	public function getPendingDeploymentByName($name) {
		// Get instances of "ZendDeployment_PendingDeployment" object (just a container for a pending task)
		$pendingDeployments = $this->getManager()->getPendingPluginDeploymentByName($name);
		if (!$pendingDeployments) {
			return null;
		}
		
	    return new ApiPendingDeployment($pendingDeployments);
	}
	
	/**
	 * @param ZendDeployment_Manager $manager
	 * @return \Deployment\Model
	 */
	public function setManager(ZendDeployment_Manager $manager) {
	    $this->manager = $manager;
	    return $this;
	}
	
	/*
	 * @var AdapterInterface
	 */
	protected $deploymentDbAdapter = null;
	
	/**
	 * @brief set the ZDD DB adapter for executing queries
	 * @param \AdapterInterface $adapter 
	 * @return  
	 */
	public function setDeploymentDbAdapter(AdapterInterface $adapter) {
		$this->deploymentDbAdapter = $adapter;
	}
	
	/**
	 * @param array $ids
	 * @param string $orderDirection
	 * @return \ZendServer\Set
	 */
	public function getMasterPluginsByIds(array $ids = array(), $orderDirection = 'ASC', $order = "id") {
		$manager = $this->getManager();
		return $manager->getMasterPlugins($this->getDefaultServers(), $ids, $orderDirection, $order);
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
	 * @return array
	 * @throws \ZendServer\Exception
	 */
	private function getDefaultServers() {
		if (isZrayStandaloneEnv()) {
			// return only the current server
			return array(0);
		}
		
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
		if (isZrayStandaloneEnv()) {
			// return only the current server
			return array(0);
		}
		
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
	
	/**
	 * @param Package $packagePath
	 * @throws \Deployment\Exception
	 * @return boolean
	 */
	public function validatePackage($packagePath) {
	    $package = Package::generate($packagePath);
	
	    if (! $package->isPlugin()) {
	        Log::err('Uploaded package file is not a plugin');
	        throw new \Deployment\Exception(_t('The uploaded package file is not plugin'), \Deployment\Exception::WRONG_TYPE);
	    }
	    return true;
	}
	
	public function updatePrerequisitesIsValidFlags($plugins, $configurationContainer) {
	    $updatesPlugins = array();
	    foreach ($plugins as $index => $existingPlugin) {
	        $prerequisites = '';
	        $metadata =  new ZendDeployment_PackageMetaData();
	        $metadata->setPackageDescriptor($existingPlugin->getPackageMetadataJson());
	
	        if ($metadata instanceof \ZendDeployment_PackageMetaData_Interface) {
	            $prerequisites = $metadata->getPrerequisites();
	            // remove <?xmlversion="1.0"? from the xml string if exists
	            $prerequisites = substr($prerequisites, strpos($prerequisites, '?'.'>') + 2);
	            $prerequisites = trim($prerequisites);
	        }
	
			if (isZrayStandaloneEnv()) {
				// @TODO: implement!
				$prerequisitesIsValid = true;
			} else {
				try {
					$configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
					$configurationContainer->createConfigurationSnapshot(
						$configuration->getGenerator()->getDirectives(),
						$configuration->getGenerator()->getExtensions(),
						$configuration->getGenerator()->getLibraries(),
						$configuration->getGenerator()->needServerData()
					);
				} catch (\Exception $e) {
					throw new \ZendServer\Exception('Package prerequisites could not be validated: ' . $e->getMessage());
				}
				$prerequisitesIsValid = $configuration->isValid($configurationContainer);
			}
	        if (!$prerequisitesIsValid) {
	            $existingPlugin->setPluginMessage(_t('%s The required prerequisites for this plugin have not been met.', array($existingPlugin->getPluginMessage())));
	        }
	        $existingPlugin->setPrerequisitesIsValidFlag($prerequisitesIsValid);
	        $updatesPlugins[$index] = $existingPlugin;
	    }
	    return $updatesPlugins;
	}
	
	public function removeBrokenUpdates($updates, $configurationContainer) {
	
	    $modifiedUpdates = array();
	    foreach ($updates as $name => $update) {
	        if (!$update['EXTRA_DATA']) {
	            $modifiedUpdates[$name] = $update;
	            continue;
	        }
	        $extra = json_decode($update['EXTRA_DATA'], true);
	         
	        if(!isset($extra['prerequisites']) || !$extra['prerequisites']) {
	            $modifiedUpdates[$name] = $update;
	            continue;
	        }
	
	        if (!$this->checkPluginDependencies($extra['prerequisites'], $configurationContainer)) {
	            // If the prerequisites are invalid - throw the update , don't show it
	            continue;
	        } else {
	            $modifiedUpdates[$name] = $update;
	        }
	    }
	    return $modifiedUpdates;
	}
	
	public function checkPluginDependencies($dependencies, $configurationContainer) {
	    
		// skip prerequisites validation for Z-Ray standalone
		if (isZrayStandaloneEnv()) {
			// @TODO: implement!
			return true;
		}
		
	    $dependenciesXML = new \SimpleXMLElement('<dependencies/>');
	    $required = $dependenciesXML->addChild('required');
	    $xml = ZendDeployment_PackageFile::arrayToXml($dependencies, $required); // should be XML object
	    $prerequisites = $dependenciesXML->asXML();
	    $prerequisites = trim($prerequisites);
	     
	    try {
	        $configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
	        $configurationContainer->createConfigurationSnapshot(
	            $configuration->getGenerator()->getDirectives(),
	            $configuration->getGenerator()->getExtensions(),
	            $configuration->getGenerator()->getLibraries(),
	            $configuration->getGenerator()->needServerData());
	    } catch (\Exception $e) {
	         throw new \ZendServer\Exception('Package prerequisites could not be validated: ' . $e->getMessage());
	    }
	    $prerequisitesIsValid = $configuration->isValid($configurationContainer);
	    return $prerequisitesIsValid;
	}
	
	/**
	 * 
	 * @param string $name
	 */
	public function getPluginByName($name) {
	    $manager = $this->getManager();
	    $pluginId = $manager->getPluginIdByName($name);
	    // doesn't exist
	    if (!$pluginId || $pluginId == -1) {
	        return null;
	    }
	    return $manager->getMasterPluginByPluginId($pluginId);
	}
	
	/**
	 * @brief Get list of all installed plugins
	 * @param array $serversList 
	 * @param string $orderDirection - default "ASC"
	 * @param string $order - default "name"
	 * @return array
	 */
	public function getPluginsList($serversList = array(), $orderDirection = 'ASC', $order = "name") {
		if (empty($serversList)) {
			$serversList = array(0);
		}
		
		// get the list from the DB handler
		return $this->getManager()->getRemoteDbHandler()->getAllPluginsInfo($serversList, $orderDirection, $order);
	}
	
	public function getAuditId() {
	    $auditId = null;
	    if ($this->getAuditMessage() instanceof AuditMessage) {
	        $auditId = $this->getAuditMessage()->getMessage()->getAuditId();
	    }
	    $auditId = is_null($auditId) ? TasksMapper::DUMMY_AUDIT_ID : $auditId;
	    return $auditId;
	}
	    
	/**
	 * 
	 * @param string $name
	 * @param integer $taskDescriptorId, not always set because in wizard the actions are separated: store task descriptor and activate it
	 * @throws Exception
	 * @return boolean
	 */
	public function updatePlugin($name, $taskDescriptorId=null) {
	    
	    $servers = $this->getRespondingServers();
	    $pendingDeployment = $this->getPendingDeploymentByName($name); /* @var $package \Deployment\Application\ApiPendingDeployment */
		
	    $auditId = null;
		if (isZrayStandaloneEnv()) {
			$auditId = 110880; // random number used as audit ID in zray standalone
		} else {
			if ($this->getAuditMessage() instanceof AuditMessage) {
				$auditId = $this->getAuditMessage()->getMessage()->getAuditId();
			}
			$auditId = is_null($auditId) ? TasksMapper::DUMMY_AUDIT_ID : $auditId;
		}
	   
	    try {
	        $plugin = $this->getPluginByName($name);
	        if (! $plugin) {
	            throw new Exception("There is no deployed plugin with passed name $name");
	        }
	        
			$deploymentPackage = $pendingDeployment->getDeploymentPackage();
	        $this->getManager()->upgradePlugin($servers, $deploymentPackage, $plugin->getPluginId(), $taskDescriptorId, $auditId);
	   
	    } catch (\ZendDeployment_Exception $e) {
	        throw \Deployment\Exception::fromZendDeploymentException($e);
	    }
	    return true;
	}
	
	/**
	 * @brief manually deploy plugin by existing folder. Insert data directly to the DB
	 * @param <unknown> $deploymentObject 
	 * @param <unknown> $folderPath 
	 * @return  
	 */
	public function manuallyDeployPluginFolder($deploymentObject, $folderPath) {
		
		// //  insert into "deployment_packages" table
		
		// Get EULA
		$eulaPath = isset($deploymentObject['eula']) && !empty($deploymentObject['eula']) ? 
			$folderPath.DIRECTORY_SEPARATOR.$deploymentObject['eula'] : null;
		$eula = $eulaPath && file_exists($eulaPath) && is_readable($eulaPath) ? file_get_contents($eulaPath) : false;
		$eula = $eula ? $eula : '';
		
		// Get README
		$readmePath = isset($deploymentObject['readme']) && !empty($deploymentObject['readme']) ? 
			$folderPath.DIRECTORY_SEPARATOR.$deploymentObject['readme'] : null;
		$readme = $readmePath && file_exists($readmePath) && is_readable($readmePath) ? file_get_contents($readmePath) : false;
		$readme = $readme ? $readme : '';
		
		// Get LOGO
		$logoPath = isset($deploymentObject['logo']) && !empty($deploymentObject['logo']) ? 
			$folderPath.DIRECTORY_SEPARATOR.$deploymentObject['logo'] : null;
		$logo = $logoPath && file_exists($logoPath) && is_readable($logoPath) ? file_get_contents($logoPath) : false;
		$logo = $logo ? base64_encode($logo) : '';
		
		// get package descriptor
		$packageDescriptor = json_encode($deploymentObject);
		
		// get package name
		$name = isset($deploymentObject['name']) && !empty($deploymentObject['name']) ? $deploymentObject['name'] : '';
		
		// get package version
		$version = isset($deploymentObject['version']) && !empty($deploymentObject['version']) ? $deploymentObject['version'] : '';
		
		/* @var Zend\Db\TableGateway\TableGateway */
		$deploymentPackagesTableGateway = new TableGateway('deployment_packages', $this->deploymentDbAdapter);
		$result = $deploymentPackagesTableGateway->insert(array(
			'package_id' => NULL,
			'path' => NULL,
			'eula' => $eula,
			'readme' => $readme,
			'logo' => $logo,
			
			'package_descriptor' => $packageDescriptor,
			'name' => $name, 
			'version' => $version,
			'monitor_rules' => NULL,
			'pagecache_rules' => NULL
		));
		if (!$result) {
			Log::warn(_t('Cannot manually add the package %s to the DB (deployment_packages). Insert failed', $folderPath));
			return false;
		}
		$packageId = $deploymentPackagesTableGateway->getLastInsertValue();
		
		// // insert into deployment_package_data
		
		$deploymentPackageDataTableGateway = new TableGateway('deployment_package_data', $this->deploymentDbAdapter);
		$result = $deploymentPackageDataTableGateway->insert(array(
			'package_data_id' => NULL,
			'package_id' => $packageId,
			'data' => NULL,
		));
		if (!$result) {
			Log::warn(_t('Cannot manually add the package %s to the DB (deployment_package_data). Insert failed', $folderPath));
			return false;
		}
		
		// // insert into deployment_plugins
		
		$deploymentPluginsTableGateway = new TableGateway('deployment_plugins', $this->deploymentDbAdapter);
		$result = $deploymentPluginsTableGateway->insert(array(
			'plugin_id' => NULL,
			'unique_plugin_id' => NULL,
		));
		if (!$result) {
			Log::warn(_t('Cannot manually add the package %s to the DB (deployment_plugins). Insert failed', $folderPath));
			return false;
		}
		$pluginId = $deploymentPluginsTableGateway->getLastInsertValue();
		
		// // insert into deployment_tasks_descriptors
		
		$deploymentTasksDescriptorsTableGateway = new TableGateway('deployment_tasks_descriptors', $this->deploymentDbAdapter);
		$result = $deploymentTasksDescriptorsTableGateway->insert(array(
			'task_descriptor_id' => NULL,
			'base_url' => NULL,
			'user_params' => NULL,
			'zend_params' => NULL,
			'package_id' => $packageId,
			'creation_time' => time(),
			'run_once_node_id' => 0,
			'status' => 'ACTIVE',
		));
		if (!$result) {
			Log::warn(_t('Cannot manually add the package %s to the DB (deployment_tasks_descriptors). Insert failed', $folderPath));
			return false;
		}
		$taskDescriptorId = $deploymentTasksDescriptorsTableGateway->getLastInsertValue();
		
		// // insert into deployment_plugins_versions
		
		$deploymentPluginsVersionsTableGateway = new TableGateway('deployment_plugins_versions', $this->deploymentDbAdapter);
		$result = $deploymentPluginsVersionsTableGateway->insert(array(
			'plugin_version_id' => NULL,
			'plugin_id' => $pluginId,
			'task_descriptor_id' => $taskDescriptorId,
			'type_route' => in_array('route', $deploymentObject['type']),
			'type_zray' => in_array('zray', $deploymentObject['type']),
			'type_zs_ui' => in_array('ui', $deploymentObject['type']),
			'creation_time' => time(),
			'last_used' => time(),
		));
		if (!$result) {
			Log::warn(_t('Cannot manually add the package %s to the DB (deployment_plugins_versions). Insert failed', $folderPath));
			return false;
		}
		$pluginVersionId = $deploymentPluginsVersionsTableGateway->getLastInsertValue();
		
		// // insert into deployment_plugins_status
		
		$deploymentPluginsStatusTableGateway = new TableGateway('deployment_plugins_status', $this->deploymentDbAdapter);
		$result = $deploymentPluginsStatusTableGateway->insert(array(
			'plugin_status_id' => NULL,
			'plugin_version_id' => $pluginVersionId,
			'status' => \Deployment\Model::STATUS_STAGED,
			'node_id' => 0,
			'install_path' => $folderPath,
			'last_message' => '',
			'last_updated' => time(),
			'next_status' => -1,
		));
		if (!$result) {
			Log::warn(_t('Cannot manually add the package %s to the DB (deployment_plugins_status). Insert failed', $folderPath));
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Returns the last stored the task descriptor id, when the functionality that needs it is deparated from the function that creates the task descriptor
	 * @return number
	 */
	public function getStoredTaskDescriptorId() {
	    return $this->storedTaskDescriptorId;
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
	 * @param string $name
	 */
	public function cancelPendingDeployment($name) {
		// delete pending tasks of the plugin $name. Delete not relevant records related to tasks
	    $this->getManager()->cancelPendingPluginDeployment($name);
	}
	
	/**
	 * @brief load the deployment package (open zip and create container)
	 * @param string $path
	 * @param string $name 
	 */
	public function storePendingDeployment($path) {
	
	    $package = new ZendDeployment_PackageFile();
	    $package->loadFile($path);

	    $name = $package->getName();
		
		// create new record in task descritor table
	    $this->storedTaskDescriptorId = $this->getManager()->storePendingPluginDeployment($package, $name);
	    
	    $pendingDeployment = $this->getManager()->getPendingPluginDeploymentByName($name);
	    return new Package($pendingDeployment->getDeploymentPackage());
	}
	
	/**
	 * @return AuditMessage
	 */
	private function getAuditMessage() {
		return $this->auditMessage;
	}
}

