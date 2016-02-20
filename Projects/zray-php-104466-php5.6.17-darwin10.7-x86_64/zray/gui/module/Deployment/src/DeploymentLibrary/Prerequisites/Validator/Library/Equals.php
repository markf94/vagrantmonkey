<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use ZendServer\Exception,
	Zend\Validator\AbstractValidator;
use ZendServer\Log\Log;
use DeploymentLibrary\Container;

class Equals extends AbstractValidator {
	const NOT_VALID  = 'notValid';
	const VALID  = 'valid';
	
	/**
	 * @var string
	 */
	protected $version;
	
	/**
	 * @param string $version
	 */
	public function __construct($version) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("%%version%% is deployed");
		$this->abstractOptions['messageTemplates'][self::NOT_VALID] = _t("%%version%% must be deployed");
		$this->abstractOptions['messageVariables']['version'] = 'version';
		$this->version = $version;
	}
	
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		if ($value instanceof Container) {
			$versions = array_map(function($item){
				return $item['version'];
			}, $value->getVersions());
			// TODO is this library deployed?
			if (in_array($this->version, $versions)) {
				$this->error(self::VALID);
				return true;
			}
		}

		$this->error(self::NOT_VALID);
		return false;
	}
}