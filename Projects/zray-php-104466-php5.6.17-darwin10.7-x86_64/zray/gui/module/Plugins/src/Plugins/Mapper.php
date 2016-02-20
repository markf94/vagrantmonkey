<?php
namespace Plugins;

use Prerequisites\Validator\Generator;
use ZendServer\Edition;
use Deployment\Application\Package;
use ZendDeployment_Manager,
	ZendServer\Log\Log,
	ZendServer\Set,
	ZendServer\Exception;
use Servers\Db\ServersAwareInterface;
use Zsd\Db\TasksMapper;
use Audit\Controller\Plugin\InjectAuditMessageInterface;
use Audit\Controller\Plugin\AuditMessage;
use ZendDeployment_PackageMetaData;
use ZendServer\EditionAwareInterface;

class Mapper implements ServersAwareInterface, EditionAwareInterface, InjectAuditMessageInterface {
	
	private $manager = null;
	
	const STATUS_NOT_EXISTS 		= 'notExists';
	
	const STATUS_UPLOADING 			= 'uploading';
	const STATUS_UPLOADING_ERROR 	= 'error_upload';
	
	const STATUS_STAGING 			= 'staging';
	const STATUS_STAGING_ERROR 		= 'error_staging';
	
	const STATUS_ACTIVE 			= 'OK';
	
	const STATUS_DEACTIVATING 		= 'deactivating';
	const STATUS_DEACTIVATING_ERROR = 'error_deactivating';
	
	const STATUS_UNSTAGING 			= 'unstaging';
	const STATUS_UNSTAGING_ERROR 	= 'error_unstaging';
	
	const STATUS_WAITING_FOR_DEPLOY = 'waiting_deploy';
	const STATUS_WAITING_FOR_REMOVE = 'waiting_remove';
	const STATUS_WAITING_FOR_REDEPLOY = 'waiting_redeploy';
	
	const STATUS_TIMEOUT_WAITING_FOR_DEPLOY = 'error_waiting_deploy';
	const STATUS_TIMEOUT_WAITING_FOR_REMOVE = 'error_waiting_remove';
	const STATUS_TIMEOUT_WAITING_FOR_REDEPLOY = 'error_waiting_redeploy';
	
	const STATUS_UNKNOWN = 'unknown';
	
	/**
	 * @var AuditMessage
	 */
	private $auditMessage;
	
	/**
	 * @var Edition
	 */
	private $edition;

	/**
	 * @param integer $pluginId
	 * @return \DeploymentLibrary\Container
	 */
	public function getPluginById($pluginId) {
		if (is_null($pluginId)) {
			return null;
		}
		return $this->getPluginsByIds(array($pluginId))->current();
	}
	
	/**
	 * @param array $ids
	 * @param string $orderDirection
	 * @return \ZendServer\Set
	 */
	public function getPluginsByIds(array $ids = array(), $orderDirection = 'ASC') {
		
		$plugins = $this->getManager()->getPluginsByIds($ids, $orderDirection);
		
		// For now not optional order of the plugin versions sorting
		foreach($plugins as $index => $plugin) {
			$versions = $plugin['versions'];
			if (is_array($versions) && !empty($versions)) {
				uasort($versions, function($versionArray1, $versionArray2) {
					return version_compare($versionArray1['version'], $versionArray2['version']) * -1; // DESC
				});
			}
			$plugins[$index]['versions'] = $versions;
		}
		$pluginsSet =  new Set($plugins);
		return $pluginsSet->setHydrateClass('\Plugins\PluginContainer');
	}

	/**
	 * @param array $ids
	 * @param string $orderDirection
	 * @return \ZendServer\Set
	 */
	public function getMasterPlugins(array $ids = array(), $orderDirection = 'ASC') {
	
	    $serverIds = $this->getRespondingServers();
	    return $this->getManager()->getMasterPlugins($serverIds, $ids);
	}
	
    /**
     * @param array $sections
     * @return array
     */
    public function getAllPluginsPrerequisites(array $sections = null) {
        $prerequisites = array();
        $plugins = $this->getPluginsByIds();
        foreach ($plugins as $plugin) { /* @var $plugin \Plugins\PluginContainer */
            foreach ($plugin->getVersions() as $pluginVersionId => $pluginVersion) {
                $configuration = $this->getManager()->getPluginVersionPackageMetaData($pluginVersionId)->getPrerequisites();
                $prerequisites[] = Generator::getConfiguration($configuration, $sections);
            }
        }
        return $prerequisites;
    }
    
    /**
     * @param integer $id
     * @return array
     */
    public function getServersStatusByPluginId($id) {
        if (!is_numeric($id) || !$this->isPluginIdExists($id)) {
            return array();
        }
    
        $manager = $this->getManager();
        $plugins = $manager->getPluginsByIds(array($id));
    
        $serverIds = $this->getRespondingServers();
        $serversData = $this->getServersMapper()->findServersById($serverIds);
        foreach ($serverIds as $serverId) {
            if (isset($serversData[$serverId])) {
                $servers[$serverId] = $this->mapPluginServerData($serverId, $plugins[$id], $serversData);
            }
        }
    
        return $servers;
    }
    
    /**
     * @return boolean
     */
    public function isPluginIdExists($id) {
        return $this->getManager()->pluginExists($id);
    }
    
    public function getAllPluginsUpdateUrl($onlyValidUrl = false) {
    	$urls = array();
    	$plugins = $this->getPluginsByIds();
    	foreach ($plugins as $plugin) { /* @var $library \Plugins\PluginContainer */
    		foreach ($plugin->getVersions() as $pluginVersionId => $pluginVersion) {
    			$packageMetaData = $this->getManager()->getPluginVersionPackageMetaData($pluginVersionId);
    			if (! is_null($packageMetaData)) {
	    			$updateUrl = $this->getManager()->getPluginVersionPackageMetaData($pluginVersionId)->getUpdateUrl();
	    			if (! isset($urls[$plugin->getPluginName()]) ||
	    				version_compare($pluginVersion['version'], $urls[$plugin->getPluginName()]['version']) > 0) {
	    				if (! $onlyValidUrl || ($onlyValidUrl && !empty($updateUrl))) {
	    					$urls[$plugin->getPluginName()] = array('version' => $pluginVersion['version'], 'url' => $updateUrl);
	    				}
	    			}
    			}
    		}
    	}
    	return $urls;
    }

	/**
	 * @return array[array]
	 */
	public function getPluginsListInfo() {
		$plugins = array();
		foreach ($this->getPluginsByIds() as $plugin) { /* @var $plugin \Plugins\PluginContainer */
			$versions = $plugin->getVersions();
			
			$plugins[$plugin->getPluginId()] = array(
					'pluginId' => $plugin->getPluginId(),
					'pluginName' => $plugin->getPluginName(),
					'status'	=> $this->calculateStatus($versions),
					'PluginVersionsCount' => count($versions),
					'installedLocation' => $this->getInstalledLocation($versions),
					'greatestVersion' => array_reduce($versions, function($max, $item) {
						if (version_compare($item['version'], $max, '>')) {
							$max = $item['version'];
						}
						return $max;
					})
			);
		}
		
		return $plugins;
	}
	
	public function getPluginVersionPackageMetaData($pluginVersionId) {
		return $this->getManager()->getPluginVersionPackageMetaData($pluginVersionId);
	}
	
	public function getPluginVersionPrerequisites($pluginVersionId) {
		return $this->getManager()->getPluginVersionPackageMetaData($pluginVersionId)->getPrerequisites();
	}
	
	public function getPluginIdByPluginVersionId($pluginVersionId) {
		foreach ($this->getPluginsByIds() as $plugin) {
			foreach ($plugin->getVersions() as $version) {
				if ($version['libraryVersionId'] == $pluginVersionId) {
					return $plugin->getPluginId();
				}
			}
		}

		return null;
	}
	
	public function getPluginInfoByPluginVersionId($pluginVersionId) {
		foreach ($this->getPluginsByIds() as $plugin) {
			foreach ($plugin->getVersions() as $version) {
				if ($version['pluginVersionId'] == $pluginVersionId) {
					return array('pluginId' => $plugin->getPluginId(), 'pluginName' => $plugin->getPluginName(), 'pluginStatus' => $plugin->getPluginStatus());
				}
			}
		}	
		
		return null;
	}
	
	/**
	 * @param array $pluginVersionIds
	 * @return array
	 */
	public function getPluginVersionsByIds($pluginVersionIds) {
		$pluginVersions = array ();
		foreach ($this->getPluginsByIds() as $plugin) {
			foreach ($plugin->getVersions() as $version) {
				if (in_array($version['pluginVersionId'], $pluginVersionIds)) {
					$pluginVersions[$version['pluginVersionId']] = $version;
				}
			}
		}
				
		return $pluginVersions;
	}
	
	/**
	 * @param integer $versionId
	 * @return Set
	 */
	public function getPluginByVersionId($versionId) {
		$libVersions = array ();
		foreach ($this->getPluginsByIds() as $plugin) {
			foreach ($plugin->getVersions() as $version) {
				if ($version['pluginVersionId'] == $versionId) {
					return $plugin;
				}
			}
		}
	
		return false;
	}
	
	/**
	 * @param integer $pluginVerId
	 * @return array
	 */
	public function getPluginVersionById($pluginVerId) {
		return current($this->getPluginVersionsByIds(array($pluginVerId)));
	}
	
	public function removePluginVersion($pluginVerId, $ignoreFailures=false) {
		// WebServer check is required only for deployment and not plugin deployment
		$servers = $this->getRespondingServers();
		Log::debug("Removing plugin version {$pluginVerId} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		try {
			return $this->getManager()->removePluginVersion($servers, $pluginVerId, $zendParams);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
	}
	
	public function removePlugin($pluginId, $ignoreFailures=false) {
		// WebServer check is required only for deployment and not plugin deployment
		$servers = $this->getRespondingServers();
		Log::debug("Removing plugin {$pluginId} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		try {
			return $this->getManager()->removePlugin($servers, $pluginId, $zendParams);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
	}
	
	public function enablePlugins($plugins) {
	    // WebServer check is required only for deployment and not plugin deployment
	    $servers = $this->getRespondingServers();
	    Log::debug("Removing plugins " . implode(', ', $plugins). " on servers ".implode(',', $servers));
	
	    $zendParams = $this->addAuditIdToZendParams(array());
	    try {
	        return $this->getManager()->enablePlugins($servers, $plugins, $zendParams);
	    } catch (\ZendDeployment_Exception $e) {
	        throw \Deployment\Exception::fromZendDeploymentException($e);
	    }
	}
	
	public function disblePlugins($plugins) {
	    // WebServer check is required only for deployment and not plugin deployment
		$servers = $this->getRespondingServers();
		
	    Log::debug("Removing plugins " . implode(', ', $plugins). " on servers ".implode(',', $servers));
	
	    $zendParams = $this->addAuditIdToZendParams(array());
	    try {
	        return $this->getManager()->disablePlugins($servers, $plugins, $zendParams);
	    } catch (\ZendDeployment_Exception $e) {
	        throw \Deployment\Exception::fromZendDeploymentException($e);
	    }
	}
	
	public function redeployPlugin($plugin, $ignoreFailures=false) {
		
	    $servers = $this->getRespondingServers();
		
		Log::debug("Redeploy plugin {$plugin->getPluginId()} on servers ".implode(',', $servers));
		$zendParams = array();
		$zendParams = $this->addAuditIdToZendParams($zendParams);
		
	    $this->getManager()->redeployPlugin($servers, $plugin->getPluginId(), $zendParams);
	}
	
	/**
	 * @param Package $packagePath
	 * @throws \Deployment\Exception
	 * @return boolean
	 */
	public function validatePackage($packagePath, $checkOnlyNameExists = false) {
		$package = Package::generate($packagePath);
		
		if (! $package->isPlugin()) {
			Log::err('Uploaded package file is not a plugin');
			throw new \Deployment\Exception(_t('The uploaded package file is not a plugin'), \Deployment\Exception::WRONG_TYPE); 
		}
		
		$manager = new ZendDeployment_Manager();
		
		if (!$checkOnlyNameExists) {
    		if ($manager->isPluginVersionExists($package->getName(), $package->getVersion())) {
    			Log::err("The plugin: {$package->getDisplayName()} {$package->getVersion()} already exists");
    			throw new \Deployment\Exception(_t('The plugin %s %s already exists', array( $package->getDisplayName(), $package->getVersion())), \Deployment\Exception::EXISTING_BASE_URL_ERROR);
    		}
		} else {
		    if (!$manager->isPluginExists($package->getName())) {
		        Log::err("The plugin: {$package->getDisplayName()} doesnt exist");
		        throw new \Deployment\Exception(_t('The plugin %s doesnt exist', array( $package->getDisplayName())), \Deployment\Exception::EXISTING_BASE_URL_ERROR);
		    } 
		}
		return true;
	}
	
	/**
	 * @param string $baseUrl
	 * @param boolean $isDefault
	 * @param array $userParams
	 * @return boolean
	 * @throws \ZendServer\Exception
	 */
	public function deployPlugin($packagePath, $isDefault, $userParams) {		
		$this->validatePackage($packagePath);
		
		$servers = $this->getRespondingServers();
		Log::debug("Deploy plugin {$packagePath} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		$zendParams['isDefault'] = $isDefault;
		
		$auditId = $this->getAuditMessage()->getMessage()->getAuditId();
		$auditId = is_null($auditId) ? TasksMapper::DUMMY_AUDIT_ID : $auditId;
		
		try {
			return $this->getManager()->deployPlugin($servers, $packagePath, $userParams, $auditId);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
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
	 * Gets all enabled* plugins dependencies
	 * @return array
	 */
	public function getAllPluginsPrerequisited(array $sections = null) {
	    $plugins = $this->getMasterPlugins(array());
	    // unset the disabled plugins
	    $enabledPlugins = array();
	    foreach ($plugins as $index => $plugin) {
	        if ($plugin->getMasterStatus() != 'UNSTAGED' && $plugin->getMasterStatus() != 'DISABLED' && $plugin->getMasterStatus() != 'WAITING_FOR_DISABLE') {
	           $enabledPlugins[$index] = $plugin;
	        }
	    }
	    $plugins = $enabledPlugins;
	    $configuration = array();
	    foreach ($plugins as $plugin) {
	        $metadata =  new ZendDeployment_PackageMetaData();
	        $metadata->setPackageDescriptor($plugin->getPackageMetadataJson());
	         
	        if ($metadata instanceof \ZendDeployment_PackageMetaData_Interface) {
	            $prerequisites = $metadata->getPrerequisites();
	            $configuration[$plugin->getPluginId()] = \Prerequisites\Validator\Generator::getConfiguration($prerequisites, $sections);
	        } else {
	
	            throw new \ZendServer\Exception('Deployment package may be corrupted. Check package details and try to redeploy.');
	        }
	    }
	
	    return $configuration;
	}
	
	/**
	 * @param integer $serverId
	 * @param array $plugin
	 * @param array $serversData
	 * @return array
	 */
	private function mapPluginServerData($serverId, $plugin, $serversData) {
	    $pluginContainer = new \Plugins\PluginContainer($plugin);
	    $server = array();
	    $server = $serversData[$serverId]->toArray();
	    $server['pluginId'] = $pluginContainer->getPluginId();
	    	
	    if(array_key_exists( 'NODE_NAME' , $server)){
	        $server['serverName'] = $server['NODE_NAME'];
	    }
	    	
	    $server['version'] = $pluginContainer->getPluginVersion();
	    // temprorally put together also the errors and health messages
	    $server['messages'] = implode('. ', $pluginContainer->getErrors());
	    
	    foreach ($pluginContainer->getVersions() as $version) {
	        if (empty($version)) continue;
	        foreach ($version['serversStatus'] as $id => $data) {
	            if ($serverId == $id) {
	                $server['status'] = $data['status'];
	            }
	        }
	    }
	    return $server;
	}
	
	/**
	 * @return array
	 * @throws \ZendServer\Exception
	 */
	private function getRespondingServers() {
		// for standalone Z-Ray, this function is not relevant - there's only one server
		if (isZrayStandaloneEnv()) {
			return array(0);
		}
		
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
	
	private function addAuditIdToZendParams($zendParams) {
		if (isZrayStandaloneEnv()) {
			// just a random number (my birthday)
			$auditId = 110880;
		} else {
			$auditId = $this->getAuditMessage()->getMessage()->getAuditId();
			$auditId = is_null($auditId) ? TasksMapper::DUMMY_AUDIT_ID : $auditId;
		}
		$zendParams['auditId'] = $auditId;
		return $zendParams;
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
	 * Calculates a status accroding to the servers statuses of the versions so we chose the worst status for 
	 * the plugin status to inform user that he has some problem/error.
	 * 
	 * @param array $versions
	 * @return string
	 */
	private function calculateStatus($versions) {
		$status = 'STAGED'; // default = 'deployed'
		foreach ($versions as $version) {
			foreach ($version['serversStatus'] as $server) {
				switch ($server['status']) {
					case 'ERROR':
					case 'TIMEOUT_WAITING_FOR_DEPLOY':
					case 'TIMEOUT_WAITING_FOR_REDEPLOY':
					case 'TIMEOUT_WAITING_FOR_REMOVE':
					case 'UPLOADING_ERROR':
					case 'STAGING_ERROR':
					case 'UNSTAGING_ERROR':
					case 'NOT_EXISTS':
						return $server['status'];
						
					case 'WAITING_FOR_DEPLOY':
					case 'WAITING_FOR_REDEPLOY':
					case 'UPLOADING':
					case 'STAGING':
					case 'UNSTAGING':
						$status = $server['status'];
						break;
					case 'STAGED':
						break;
					default:
						return $server['status'];
				}
			}
		}
	
		return $status;
	}

	private function getInstalledLocation($versions) {
		$currVer = current($versions);
		return $currVer['installedLocation'];
	}
	
	public function setDefaultPlugin($servers, $pluginVersionId) {
		$this->getManager()->setDefaultPlugin($servers, $pluginVersionId);
	}
	
	/**
	 * @return AuditMessage
	 */
	private function getAuditMessage() {
		return $this->auditMessage;
	}
	/* (non-PHPdoc)
	 * @see \Audit\Controller\Plugin\InjectAuditMessageInterface::setAuditMessage()
	 */
	public function setAuditMessage($auditMessage) {
		$this->auditMessage = $auditMessage;
	}


}