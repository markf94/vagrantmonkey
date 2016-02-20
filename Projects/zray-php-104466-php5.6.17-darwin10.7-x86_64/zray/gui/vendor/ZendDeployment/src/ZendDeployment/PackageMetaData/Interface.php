<?php

interface ZendDeployment_PackageMetaData_Interface
{
	
	/**
	 * @return string - name of application
	 */
	public function getName();
	
	/**
	 * @return string - version of application
	 */
	public function getVersion();

	/**
	 * @return string - content of the EULA if exists, empty string if none
	 */
	public function getEula();

	/**
	 * @return string - base64 encoding of the logo, empty string if none
	 */
	public function getLogo();

	/**
	 * PHP version, ZF version, extensions (existence), directives,
	 * Zend Components (existence), ZS version
	 * @return string XML of
	 * 		zf version
	 * 		zs version
	 * 		php version
	 * 		extensions
	 * 		directives
	 * 		components
	 *
	 * @throws ZendDeployment_Exception
	 */
	public function getPrerequisites();

	/**
	 * @return array - Zend_Form ini definition
	 * @see http://framework.zend.com/manual/en/zend.form.forms.html
	 */
	public function getRequiredParams();
	
	/**
	 * @return string - Contents of the package descriptor
	 */
	public function getPackageDescriptor();	
	
	/**
	 * Returns the package id
	 * 
	 * @return string 
	 */
	public function getPackageId();	
	
	/**
	 * Returns the package path
	 * 
	 * @return string 
	 */
	public function getPackagePath();
	
	public function isNull();
	
	/**
	 * Returns the health check path
	 * @return string 
	 */
	public function getHealthCheckPath();
	
	/**
	 * Returns the contents of the monitor rules
	 * @return string 
	 */
	public function getMonitorRules();
	
	/**
	 * Returns the contents of the page cache rules
	 * @return string
	 */
	public function getPageCacheRules();
	
	/**
	 * @return the $_updateUrl
	 */
	public function getUpdateUrl();	
	
	/**
	 * @return the $_releaseDate
	 */
	public function getReleaseDate();
	
	/**
	 * @return boolean
	 */
	public function isMonitorRulesFileExists();
	
	/**
	 * @return boolean
	 */
	public function isPageCacheRulesFileExists();
}

?>