<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;

class LibraryVersionInfoXml extends AbstractHelper {
	
	/**
	 * @param $libraryVersion
	 * @return string
	 */
	public function __invoke($libraryVersion, $prerequisites = null) {
		$prerequisitesOutput = '';
		if ($prerequisites) {
			$prerequisitesOutput = "<prerequisites>
										$prerequisites
									</prerequisites>
								   ";
		}
		
		$releaseDateTimestamp = '';
		if (! empty($libraryVersion['releaseDate'])) {
			$releaseDateTimestamp = strtotime($libraryVersion['releaseDate']);
		}
		
		$default = $libraryVersion['default'] ? 'Yes' : 'No';
		
		return 
	     	"<libraryVersion>
	    	<libraryVersionId>{$libraryVersion['libraryVersionId']}</libraryVersionId>
	    	<version>" . htmlentities($libraryVersion['version']) . "</version>
	    	<default>{$default}</default>
	    	<status>{$this->getView()->libStatus($this->getView()->normalizeStatus(array($libraryVersion)))}</status>
	    	<installedLocation><![CDATA[{$libraryVersion['installedLocation']}]]></installedLocation>
	    	<isDefinedLibrary>{$libraryVersion['isDefinedLibrary']}</isDefinedLibrary>
	    	<creationTime>{$this->getView()->WebapiDate($libraryVersion['creationTime'])}</creationTime>
        	<creationTimeTimestamp>{$libraryVersion['creationTime']}</creationTimeTimestamp>
        	<releaseDate><![CDATA[{$libraryVersion['releaseDate']}]]></releaseDate>
        	<releaseDateTimestamp><![CDATA[{$releaseDateTimestamp}]]></releaseDateTimestamp>
        	" . $prerequisitesOutput .
        	"<servers>{$this->getView()->libraryVersionServerInfoXml($libraryVersion['serversStatus'])}</servers>	
	    	</libraryVersion>";

	}
	
}

