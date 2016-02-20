<?php

interface ZendDeployment_PackageFile_Interface {

	const ID_NONE = -1;
	
	const TYPE_APPLICATION = 0;
	const TYPE_LIBRARY = 1;
	const TYPE_PLUGIN = 2;
	
	/**
	 * @param string $packageFilepath
	 * @return boolean
	 */
	public static function isValid($packageFilepath);
	
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
	 * Returns the package file path
	 * @return string
	 */
	public function getPackagePath();
	
	/**
	 * Returns the contents of the monitor rules
	 * @return string
	 */
	public function getMonitorRules();
	
	/**
	 * Returns the type of the package
	 * @return (TYPE_APPLICATION/TYPE_LIBRARY)
	 */
	public function getType();

}
	
