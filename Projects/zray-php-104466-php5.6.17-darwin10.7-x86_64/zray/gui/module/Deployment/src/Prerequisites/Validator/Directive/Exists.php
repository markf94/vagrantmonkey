<?php
namespace Prerequisites\Validator\Directive;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\DirectiveContainer;

class Exists extends AbstractValidator {
	const NOT_EXISTS  = 'notExists';
	const VALID  = 'validEDirectiveExists';
    
	/**
	 * @var string
	 */
	protected $name;
	    	
	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = trim($name);
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("Directive '%%name%%' exists");
		$this->abstractOptions['messageTemplates'][self::NOT_EXISTS] = _t("Directive '%%name%%' not exists");
		$this->abstractOptions['messageVariables']['name'] = 'name';
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		if (! ($value instanceof DirectiveContainer)) {
			$this->error(self::NOT_EXISTS);
			return false;
		}
		
		return true;
	}
}