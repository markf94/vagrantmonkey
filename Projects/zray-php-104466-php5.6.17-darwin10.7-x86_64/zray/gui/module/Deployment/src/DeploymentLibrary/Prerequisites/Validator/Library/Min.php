<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use Zend\Validator\AbstractValidator;
use DeploymentLibrary\Container;
use ZendServer\Log\Log;

class Min extends AbstractValidator {
	const NOT_MIN  = 'notMin';
	const NOT_FOUND  = 'libraryNotFoundMin';
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
		$this->abstractOptions['messageTemplates'][self::NOT_FOUND] = _t("Version should be at least %%version%% (none found)");
		$this->abstractOptions['messageVariables']['version'] = 'version';
		$this->version = $version;
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		if (! $value) {
			$this->error(self::NOT_FOUND);
			return false;
		}
		
		if ($value instanceof Container) {
			$greatestVersion = array_reduce($value->getVersions(), function($greatest, $lib) {
				if (version_compare($lib['version'], $greatest, '>=')) {
					return $lib['version'];
				}
				return $greatest;
			}, '0.0.0');
			
			$this->setValue($greatestVersion);
			
			$versions = array_map(function($item){
				return $item['version'];
			}, $value->getVersions());

			foreach ($versions as $version) {
				if (version_compare($this->version, $version, '<=')) {
					$this->error(self::VALID, $version);
					return true;
				}
			}
		}
		

		$this->error(self::NOT_MIN);
		return false;
	}
}