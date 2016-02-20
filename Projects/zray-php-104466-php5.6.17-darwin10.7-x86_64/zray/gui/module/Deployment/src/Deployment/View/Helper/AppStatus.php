<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper,
Deployment\Application,
ZendServer\Log\Log,
Deployment\Model;

class AppStatus extends AbstractHelper {
	public function __invoke($status) {
		switch ($status) {
			case Model::STATUS_TIMEOUT_WAITING_FOR_DEPLOY:
			case Model::STATUS_TIMEOUT_WAITING_FOR_REDEPLOY:
			case Model::STATUS_TIMEOUT_WAITING_FOR_REMOVE:
			case Model::STATUS_TIMEOUT_WAITING_FOR_ROLLBACK:
			case Model::STATUS_TIMEOUT_WAITING_FOR_UPGRADE:
				return 'error';				
			case Model::STATUS_UPLOADING_ERROR:
				return 'uploadError';
			case Model::STATUS_WAITING_FOR_DEPLOY:
			case Model::STATUS_WAITING_FOR_REDEPLOY:
			case Model::STATUS_WAITING_FOR_UPGRADE:
			case Model::STATUS_UPLOADING:
			case Model::STATUS_STAGING:
				return 'staging';
			case Model::STATUS_STAGING_ERROR:
				return 'stageError';
			// plugins statuses
			case Model::STATUS_STAGED:
			    return 'STAGED';
			case Model::STATUS_UNSTAGED:
			    return 'UNSTAGED';
			case Model::STATUS_DISABLED:
			    return 'DISABLED';
			case Model::STATUS_ACTIVE:
				return 'deployed';
			case Model::STATUS_ACTIVATING:
				return 'activating';
			case Model::STATUS_ACTIVATING_ERROR:
				return 'activateError';
	
			case Model::STATUS_WAITING_FOR_REMOVE:
			case Model::STATUS_DEACTIVATING:
				return 'deactivating';
			case Model::STATUS_DEACTIVATING_ERROR:
				return 'deactivateError';
	
			case Model::STATUS_UNSTAGING:
				return 'unstaging';
			case Model::STATUS_UNSTAGING_ERROR:
				return 'unstageError';
	
			case Model::STATUS_NOT_EXISTS:
				return 'notExists';
	
			case Model::STATUS_WAITING_FOR_ROLLBACK:
				return 'rollingBack';
	
			default:
				Log::notice('Invalid status ' . var_export($status,true));
				return 'unknown';
		}
	}
}