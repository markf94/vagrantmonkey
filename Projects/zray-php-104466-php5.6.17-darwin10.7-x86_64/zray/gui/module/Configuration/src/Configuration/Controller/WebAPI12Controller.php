<?php

namespace Configuration\Controller;

use ZendServer\FS\FS;
use ZendServer\Mvc\Controller\WebAPIActionController,
	Application\Module,
	Servers\View\Helper\ServerStatus;

class WebAPI12Controller extends WebAPIActionController {

	const DEPLOYMENT_VERSION = '2.0'; // zs 5.x was 1.0, hence 6.x will be declared as 2.0..
	
	const STATUS_NOT_LICENSED = 'notLicensed';
	
	public function getSystemInfoAction() {
		$this->isMethodGet();	
		$licenseInfo = $this->getZemUtilsWrapper()->getLicenseInfo();
		$status = $this->determineSystemStatus($licenseInfo);

		return array(	'status' => $status,
						'edition' => $this->convertZSEdition(),
						'version' => Module::config('package', 'version'),
						'phpversion' => phpversion(),
						'os' => FS::getOSAsString(),
						'deploymentversion' => $this->getDeploymentMapper()->isDeploySupportedByWebserver() ? self::DEPLOYMENT_VERSION : '0',
						'serverLicenseInfo' => $licenseInfo,
						'numberOfNodes' => $this->getServersMapper()->countAllServers(),
			);
	}

	private function determineSystemStatus($licenseInfo) {		
		$systemStatus = $this->getServersMapper()->getSystemStatus();
		if ($systemStatus === ServerStatus::STATUS_RESTART_REQUIRED) {
			return ServerStatus::getServerStatusAsString(ServerStatus::STATUS_RESTART_REQUIRED);// if pendingRestart, then license problems might be solved after a restart
		}

		if (! $licenseInfo->isLicenseOk()) { // we assume in cluster, that the state of the node's license reflects the cluster license (we don't deal with the situation where in a certain node, someone edited manually the license directives)
			return self::STATUS_NOT_LICENSED;
		}
		
		return ServerStatus::getServerStatusAsString(ServerStatus::STATUS_OK);
	}	
	
	private function convertZSEdition() {	
		return 'ZendServer'; // other options: ZendServerClusterManager / ZendServerCommunityEdition are not relevant
	}
}
