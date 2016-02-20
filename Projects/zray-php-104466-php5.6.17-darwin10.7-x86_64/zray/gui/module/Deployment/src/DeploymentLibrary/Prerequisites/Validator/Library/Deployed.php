<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator;
use ZendServer\Log\Log;
use DeploymentLibrary\Container;

class Deployed extends AbstractValidator {
	const NOT_VALID  = 'notValid';
	const VALID  = 'valid';
    
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @param string $version
	 */
	public function __construct($name) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("is deployed");
		$this->abstractOptions['messageTemplates'][self::NOT_VALID] = _t("%s must be deployed", array($name));
		$this->name = $name;
	}
	
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		if ($value instanceof Container) {
			$this->error(self::VALID);
			return true;
		}

		$this->error(self::NOT_VALID);
		return false;
	}
}