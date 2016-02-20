<?php
namespace ZendServer\Configuration;

use ZendServer\Configuration\Manager,
	ZendServer\Set,
	Zend\Json\Json,
	Configuration\DdMapper;
use ZendServer\Log\Log;
use Application\ConfigAwareInterface;

class Container implements ConfigAwareInterface {
	const OS_TYPE_NIX		= 1;
	const OS_TYPE_WINDOWS	= 2;
	const OS_TYPE_IBMI		= 3;
	const OS_TYPE_MAC		= 4;
	
	/**
	 * build => marketing name
	 */
	private static $windowsOsTable = array(	528 	=> 'Windows NT',
			807 	=> 'Windows NT',
			1057 	=> 'Windows NT',
			1381	=> 'Windows NT',
	
			2195	=> 'Windows 2000',
			2600	=> 'Windows XP',
			3790	=> 'Windows XP or Windows Server 2003',
			6000	=> 'Windows Vista',
			6001	=> 'Windows Vista or Windows Server 2008',
			6002	=> 'Windows Vista or Windows Server 2008');
	
	/**
	 * @var array
	 */
	private $directives = array();
	/**
	 * @var array
	 */
	private $extensions = array();
	/**
	 * @var array
	 */
	private $components = array();
	/**
	 * @var string
	 */
	private $phpVersion	= '';
	/**
	 * @var string
	 */
	private $zendFramework1Version = '';
	/**
	 * @var string
	 */
	private $zendFramework2Version = '';
	/**
	 * @var string
	 */
	private $zendServerVersion = '';
	
	/**
	 * @var integer
	 */
	private $osType = 0;
	
	/**
	 * @var string
	 */
	private $osName = '';
	
	/**
	 * @var \Configuration\MapperDirectives
	 */
	private $directivesMapper = null;
	
	/**
	 * @var \Configuration\MapperExtensions
	 */
	private $extensionsMapper = null;
	
	/**
	 * @var \Configuration\DdMapper
	 */
	private $ddMapper = null;
	
	/**
	 * @var \DeploymentLibrary\Mapper
	 */
	private $librariesMapper;
	
	/**
	 * @var Manager
	 */
	private $manager;
	
	/**
	 * @var Config
	 */
	private $config;

    /**
     * @var Set
     */
    private $libraries;

    /**
     * @param array $directives
     * @param array $extensions
     * @param array $libraries
     * @param bool $retrieveServerData
     */
    public function createConfigurationSnapshot(array $directives = null, array $extensions = null, array $libraries = null, $retrieveServerData = false) {
		$this->directives = $this->getAllDirectives($directives);
	
		$this->extensions = $this->getAllExtension($extensions);
		
		$this->components = $this->extensions; // sub array of the extensions
		
		$manager = $this->getManager();
		$this->osName = $manager->getOsName();
		$this->osType = $manager->getOsType();
		
		$this->phpVersion = PHP_VERSION;
		$this->zendServerVersion = isset($this->config['version']) ? $this->config['version'] : '';

        $this->libraries = $this->getAllLibraries($libraries);
	}
	
	/**
	 * @return \Configuration\DdMapper
	 */
	public function getDdMapper() {
		return $this->ddMapper;
	}
	
	/**
	 * @param \Configuration\DdMapper $ddMapper
	 */
	public function setDdMapper($ddMapper) {
		$this->ddMapper = $ddMapper;
	}
	
	/**
	 * @return \DeploymentLibrary\Mapper
	 */
	public function getLibrariesMapper() {
		return $this->librariesMapper;
	}

	/**
	 * @param \DeploymentLibrary\Mapper $librariesMapper
	 */
	public function setLibrariesMapper($librariesMapper) {
		$this->librariesMapper = $librariesMapper;
	}

	/**
	 * @return \Configuration\MapperDirectives
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}
	
	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
	}
	
	/**
	 * @param \Configuration\DirectiveContainer $directives
	 */
	public function setDirectives($directives) {
		$this->directives = $directives;
	}
	
	/**
	 * @param \Configuration\DirectiveContainer $directives
	 */
	public function setExtensions($extensions) {
		$this->extensions = $extensions;
		$this->components = $extensions;
	}
	
	/**
	 * @return \Configuration\MapperExtensions
	 */
	public function getExtensionsMapper() {
		return $this->extensionsMapper;
	}
	
	
	/**
	 * @return Manager
	 */
	public function getManager() {
		return $this->manager;
	}


	/**
	 * @param \ZendServer\Configuration\Config $config
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 * @param \Configuration\MapperExtensions $extensionsMapper
	 */
	public function setExtensionsMapper($extensionsMapper) {
		$this->extensionsMapper = $extensionsMapper;
	}
	
	/**
	 * @param \ZendServer\Configuration\Manager $manager
	 */
	public function setManager($manager) {
		$this->manager = $manager;
	}

	/* 
	 * @return string
	 */
	public function getzendFramework1Version() {
		return $this->zendFramework1Version;
	}

	/*
	 * @return string
	*/
	public function getzendFramework2Version() {
		return $this->zendFramework2Version;
	}
	
	/*
	 * @return string
	*/
	public function getPhpVersion() {
		return $this->phpVersion;
	}

	/*
	 * (non-PHPdoc)
	 * @see Configuration_Container_Interface::getDirectives()
	 */
	public function getDirectives() {
		return $this->directives;
	}

	/* (non-PHPdoc)
	 * @see Configuration_Container_Interface::getZendServerVersion()
	 */
	public function getZendServerVersion() {
		return $this->zendServerVersion;
	}

	/* (non-PHPdoc)
	 * @see Configuration_Container_Interface::getComponents()
	 */
	public function getComponents() {
		return $this->components;
	}

	/* (non-PHPdoc)
	 * @see Configuration_Container_Interface::getExtensions()
	 */
	public function getExtensions() {
		return $this->extensions;
	}
	
	/**
	 * @return array
	 */
	public function getLibraries() {
        if (is_null($this->libraries)) {
            $this->libraries = $this->getLibrariesMapper()->getLibrariesByIds();
        }
		return $this->libraries;
	}
	
	/**
	 * @param array $libraryNames
	 * @return \ZendServer\Set
	 */
	private function getAllLibraries(array $libraryNames = null) {
		$libraries = $this->getLibrariesMapper()->getLibrariesByIds();
		$librariesMap = array();
		foreach($libraries as $library) { /* @var $library \DeploymentLibrary\Container */
            if (is_null($libraryNames) || in_array($library->getLibraryName(), $libraryNames)) {
                $librariesMap[] = $library->toArray();
			}
		}
		return new Set($librariesMap, 'DeploymentLibrary\Container');
	}
	
	/**
	 * @param array $directives
	 * @return \ZendServer\Set
	 */
	private function getAllDirectives(array $directives = null) {
		if (is_null($directives)) {
			$directives = $this->getDirectivesMapper()->selectAllDirectives();
		} else {
			$directives = $this->getDirectivesMapper()->selectSpecificDirectives($directives);
		}
	
		$filteredDirectives = array();
		foreach ($directives as $idx=>$directive) {  /* @var $directive \Configuration\DirectiveContainer */
			if ($directive->isVisible()) {
				$filteredDirectives[$idx] = $directive->toArray();
			}
		}
		$directivesSet = new Set($filteredDirectives, null); // as we already have containers
		$directivesSet->setHydrateClass('Configuration\DirectiveContainer');
		Log::debug("Directives count: {$directivesSet->count()}");
	
		$directivesSet = $this->getDdMapper()->addDirectivesData($directivesSet);
	
		return $directivesSet;
	}
	
	/**
	 * @param array $extensions
	 * @return \ZendServer\Set
	 */
	private function getAllExtension(array $extensions = null) {
		if (is_null($extensions)) {
			$extensions = $this->getExtensionsMapper()->selectAllExtensions();
		} else {
			$extensions = $this->getExtensionsMapper()->selectExtensions($extensions);
		}
		
	
		Log::debug("Extensions count: {$extensions->count()}");
		$extensions = $this->convertSetToExtensionsArray($extensions);
		$extensions = $this->addDummyExtensions($extensions, 'all');
	
		$extensions = $this->addExtensionsData($extensions);
	
		return $extensions;
	}
	
	/**
	 * this method adds the dummy extensions to our list of named extensions
	 * @param array $extensionsByName
	 */
	private function addDummyExtensions(array $extensionsByName, $extType) {
		$dummyExtensions = $this->getExtensionsMapper()->selectExtensions($this->getDdMapper()->getDummyExtensions());
		$dummyExtensions = $this->convertSetToExtensionsArray($dummyExtensions);
	
		$extensionsByName = array_merge($dummyExtensions + $extensionsByName);
	
		return $extensionsByName;
	}
	
	private function addExtensionsErrors($extensions) {
		$extensionMessages = $this->getExtensionsMessages($extensions);
		$directiveMessages = $this->getDirectivesMessages($extensions); // we would like also to display in the extension row, it's directive related messages
	
		$this->addErrorsToContainer($extensions, $extensionMessages);
		$this->addErrorsToContainer($extensions, $directiveMessages);
	
		return $extensions;
	}
	
	private function addExtensionsData($extensions) {
		return $this->getDdMapper()->addExtensionsData($extensions);
	}
	
	/**
	 * this method takes a Set, and returns an associative array based on the extension name
	 * @param \ZendServer\Set $extensions
	 */
	private function convertSetToExtensionsArray(\ZendServer\Set $extensions) {
		$extensionsByName = array();
		foreach ($extensions as $extension) { /* @var $extension \Configuration\ExtensionContainer */
			$extensionsByName[$extension->getName()] = $extension; // names as keys
		}
	
		return $extensionsByName;
	}
	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::getAwareNamespace()
	 */
	public function getAwareNamespace() {
		return array('package');
	}

}