<?php

namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;

class LibraryInfoJson extends AbstractHelper {
	
	/**
	 * @param \DeploymentLibrary\Container $library
	 * @return string
	 */
	public function __invoke(\DeploymentLibrary\Container $library, $prerequisites = null, $respondingServersCount=null, $serversData=null) {
	   $libraryVersions = array();
	   foreach($library->getVersions() as $version) {
	   		$libraryVersions[] = $this->getView()->LibraryVersionInfoJson($version, $prerequisites, $serversData);
	   }
	   
	   $status = $this->getView()->normalizeStatus($library->getVersions());
	   $updateUrl = $this->getView()->normalizeUpdateUrl($library->getVersions());
	   $defaultVersion = $this->getView()->normalizeDefaultVersion($library->getVersions());
	   
	   $libInfo = array(	'libraryId' => $library->getLibraryId(),
				    		'libraryName' => $library->getLibraryName(),
	   						'updateUrl' => $library->getUpdateUrl(),
				    		'status' => $status,
	   						'updateUrl' => $updateUrl,
	   						'defaultVersion' => $defaultVersion, 
				    		'libraryVersions' => $libraryVersions);
	   
	   return $this->getView()->json($libInfo);
	}

}