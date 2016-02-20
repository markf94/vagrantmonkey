<?php
namespace Prerequisites\Validator\Extension;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\ExtensionContainer;

class Exclude extends AbstractValidator {
	const NOT_EXCLUDE  = 'notExclude';
	const VALID  = 'validExtensionExclude';
    
	/**
	 * @var string
	 */
	protected $version;
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @param string $version
	 */
	public function __construct($version) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("'%%name%%' version is not %%version%%");
		$this->abstractOptions['messageTemplates'][self::NOT_EXCLUDE] = _t("'%%name%%' version must not be %%version%%");
		$this->abstractOptions['messageVariables']['name'] = 'name';
		$this->abstractOptions['messageVariables']['version'] = 'version';
		
		$this->version = $version;
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		if (! ($value instanceof ExtensionContainer)) {
			throw new Exception(_t('The supplied value is not an extension element'), Exception::ASSERT);
		}
			
		$compareValue = $value->getVersion();
		$this->setValue($compareValue);
		$this->name = $value->getName();
		
		if (version_compare($this->version, $compareValue, '!=')) {
			$this->error(self::VALID);
			return true;
		}

		$this->error(self::NOT_EXCLUDE);
		return false;
	}
}