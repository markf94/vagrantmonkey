<?php
namespace Prerequisites\Validate;

use ZendServer\Exception,
	Zend\Validator\ValidatorInterface,
	Prerequisites\Validator\Generator;
use ZendServer\Log\Log;
use ZendServer\Set;
use DevBar\ZRayModule;

class Configuration implements ValidatorInterface {
	/**
	 * @var array
	 */
	private $validators = array();
	
	/**
	 * @var array
	 */
	private $messages = array();
	
	/**
	 * @var Generator
	 */
	private $generator;
	
	/**
	 * @param array $validatorCollections
	 * @throws Exception
	 */
	public function __construct(array $validatorCollections) {
		if (0 < count(array_diff(
			array_keys($validatorCollections),
			array(
				Generator::VERSION_VALIDATOR_ELEMENT,
				Generator::COMPONENT_VALIDATOR_ELEMENT,
				Generator::EXTENSION_VALIDATOR_ELEMENT,
				Generator::DIRECTIVE_VALIDATOR_ELEMENT,
				Generator::LIBRARY_VALIDATOR_ELEMENT,
		)
		))) {
			throw new Exception(
							_t('Configuration validators array is of an incorrect structure'),
							Exception::ASSERT);
		}
		$this->validators = $validatorCollections;
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {/* @var $value \ZendServer\Configuration\Container */

        $validFilter = array();

        $directives = array();
        if (isset($this->validators[Generator::DIRECTIVE_VALIDATOR_ELEMENT])) {
            foreach ($value->getDirectives() as $directive) {
                $directives[strtolower($directive->getName())] = $directive;
            }
            $validFilter[] = $this->executeValidator(
                Generator::DIRECTIVE_VALIDATOR_ELEMENT,
                $directives);
        }

		$allExtensions = array();
        if (isset($this->validators[Generator::EXTENSION_VALIDATOR_ELEMENT])) {
            foreach ($value->getExtensions() as $extension) {
                $allExtensions[$extension->getName()] = $extension;
            }

            $validFilter[] = $this->executeValidator(
                Generator::COMPONENT_VALIDATOR_ELEMENT,
                $allExtensions); // component = extension mark as IS_ZEND_COMPONENT

            $validFilter[] = $this->executeValidator(
                Generator::EXTENSION_VALIDATOR_ELEMENT,
                $allExtensions);
        }

		$libraries = array();
        if (isset($this->validators[Generator::LIBRARY_VALIDATOR_ELEMENT])) {
            if (is_array($value->getLibraries())) {
                $librariesMap = new Set($value->getLibraries(), 'DeploymentLibrary\Container');
            } else {
                $librariesMap = $value->getLibraries();
            }

            foreach($librariesMap as $library) { /* @var $library \DeploymentLibrary\Container */
                $libraries[$library->getLibraryName()] = $library;
            }

            $validFilter[] = $this->executeValidator(
                Generator::LIBRARY_VALIDATOR_ELEMENT,
                $libraries);
        }


        if (isset($this->validators[Generator::VERSION_VALIDATOR_ELEMENT])) {
            $validFilter[] = $this->executeValidator(
                Generator::VERSION_VALIDATOR_ELEMENT,
                array(
                    Generator::PHP_ELEMENT          => 	$value->getPhpVersion(),
                    Generator::ZEND_SERVER_ELEMENT  => 	$value->getZendServerVersion(),
                    Generator::PLUGIN_ELEMENT       => 	ZRayModule::PLUGIN_CURRENT_VERSION,
                ));
        }

		return array_reduce($validFilter, array($this, 'conjunction'), true);
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::getMessages()
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * @return Generator
	 */
	public function getGenerator() {
		return $this->generator;
	}

	/**
	 * @param \Prerequisites\Validator\Generator $generator
	 */
	public function setGenerator($generator) {
		$this->generator = $generator;
	}

	/**
	 * @param string $namespace
	 * @param array $value
	 * @return boolean
	 */
	private function executeValidator($namespace, $value) {
		$validator = $this->validators[$namespace];
		if (is_null($validator)) {
			return true;
		}
		$result = $validator->isValid($value);
		
		$messages = $validator->getMessages();
		if (0 < count($messages)) {
			$this->messages[$namespace] = $validator->getMessages();
		}
		return $result;
	}
	
	/**
	 * Create product of conjunctions for the given v and w, to be used by array_reduce
	 * @see Deployment_Prerequisites_Validate_Configuration::isValid
	 * @param mixed $v
	 * @param mixed $w
	 * @return boolean
	 */
	private static function conjunction($v, $w) {
		$v &= $w;
		return (boolean)$v;
	}
}
