<?php

namespace Deployment\Mapper;

use Deployment\Model;
use Deployment\Exception;
use ZendServer\Log\Log;
use Vhost\Mapper\Vhost;
use Vhost\Entity\Vhost as VhostEntity;
use Vhost\Mapper\Exception as VhostException;
class Deploy {
	/**
	 * @var Model
	 */
	private $deploymentMapper;

	/**
	 * @var Vhost
	 */
	private $vhostsMapper;
	
	/**
	 * @var \ZendDeployment_Manager
	 */
	private $manager = null;
	
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
			/* @var \ZendDeployment_Manager */
			$manager = $this->getManager();
			$serverId = $edition->getServerId();
			$manager->downloadFile($serverId, $appId, $libId, $url, $extraData);
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
	 * @param string $baseUrl
	 * @return boolean
	 * @throws \ZendServer\Exception
	 */
	public function deployApplication($baseUrl) {
		if (! $this->getDeploymentMapper()->isDeploySupportedByWebserver()) {
			throw new Exception(_t('Deployment is not supported on this Web server'));
		}
		$servers = $this->getDeploymentMapper()->getRespondingServers();
		Log::debug("Deploy app {$baseUrl} on servers ".implode(',', $servers));
		
		$pendingDeployment = $this->getDeploymentMapper()->getPendingDeploymentByBaseUrl($baseUrl); /* @var $package \Deployment\Application\ApiPendingDeployment */
		$zendParams = $this->getDeploymentMapper()->addAuditIdToZendParams($pendingDeployment->getZendParams());
		try {
			if ($zendParams['defaultServer']) {
				$zendParams['vhostId'] = $this->getVhostsMapper()->getDefaultServerVhost()->getId();
			} elseif ($zendParams['createVhost']) {
				$vhost = $this->getVhostsMapper()->createVhostFromURL($baseUrl);
				if (! $vhost instanceof VhostEntity) {
					throw new \Vhost\Mapper\Exception(_t('Vhost creation failed, deployment failed'), VhostException::VHOST_OPERATION_FAILED);
				}
				$zendParams['vhostId'] = $vhost->getId();
			} else {
				$vhost = $this->getVhostsMapper()->vhostFromURL($baseUrl);
				if (! $vhost instanceof VhostEntity) {
					throw new \Vhost\Mapper\Exception(_t('Vhost creation failed, deployment failed'), VhostException::VHOST_OPERATION_FAILED);
				}
				$zendParams['vhostId'] = $vhost->getId();
			}
			
			// set ignoreFailure only to demo app
			if ($pendingDeployment->getDeploymentPackage()->getName() == \Deployment\Controller\WizardController::DEMO_APP_PACKAGE_NAME || 
				$pendingDeployment->getDeploymentPackage()->getName() == \Deployment\Controller\WizardController::SAMPLES_APP_PACKAGE_NAME) {
				$zendParams['ignoreFailures'] = '1';
			}
			
			$this->getManager()->deployApplication($servers, $pendingDeployment->getDeploymentPackage(), $pendingDeployment->getUserParams(), $zendParams);
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
	 * @return Vhost
	 */
	public function getVhostsMapper() {
		return $this->vhostsMapper;
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
	 * @param \Vhost\Mapper\Vhost $vhostsMapper
	 */
	public function setVhostsMapper($vhostsMapper) {
		$this->vhostsMapper = $vhostsMapper;
	}

	/**
	 * @param \Deployment\Model $deploymentMapper
	 */
	public function setDeploymentMapper($deploymentMapper) {
		$this->deploymentMapper = $deploymentMapper;
	}

}

