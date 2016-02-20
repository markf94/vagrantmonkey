<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;

class LibraryVersionServerInfoJson extends AbstractHelper {

	/**
	 * @param array $libraryVersion
	 * @return string
	 */
	public function __invoke($servers, $serversInfoData=null) {
		$libServerInfo = array();
		foreach ($servers as $serverId => $serverData) {
			$server = array();
			$server['id'] 					= $serverId;
			$server['status']				= $this->getView()->libStatus($serverData['status']);
			$server['lastMessage'] 			= $serverData['lastMessage'];
			$server['lastUpdatedTimestamp'] = $serverData['lastUpdated'];
			
			if ($serverData && isset($serversInfoData[$serverId])) {
    			$serverInfo = $serversInfoData[$serverId]->toArray();
    			
    			if(array_key_exists( 'NODE_NAME' , $serverInfo)){
    			    $server['serverName'] = $serverInfo['NODE_NAME'];
    			}
			}
			
			$libServerInfo[] = $server;
		}
		
   		return $libServerInfo;
	}
}
