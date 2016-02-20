<?php
namespace Prerequisites\Validator\Component;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\ExtensionContainer;

class Loaded extends AbstractValidator {
	const NOT_LOADED  = 'notLoaded';
	const VALID  = 'validComponentLoaded';
    
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("The '%%name%%' component is loaded");
		$this->abstractOptions['messageTemplates'][self::NOT_LOADED] = _t("the '%%name%%' component is not loaded");
		$this->abstractOptions['messageVariables']['name'] = 'name';
		
		$this->name = $name;
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		if (! ($value instanceof ExtensionContainer)) {
			$this->error(self::NOT_LOADED);
			return false;
		}
		
		if (! $value->isLoaded()) {
			$this->error(self::NOT_LOADED);
			return false;
		}
		
		$this->error(self::VALID);
		return true;
	}
}