<?php
namespace Prerequisites\Validator\Extension;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\ExtensionContainer;

class Min extends AbstractValidator {
	const NOT_MIN  = 'notMin';
	const VALID  = 'validExtensionMin';
    
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
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("'%%name%%' version is %%version%%");
		$this->abstractOptions['messageTemplates'][self::NOT_MIN] = _t("'%%name%%' version should be at least %%version%% (is %%value%%)");
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
		
		if (version_compare($this->version, $compareValue, '<=')) {
			$this->error(self::VALID);
			return true;
		}

		$this->error(self::NOT_MIN);
		return false;
	}
}