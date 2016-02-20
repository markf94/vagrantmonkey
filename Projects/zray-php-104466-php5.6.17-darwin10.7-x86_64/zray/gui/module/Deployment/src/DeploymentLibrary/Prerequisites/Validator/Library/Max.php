<?php
namespace DeploymentLibrary\Prerequisites\Validator\Library;

use ZendServer\Exception,
Zend\Validator\AbstractValidator;
use DeploymentLibrary\Container;
use ZendServer\Log\Log;

class Max extends AbstractValidator {
	const NOT_MAX  = 'notMax';
	const NOT_FOUND  = 'libraryNotFoundMax';
	const VALID  = 'validVersionMax';
    
	/**
	 * @var string
	 */
	protected $version;
	
	/**
	 * @param string $version
	 */
	public function __construct($version) {
		$this->abstractOptions['messageTemplates'][self::VALID] = _t("Version should be at most %%version%% (is %%value%%)");
		$this->abstractOptions['messageTemplates'][self::NOT_MAX] = _t("Version should be at most %%version%% (is %%value%%)");
		$this->abstractOptions['messageTemplates'][self::NOT_FOUND] = _t("Version should be at most %%version%% (none found)");
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
			$leastVersion = array_reduce($value->getVersions(), function($least, $lib){
				if (version_compare($lib['version'], $least, '<=') || is_null($least)) {
					return $lib['version'];
				}
				return $least;
			}, null);
					
			$this->setValue($leastVersion);
				
			$versions = array_map(function($item){
				return $item['version'];
			}, $value->getVersions());
		
			foreach ($versions as $version) {
				if (version_compare($this->version, $version, '>=')) {
					$this->error(self::VALID);
					return true;
				}
			}
		}

		$this->error(self::NOT_MAX);
		return false;
	}
}