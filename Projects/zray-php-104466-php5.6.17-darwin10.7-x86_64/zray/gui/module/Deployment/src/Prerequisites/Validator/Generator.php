<?php
namespace Prerequisites\Validator;

use ZendServer\Exception,
	ZendServer\Log\Log,
	Prerequisites\Validate,
	Prerequisites\Validate\Collection,
	ZendServer\Set,
	Prerequisites\Validate\Configuration;

class Generator {
	const PHP_ELEMENT 				  = 'php';
	const ZEND_SERVER_ELEMENT 		  = 'zendserver';
	const ZEND_FRAMEWORK_ELEMENT 	  = 'zendframework';
	const ZEND_FRAMEWORK_ELEMENT2 	  = 'zendframework2';
	const PLUGIN_ELEMENT 		      = 'plugin';
	
	const DIRECTIVE_VALIDATOR_ELEMENT = 'directive';
	const COMPONENT_VALIDATOR_ELEMENT = 'zendservercomponent';
	const VERSION_VALIDATOR_ELEMENT	  = 'version';
	const EXTENSION_VALIDATOR_ELEMENT = 'extension';
	const LIBRARY_VALIDATOR_ELEMENT   = 'library';
	
	private $validators = array();
	
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
	private $libraries = array();
	
	/**
	 * @var boolean
	 */
	private $needServerData = false;
	
	/**
	 * @param string $xml
	 * @throws Zwas_Exception
	 * @return array of validators in mix structure
	 */
	private function parseXml($xml, array $sections) {
		if (!$xml) return array(); // empty string protection
		
		try {
			$definition = new \SimpleXMLElement($xml);
		} catch (Exception $e) {
			Log::logException('Invalid XML provided', $e);
			throw new Exception(_t('Unable to create a prerequisites validator'));
		}
		
		$required = $definition->required;

        if (in_array(self::VERSION_VALIDATOR_ELEMENT, $sections)) {
            $this->setPhpVersionValidators($required);
            $this->setZendServerVersionValidators($required);
            $this->setZendFrameworkVersionValidators($required);
            $this->setZendFramework2VersionValidators($required);
            $this->setPluginVersionValidators($required);
        }

        if (in_array(self::COMPONENT_VALIDATOR_ELEMENT, $sections)) {
            $this->setComponentValidators($required);
        }

        if (in_array(self::EXTENSION_VALIDATOR_ELEMENT, $sections)) {
            $this->setExtensionValidators($required);
        }

        if (in_array(self::DIRECTIVE_VALIDATOR_ELEMENT, $sections)) {
            $this->setDirectiveValidators($required);
        }

        if (in_array(self::LIBRARY_VALIDATOR_ELEMENT, $sections)) {
            $this->setLibraryValidators($required);
        }


		return $this->getValidators();
	}
	
	/**
	 * 
	 * @param string $prerequisites
     * @param array $sections an array of prerequisites sections to generate. If null, will generate all sections
	 * @return \Prerequisites\Validate\Configuration
	 */
	public static function getConfiguration($prerequisites, array $sections = null) {
	
		$generator = new self();

        $formalSections = array(
            self::VERSION_VALIDATOR_ELEMENT,
            self::COMPONENT_VALIDATOR_ELEMENT,
            self::EXTENSION_VALIDATOR_ELEMENT,
            self::DIRECTIVE_VALIDATOR_ELEMENT,
            self::LIBRARY_VALIDATOR_ELEMENT);

        if (is_null($sections) || (! is_array($sections))) {
            $sections = $formalSections;
        } else {
            if (! count($sections)) {
                throw new Exception(_t('Must specify sections or be null'), Exception::ASSERT);
            }

            if (0 < count(array_diff($sections, $formalSections))) {
                throw new Exception(_t('Invalid configuration section specified'), Exception::ASSERT);
            }
        }

        $validators = $generator->parseXml($prerequisites, $sections);
        $validationCollections = array();

        if (isset($validators[self::VERSION_VALIDATOR_ELEMENT])) {
            $validationCollections[self::VERSION_VALIDATOR_ELEMENT] = new Collection($validators[self::VERSION_VALIDATOR_ELEMENT]);
        }
        if (isset($validators[self::COMPONENT_VALIDATOR_ELEMENT])) {
            $validationCollections[self::COMPONENT_VALIDATOR_ELEMENT] = new Collection($validators[self::COMPONENT_VALIDATOR_ELEMENT]);
        }
        if (isset($validators[self::EXTENSION_VALIDATOR_ELEMENT])) {
            $validationCollections[self::EXTENSION_VALIDATOR_ELEMENT] = new Collection($validators[self::EXTENSION_VALIDATOR_ELEMENT]);
        }
        if (isset($validators[self::DIRECTIVE_VALIDATOR_ELEMENT])) {
            $validationCollections[self::DIRECTIVE_VALIDATOR_ELEMENT] = new Collection($validators[self::DIRECTIVE_VALIDATOR_ELEMENT]);
        }
        if (isset($validators[self::LIBRARY_VALIDATOR_ELEMENT])) {
            $validationCollections[self::LIBRARY_VALIDATOR_ELEMENT] = new Collection($validators[self::LIBRARY_VALIDATOR_ELEMENT]);
		}
	
		$config = new Configuration($validationCollections);
		$config->setGenerator($generator);
		return $config;
	}
	
	/**
	 * @param string $xml
	 * @throws Zwas_Exception
	 * @return array of validators in mix structure
	 */
	public static function hasPrerequisites($xml) {
		try {
			$definition = new \SimpleXMLElement($xml);
		} catch (\Exception $e) {
			Log::logException('Invalid XML provided', $e);
			return false;
		}
		
		$children = $definition->xpath('//required/*'); /* @var $required SimpleXmlElement */
		if (is_array($children) && (0 < count($children))) {
			return true;
		}
		return false;
	}
	
	public function getValidators() {
		return $this->validators;
	}
	
	/**
	 * @param SimpleXMLElement $definition
	 */
	private function setDirectiveValidators(\SimpleXMLElement $definition) {
		$this->validators[self::DIRECTIVE_VALIDATOR_ELEMENT] = array();
		
		if (! isset($definition->directive)) {
			return;
		}
		
		foreach ($definition->directive as $directive) { /* @var $component SimpleXMLElement */
			if (! isset($directive->name)) {
				continue;
			}
			$name = (string)$directive->name;
			$this->directives[] = $name;
			$validator = new Validate();
			$is_required = true;
			
			if ($this->isDirectiveRequiredOff($directive)) { // if the directive is required to be off, then we don't care whether it exists
				$isRequiredOn = false;
			} else {
				$isRequiredOn = true;
				$validator->addValidator(new \Prerequisites\Validator\Directive\Exists($name), true);
			}
			
			if (isset($directive->equals)) {
				$validator->addValidator(new \Prerequisites\Validator\Directive\Equal(
					(string) $directive->equals, $isRequiredOn));
			} else {
				if (isset($directive->min)) {
					$validator->addValidator(new \Prerequisites\Validator\Directive\Min(
						(string) $directive->min));
				}
				
				if (isset($directive->max)) {
					$validator->addValidator(new \Prerequisites\Validator\Directive\Max(
						(string) $directive->max));
				}
			}
			
			$this->validators[self::DIRECTIVE_VALIDATOR_ELEMENT][$name] = $validator;
		}
	}
	
	private function isDirectiveRequiredOff($directive) {
		return isset($directive->equals) && ((boolean) $directive->equals === false || preg_match('/off/i',  strval($directive->equals)) === 1); // should equal off,0..
	}
	
	/**
	 * @return array
	 */
	public function getDirectives() {
		return $this->directives;
	}

	/**
	 * @return array
	 */
	public function getExtensions() {
		return $this->extensions;
	}

	/**
	 * @return array
	 */
	public function getLibraries() {
		return $this->libraries;
	}

	/**
	 * @return boolean
	 */
	public function needServerData() {
		return $this->needServerData;
	}

	/**
	 * @param SimpleXMLElement $definition
	 */
	private function setExtensionValidators(\SimpleXMLElement $definition) {
		$this->validators[self::EXTENSION_VALIDATOR_ELEMENT] = array();
		 
		if (! isset($definition->extension)) {
			return;
		}
		
		foreach ($definition->extension as $extension) { /* @var $component SimpleXMLElement */
			if (! isset($extension->name)) {
				continue;
			}
			$name = (string)$extension->name;
			$this->extensions[] = $name;
			$validator = new Validate();
			
			if (isset($extension->conflicts)) {
				$validator->addValidator(new \Prerequisites\Validator\Extension\Conflicts($name));
			} else {
				$validator->addValidator(new \Prerequisites\Validator\Extension\Loaded($name), true);
				
				if (isset($extension->equals)) {
					$validator->addValidator(new \Prerequisites\Validator\Extension\Equal(
						(string) $extension->equals));
				} else {
					if (isset($extension->min)) {
						$validator->addValidator(new \Prerequisites\Validator\Extension\Min(
							(string) $extension->min));
					}
					
					if (isset($extension->max)) {
						$validator->addValidator(new \Prerequisites\Validator\Extension\Max(
							(string) $extension->max));
					}
				}
				
				if (isset($extension->exclude)) {
					$validator->addValidator(new \Prerequisites\Validator\Extension\Exclude(
						(string) $extension->exclude));
				}
			}
			
			$this->validators[self::EXTENSION_VALIDATOR_ELEMENT][$name] = $validator;
		}
	}
	
	/**
	 * @param SimpleXMLElement $definition
	 */
	private function setComponentValidators(\SimpleXMLElement $definition) {
		$this->validators[self::COMPONENT_VALIDATOR_ELEMENT] = array();
		
		if (! isset($definition->zendservercomponent)) {
			return;
		}
		
		foreach ($definition->zendservercomponent as $component) { /* @var $component SimpleXMLElement */
			if (! isset($component->name)) {
				continue;
			}
			$name = (string)$component->name;
			$this->extensions[] = $name;
			$validator = new Validate();
			
			if (isset($component->conflicts)) {
				$validator->addValidator(new \Prerequisites\Validator\Component\Conflicts($name));
			} else {
				$validator->addValidator(new \Prerequisites\Validator\Component\Loaded($name), true);
				
				if (isset($component->equals)) {
					$validator->addValidator(new \Prerequisites\Validator\Component\Equal(
						(string) $component->equals));
				} else {
					if (isset($component->min)) {
						$validator->addValidator(new \Prerequisites\Validator\Component\Min(
							(string) $component->min));
					}
					
					if (isset($component->max)) {
						$validator->addValidator(new \Prerequisites\Validator\Component\Max(
							(string) $component->max));
					}
				}
				
				if (isset($component->exclude)) {
					$validator->addValidator(new \Prerequisites\Validator\Component\Exclude(
						(string) $component->exclude));
				}
			}
			
			$this->validators[self::COMPONENT_VALIDATOR_ELEMENT][strtolower($name)] = $validator;
		}
	}
		
	/**
	 * @param \SimpleXMLElement $definition
	 */
	private function setPhpVersionValidators(\SimpleXMLElement $definition) {
		$this->setVersionValidator(self::PHP_ELEMENT, $definition);
	}
	
	/**
	 * @param \SimpleXMLElement $definition
	 */
	private function setZendServerVersionValidators(\SimpleXMLElement $definition) {
		$this->setVersionValidator(self::ZEND_SERVER_ELEMENT, $definition);
	}
	
	/**
	 * @param \SimpleXMLElement $definition
	 */
	private function setPluginVersionValidators(\SimpleXMLElement $definition) {
	    $this->setVersionValidator(self::PLUGIN_ELEMENT, $definition);
	}
	
	/**
	 * @param \SimpleXMLElement $definition
	 */
	private function setZendFrameworkVersionValidators(\SimpleXMLElement $definition) {
		if (isset($definition->zendframework)) {
			$this->setLibraryValidator($definition->zendframework, 'Zend Framework 1');
		}
	}

	/**
	 * @param \SimpleXMLElement $definition
	 */
	private function setZendFramework2VersionValidators(\SimpleXMLElement $definition) {
		if (isset($definition->zendframework2)) {
			$this->setLibraryValidator($definition->zendframework2, 'Zend Framework 2');
		}
	}
	
	/**
	 * @param \SimpleXMLElement $definition
	 */
	private function setLibraryValidators(\SimpleXMLElement $definition) {
		if (isset($definition->library)) {
			foreach ($definition->library as $library) {
				$this->setLibraryValidator($library, (string)$library->name);
			}
		}
	}
	/**
	 * @param \SimpleXMLElement $library
	 * @param string $name
	 */
	private function setLibraryValidator(\SimpleXMLElement $library, $name) {
		$validator = new Validate();
		$this->libraries[] = $name;
		if (isset($library->min) || isset($library->max)) {
			if (isset($library->min)) {
				$validator->addValidator(new \DeploymentLibrary\Prerequisites\Validator\Library\Min((string) $library->min));
			}
			if (isset($library->max)) {
				$validator->addValidator(new \DeploymentLibrary\Prerequisites\Validator\Library\Max((string) $library->max));
			}
		} elseif (isset($library->equals)) {
			$validator->addValidator(new \DeploymentLibrary\Prerequisites\Validator\Library\Equals((string) $library->equals));
		} else {
			$validator->addValidator(new \DeploymentLibrary\Prerequisites\Validator\Library\Deployed($name));
		}
		$this->validators[self::LIBRARY_VALIDATOR_ELEMENT][$name] = $validator;
	}
	
	/**
	 * @param string $type
	 * @param SimpleXMLElement $definition
	 */
	private function setVersionValidator($type, \SimpleXMLElement $definition) {
		$version = $definition->{$type};
		$validator = new Validate();
		
		if (isset($version->equals)) {
			$validator->addValidator(new \Prerequisites\Validator\Version\Equal(
				(string) $version->equals));
		} else {
			if (isset($version->min)) {
				$validator->addValidator(new \Prerequisites\Validator\Version\Min(
					(string) $version->min));
			}
			
			if (isset($version->max)) {
				$validator->addValidator(new \Prerequisites\Validator\Version\Max(
					(string) $version->max));
			}
		}
		
		if (isset($version->exclude)) {
			$validator->addValidator(new \Prerequisites\Validator\Version\Exclude(
				(string) $version->exclude));
		}
		
		$this->validators[self::VERSION_VALIDATOR_ELEMENT][$type] = $validator;
	}
	
	private function __construct() {}
	private function __clone() {}
}