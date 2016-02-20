<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;

class LibraryVersionInfoJson extends AbstractHelper {

	/**
	 * @param array $libraryVersion
	 * @return string
	 */
	public function __invoke($libraryVersion, $prerequisites = null, $serversData=null) {
		
		$releaseDateTimestamp = '';
		if (! empty($libraryVersion['releaseDate'])) {
			$releaseDateTimestamp = strtotime($libraryVersion['releaseDate']);
		}
		 
		
		$libVerInfo = array();
		$libVerInfo['version'] 					= $libraryVersion['version'];
		$libVerInfo['default'] 					= $libraryVersion['default'];
   		$libVerInfo['libraryVersionId'] 		= $libraryVersion['libraryVersionId'];
   		$libVerInfo['status'] 					= $this->getView()->libStatus($this->getView()->normalizeStatus(array($libraryVersion)));
   		$libVerInfo['installedLocation'] 		= $libraryVersion['installedLocation'];
   		$libVerInfo['isDefinedLibrary'] 		= $libraryVersion['isDefinedLibrary'];
   		$libVerInfo['creationTime'] 			= $this->getView()->webapidate($libraryVersion['creationTime']);
   		$libVerInfo['creationTimeTimestamp']	= $libraryVersion['creationTime'];
   		$libVerInfo['releaseDate']				= $libraryVersion['releaseDate'];
   		$libVerInfo['releaseDateTimestamp']		= $releaseDateTimestamp;
   		$libVerInfo['servers'] 					= $this->getView()->libraryVersionServerInfoJson($libraryVersion['serversStatus'], $serversData);
   		if ($prerequisites) {
   			$libVerInfo['prerequisites'] = $prerequisites;
   		}
   		return $libVerInfo;
	}
}
