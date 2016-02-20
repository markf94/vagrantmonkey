<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;

class LibraryVersionServerInfoXml extends AbstractHelper {
	
	/**
	 * @param $libraryVersion
	 * @return string
	 */
	public function __invoke($servers) {
	 	$libraryServersXML = "";
		foreach ($servers as $serverId => $serverData) {
			$libraryServersXML .=
				"<libraryServer>
				<id>{$serverId}</id>
				<status>{$this->getView()->libStatus($serverData['status'])}</status>
				<lastMessage><![CDATA[{$serverData['lastMessage']}]]></lastMessage>
				<lastUpdatedTimestamp>{$serverData['lastUpdated']}</lastUpdatedTimestamp>
				</libraryServer>";
		}

		return $libraryServersXML;
	}
}

