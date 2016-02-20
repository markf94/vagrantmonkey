<?php

namespace ZendServer\Validator;

use Zend\Validator\AbstractValidator;
use ZendServer\Log\Log;

class IpRange extends AbstractValidator {

	const INVALID_IP_RANGE = 'invalidIpRange';

	protected $messageTemplates = array(
			self::INVALID_IP_RANGE => "ip '%value%' is not in range"
	);

	/**
	 * @var string
	 */
	private $ip;

	/**
	 * @var int
	 */
	private $mask;

	public function __construct($ip, $mask) {
		$this->setIp($ip);
		$this->setMask($mask);
	}

	/**
	 * @param string $ip
	 * @return IpRange
	 */
	public function setIp($ip) {
		$this->ip = $ip;
		return $this;
	}

	/**
	 * @param integer $mask
	 * @return IpRange
	 */
	public function setMask($mask) {
		$this->mask = $mask;
		return $this;
	}

	public function isValid($value) {
		$this->setValue($value);

		if ($this->mask == 32) {
			return ($value == $this->ip);

		} else {
				
			$longValue	= ip2long($value);
			$longHost	= ip2long($this->ip);
				
			$longMask	= (0xffffffff << (32 - $this->mask));
				
			$maskedValue	= $longValue & $longMask;
			$maskedHost		= $longHost & $longMask;
				
			return ($maskedHost == $maskedValue);
		}
	}

}