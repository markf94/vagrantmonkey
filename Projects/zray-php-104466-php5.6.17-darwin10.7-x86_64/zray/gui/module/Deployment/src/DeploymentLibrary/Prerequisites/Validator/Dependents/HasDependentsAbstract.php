<?php

namespace DeploymentLibrary\Prerequisites\Validator\Dependents;

use Prerequisites\Validator\Generator;
use ZendServer\Log\Log;
use ZendServer\Set;
use ZendServer\Configuration\Container;
use Deployment\Model as appsMapper;
use DeploymentLibrary\Mapper as libsMapper;
use Zend\Filter\File\LowerCase;

/**
 * Mediator class for checking dependents (prerequisites) on a particular library
 * @author yonni
 * 
 */
abstract class HasDependentsAbstract {
	
	/**
	 * @var Container
	 */
	private $configurationContainer;
	
	/**
	 * @var appsMapper
	 */
	private $deploymentMapper;
	
	/**
	 * @var libsMapper
	 */
	private $librariesMapper;
	
	/**
	 * @var pluginsMapper
	 */
	private $pluginsMapper;
	
	/**
	 * 
	 * Will removing this object break any dependencies?
	 * Does it have any objects in the system which have $value as a prerequisite?
	 * 
	 * @param mixed $value
	 * @return boolean
	 */
	abstract public function breaksDependents($value, &$brokenPlugin);
	
	/**
	 * @return Container
	 */
	public function getConfigurationContainer() {
		return $this->configurationContainer;
	}

	/**
	 * @return the $deploymentMapper
	 */
	public function getDeploymentMapper() {
		return $this->deploymentMapper;
	}
	
	/**
	 * @return the $pluginsMapper
	 */
	public function getPluginsMapper() {
	    return $this->pluginsMapper;
	}

	/**
	 * @param \Plugins\Mapper $pluginsMapper
	 */
	public function setPluginsMapper($pluginsMapper) {
	    $this->pluginsMapper = $pluginsMapper;
	}
	
	/**
	 * @return the $librariesMapper
	 */
	public function getLibrariesMapper() {
		return $this->librariesMapper;
	}

	/**
	 * @param \Deployment\Model $deploymentMapper
	 */
	public function setDeploymentMapper($deploymentMapper) {
		$this->deploymentMapper = $deploymentMapper;
	}

	/**
	 * @param \DeploymentLibrary\Mapper $librariesMapper
	 */
	public function setLibrariesMapper($librariesMapper) {
		$this->librariesMapper = $librariesMapper;
	}

	/**
	 * @param \ZendServer\Configuration\Container $configurationContainer
	 */
	public function setConfigurationContainer($configurationContainer) {
		$this->configurationContainer = $configurationContainer;
	}

	/**
	 * @param array $libraries
	 * @return boolean
	 */
	protected function validateLibrariesDependents(array $libraries, $libName = '', &$brokenPlugin) {
		$configurationContainer = $this->getConfigurationContainer();
        /// pass empty arrays to avoid focus on libraries only
		$configurationContainer->createConfigurationSnapshot(array(), array(), array_map(function($item){
            return $item['libraryName'];
        },$libraries));

        $appRequisites = $this->getDeploymentMapper()->getAllApplicationsPrerequisited(array(Generator::LIBRARY_VALIDATOR_ELEMENT));
        $libRequisites = $this->getLibrariesMapper()->getAllLibrariesPrerequisites(array(Generator::LIBRARY_VALIDATOR_ELEMENT));
        
        // validate the plugins prerequisires
        try {
            $pluginsRequisites = $this->getPluginsMapper()->getAllPluginsPrerequisited();
        } catch (\Exception $e) {
            $pluginsRequisites = array();
            // ignore corrupted plugin packages
        }
        

        foreach ($pluginsRequisites as $plugin => $configuration) {
            if (! $configuration->isValid($configurationContainer)) {
            // check for the specific library error, not all libraries in the system
            	$messages = $configuration->getMessages();
            	// in the messages the libraries names are in lowercase
            	$libName = strtolower($libName);
            	if (isset($messages['library']) && isset($messages['library'][$libName])) {
	            	$libValidatorKey = key($messages['library'][$libName]);
	            	if (stripos($libValidatorKey, 'not') !== false) {
                        $brokenPlugin = $plugin;
	            		return true;
	            	}
            	}
            }
        }
        
        $prerequisites = array_merge($appRequisites, $libRequisites);
        
        foreach ($prerequisites as $configuration) { /* @var $configuration \Prerequisites\Validate\Configuration */
            if (! $configuration->isValid($configurationContainer)) {
            	// check for the specific library error, not all libraries in the system
            	$messages = $configuration->getMessages();
            	// in the messages the libraries names are in lowercase
            	$libName = strtolower($libName);
            	if (isset($messages['library']) && isset($messages['library'][$libName])) {
	            	$libValidatorKey = key($messages['library'][$libName]);
	            	if (stripos($libValidatorKey, 'not') !== false) {
	            		return true;
	            	}
            	}
			}
		}
		
		return false;
	}

}

