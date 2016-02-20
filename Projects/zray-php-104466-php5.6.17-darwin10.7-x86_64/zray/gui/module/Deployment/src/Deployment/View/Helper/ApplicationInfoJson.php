<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class ApplicationInfoJson extends AbstractHelper {
	
	/**
	 * @param \Deployment\Application\Container $application
	 * @return string
	 */
	public function __invoke(\Deployment\Application\Container $application, $servers=array(), $respondingServersCount=null) {
            
	    $deployedVersions = array (
	        'deployedVersion' => $application->getVersion()
	    );
	    if ($application->isRollbackable()) {
	        $deployedVersions['applicationRollbackVersion'] = $application->getRollbackToVersion()->getVersion();
	    }
	    
	   $appInfo = array(	'id' => $application->getApplicationId(),
				    		'baseUrl' => $this->getView()->applicationUrl($application->getBaseUrl()),
				    		'appName' => $application->getApplicationName(),
				    		'userAppName' => $application->getUserApplicationName(),
				    		'installedLocation' => $application->getInstallPath(),
				    		'status' => $this->getView()->appStatus($application->getStatus()),
				    		'healthCheck' => $this->getView()->appHealthCheckStatus($this->getAppHealthStatus($servers, $application->getHealthStatus())),
				    		'isRollbackable' => $application->isRollbackable() ? true : false,
				    		'isRedeployable' => (! $application->cannotRedeploy()) ? true : false,
				    		'servers' => $this->createServersData($servers, $application->getVersion(), $application->getStatus()),
				    		'deployedVersions' => $deployedVersions,
				    		'messageList' => $this->getMessages($application),
				    		'creationTime' => $this->getView()->WebapiDate($application->getCreationTime()),
				    		'creationTimeTimestamp' => $application->getCreationTime(),
				    		'lastUsed' => $this->getView()->WebapiDate($application->getLastUsed()),
				    		'lastUsedTimestamp' => $application->getLastUsed(),
				    		'isDefinedApplication' => $application->isDefinedApplication(),
				    		'vhostId' => $application->getVhostId(),
				    );
	  
	   /* Freezed, no need to update the status for the not responding servers. Bug #ZSRV-9670
	   if($respondingServersCount !== null && ($respondingServersCount != count($servers))) {
	   		$appInfo['status'] = $this->getView()->appStatus(Model::STATUS_UPLOADING_ERROR);
	   }*/
	   return $this->getView()->json($appInfo);
	}
	
	private function getAppHealthStatus($servers, $defaultStatus) {
		foreach ($servers as $serverId => $serverData) {
			if ($serverData['healthStatus'] != Model::HEALTH_OK) {
				return $serverData['healthStatus'] ;
			}
		}
		return $defaultStatus;
	}
	
	private function createServersData($servers, $deployedVersion, $status) {
		$serversData = array();
		if ($servers) {
			foreach ($servers as $serverId => $serverData) {
				$serversData['applicationServer'][$serverId]['deployedVersion'] = $serverData['version'];
				$serversData['applicationServer'][$serverId]['status'] = $this->getView()->appStatus($serverData['status']);
			}
		}
		else {
			$serversData['applicationServer'][0]['deployedVersion'] = $deployedVersion;
			$serversData['applicationServer'][0]['status'] = $this->getView()->appStatus($status);			
		}
		
		return $serversData;
	}
	
	private function getMessages(\Deployment\Application\Container $application)
	{
	    $messages = array();
	    $errors = $application->getErrors();
	    if (count($errors)) {
	        foreach ($errors as $error) {
	            $messages[] = array('type' => 'error', 'message' => $error);
	        }
	    }
	    $appHealthStatus = $application->getConvertedHealthStatus();
	    if ($appHealthStatus != Model::HEALTH_OK && $application->getHealthMessage()) {
	        $messages[] = array('type' => 'warning', 'message' => $application->getHealthMessage());
	    }
	    
	    return $messages;
	}
}

