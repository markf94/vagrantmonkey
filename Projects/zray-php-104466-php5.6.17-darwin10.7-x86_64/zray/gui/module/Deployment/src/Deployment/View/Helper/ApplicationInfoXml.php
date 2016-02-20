<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class ApplicationInfoXml extends AbstractHelper {
	
	/**
	 * @param \Deployment\Application\Container $application
	 * @return string
	 */
	public function __invoke(\Deployment\Application\Container $application, array $servers=array()) {            
	    $deployedVersions = '<deployedVersion>' . $application->getVersion() . '</deployedVersion>';
	    if ($application->isRollbackable()) {
	        $deployedVersions .= ('<applicationRollbackVersion>' . $application->getRollbackToVersion()->getVersion() . '</applicationRollbackVersion>');
	    }    
	    
	    $rollbackable = $application->isRollbackable() ? 'true' : 'false';
	    $redeployable = (! $application->cannotRedeploy()) ? 'true' : 'false';
	    $isDefinedApplication = ($application->isDefinedApplication()) ? 'true' : 'false';
	    
	    $serversXML = '';
	    foreach ($servers as $server => $serverData) {
	    	$serversXML .= "<applicationServer>
<id>$server</id>
<deployedVersion>" . $serverData['version'] . "</deployedVersion>
<status>" . $this->getView()->appStatus($serverData['status']) . '</status>
</applicationServer>';
	    }
	    
	    if (empty($servers)) {
	    	$serversXML .= "<applicationServer>
	    	<id>0</id>
	    	$deployedVersions
<status>{$this->getView()->appStatus($application->getStatus())}</status>
</applicationServer>";
	    }
	    
	    return <<<XML
    <applicationInfo>
        <id>{$application->getApplicationId()}</id>
        <baseUrl><![CDATA[{$this->getView()->applicationUrl($application->getBaseUrl())}]]></baseUrl>
        <appName><![CDATA[{$application->getApplicationName()}]]></appName>
        <userAppName><![CDATA[{$application->getUserApplicationName()}]]></userAppName>
        <installedLocation><![CDATA[{$application->getInstallPath()}]]></installedLocation>
        <status>{$this->getView()->appStatus($application->getStatus())}</status>
        <isRollbackable>{$rollbackable}</isRollbackable>
        <isRedeployable>{$redeployable}</isRedeployable>
        <servers>{$serversXML}
        </servers>
        $deployedVersions
        <messageList>{$this->getMessages($application)}</messageList>
        <creationTime>{$this->getView()->WebapiDate($application->getCreationTime())}</creationTime>
        <creationTimeTimestamp>{$application->getCreationTime()}</creationTimeTimestamp>
        <lastUsed>{$this->getView()->WebapiDate($application->getLastUsed())}</lastUsed>
        <lastUsedTimestamp>{$application->getLastUsed()}</lastUsedTimestamp>
        <isDefinedApplication>{$isDefinedApplication}</isDefinedApplication> 		
        <vhostId>{$isDefinedApplication}</vhostId> 		
    </applicationInfo>
XML;
	}
	
	private function getMessages(\Deployment\Application\Container $application)
	{		
		$messagesXml = '';	
		foreach($application->getErrors() as $error) {
			$messagesXml .= "<error><![CDATA[{$error}]]></error>";
		}
		
		$appHealthStatus = $application->getConvertedHealthStatus();
		if ($appHealthStatus != Model::HEALTH_OK && ($message=$application->getHealthMessage())) {
			$messagesXml .= "<warning><![CDATA[{$message}]]></warning>";
		}
				
		return $messagesXml;		
	}	
	
}

