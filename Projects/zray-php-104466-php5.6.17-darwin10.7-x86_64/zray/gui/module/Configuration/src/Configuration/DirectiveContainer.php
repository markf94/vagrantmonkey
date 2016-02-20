<?php
namespace Configuration;
use Zend\Json\Json,
	ZendServer\Exception as ZSException;

class DirectiveContainer {
	const TYPE_STRING 		= 'string';
	const TYPE_BOOLEAN 		= 'boolean';
	const TYPE_SELECT 		= 'select';
	const TYPE_INT 			= 'int';
	const TYPE_SHORTHAND 	= 'shorthand';
	const TYPE_INT_BOOLEAN	= 'int_boolean';
	
	/**
	 * @var string
	 */
	protected $previousValue;
	
	/**
	 * @var array
	 */
	protected $directive;
	
	/**
	 * @param array $directive
	 */
	public function __construct(array $directive) {
		$this->directive = $directive;
		if (isset($directive['previousValue'])) {
			$this->setPreviousValue($directive['previousValue']);
		}
	}
	
	public function isExists() {
		return (!empty($this->directive));
	}
	
	public function toArray() {
		return $this->directive;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return isset($this->directive['NAME']) ? $this->directive['NAME'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getExtension() {		
		return $this->directive['EXTENSION'];
	}
	
	/**
	 * @return string
	 */
	public function getDaemon() {
		return $this->directive['DAEMON'];
	}

	/**
	 * @return string
	 */
	public function getFileValue() {
		return isset($this->directive['DISK_VALUE']) ? $this->directive['DISK_VALUE'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getDefaultValue() {
		return $this->directive['MEMORY_VALUE'];
	}
	
	/**
	 * @return string
	 */
	public function getSection() {
		return isset($this->directive['section']) ? $this->directive['section'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return isset($this->directive['shortDescription']) ? $this->directive['shortDescription'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getUnits() {
		return (isset($this->directive['units']) && $this->directive['units']) ? $this->directive['units'] : '';
	}

	/**
	 * (visible property is Dervied from Xml's visibility attribute)
	 * @return boolean
	 */
	public function isVisible() {
		return !isset($this->directive['visible']) || $this->directive['visible'] == '1'; // unrecognized directives will not have this flag
	}
			
	/**
	 *  @return array/''
	 */
	public function getlistValues() {
		if (isset($this->directive['validation']['listValues'])) {
			if ($this->directive['validation']['listValues']){
				return unserialize($this->directive['validation']['listValues']);
			}
		}
		return '';
	}
	
	/**
	 * @return string
	 */
	public function getType() {
		return $this->typeTranslate();
	}
	
	/**
	 * @return string
	 */
	private function typeTranslate() {
		if (isset($this->directive['type'])) {
			switch ($this->directive['type']) {
				case 1:
					$type = self::TYPE_STRING;
					break;
				case 2:
					$type = self::TYPE_BOOLEAN;
					break;
				case 3:
					$type = self::TYPE_SELECT;
					break;
				case 4:
					$type = self::TYPE_INT;
					break;
				case 5:
					$type = self::TYPE_SHORTHAND;
					break;
				case 8:
					$type = self::TYPE_INT_BOOLEAN;
					break;
			
				default:
					$type = self::TYPE_STRING;
					break;
			}
		} else {
			$type = self::TYPE_STRING;
		}
		return $type;
	}
	/**
	 * @return string $previousValue
	 */
	public function getPreviousValue() {
		if (is_null($this->previousValue)) {
			return '';
		}
		return $this->previousValue;
	}
	
	/**
	 * @return boolean
	 */
	public function hasPreviousValue() {
		return (! is_null($this->previousValue));
	}

	/**
	 * @param string $previousValue
	 * @return \Configuration\DirectiveContainer
	 */
	public function setPreviousValue($previousValue) {
		$this->previousValue = $previousValue;
		return $this;
	}

	public function getContext() {
		if ($this->getExtension()) {
			return 'Extension';
		} elseif ($this->getDaemon()) {
			return 'Daemon';
		} else {
			throw new ZSException("The directive {$this->getName()} does not have a context");
		}
	}
	
	public function getContextName() {
		if ($this->getExtension()) {
			return $this->getExtension();
		} elseif ($this->getDaemon()) {
			return $this->getDaemon();
		} else {
			throw new ZSException("The directive {$this->getName()} does not have a context name");
		}
	}
	
	/**
	 * @param string $value
	 * @return \Configuration\DirectiveContainer
	 */
	public function setFileValue($value) {
		$this->directive['DISK_VALUE'] = $value;
		return $this;
	}
		
}