<?php

require_once dirname ( __FILE__ ) . '/PackageFile/Interface.php';
require_once dirname ( __FILE__ ) . '/Exception.php';
require_once dirname ( __FILE__ ) . '/Logger.php';
require_once dirname ( __FILE__ ) . '/PackageMetaData.php';
use ZendServer\Log\Log;


class ZendDeployment_PackageFile implements ZendDeployment_PackageFile_Interface {
	
	const DEPLOYMENT_DESCRIPTOR_FILENAME = "deployment.xml";
	const DEPLOYMENT_PLUGIN_DESCRIPTOR_FILENAME = "deployment.json";
	const DEPLOYMENT_MON_RULES_FILENAME = 'scripts/monitor_rules.xml';
	const DEPLOYMENT_PAGECACHE_RULES_FILENAME = 'scripts/pagecache_rules.xml';
	
	private $_packagePath;
	private $_zipHandle;
	private $_appName;
	private $_appVersion;
	private $_appEula;
	private $_appReadme;
	private $_appLogo;
	private $_appPrerequisites;
	private $_appRequiredParams;
	private $_peristentId;
	private $_packageDescriptor;
	private $_monitorRules;
	private $_pageCacheRules;
	private $_type;
	private $_plugin_type;
	private $_plugin_display_name;
	
	private $_dbHandler;
	
	
	public function __construct($packageFilepath = NULL) {
		
		$this->_peristentId = self::ID_NONE;
		
		if ($packageFilepath) {
			$this->loadFile ( $packageFilepath );
		}
	}
	
	public function __destruct() {
	
	}
	
	/**
	 * Load an existing package file
	 * @throws ZendDeployment_Exception
	 */
	public function loadFile($packageFilepath) {
		
		ZDBG1 ( "Creating package on file $packageFilepath" );
		
		$this->_packagePath = $packageFilepath;
		
		if (!is_readable($packageFilepath)) {
			throw new ZendDeployment_Exception ( "Unable to read package $packageFilepath.", ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
		}
		
		// open the archive
		$this->_zipHandle = new ZipArchive ();
		$res = $this->_zipHandle->open ( $packageFilepath, ZIPARCHIVE::CHECKCONS );
		if ($res !== TRUE) {
			throw new ZendDeployment_Exception ( "Package file is an invalid archive", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
		}
		
		//look for descriptor file and monitor and page cache rules file
		$descInZip = "";
		$jsonDescInZip = "";
		$monRulesInZip = "";
		$pcRulesInZip = "";
		
		$potentialRootDirectory = false;
		$rootDirectory = false;
		
		for($i = 0; $i < $this->_zipHandle->numFiles; $i ++) {
			$name = $this->_zipHandle->getNameIndex ( $i );
			if($i == 0 && strlen($name) > 1 && substr($name,-1) == '/') {
				$potentialRootDirectory = $name;
			}
			if ( $name == ZendDeployment_PackageFile::DEPLOYMENT_DESCRIPTOR_FILENAME ) {
				$descInZip = $name;
				continue;
			} elseif ( $name == ZendDeployment_PackageFile::DEPLOYMENT_PLUGIN_DESCRIPTOR_FILENAME ) {
				$jsonDescInZip = $name;
				continue;
			} elseif ( $potentialRootDirectory && ($name == $potentialRootDirectory.ZendDeployment_PackageFile::DEPLOYMENT_DESCRIPTOR_FILENAME || $name == $potentialRootDirectory.ZendDeployment_PackageFile::DEPLOYMENT_PLUGIN_DESCRIPTOR_FILENAME ) ) {
				$rootDirectory = str_replace('/', '', $potentialRootDirectory);
				break;
			} 
			
			$firstSep = strpos ( $name, "/" );
			if ($firstSep === false) {
				continue;
			}
			
			if ($name == ZendDeployment_PackageFile::DEPLOYMENT_MON_RULES_FILENAME) {
				$monRulesInZip = $name;
			} else if ($name == ZendDeployment_PackageFile::DEPLOYMENT_PAGECACHE_RULES_FILENAME) {
				$pcRulesInZip = $name;
			}
		}
		
		if($rootDirectory){
			
			for($i = 0; $i < $this->_zipHandle->numFiles; $i ++) {
				$name = $this->_zipHandle->getNameIndex ( $i );
				$newName = str_replace($rootDirectory . '/', '', $name);
				if ($newName) {
				    $this->_zipHandle->renameName($name, $newName);
				}
			}
			$this->_zipHandle->deleteName($rootDirectory . '/');
			$this->_zipHandle->close();
			return $this->loadFile($packageFilepath);
		}
		
		if (! $descInZip && ! $jsonDescInZip) {
			throw new ZendDeployment_Exception ( "Unable to locate package descriptor in the package at $packageFilepath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
		}
		ZDBG1 ( "Package descriptor found at $descInZip $jsonDescInZip" );
		
		// extract package to a tmp dir
		$tmpPackageDir = tempnam ( ZendDeployment_Manager::getZendTempDir (), basename ( $packageFilepath ) );
		unlink ( $tmpPackageDir );
		if (! mkdir ( $tmpPackageDir )) {
			throw new ZendDeployment_Exception ( "Unable to create temp dir to extract package ($tmpPackageDir)", ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
		}
		ZDBG1 ( "Package will be extracted to $tmpPackageDir" );
		if ($descInZip) {
		    if (! $descEntry = $this->_zipHandle->extractTo ( $tmpPackageDir, array ($descInZip ) )) {
		        throw new ZendDeployment_Exception ( "Unable to extract package descriptor to $tmpPackageDir. " . $this->_zipHandle->getStatusString (), ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
		    }
		    $extractedDescPath = $tmpPackageDir . DIRECTORY_SEPARATOR . $descInZip;
		}
		if ($jsonDescInZip) {
		    if (! $descEntry = $this->_zipHandle->extractTo ( $tmpPackageDir, array ($jsonDescInZip ) )) {
		         throw new ZendDeployment_Exception ( "Unable to extract package descriptor to $tmpPackageDir. " . $this->_zipHandle->getStatusString (), ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
		    }
		    $extractedDescPath = $tmpPackageDir . DIRECTORY_SEPARATOR . $jsonDescInZip;
		}
				
		$this->_packageDescriptor = file_get_contents ( $extractedDescPath );
		
		if ($monRulesInZip) {
			ZDBG1 ( "Monitor rules found at $monRulesInZip" );
			if ($monRulesInZip && !$descEntry = $this->_zipHandle->extractTo ( $tmpPackageDir, array ($monRulesInZip ) )) {
				throw new ZendDeployment_Exception ( "Unable to extract monitor rules to $tmpPackageDir. " . $this->_zipHandle->getStatusString (), ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
			}
			$this->_monitorRules = @file_get_contents($tmpPackageDir . DIRECTORY_SEPARATOR . ZendDeployment_PackageFile::DEPLOYMENT_MON_RULES_FILENAME );
		}
		
		if ($pcRulesInZip) {
			ZDBG1 ( "Page Cache rules found at $pcRulesInZip" );
			if ($pcRulesInZip && !$descEntry = $this->_zipHandle->extractTo ( $tmpPackageDir, array ($pcRulesInZip ) )) {
				throw new ZendDeployment_Exception ( "Unable to extract page cache rules to $tmpPackageDir. " . $this->_zipHandle->getStatusString (), ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR );
			}
			$this->_pageCacheRules = @file_get_contents($tmpPackageDir . DIRECTORY_SEPARATOR . ZendDeployment_PackageFile::DEPLOYMENT_PAGECACHE_RULES_FILENAME );
		}
		
		//validate the descriptor file and extract its info
		if ($jsonDescInZip) {
		    $this->readDescriptorJsonFile( $extractedDescPath, $tmpPackageDir );
		} else {
		    $this->readDescriptorFile ( $extractedDescPath, $tmpPackageDir );
		}
		
		ZDBG2 ( "Deleting package leftovers at $tmpPackageDir" );
		$this->rmdir ( $tmpPackageDir );
	
	}
	
	private function rmDir($path) {
		
		$files = glob ( $path . "/*" );
		
		foreach ( $files as $file ) {
			if (is_dir ( $file )) {
				$this->rmDir ( $file );
			} else {
				unlink ( $file );
			}
		}
		@rmdir ( $path );
	}
	
	/**
	 * 
	 * Set the package id in the database
	 * @param integer $id
	 */
	public function setPersistentId($id) {
		$this->_peristentId = $id;
	}
	
	public function getPersistentId() {
		return $this->_peristentId;
	}
	
	public function getPackagePath() {
		return $this->_packagePath;
	}
	
	/**
	 * Load a package file by its contents
	 * @throws ZendDeployment_Exception
	 */
	public function loadContents($contents) {
		
		ZDBG1 ( "Loading package contents" );
		$tmpPackagePath = tempnam ( ZendDeployment_Manager::getZendTempDir (), "zendPkg" );
		file_put_contents ( $tmpPackagePath, $contents );
		$this->loadFile ( $tmpPackagePath );
		ZDBG2 ( "Loaded package path - " . $tmpPackagePath );
	}
	
	private function checkContainsEntry($name) {
		for($i = 0; $i < $this->_zipHandle->numFiles; $i ++) {
			if (strpos ( $this->_zipHandle->getNameIndex ( $i ), $name ) === 0) {
				return true;
			}
		}
		
		return false;
	}
	
	/*
	 * Parse the descriptor file and fill in the object details
	 */
	private function readDescriptorFile($descPath, $packageDir) {
		
		ZDBG1 ( "Parsing package descriptor at " . $descPath );
		
		$dom = new DOMDocument ();
		if ($dom->Load ( $descPath ) === FALSE) {
			throw new ZendDeployment_Exception ( "Unable to parse package descriptor file at $descPath", ZendDeployment_Exception_Interface::INVALID_PACKAGE_DESCRIPTOR );
		}
		
		// validate the xml against the schema file
		$schemaFile = get_cfg_var ( "zend.install_dir" ) . "/share/deployment.xsd";
		if (! $dom->schemaValidate ( $schemaFile )) {
			throw new ZendDeployment_Exception ( "Package descriptor at $descPath failed schema vaildation. " . libxml_get_last_error ()->message, ZendDeployment_Exception_Interface::INVALID_PACKAGE_DESCRIPTOR );
		}
		
		$xml = @simplexml_import_dom ( $dom );
		
		if (!isset($xml->type)){
			$this->_type = self::TYPE_APPLICATION;
		} else {
			switch ((string) $xml->type) {
				case "library":
					$this->_type = self::TYPE_LIBRARY;
					break;
				case "application":
				default:
					$this->_type = self::TYPE_APPLICATION;
					break;
			}
		}

		if ($this->_type == self::TYPE_APPLICATION) {
			ZDBG1("Package contains an APPLICATION");
		} else {
			ZDBG1("Package contains an LIBRARY");
		}
		
		$this->_appName = ( string ) $xml->name;
		if (! $this->_appName) {
			throw new ZendDeployment_Exception ( "Unable to locate application name in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
		}
		$this->_appVersion = ( string ) $xml->version->release;
		if (! $this->_appVersion) {
			throw new ZendDeployment_Exception ( "Unable to locate application version in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
		}
		
		$this->_appEula = NULL;
		if ($xml->eula) {
			$eulaPath = ( string ) $xml->eula;
			if ($eulaPath) {
				$this->_zipHandle->extractTo ( $packageDir, array ($eulaPath ) );
				$eulaPath = $packageDir . "/$eulaPath";
				if (! file_exists ( $eulaPath )) {
					throw new ZendDeployment_Exception ( "Unable to read application eula file in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
				}
				$this->_appEula = file_get_contents ( $eulaPath );
			}
		}
		
		if ($xml->scriptsdir) {
			$scriptsDir = ( string ) $xml->scriptsdir;
			ZDBG2 ( "Checking existance of deployment scripts in '$scriptsDir'" );
			if (! $this->checkContainsEntry ( $scriptsDir . "/" )) {
				throw new ZendDeployment_Exception ( "Unable to find scripts dir '$scriptsDir' in the package", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
			}
		}
		
		if ($xml->appdir) {
			$appdir = ( string ) $xml->appdir;
			if ($appdir) {
				ZDBG2 ( "Checking existance of deployment app dir in '$appdir'" );
				if (! $this->checkContainsEntry ( $appdir . "/" )) {
					throw new ZendDeployment_Exception ( "Unable to find application dir '$appdir' in the package", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
				}
			}
		}
		
		if ($xml->docroot) {
			$docroot = ( string ) $xml->docroot;
			if (!$docroot) {
				$docroot = $appdir;
			}
			if ($docroot) {
				ZDBG2 ( "Checking existance of deployment docroot dir in '$docroot'" );
				$docrootClean = substr($docroot, -1) == '/' ? $docroot : "$docroot/";
				if (! $this->checkContainsEntry ( $docrootClean )) {
					throw new ZendDeployment_Exception ( "Unable to find application dir '$docroot' in the package", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
				}
			}
		}
		
		$this->_appReadme = NULL;
		$appDir = (string) $xml->appdir;
		$libDir = (string) $xml->libdir;
		
		if (! empty($appDir) || ! empty($libDir)) {
			$checkDir = (! empty($appDir)) ? $appDir : $libDir; 
			for($i = 0; $i < $this->_zipHandle->numFiles; $i ++) {
				$name = $this->_zipHandle->getNameIndex ( $i );
				
				$appDirPos = strpos($name, $checkDir . '/');
				if ($appDirPos !== false && $appDirPos == 0 && in_array(str_replace($checkDir . '/', '', strtolower($name)), array('readme', 'readme.txt', 'readme.md'))) {
					$this->_zipHandle->extractTo ( $packageDir, array ($name) );
					$this->_appReadme = file_get_contents ( $packageDir . "/$name" );
					break;
				}
			}
		}
		
		$iconPath = ( string ) $xml->icon;
		if ($iconPath) {
			$this->_zipHandle->extractTo ( $packageDir, array ($iconPath ) );
			$iconPath = $packageDir . "/$iconPath";
			if (! file_exists ( $iconPath )) {
				throw new ZendDeployment_Exception ( "Unable to read application icon file sepecified in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
			}
			$this->_appLogo = file_get_contents ( $iconPath );
		}
		
		// take the "<dependencies>" XML part and save it
		if ($xml->dependencies) {
			$this->_appPrerequisites = $xml->dependencies->asXML ();
		} else {
			$this->_appPrerequisites = "";
		}
		
		// convert the "<parameters>" XML part to an ini input for zend form
		$this->_appRequiredParams = ZendDeployment_PackageMetaData::createPackageParams ( $xml->parameters );
	
	}
	
	/*
	 * Parse the descriptor file and fill in the object details
	 */
	private function readDescriptorJsonFile($descPath, $packageDir) {
	
	    ZDBG1 ( "Parsing package descriptor at " . $descPath );
	    
	    $string = file_get_contents($descPath);
	    if (!($json = json_decode($string))) {
	        throw new ZendDeployment_Exception ( "Unable to parse package descriptor file at $descPath", ZendDeployment_Exception_Interface::INVALID_PACKAGE_DESCRIPTOR );
	    }
	
	    $this->_type = self::TYPE_PLUGIN;
	    
	    if (property_exists($json, 'type')) {
	        $this->_plugin_type = (array) $json->type;
	    }
	    
	    ZDBG1("Plugin package of type: " . implode(',', $this->_plugin_type));

	    if (property_exists($json, 'displayName')) {
	        $this->_plugin_display_name = $json->displayName;
	    }
	    
	    ZDBG1("Plugin display name is: " . $this->_plugin_display_name);
	
	    $this->_appName = ( string ) $json->name;
	    if (! $this->_appName) {
	        throw new ZendDeployment_Exception ( "Unable to locate plugin name in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
	    }
	    $this->_appVersion = ( string ) $json->version;
	    if (! $this->_appVersion) {
	        throw new ZendDeployment_Exception ( "Unable to locate plugin version in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
	    }
	
	    $this->_appEula = NULL;
	    if (property_exists($json, 'eula')) {
	        $eulaPath = ( string ) $json->eula;
	        if ($eulaPath) {
	            $this->_zipHandle->extractTo ( $packageDir, array ($eulaPath ) );
	            $eulaPath = $packageDir . "/$eulaPath";
	            if (! file_exists ( $eulaPath )) {
	                throw new ZendDeployment_Exception ( "Unable to read plugin eula file in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
	            }
	            $this->_appEula = file_get_contents ( $eulaPath );
	        }
	    }
	
	    //$this->_appReadme = NULL;
	    $this->_appReadme = NULL;
	    if (property_exists($json, 'readme')) {
	        $readmePath = ( string ) $json->readme;
	        if ($readmePath) {
	            $this->_zipHandle->extractTo ( $packageDir, array ($readmePath ) );
	            $readmePath = $packageDir . "/$readmePath";
	            if (! file_exists ( $readmePath )) {
	                throw new ZendDeployment_Exception ( "Unable to read plugin reame file in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
	            }
	            $this->_appReadme = file_get_contents ( $readmePath );
	           //array('readme', 'readme.txt', 'readme.md'))) {
				
			}
		}
	   
		$this->_appLogo = null;
		if (property_exists($json, 'logo')) {
    	    $iconPath = ( string ) $json->logo;
    	    if ($iconPath) {
    	        $this->_zipHandle->extractTo ( $packageDir, array ($iconPath ) );
    	        $iconPath = $packageDir . "/$iconPath";
    	        if (! file_exists ( $iconPath )) {
    	            throw new ZendDeployment_Exception ( "Unable to read plugin icon file sepecified in package descriptor of $this->_packagePath", ZendDeployment_Exception_Interface::INVALID_PACKAGE );
    	        }
    	        $this->_appLogo = file_get_contents ( $iconPath );
    	    }
		}
	
		$this->_plugin_display_name = "";
		if (property_exists($json, 'display_name')) {
		    $this->_plugin_display_name = ( string ) $json->display_name;
		}
		
	    // take the "<dependencies>" json part, convert to XML, save it
	    if (property_exists($json, 'dependencies')) {
	        $dependencies = (array)$json->dependencies;
	        $dependenciesXML = new SimpleXMLElement('<dependencies/>');
	        $dependenciesXML->addChild('required');
	        $xml = self::arrayToXml($dependencies, $dependenciesXML->required); // should be XML object
	        $this->_appPrerequisites = $dependenciesXML->asXML();
	    } else {
	        $this->_appPrerequisites = "";
	    }
	
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::isValid()
	 */
	public static function isValid($packageFilepath) {
		try {
			$pkg = new ZendDeployment_PackageFile ();
			$pkg->loadFile ( $packageFilepath );
			return true;
		} catch ( ZendDeployment_Exception $ex ) {
			if ($ex->getCode () != ZendDeployment_Exception_Interface::FILE_SYSTEM_ERROR) {
				return false;
			} else {
				throw $ex;
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getEula()
	 */
	public function getEula() {
		return $this->_appEula;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getEula()
	 */
	public function getReadme() {
		return $this->_appReadme;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getLogo()
	 */
	public function getLogo() {
		return $this->_appLogo;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getPrerequisites()
	 */
	public function getPrerequisites() {
		return $this->_appPrerequisites;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getRequiredParams()
	 */
	public function getRequiredParams() {
		return $this->_appRequiredParams;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getName()
	 */
	public function getName() {
		return $this->_appName;
	}
	
	public function setName($name) {
		$this->_appName = $name;
	}
	
	public function setVersion($appVersion) {
		$this->_appVersion = $appVersion;
	}
	
	public function setLogo($logo) {
		$this->_appLogo = $logo;
	}
	
	/* (non-PHPdoc)
	 * @see ZendDeployment_PackageFile::getVersion()
	 */
	public function getVersion() {
		return $this->_appVersion;
	}
	
	public function getPackageDescriptor() {
		return $this->_packageDescriptor;
	}
	
	public function getMonitorRules() {
		return $this->_monitorRules;
	}
	
	/**
	 * @return the $_pageCacheRules
	 */
	public function getPageCacheRules() {
		return $this->_pageCacheRules;
	}
	
	/**
	 * @param field_type $_pageCacheRules
	 */
	public function setPageCacheRules($_pageCacheRules) {
		$this->_pageCacheRules = $_pageCacheRules;
	}
	
	public function getType() {
		return $this->_type;
	}
	
	public function setType($type) {
		$this->_type = $type;
	}
	
	public function getPluginType() {
	    return $this->_plugin_type;
	}
	
	public function setPluginType($type) {
	    $this->_plugin_type = $type;
	}
	
	public function getDisplayName() {
	    return $this->_plugin_display_name;
	}
	
	public function setDisplayName($name) {
	    $this->_plugin_display_name = $name;
	}
	
	static public function arrayToXml(array $arr, SimpleXMLElement &$xml) {
	   foreach ($arr as $k => $v) {
            if (is_object($v)) { // stdClass
                $v = (array)$v;
            }
             
            if (in_array($k, array("extension", "directive", "library", "zendservercomponent"))) {
                foreach ($v as $ext) {
                    $extension = $xml->addChild($k);
                    $ext = (array)$ext;
                    foreach ($ext as $extProperty => $extPropertyValue) {
                        $extension->addChild($extProperty, $extPropertyValue);
                    }
                }
                
            } else {
                $element = $xml->addChild($k);
                if (is_array($v)) {
                    foreach ($v as $key => $value) {
                        $element->addChild($key, $value);
                    }
                } else {
                    $xml->addChild($k, $v);
                }
            }
        }
        return $xml;
	}
	
}
