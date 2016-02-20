<?php
namespace ZendServer\Validator;

use Zend\Uri\Http,
	Zend\Uri\Uri,
	ZendServer\Log\Log,
	Zend\Validator\AbstractValidator,
	Zend\Validator\HostName,
	Zend\Uri\Exception;

class HostWithPort extends AbstractValidator {
	
	const INVALID_HOST = 'invalidHost';
	const INVALID_PORT = 'invalidPort';

	/**
	 * @var array
	 */
	protected $messageTemplates = array(
		self::INVALID_HOST  => "'%value%' has an invalid host",
		self::INVALID_PORT  => "'%value%' has an invalid port",
	);
		
	public function __construct($options = array()) {
		parent::__construct($options);
	}
	
	public function isValid($value) {
		
		/// clean out scheme so it won't interfere with port-host separation
		if (strstr($value,'http://')) {
			$value = substr($value, 7);
		} elseif (strstr($value,'https://')) {
			$value = substr($value, 8);
		}
		
		$this->setValue($value);

		$result = true;

		$hostToCheck = $value;
		$parts = explode(":", $value);
		if ($parts) {
			$hostToCheck = $parts[0];
			if (isset($parts[1])) {
				Log::debug("validating port " . $parts[1]);
				$validPort = false;
				$port = intval($parts[1]);
				if ($port > 0) {
					$validPort = true;
				}
				
				if (!$validPort) {
					$this->error(self::INVALID_PORT);
					return false;
				}										
			}	
		} 
		
		Log::debug("validating hostname " . $hostToCheck);
		$hostValidator = new \Zend\Validator\Hostname(\Zend\Validator\Hostname::ALLOW_ALL); // allow flexibility in setting the logical name 
		if (!$hostValidator->isValid($hostToCheck)) {
			$this->error(self::INVALID_HOST);
			return false;
		}
		
		return true;
	}
	
}