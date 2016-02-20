<?php
namespace ZendServer\Configuration\Ui\Directives;

use ZendServer\Exception,
	Application\Module;

class Container {
	
	/**
	 * @var string
	 */
	private $name;
	
	/**
	 * @var string
	 */
	private $section;
	
	/**
	 * @var string
	 */
	private $key;
	
	/**
	 * @var string
	 */
	private $value;
	
	/**
	 * @var array
	 */
	private $optionsValue;
	
	/**
	 * @var string
	 */
	private $description;
	
	/**
	 * @var string
	 */
	private $type = 'text';
	
	/**
	 * @var array
	 */
	private $validators = array();
	
	public function __construct(array $data) {
		
		$this->populate($data);
		
		if (! isset($data['value'])) {
			$this->setValue(Module::config($this->getSection(), $this->getKey()));
		}
	}
	
	/**
	 * @param string $name
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param array $optionsValue
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setOptionsValue($optionsValue) {
		$this->optionsValue = $optionsValue;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getOptionsValue() {
		return $this->optionsValue;
	}
	
	/**
	 * @param string $type
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @param string $description
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * @param string $section
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setSection($section) {
		$this->section = $section;
		return $this;
	}
	
	/**
	 * @param string $validators
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setValidators($validators) {
		$this->validators = $validators;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getValidators() {
		return $this->validators;
	}
	
	/**
	 * @return string
	 */
	public function getSection() {
		return $this->section;
	}
	
	/**
	 * @param string $key
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * @param string $value
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}
 	
	/**
	 * @param array $data
	 * @return \ZendServer\Configuration\Ui\Directives\Container
	 */
	private function populate(array $data) {
		if (isset($data['name'])) {
			$this->setName($data['name']);
		}
		if (isset($data['key'])) {
			$this->setKey($data['key']);
		}
		if (isset($data['section'])) {
			$this->setSection($data['section']);
		}
		if (isset($data['value'])) {
			$this->setValue($data['value']);
		}
		if (isset($data['validators'])) {
			$this->setValidators($data['validators']);
		}
		if (isset($data['description'])) {
			$this->setDescription($data['description']);
		}
		if (isset($data['type'])) {
			$this->setType($data['type']);
		}
		if (isset($data['optionsValue'])) {
			$this->setOptionsValue($data['optionsValue']);
		}
		
		return $this;
	}
	
}