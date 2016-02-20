<?php
namespace Deployment\Application;

use ZendServer\Log\Log,
ZendDeployment_PackageFile,
ZendServer\Exception;

class Package {

	const PACKAGE_APPLICATION = 0;
	const PACKAGE_LIBRARY = 1;
	const PACKAGE_PLUGIN = 2;
	
	/**
	 * @var \ZendDeployment_PackageMetaData
	 */
	private $packageFile = null;
	
	/**
	 * @param \ZendDeployment_PackageMetaData $packageFilepath
	 */
	public function __construct($packageFile) {
		$this->packageFile = $packageFile;
	}

	/**
	 * 
	 * @param string $packageFilepath
	 * @throws \Exception
	 * @return \Deployment\Application\Package
	 */
	public static function generate($packageFilepath) {
		
		$packageFile = new ZendDeployment_PackageFile();
		$packageFile->loadFile($packageFilepath);
		
		return new self($packageFile);
	}
		
	/**
	 * @param string $packageFilepath
	 * @return boolean
	 */
	public static function isValid($packageFilepath) {
		return ZendDeployment_PackageFile::isValid($packageFilepath);
	}
	
	/**
	 * @return \ZendDeployment_PackageFile_Interface|null
	 */
	public function getPackageFile() {
		return $this->packageFile;
	}
	
	/**
	 * @return boolean
	 */
	public function isApplication() {
		return $this->packageFile->getType() == self::PACKAGE_APPLICATION;
	}
	
	/**
	 * @return boolean
	 */
	public function isLibrary() {
		return $this->packageFile->getType() == self::PACKAGE_LIBRARY;
	}
	
	/**
	 * @return boolean
	 */
	public function isPlugin() {
	    return $this->packageFile->getType() == self::PACKAGE_PLUGIN;
	}
	
	/**
	 * @return boolean
	 */
	public function getPluginType() {
	    return $this->packageFile->getPluginType();
	}
	
	/**
	 * @return string - name of application
	 */
	public function getName() {
		return $this->packageFile->getName();
	}
	
	/**
	 * @return string - display name of application
	 */
	public function getDisplayName() {
	    $displayName = $this->packageFile->getDisplayName();
	    if (!$displayName) {
	        return $this->getName();
	    }
	    return $displayName;
	}
	
	/**
	 * @return string - version of application
	 */
	public function getVersion() {
		return $this->packageFile->getVersion();
	}

	/**
	 * @return string - content of the EULA if exists, empty string if none
	 */
	public function getEula() {
		return $this->packageFile->getEula();
	}

	/**
	 * @return string - content of the README if exists, empty string if none
	 */
	public function getReadme() {
		return $this->packageFile->getReadme();
	}

	/**
	 * @return string - base64 encoding of the logo, empty string if none
	 */
	public function getLogo() {
		return $this->packageFile->getLogo();
	}

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
	public function getPrerequisites() {
		$prequisites = $this->packageFile->getPrerequisites();
		if ('' == (string)$prequisites) {
			$prequisites = "<dependencies>\n</dependencies>";
		}
		return $prequisites;
	}

	/**
	 * @return string - Zend_Form ini definition
	 * @see http://framework.zend.com/manual/en/zend.form.forms.html
	 */
	public function getRequiredParams() {
		return $this->packageFile->getRequiredParams();
	}

	/**
	 * @return boolean
	 */
	public function hasRequiredParams() {
		$requiredParams = $this->packageFile->getRequiredParams();
		return (isset($requiredParams['elements']) && count($requiredParams['elements']) > 0);
	}
}

