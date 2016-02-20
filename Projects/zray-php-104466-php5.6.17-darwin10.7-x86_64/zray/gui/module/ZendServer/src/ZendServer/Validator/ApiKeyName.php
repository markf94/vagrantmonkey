<?php

namespace ZendServer\Validator;

class ApiKeyName extends AbstractZendServerValidator {
	
	const INVALID_NAME			= 'invalidName';
	const INVALID_CHARACTERS	= 'invalidCharacters';
	const INVALID_WHITESPACE	= 'invalidWhitspaces';

	protected $abstractOptions = array(
		'messageTemplates' => array(
			self::INVALID_NAME			=> "'%value%' is not a valid API name",
			self::INVALID_CHARACTERS	=> 'API names may not contain ()<>,;:\"/[]?={}%&|',
			self::INVALID_WHITESPACE	=> 'API names may not start or end with a white space',
		),
		'translatorDisabled' => true,
		'valueObscured' => false,
		'messageVariables' => array()
	);	

	
	/**
	 * @param string $name
	 * @return IpRange
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	/* (non-PHPdoc)
	 * 
	 * Based on the HTTP RFC use what the RFC defines as "token" characters, along with space and '@'.
	 * This basically means all ASCII characters between 0x20 and 0x7e (including), 
	 * except for the following characters:
	 * ( ) < > , ; : \ " / [ ] ? = { }
	 * Also, it cannot begin or end with space.
	 * We also, do not allow the following characters which complicate the name handling: %,&,|
	 * 
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		$this->_messages = array();
		
		if (strlen($value) !== strcspn($value, '()<>,;:\"/[]?={}%&|')) {
			$this->error(self::INVALID_CHARACTERS);
			return false;
		}
		
		if (0 === preg_match('/^[\x20-\x7e]+$/', $value)) {
			$this->error(self::INVALID_NAME);
			return false;
		}
		
		if (1 === preg_match('/^(?:\s+.+)|(?:.+\s+)$/m', $value)) {
			$this->error(self::INVALID_WHITESPACE);
			return false;
		}
		
		return true;
	}
	
	protected function error($messageKey, $value = null) {
		return _t(parent::error($messageKey, $value));
	}
}