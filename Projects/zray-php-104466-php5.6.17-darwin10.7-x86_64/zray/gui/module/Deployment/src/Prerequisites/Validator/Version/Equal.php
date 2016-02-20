<?php
namespace Prerequisites\Validator\Version;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator;

class Equal extends AbstractValidator {
	const NOT_EQUAL  = 'notEqual';
	const VALID  = 'validVersionEqual';
    
	/**
	 * @var string
	 */
	protected $version;
	
	/**
	 * @param string $version
	 */
	public function __construct($version) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("Version should be %%version%% (is %%value%%)");
		$this->abstractOptions['messageTemplates'][self::NOT_EQUAL] = _t("Version should be %%version%% (is %%value%%)");
		$this->abstractOptions['messageVariables']['version'] = 'version';
		
		$this->version = $version;
	}
	
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		
		if (version_compare($this->version, $value, '==')) {
			$this->error(self::VALID);
			return true;
		}

		$this->error(self::NOT_EQUAL);
		return false;
	}
}