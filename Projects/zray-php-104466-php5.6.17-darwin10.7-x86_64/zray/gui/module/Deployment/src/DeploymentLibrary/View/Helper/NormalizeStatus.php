<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;
use ZendServer\Log\Log,
	DeploymentLibrary\Mapper;

class NormalizeStatus extends AbstractHelper {
	
	/**
	 * @param string
	 * @return string
	 */
	public function __invoke($versions) {
		$status = 'STAGED'; // default = 'deployed'
		foreach ($versions as $version) {
			if (isset($version['serversStatus'])) {
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
		}
	
		return $status;
	}
	
}

