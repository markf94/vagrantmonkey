<?php

namespace Configuration;
use ZendServer\Log\Log;
use ZendServer\Exception;
use ZendServer\Validator\ErrorReporting;
use ZendServer\Validator\NullValidator;

use Zend\Validator\Between;

use Zend\Validator\ValidatorChain;

use ZendServer\Validator\Integer;

use ZendServer\Validator\Boolean;

use Zend\Validator\InArray;

use Zend\Validator\Regex;


use Zend\Json\Json,
ZendServer\Set;
use ZendServer\Validator\DirectiveStringValidator;
use ZendServer\Validator\PhpExpression;
use Zend\Validator\EmailAddress;
use Zend\Validator\Uri;
use Application\Validators\DefaultServer;
use Zend\InputFilter\Input;
use ZendServer\Validator\HostWithPort;
use Zend\Validator\GreaterThan;
use Zend\Validator\LessThan;
use ZendServer\Validator\FloatValidator;

class DdMapper {
	const DIRECTIVE_TYPE_STRING = 1;
    const DIRECTIVE_TYPE_BOOLEAN = 2;
    const DIRECTIVE_TYPE_INTEGER = 4;
    const DIRECTIVE_TYPE_FLOAT = 9;
    /**
	 * 
	 * @var stdClass
	 */
	public $data;
	
	/**
	 * 
	 * @param string $zeMapFileContent
	 */
	public function __construct($zeMapFileContent) {
		$this->data = Json::decode($zeMapFileContent, Json::TYPE_ARRAY);
	}
	
	/**
	 * Generate a map that indicates the zemExtension flag of the extensions
	 * @return array[boolean]
	 */
	public function getComponentsDisplayMap() {
		return array_map(function($extension){
			return isset($extension['zemExtension']) ? $extension['zemExtension'] : false;
		}, $this->data);
	}
	
	/**
	 * @param \Configuration\DirectiveContainer $directive
	 * @return array
	 * @throws \ZendServer\Exception
	 */
	public function addDirectiveData (\Configuration\DirectiveContainer $directive) {
		$directive = $directive->toArray();
		
		if (! isset($directive['NAME'])) {
			throw new Exception('Provided directive array should contain the \'NAME\' field');
		}
	
		$directive['DAEMON'] ? $componentName = $directive['DAEMON'] : $componentName = $directive['EXTENSION']; // we check also component, as some directives appear both under extension and daemon
		$directiveData = $this->getDirectiveRow($directive['NAME'], $componentName);
		return array_merge($directive, (array) $directiveData);
	}
	
	/**
	 * 
	 * @param array|Set $directives
	 * @throws \ZendServer\Exception
	 * @return \ZendServer\Set
	 */
	public function addDirectivesData($directives) {
		$detailedDirectives = array();
		foreach ($directives as $directive) {
			if (!$directive instanceOf \Configuration\DirectiveContainer) {
				throw new Exception('Provided directives array should contain the DirectiveContainer objects');
			}
			try {
				$directiveData = $this->addDirectiveData($directive);
				$directiveContext = $directive->getContext();
				$directiveContextName = $directive->getContextName();
			} catch (Exception $ex) {
				Log::notice("Directive {$directive->getName()} is missing data and will not be displayed");
				continue;
			}
			$detailedDirectives[$directive->getName()] = $directiveData;
		}
		
		return new Set($detailedDirectives, '\Configuration\DirectiveContainer');
	}

	/**
	 *
	 * @param array|Set $directives
	 * @throws \ZendServer\Exception
	 * @return \ZendServer\Set
	 */
	public function addDirectivesMetadataOnly($directives, $ext) {
		$detailedDirectives = array();
		foreach ($directives as $directive) {
			$detailedDirectives[$directive] = $this->getDirectiveRow($directive, $ext);
			$detailedDirectives[$directive]['NAME'] = $directive;
		}

		return new Set($detailedDirectives, '\Configuration\DirectiveContainer');
	}
	
	
	/**
	 * 
	 * @param array $extension
	 * @throws \ZendServer\Exception
	 * @return array
	 */
	public function addExtensionData(\Configuration\ExtensionContainer $extension) {
		$extension = $extension->toArray();
		
		if (! isset($extension['NAME'])) {
			throw new Exception('Provided extension array should contain the \'NAME\' field');
		}

		$extName = $extension['NAME'];
		if (!isset($this->data[$extName])) {
			Log::notice("could not find extension {$extName} in Zend Server metadata - user defined?");
			return $extension;
		}

		$dataArray = (array) $this->data[$extName];
		unset($dataArray['directives']);
		return array_merge($extension, $dataArray);		
	}
	
	/**
	 *
	 * @param array $daemon
	 * @throws \ZendServer\Exception
	 * @return array
	 */
	public function addDaemonData(\Configuration\DaemonContainer $daemon) {
		$daemon = $daemon->toArray();
		if (! isset($daemon['name'])) {
			throw new Exception('Provided extension array should contain the \'name\' field');
		}
	
		$daemonName = $daemon['name'];
		if (!isset($this->data[$daemonName])) {
			Log::notice("could not find extension {$daemonName} in Zend Server metadata - user defined?");
			return $daemon;
		}
	
		$dataArray = (array) $this->data[$daemonName];
		unset($dataArray['directives']);
		return array_merge($daemon, $dataArray);
	}
	
	/**
	 *
	 * @param array|Set $daemons
	 * @throws \ZendServer\Exception
	 * @return \ZendServer\Set
	 */
	public function addDaemonsData ($daemons) {
		$detailedDaemons = array();
		foreach ($daemons as $idx => $daemon) {
			if (!$daemon instanceOf \Configuration\DaemonContainer) {
				throw new Exception('Provided daemons array should contain the DaemonContainer objects');
			}
			$detailedDaemons[] = $this->addDaemonData($daemon);
		}
	
		return new Set($detailedDaemons, '\Configuration\DaemonContainer');
	}
	
	/**
	 * 
	 * @param array $extensions
	 * @throws \ZendServer\Exception
	 * @return \ZendServer\Set
	 */
	public function addExtensionsData (array $extensions) {
		$detailedExtensions = array();
		foreach ($extensions as $idx => $extension) {
			if (!$extension instanceOf \Configuration\ExtensionContainer) {
				throw new Exception('Provided extensions array should contain the ExtensionContainer objects');
			}
			$detailedExtensions[$idx] = $this->addExtensionData($extension);
		}
		
		return new Set($detailedExtensions, '\Configuration\ExtensionContainer');
	}
	
	/**
	 * 
	 * @param \Configuration\ExtensionContainer or \Configuration\DaemonContainer $extension
	 * @param string $filter
	 * @return bool
	 */
	public function matchExtensionStrings($extension, $filter) {
		if (! isset($this->data[$extension->getName()])) {
			Log::notice("could not find extension {$extension->getName()} in Zend Server metadata - user defined?");
			return false;
		}

		if (! $filter) {
			return true;
		}
		
		// match extension name/shortDescription/logDescription
		if(	strstr(strtolower($extension->getName()), $filter) ||
			strstr(strtolower($extension->getShortDescription()), $filter) ||
			strstr(strtolower($extension->getLongDescription()), $filter)) {
			return true;
		}
		
		return false;
	}

	/**
	 *
	 * @param \Configuration\ExtensionContainer or \Configuration\DaemonContainer $extension
	 * @param string $filter
	 * @return bool
	 */
	public function matchExtensionDirectives($extension, $filter) {
		if (! isset($this->data[$extension->getName()])) {
			Log::notice("could not find extension {$extension->getName()} in Zend Server metadata - user defined?");
			return false;
		}
	
		// match the directive/directiveDescription
		$extData = $this->data[$extension->getName()];			
		$matchedDirectives = array();
		foreach ($extData['directives'] as $name => $directive) {
			if (	strstr(strtolower($directive["shortDescription"]), $filter) ||
					strstr(strtolower($name), $filter)) {
				$matchedDirectives[] = $name;
			}
		}
	
		return $matchedDirectives;
	}
		
	/**
	 * 
	 * @param \Configuration\DirectiveContainer $directive
	 * @param string $filter
	 */
	public function matchDirective(\Configuration\DirectiveContainer $directive, $filter) {
		if (! $filter) {
			return true;
		}
		if (strstr(strtolower($directive->getName()), $filter)) {
			return true;
		}
		if (isset($this->data[$directive->getExtension()])) {
			$directivesArray = $this->data[$directive->getExtension()]['directives'];
			if (isset($directivesArray[$directive->getName()])) {
				if (strstr(strtolower($directivesArray[$directive->getName()]['shortDescription']), $filter)) {
						return true;
					
				}
			}
		}
		return false;
	}
	
	public function getDummyExtensions() {
		$dummyExtensions = array();
		
		foreach ($this->data as $extName => $data) {
			if (isset($data['dummy']) && $data['dummy']) {
				$dummyExtensions[] = $extName;
			}			
		}
		
		return $dummyExtensions;
	}
		
	/**
	 * @param string $directiveName
	 * @return \Zend\Validator\Validator
	 */
	public function directiveValidator($directiveName) {
		$directiveRow = $this->getDirectiveRow($directiveName);
		
		$input = new Input();
		$validator = new ValidatorChain();
		if (!$directiveRow) {
			Log::debug("The directive $directiveName wasn't found in the map file, so we got an unrecognized directive");
			$validator->addValidator(new NullValidator());
			$input->setAllowEmpty(true);
			return $input->setValidatorChain($validator);
		}
		
		$validation = $directiveRow['validation'];
		
        /// special cases
        if ($directiveName == 'error_reporting') {
            $validator->addValidator(new ErrorReporting());
        } elseif (isset($validation['html']) && $validation['html']) {
			$validator->addValidator(new Regex('#^[[:print:]]*$#')); /// allow any graphical characters
		} elseif ($validation['regex']) {
			$validator->addValidator(new Regex($validation['regex']));
		} elseif ($validation['listValues']) {
			$validatorInArray = new InArray();
			$validatorInArray->setHaystack(array_keys((array) unserialize($validation['listValues']))); // the keys represent the values
			$validatorInArray->setStrict(InArray::COMPARE_NOT_STRICT_AND_PREVENT_STR_TO_INT_VULNERABILITY); // as serialize() and unserialize() causes array keys such as ["1"] to become [1], we loose the strict check
			$validator->addValidator($validatorInArray);
		} elseif (! is_null($validation['minValue']) || (! is_null($validation['maxValue']))) {
			// if it's between validator we should add aslo the numeric validator, since mixed values like abc5 returns isValid() true between 0 and 1000
			$validator->addValidator(new Integer());
			
			$options = array('inclusive' => true);
			if (! is_null($validation['minValue'])) {
				$options['min'] = intval($validation['minValue']);
				$validator->addValidator(new GreaterThan($options));
			}
			
			if (! is_null($validation['maxValue'])) {
				$options['max'] = intval($validation['maxValue']);
				$validator->addValidator(new LessThan($options));
			}
		} elseif ($validation['email']) {
			$validator->addValidator(new EmailAddress());
		} elseif ($validation['uri']) {
			$validator->addValidator(new Uri(array('allowRelative' => false, 'allowAbsolute' => true)));
		} elseif ($validation['host']) {
			$validator->addValidator(new HostWithPort());
		} elseif ($validation['defaultServer']) {
			$validator->addValidator(new DefaultServer());
		} elseif ($directiveRow['type'] == self::DIRECTIVE_TYPE_INTEGER) { // integer
			$validator->addValidator(new Integer());
		} elseif ($directiveRow['type'] == self::DIRECTIVE_TYPE_FLOAT) { // integer
			$validator->addValidator(new FloatValidator());
		} elseif ($directiveRow['type'] == self::DIRECTIVE_TYPE_BOOLEAN) { // boolean
			$validator->addValidator(new Boolean());
		} else {
			$validator->addValidator(new DirectiveStringValidator()); // string validator
		}
		
		if ((! isset($validation['allowempty'])) || $validation['allowempty']) {
			/// Allow an empty string as default behavior or if explicitly defined as true
			$input->setRequired(false);
			$input->setAllowEmpty(true);
		}
		$input->setValidatorChain($validator);
		
		Log::debug("Validator for {$directiveName} is " . get_class($validator));
		return $input;
	}
	

	/**
	 * @param string $directiveName
	 * @param string $componentName
	 * @return array
	 */
	private function getDirectiveRow($directiveName, $componentName='') { // @todo - temporary workaround till configurationValidateDirectives() will receive extension	
		foreach($this->data as $component => $data) {
			if (isset($data['directives'][$directiveName]) && ($componentName === '' || $componentName === $component)) {
				return $data['directives'][$directiveName];
			}
		}
		return array();
	}
}
