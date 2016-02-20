<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;

class LibraryInfoXml extends AbstractHelper {
	
	/**
	 * @param \DeploymentLibrary\Container $library
	 * @return string
	 */
	public function __invoke(\DeploymentLibrary\Container $library, $prerequisites = null) {
		$libraryVersions = '';
	    foreach ($library->getVersions() as $version) {
	    	$libraryVersions .= $this->getView()->LibraryVersionInfoXml($version, $prerequisites);
	    }
	    
	    $status = $this->getView()->normalizeStatus($library->getVersions());
	    $updateUrl = $this->getView()->normalizeUpdateUrl($library->getVersions());
	    $defaultVersion = $this->getView()->normalizeDefaultVersion($library->getVersions());
	    
	    return <<<XML
    <libraryInfo>
        <libraryId>{$library->getLibraryId()}</libraryId>
        <libraryName>{$library->getLibraryName()}</libraryName>
        <status>{$this->getView()->libStatus($status)}</status>
        <updateUrl><![CDATA[{$updateUrl}]]></updateUrl>
        <defaultVersion><![CDATA[{$defaultVersion}]]></defaultVersion>
        <libraryVersions>
        	{$libraryVersions}
        </libraryVersions>
    </libraryInfo>
XML;
	}
}

