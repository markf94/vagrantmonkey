<?php

namespace Plugins\Mapper;

use Plugins\Model;
use Deployment\Exception;
use ZendServer\Log\Log;

class Deploy {
    
	/**
	 * @var Model
	 */
	private $deploymentMapper;
	
	/**
	 * @var \ZendDeployment_Manager
	 */
	private $manager = null;
	
	/**
	 * @var \Plugins\Mapper
	 */
	private $mapper = null;
	
	public function downloadFile($appId, $libId, $url, $extraData) {
		if (! is_array($extraData)) {
			$extraData = json_decode($extraData, true);
		}
		
		if (! is_null($libId) && ! is_int($libId)) {
			$libId = (int) $libId;
		}
		
		if (! is_null($appId) && ! is_int($appId)) {
			$appId = (int) $appId;
		}
		
		$edition = new \ZendServer\Edition();
		
		
		try {
			$this->getManager()->downloadFile($edition->getServerId(), $appId, $libId, $url, $extraData);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
		
		return true;
	}
	
	public function cancelDownloadFile($downloadId) {
		Log::debug("Canceling download {$downloadId}");
		
		$edition = new \ZendServer\Edition();
		
		$this->getManager()->cancelDownloadFile($edition->getServerId(), $downloadId);
	}
	
	/**
	 * @param string $name
	 * @return boolean
	 * @throws \ZendServer\Exception
	 */
	public function deployPlugin($name) {

	    $servers = $this->getDeploymentMapper()->getRespondingServers();
		Log::debug("Deploy plugin {$name} on servers ".implode(',', $servers));
		
		
		try {
			// get instance of "\Deployment\Application\ApiPendingDeployment" object from a pending deployment object $name
			$pendingDeployment = $this->getDeploymentMapper()->getPendingDeploymentByName($name);
			if (!$pendingDeployment) {
				throw new \ZendDeployment_Exception(_t('No pending deployment found. Nothing to deploy'));
			}
			
			if (isZrayStandaloneEnv()) {
				// generate random audit id. I swear it's random! (kidding, that's my birthday)
				$auditId = 110880;
			} else {
				$auditId = $this->getDeploymentMapper()->getAuditId();
			}
			
			$this->getManager()->deployPlugin($servers, $pendingDeployment->getDeploymentPackage(), $name, $auditId);
		} catch (\ZendDeployment_Exception $e) {
			throw \Deployment\Exception::fromZendDeploymentException($e);
		}
		return true;
	}
	
	/**
	 * @return Model
	 */
	public function getDeploymentMapper() {
		return $this->deploymentMapper;
	}

	/**
	 * @return ZendDeployment_Manager
	 */
	public function getManager() {
		return $this->manager;
	}

	/**
	 * @param ZendDeployment_Manager $manager
	 */
	public function setManager($manager) {
		$this->manager = $manager;
	}

	/**
	 * @param \Plugins\Model $deploymentMapper
	 */
	public function setDeploymentMapper($deploymentMapper) {
		$this->deploymentMapper = $deploymentMapper;
	}
	
	/**
	 * @param \Plugins\Mapper $mapper
	 */
	public function setMapper($mapper) {
	    $this->mapper = $mapper;
	}
	
	/**
	 * 
	 * @return \Plugins\Mapper
	 */
	public function getMapper() {
	    return $this->mapper;
	}

}

