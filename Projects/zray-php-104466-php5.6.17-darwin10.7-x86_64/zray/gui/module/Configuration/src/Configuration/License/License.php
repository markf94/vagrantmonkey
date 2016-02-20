<?php

namespace Configuration\License;

use ZendServer\Exception;

class License {

	
	const EDITION_ENTERPRISE	 		= 'ENTERPRISE';
	const EDITION_DEVELOPER_ENTERPRISE	= 'DEVELOPER_ENTERPRISE';
	const EDITION_DEVELOPER		 		= 'DEVELOPER';
	const EDITION_FREE			 		= 'FREE';
	const EDITION_EMPTY			 		= 'EMPTY';
	const EDITION_BASIC			 		= 'SMALL BUSINESS';
	const EDITION_PROFESSIONAL	 		= 'PROFESSIONAL';
	const EDITION_ENTERPRISE_TRIAL		= 'ENTERPRISE_TRIAL';
	
	protected $editions = array(
			2 => 'ENTERPRISE',
			3 => 'DEVELOPER',
			5 => 'FREE',
			6 => 'SMALL BUSINESS',
			7 => 'PROFESSIONAL',
			8 => 'DEVELOPER_ENTERPRISE',
			self::EDITION_EMPTY => self::EDITION_EMPTY,
	);
	
	/**
	 * @var array
	 */
	private $licenseData;
	

	/**
	 * @param array $licenseData
	 */
	public function __construct($licenseData) {
		$this->licenseData = $licenseData;
	}
	
	/**
	 * @return string $userName
	 */
	public function getUserName() {
		return $this->licenseData['user_name'];
	}

	/**
	 * @return string $serialNumber
	 */
	public function getSerialNumber() {
		return $this->licenseData['serial_number'];
	}

	/**
	 * @return number $edition
	 */
	public function getEdition() {
		if ((! $this->isSignatureValid()) && (! strlen($this->getSerialNumber()))) {
			return self::EDITION_EMPTY;
		}
		if (! $this->isLicenseKnown()) {		
			return self::EDITION_EMPTY;
		}
		
		return $this->editions[$this->licenseData['edition']];
	}

	/**
	 * @return number $expiration
	 */
	public function getExpiration() {
		return $this->licenseData['expiration_date'];
	}

	/**
	 * @return number $numOfServers
	 */
	public function getNumOfServers() {
		return $this->licenseData['num_of_nodes'];
	}

	/**
	 * @return boolean $signatureInvalid
	 */
	public function isSignatureValid() {
		return isset($this->licenseData['signature_invalid']) ? (! $this->licenseData['signature_invalid']): false;
	}

	/**
	 * @return boolean $licenseOk
	 */
	public function isLicenseOk() {
		return $this->licenseData['license_ok'];
	}

	/**
	 * @return boolean
	 */
	public function isLicenseKnown() {
		$manager = new \ZendServer\Configuration\Manager();
		
		// return false for free license to non-ibmi 
		if (isset($this->editions[$this->licenseData['edition']]) 
			&& $this->editions[$this->licenseData['edition']] == self::EDITION_FREE && $manager->getOsType() != \ZendServer\Configuration\Manager::OS_TYPE_IBMI
		) {
			return false;	
		}
		
		return isset($this->editions[$this->licenseData['edition']]);
	}
	
	/**
	 * @return boolean
	 */
	public function isFirstLicense() {
		return $this->licenseData['is_first_license'];
	}
	
	/**
	 * @return boolean $evaluation
	 */
	public function isEvaluation() {
		return isset($this->licenseData['evaluation']) ? $this->licenseData['evaluation'] :  false;
	}
	
	/**
	 * @return boolean $evaluation
	 */
	public function isLicenseExpired() {
		return true === $this->licenseData['license_expired'];
	}

	/**
	 * @return boolean
	 */
	public function isServersUnlimited() {
		return ($this->getNumOfServers() == 0);
	}

	/**
	 * @return boolean
	 */
	public function isNeverExpires() {
		return ($this->licenseData['date_lock'] == 0 || $this->isCloudLicense());
	}
	/**
	 * @return boolean
	 */
	public function isCloudLicense() {
		return (isset($this->licenseData['is_cloud']) ? (boolean)intval($this->licenseData['is_cloud']) : false);
	}
}

