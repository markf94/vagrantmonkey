<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;
use ZendServer\Log\Log,
	DeploymentLibrary\Mapper;

class LibStatus extends AbstractHelper {
	
	/**
	 * @param string
	 * @return string
	 */
	public function __invoke($status) {
		switch ($status) {
			case 'ERROR':
			case 'TIMEOUT_WAITING_FOR_DEPLOY':
			case 'TIMEOUT_WAITING_FOR_REDEPLOY':
			case 'TIMEOUT_WAITING_FOR_REMOVE':
				return 'error';
			case 'UPLOADING_ERROR':
				return 'uploadError';
			case 'WAITING_FOR_DEPLOY':
			case 'WAITING_FOR_REDEPLOY':
			case 'UPLOADING':
			case 'STAGING':
				return 'staging';
			case 'STAGING_ERROR':
				return 'stageError';
			case 'STAGED':
				return 'deployed';
			case 'WAITING_FOR_REMOVE':
			case 'UNSTAGING':
				return 'unstaging';
			case 'UNSTAGING_ERROR':
				return 'unstageError';
			case 'NOT_EXISTS':
				return 'notExists';
			default:
				Log::notice('Invalid status ' . $status);
				return 'unknown';
		}
	}
	
}

