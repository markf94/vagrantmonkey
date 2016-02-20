<?php
namespace ZendServer\Configuration\Directives;

use ZendServer\Exception;

class Container {
	
	const TYPE_STRING 		= 1;
	const TYPE_BOOLEAN 		= 2;
	const TYPE_SELECT 		= 3;
	const TYPE_INT 			= 4;
	const TYPE_SHORTHAND 	= 5;
	const TYPE_INT_BOOLEAN	= 8;
	
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $fileValue;
	/**
	 * @var string
	 */
	private $memoryValue;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var string
	 */
	private $section;
	/**
	 * @var string
	 */
	private $extension;
	/**
	 * @var integer
	 */
	private $type;
	/**
	 * @var integer
	 */
	private $minValue;
	/**
	 * @var integer
	 */
	private $maxValue;
	/**
	 * @var string
	 */
	private $regex;
	/**
	 * @var string
	 */
	private $listValues;
	/**
	 * @var boolean
	 */
	private $disabled;
	/**
	 * @var string
	 */
	private $iniFile;
	/**
	 * @var integer
	 */
	private $length;
	/**
	 * @var string
	 */
	private $units;
	/**
	 * A flag to mark if the directie's value awaits a restart to change
	 * saves the need to translate and compare the value and the global value each time
	 * @var boolean
	 */
	private $awaitsRestart = false;	
	/**
	 * A flag to mark if this is a Zwas Component
	 * @var boolean
	 */
	private $belongsToZendServerExtension = false;
	
	/**
	 * Directive element constructor
	 *
	 * @param array $data is array with the following 
	 * 						possible keys - name, value, iniFile, disabled, minValue, type
	 * 										maxValue, description, extension, section, regex
	 * @throws Zwas_Exception
	 */
	public function __construct(array $data) {
		
		if (! array_key_exists('name', $data)) {
			throw new Exception('An ' . __CLASS__ . ' must have a name defined', Exception::ASSERT);
		}
		
		$this->populate($data);
	}
	
	/**
	 * Get the directive's value currently in the ini file
	 * @return string
	 */
	public function getFileValue() {
		return $this->fileValue;
	}
	
	/**
	 * Get the directive's value currently loaded in memory
	 * @return string
	 */
	public function getMemoryValue() {
		return $this->memoryValue;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @return integer
	 */
	public function getMinValue() {
		return $this->minValue;
	}
	
	/**
	 * @return integer
	 */
	public function getMaxValue() {
		return $this->maxValue;
	}
	
	/**
	 * @return string
	 */
	public function getSection() {
		return $this->section;
	}
	
	/**
	 * @return string
	 */
	public function getExtension() {
		return $this->extension;
	}
	
	/**
	 * @return string
	 */
	public function getListValues() {
		return $this->listValues;
	}
	
	/**
	 * @return string
	 */
	public function getRegex() {
		return $this->regex;
	}
	
	/**
	 * @return boolean
	 */
	public function isDisabled() {
		return (boolean)$this->disabled;
	}
	
	/**
	 * @return integer
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @return integer
	 */
	public function getLength() {
		return $this->length;
	}
	
	/**
	 * @return string
	 */
	public function getIniFile() {
		return $this->iniFile;
	}
	
	/**
	 * @return string
	 */
	public function getUnits() {
		return $this->units;
	}
	
	/**
	 * Does this directive await a webserver restart before its loaded value will change?
	 * @return boolean
	 */
	public function awaitsRestart() {
		return (boolean)$this->awaitsRestart;
	}	
	
	/**
	 * @return boolean
	 */
	public function belongsToZendServerExtension() {
		return (boolean)$this->belongsToZendServerExtension;
	}
	
	/**
	 * Set the directive's value currently written in the ini file
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setFileValue($value) {
		$this->fileValue = (string)$value;
		return $this;
	}
	
	/**
	 * Set the directive's value currently loaded in memory 
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setMemoryValue($value) {
		$this->memoryValue = (string)$value;
		return $this;
	}
	
	/**
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setName($name) {
		$this->name = (string)$name;
		return $this;
	}
	
	/**
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setIniFile($iniFile) {
		$this->iniFile = (string)$iniFile;
		return $this;
	}
	
	/**
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setDescription($description) {
		$this->description = (string)$description;
		return $this;
	}
	
	/**
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setSection($section) {
		$this->section = (string)$section;
		return $this;
	}
	
	/**
	 * @param string $value
	 * @return Directives_Element
	 */
	public function setExtension($extension) {
		$this->extension = (string)$extension;
		return $this;
	}
	
	/**
	 * @param boolean $value
	 * @return Directives_Element
	 */
	public function setDisabled($disabled) {
		$this->disabled = (boolean)$disabled;
		return $this;
	}
	
	/**
	 * @param integer $value
	 * @return Directives_Element
	 */
	public function setType($type) {
		$this->type = (integer)$type;
		return $this;
	}

	/**
	 * @return string
	 * @return Directives_Element
	 */
	public function setUnits($units) {
		$this->units = (string)$units;
		return $this;
	}
	
	/**
	 * @param boolean $value
	 * @return Directives_Element
	 */
	public function setAwaitsRestart($value) {
		$this->awaitsRestart = (boolean)$value;
		return $this;
	}	
	
	/**
	 * @param boolean $value
	 * @return Directives_Element
	 */
	public function setbelongsToZendServerExtension($value) {
		$this->belongsToZendServerExtension = (boolean)$value;
		return $this;
	}
		
 	/** Populate the object with data from an array
	 * Available array keys: name, value, iniFile, disabled, minValue, type,
	 * 	maxValue, description, extension, section, regex
	 * 
	 * @param array $data
	 * @return Directives_Element
	 */
	private function populate(array $data) {
		if (isset($data['name'])) {
			$this->setName($data['name']);
		}
		if (isset($data['value'])) {
			$this->setFileValue($data['value']);
		}
		if (isset($data['awaitsRestart'])) {
			$this->setAwaitsRestart($data['awaitsRestart']);
		}
		if (isset($data['iniFile'])) {
			$this->setIniFile($data['iniFile']);
		}
		
		if (isset($data['section'])) {
			$this->setSection($data['section']);
			
		} // the directive_element must have  at least defined section
		elseif (!isset($data['extension'])) {
			$this->setSection('New / Unrecognized');
		}
		
		if (isset($data['extension'])) {
			$this->setExtension($data['extension']);
		}
		
		if (isset($data['type'])) {
			$this->setType($data['type']);
		} else {
			// if the type wasn't defined (unknown directive) - it's string by default
			$this->setType(self::TYPE_STRING);
		}
		
		if (isset($data['minValue'])) {
			$this->minValue = $data['minValue'];
		}
		if (isset($data['maxValue'])) {
			$this->maxValue = $data['maxValue'];
		}
		if (isset($data['description'])) {
			$this->setDescription($data['description']);
		}
		if (isset($data['regex'])) {
			$this->regex = (string)$data['regex'];
		}
		if (isset($data['disabled'])) {
			$this->setDisabled($data['disabled']);
		}
		if (isset($data['listValues'])) {
			$this->listValues = unserialize($data['listValues']);
		}
		if (isset($data['globalValue'])) {
			$this->setMemoryValue($data['globalValue']);
		}
		if (isset($data['units'])) {
			$this->setUnits($data['units']);
		}
		if (isset($data['zenithExtension'])) {
			$this->setbelongsToZendServerExtension($data['zenithExtension']);
		}
		return $this;
	}
	
}