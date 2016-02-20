<?php
namespace DeploymentLibrary;

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

class Mapper implements ServersAwareInterface, InjectAuditMessageInterface {
	
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
	 * @var boolean
	 */
	private $deploySupportedByWebserver;
	
	/**
	 * @var AuditMessage
	 */
	private $auditMessage;
	
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
	
	/**
	 * @param integer $libId
	 * @return \DeploymentLibrary\Container
	 */
	public function getLibraryById($libId) {
		if (is_null($libId)) {
			return null;
		}
		return $this->getLibrariesByIds(array($libId))->current();
	}
	
	/**
	 * @param array $ids
	 * @param string $orderDirection
	 * @return \ZendServer\Set
	 */
	public function getLibrariesByIds(array $ids = array(), $orderDirection = 'ASC') {
		
		$libs = $this->getManager()->getLibrariesByIds();
		
		if (count($ids) > 0) {
			$libs = array_intersect_key($libs, array_flip($ids));
		}
		
		if (is_array($libs) && !empty($libs)) {
			uasort($libs, function($a, $b) use ($orderDirection) {
				// strcmp returns 1|0|-1. Direction will flip the sign but do nothing else
				return strcasecmp($a['libraryName'], $b['libraryName']) * ($orderDirection == 'ASC' ? 1 : -1);// non case sensitive
			});			
		}	
		
		// For now not optional order of the library versions sorting
		foreach($libs as $index => $lib) {
			$versions = $lib['versions'];
			if (is_array($versions) && !empty($versions)) {
				uasort($versions, function($versionArray1, $versionArray2) {
					return version_compare($versionArray1['version'], $versionArray2['version']) * -1; // DESC
				});
			}
			$libs[$index]['versions'] = $versions;
		}
		$libsSet =  new Set($libs);
		return $libsSet->setHydrateClass('\DeploymentLibrary\Container');
	}

    /**
     * @param array $sections
     * @return array
     */
    public function getAllLibrariesPrerequisites(array $sections = null) {
        $prerequisites = array();
        $libraries = $this->getLibrariesByIds();
        foreach ($libraries as $library) { /* @var $library \DeploymentLibrary\Container */
            foreach ($library->getVersions() as $libVersionId => $libVersion) {
                $configuration = $this->getManager()->getLibraryVersionPackageMetaData($libVersionId)->getPrerequisites();
                $prerequisites[] = Generator::getConfiguration($configuration, $sections);
            }
        }
        return $prerequisites;
    }
    
    public function getAllLibrariesUpdateUrl($onlyValidUrl = false) {
    	$urls = array();
    	$libraries = $this->getLibrariesByIds();
    	foreach ($libraries as $library) { /* @var $library \DeploymentLibrary\Container */
    		foreach ($library->getVersions() as $libVersionId => $libVersion) {
    			$packageMetaData = $this->getManager()->getLibraryVersionPackageMetaData($libVersionId);
    			if (! is_null($packageMetaData)) {
	    			$updateUrl = $this->getManager()->getLibraryVersionPackageMetaData($libVersionId)->getUpdateUrl();
	    			if (! isset($urls[$library->getLibraryName()]) ||
	    				version_compare($libVersion['version'], $urls[$library->getLibraryName()]['version']) > 0) {
	    				if (! $onlyValidUrl || ($onlyValidUrl && !empty($updateUrl))) {
	    					$urls[$library->getLibraryName()] = array('version' => $libVersion['version'], 'url' => $updateUrl);
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
	public function getLibrariesListInfo() {
		$libraries = array();
		foreach ($this->getLibrariesByIds() as $library) { /* @var $library \DeploymentLibrary\Container */
			$versions = $library->getVersions();
			foreach($versions as $version){
				if($version['default'] === true){
					$defaultVersionLibrary = $version;
				}
			}
			
			$libraries[$library->getLibraryId()] = array(
					'libraryId' => $library->getLibraryId(),
					'libraryName' => $library->getLibraryName(),
					'status'	=> $this->calculateStatus($versions),
					'libraryVersionsCount' => count($versions),
					'installedLocation' => $this->getInstalledLocation($versions),
					'defaultVersion' => $defaultVersionLibrary['version'],
					'greatestVersion' => array_reduce($versions, function($max, $item) {
						if (version_compare($item['version'], $max, '>')) {
							$max = $item['version'];
						}
						return $max;
					})
			); 
		}
		
		return $libraries;
	}
	
	public function getLibraryVersionPackageMetaData($libVersionId) {
		return $this->getManager()->getLibraryVersionPackageMetaData($libVersionId);
	}
	
	public function getLibraryVersionPrerequisites($libVersionId) {
		return $this->getManager()->getLibraryVersionPackageMetaData($libVersionId)->getPrerequisites();
	}
	
	public function getLibraryIdByLibraryVersionId($libVersionId) {
		foreach ($this->getLibrariesByIds() as $library) {
			foreach ($library->getVersions() as $version) {
				if ($version['libraryVersionId'] == $libVersionId) {
					return $library->getLibraryId();
				}
			}
		}

		return null;
	}
	
	public function getLibraryInfoByLibraryVersionId($libVersionId) {
		foreach ($this->getLibrariesByIds() as $library) {
			foreach ($library->getVersions() as $version) {
				if ($version['libraryVersionId'] == $libVersionId) {
					return array('libId' => $library->getLibraryId(), 'libName' => $library->getLibraryName(), 'libStatus' => $library->getLibStatus());
				}
			}
		}	
		
		return null;
	}
	
	/**
	 * @param array $libraryVersionIds
	 * @return array
	 */
	public function getLibraryVersionsByIds($libraryVersionIds) {
		$libVersions = array ();
		foreach ($this->getLibrariesByIds() as $library) {
			foreach ($library->getVersions() as $version) {
				if (in_array($version['libraryVersionId'], $libraryVersionIds)) {
					$libVersions[$version['libraryVersionId']] = $version;
				}
			}
		}
				
		return $libVersions;
	}
	
	/**
	 * @param integer $versionId
	 * @return Set
	 */
	public function getLibraryByVersionId($versionId) {
		$libVersions = array ();
		foreach ($this->getLibrariesByIds() as $library) {
			foreach ($library->getVersions() as $version) {
				if ($version['libraryVersionId'] == $versionId) {
					return $library;
				}
			}
		}
	
		return false;
	}
	
	/**
	 * @param integer $libVerId
	 * @return array
	 */
	public function getLibraryVersionById($libVerId) {
		return current($this->getLibraryVersionsByIds(array($libVerId)));
	}
	
	public function removeLibraryVersion($libVerId, $ignoreFailures=false) {
		// WebServer check is required only for deployment and not library deployment
		$servers = $this->getRespondingServers();
		Log::debug("Removing library version {$libVerId} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		try {
			return $this->getManager()->removeLibraryVersion($servers, $libVerId, $zendParams);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
	}
	
	public function removeLibrary($libId, $ignoreFailures=false) {
		// WebServer check is required only for deployment and not library deployment
		$servers = $this->getRespondingServers();
		Log::debug("Removing library {$libId} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		try {
			return $this->getManager()->removeLibrary($servers, $libId, $zendParams);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
	}
	
	public function redeployLibrary($libId, $ignoreFailures=false) {
		// WebServer check is required only for deployment and not library deployment
		$servers = $this->getRespondingServers();
		Log::debug("Redeploying library {$libId} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		try {
			return $this->getManager()->redeployLibraryVersion($servers, $libId, $zendParams);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
	}
	
	/**
	 * @param Package $packagePath
	 * @throws \Deployment\Exception
	 * @return boolean
	 */
	public function validatePackage($packagePath) {
		$package = Package::generate($packagePath);
		
		if (! $package->isLibrary()) {
			Log::err('Uploaded package file is not a library, it may be an application');
			throw new \Deployment\Exception(_t('The uploaded package file is not a library'), \Deployment\Exception::WRONG_TYPE); 
		}
		
		$manager = new ZendDeployment_Manager();
		if ($manager->isLibraryVersionExists($package->getName(), $package->getVersion())) {
			Log::err("The library: {$package->getName()} {$package->getVersion()} already exists");
			throw new \Deployment\Exception(_t('The library %s %s already exists', array( $package->getName(), $package->getVersion())), \Deployment\Exception::EXISTING_BASE_URL_ERROR);
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
	public function deployLibrary($packagePath, $isDefault, $userParams) {		
		$this->validatePackage($packagePath);
		
		$servers = $this->getRespondingServers();
		Log::debug("Deploy library {$packagePath} on servers ".implode(',', $servers));
	
		$zendParams = $this->addAuditIdToZendParams(array());
		$zendParams['isDefault'] = $isDefault;
		
		try {
			return $this->getManager()->deployLibrary($servers, $packagePath, $userParams, $zendParams);
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
	 * @return array
	 * @throws \ZendServer\Exception
	 */
	private function getRespondingServers() {
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
		$auditId = $this->getAuditMessage()->getMessage()->getAuditId();
		$auditId = is_null($auditId) ? TasksMapper::DUMMY_AUDIT_ID : $auditId;
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
	 * the library status to inform user that he has some problem/error.
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
	
	public function setDefaultLibrary($servers, $libraryVersionId) {
		$this->getManager()->setDefaultLibrary($servers, $libraryVersionId);
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