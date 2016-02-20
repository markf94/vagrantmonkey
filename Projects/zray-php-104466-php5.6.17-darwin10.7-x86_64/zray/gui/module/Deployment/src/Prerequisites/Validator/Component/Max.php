<?php
namespace Prerequisites\Validator\Component;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator,
	Configuration\ExtensionContainer;

class Max extends AbstractValidator {
	const NOT_MAX  = 'notMax';
	const VALID  = 'validComponentMax';
    
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
		$this->abstractOptions['messageTemplates'][self::NOT_MAX] = _t("'%%name%%' version should be at most %%version%% (is %%value%%)");
		$this->abstractOptions['messageVariables']['name'] = 'name';
		$this->abstractOptions['messageVariables']['version'] = 'version';
		
		$this->version = $version;
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Validate_Interface::isValid()
	 */
	public function isValid($value) {
		if (! ($value instanceof ExtensionContainer)) {
			throw new Exception(_t('The value supplied is not a component element'), Exception::ASSERT);
		}
		
		$compareValue = $value->getVersion();
		$this->setValue($compareValue);
		$this->name = $value->getName();
		
		if (version_compare($this->version, $compareValue, '>=')) {
			$this->error(self::VALID);
			return true;
		}

		$this->error(self::NOT_MAX);
		return false;
	}
}