<?php
namespace Prerequisites\Validate;

use ZendServer\Exception,
	Zend\Validator\ValidatorInterface,
	ZendServer\Collection as zendServerCollection;
use ZendServer\Log\Log;

class Collection extends zendServerCollection implements ValidatorInterface {
	
	/**
	 * @var array
	 */
	private $messages = array();
	
	/**
	 * @param array $items of Zend_Validate_Interface
	 */
	public function __construct($items = array()) {
		if (is_array($items)) {
			foreach($items as $key => $validator) {
				/// normalize key values to lowercase
				$this->offsetSet(strtolower($key), $validator);
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		$valid = true;
		if (is_array($value)) {
			/// normalize key values to lowercase
			$value = array_change_key_case($value, CASE_LOWER);
			foreach ($this as $key => $validator) { /* @var $validator Zend_Validate_Interface */
				if (! isset($value[$key])) {
					$value[$key] = null;
				}
				if ($validator->isValid($value[$key])) {
					$valid &= true;
				} else {
					$valid = false;
				}

				$messages = $validator->getMessages();
				if (0 < count($messages)) {
					if (!isset($this->messages[$key])) {
						$this->messages[$key] = array();
					}
					$this->messages[$key] += $messages;
				}
			}
			return (boolean)$valid;
		} else {
			throw new Exception(
						_t('Collection validator expects an associative array'),
						Exception::ASSERT);
		}
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::getMessages()
	 */
	public function getMessages() {
		return $this->messages;
	}
	
	/* (non-PHPdoc)
	 * @see Zwas_Collection::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		if ($value instanceof ValidatorInterface) {
			$this->items[$offset] = $value;
		}
	}
}
