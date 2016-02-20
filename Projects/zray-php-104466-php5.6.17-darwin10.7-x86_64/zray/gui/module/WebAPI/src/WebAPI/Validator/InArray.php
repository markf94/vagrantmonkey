<?php

namespace WebAPI\Validator;

use Zend\Validator\InArray as baseInArray;

class InArray extends baseInArray {
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::NOT_IN_ARRAY => 'Parameter must be one of %haystack%',
	);
	
	/**
	 * @var array Error message template variables
	 */
	protected $messageVariables = array(
			'haystack' => array('options' => 'haystack'),
	);
	
	public function __construct($options = null) {
		$this->setHaystack($options['haystack']);
	}
}

