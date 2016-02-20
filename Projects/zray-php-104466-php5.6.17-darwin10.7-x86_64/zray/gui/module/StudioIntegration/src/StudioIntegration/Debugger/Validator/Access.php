<?php

namespace StudioIntegration\Debugger\Validator;

use Zend\Validator\AbstractValidator;
use ZendServer\Validator\IpRange;
use ZendServer\Log\Log;

class Access extends AbstractValidator {
	
	const HOST_NOT_IN_ALLOWED = 'HOST_NOT_IN_ALLOWED';
	const HOST_IS_DENIED = 'HOST_IS_DENIED';
	
	protected $allowhosts = '';
	protected $denyhosts = '';
	/**
	 * @var array
	 */
	private $allowedHosts = array();
	/**
	 * @var array
	 */
	private $deniedHosts = array();
	
	protected $messageTemplates = array(
		self::HOST_NOT_IN_ALLOWED => 'Host %value% is not in any allowed ip range (%allowhosts%)',
		self::HOST_IS_DENIED => 'Host %value% is denied access (%denyhosts%)',
	);
	
	protected $messageVariables = array('allowhosts' => 'allowhosts', 'denyhosts' => 'denyhosts');
	
	/**
	 * @param array $options
	 */
	public function __construct($options = null) {
		if (isset($options['allow_hosts'])) {
			$this->allowedHosts = explode(',', $options['allow_hosts']);
			$this->allowedHosts = array_map(array($this, 'getIpRangeValidator'), $this->allowedHosts);
			$this->allowhosts = $options['allow_hosts'];
		}
		
		if (isset($options['deny_hosts'])) {
			$this->deniedHosts = explode(',', $options['deny_hosts']);
			$this->deniedHosts = array_map(array($this, 'getIpRangeValidator'), $this->deniedHosts);
			$this->denyhosts = $options['deny_hosts'];
		}

		parent::__construct($options);
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		if ($value == '127.0.0.1') {
			return true;
		}

		foreach($this->allowedHosts as $rangeValidator) { /* @var $rangeValidator IpRange */
			if ($rangeValidator instanceof IpRange && $rangeValidator->isValid($value)) {
				/// found valid range containing the ip address
				foreach($this->deniedHosts as $rangeValidator) { /* @var $rangeValidator IpRange */
					/// make sure this specific ip is not in any denied ranges
					if ($rangeValidator instanceof IpRange && $rangeValidator->isValid($value)) {
						$this->error(self::HOST_IS_DENIED);
						return false;
					}
				}
				return true;
			}
		}
		$this->error(self::HOST_NOT_IN_ALLOWED);
		return false;
	}
	
	public function getIpRangeValidator($cidr){
		if (preg_match('/^(?<ip>[0-9\\.]+)(\\/(?<mask>[0-9]{1,2}))?$/', $cidr, $matches) > 0) {
			if (!isset($matches['mask']) || $matches['mask'] == '') {
				$matches['mask'] = '32';
			}
			return new IpRange($matches['ip'], $matches['mask']);
		}
		return null;
	}
}