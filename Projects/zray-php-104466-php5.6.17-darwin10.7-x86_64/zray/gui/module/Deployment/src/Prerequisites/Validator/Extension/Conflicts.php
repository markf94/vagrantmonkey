<?php
namespace Prerequisites\Validator\Extension;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\ExtensionContainer;

class Conflicts extends AbstractValidator {
	const NOT_CONFLICTS  = 'notConflicts';
	const VALID  = 'valid';
    
	/**
	 * @var string
	 */
	protected $name;
	
	public function __construct($name) {
		$this->name = $name;
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("'%%name%%' is not installed");
		$this->abstractOptions['messageTemplates'][self::NOT_CONFLICTS] = _t("'%%name%%' must not be installed");
		$this->abstractOptions['messageVariables']['name'] = 'name';
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		
		if (is_null($value)) {
			$this->error(self::VALID);
			return true;
		}
		
		if (! ($value instanceof ExtensionContainer)) {
			throw new Exception(_t('The supplied value is not an extension element'), Exception::ASSERT);
		}
		
		$this->name = $value->getName();

		$this->error(self::NOT_CONFLICTS);
		return false;
	}
}