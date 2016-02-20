<?php
namespace Prerequisites\Validator\Version;

use ZendServer\Exception,
Zend\Validator\AbstractValidator;

class Min extends AbstractValidator {
	const NOT_MIN  = 'notMin';
	const VALID  = 'validVersionMin';
    
	/**
	 * @var string
	 */
	protected $version;
	
	/**
	 * @param string $version
	 */
	public function __construct($version) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("Version should be at least %%version%% (is %%value%%)");
		$this->abstractOptions['messageTemplates'][self::NOT_MIN] = _t("Version should be at least %%version%% (is %%value%%)");
		$this->abstractOptions['messageVariables']['version'] = 'version';
		
		$this->version = $version;
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		
		if (version_compare($this->version, $value, '<=')) {
			$this->error(self::VALID);
			return true;
		}

		$this->error(self::NOT_MIN);
		return false;
	}
}