<?php

namespace Configuration\License\Validator;

use ZendServer\Edition;
use ZendServer\Validator\AbstractZendServerValidator;
use Configuration\License\Wrapper;
class LicenseValidator extends AbstractZendServerValidator {

	const INVALID_STRING_LENGTH		= 1;
	const INVALID_STRING_CHARACTERS	= 2;
	const INVALID_EDITION			= 4;
	const INVALID_SIGNATURE			= 8;
	const LICENSE_NOT_OK			= 16;
	const LICENSE_EXPIRED			= 32;	
	const LICENSE_NOT_CLUSTER		= 64;
	
	const LICENSE_STRING_LENGTH				= 32;
	const DAY_BEFORE_EXPIRE_NOTIFICATION	= 60;
	

	/**
	 * @var Configuration\License\ZemUtilsWrapper
	 */
	private $utilsWrapper = null;	
	
	/**
	 * @var string
	 */
	protected $user;
	
	/**
	 * @var Edition
	 */
	protected $edition;
	
	public function __construct($user) {		
		$this->messageTemplates = array(
				self::INVALID_STRING_LENGTH 		=> "Invalid license key. License keys must be exactly '%value%' characters long",
				self::INVALID_STRING_CHARACTERS 	=> "Invalid license key. License keys can only include the characters A-Z and 0-9",
				self::INVALID_EDITION 				=> "The license key entered is invalid for this version of Zend Server",
				self::INVALID_SIGNATURE 			=> "The license key entered has an invalid signature",
				self::LICENSE_NOT_OK 				=> "Order number does not match the license value",
				self::LICENSE_EXPIRED			 	=> "The entered license key has expired and is no longer valid",
				self::LICENSE_NOT_CLUSTER 			=> "The license does not support cluster mode, disconnect from the cluster",
		);
		 
		$this->user = $user;
		
		parent::__construct();
	}
	
	public function isValid($value) {		
		$this->setValue($value);
	
		$licenseValidate = new \Zend\Validator\Regex('/^\s*\S{' . self::LICENSE_STRING_LENGTH . '}\s*$/');
		if (! $licenseValidate->isValid($value)) {
			$this->error(self::INVALID_STRING_LENGTH, self::LICENSE_STRING_LENGTH);			
			return false;
		}
	
		$licenseValidate = new \Zend\Validator\Regex('/^[a-zA-Z0-9]+$/');
		if (! $licenseValidate->isValid($value)) {
			$this->error(self::INVALID_STRING_CHARACTERS);
			return false;
		}

		$licenseData = $this->getLicenseWrapper()->getSerialNumberInfo($value, $this->user);
		
		if (! $licenseData->isSignatureValid()) {
			$this->error(self::INVALID_SIGNATURE);
			return false;
		}
		
		if ($licenseData->isLicenseExpired()) {
			$this->error(self::LICENSE_EXPIRED);
			return false;
		}		

		if (! $licenseData->isLicenseOk()) {
			$this->error(self::LICENSE_NOT_OK);
			return false;
		}

		if (! $licenseData->isLicenseKnown()) {
			$this->error(self::INVALID_EDITION);
			return false;
		}		
		
		$clusterEditions = array(\Configuration\License\License::EDITION_PROFESSIONAL, \Configuration\License\License::EDITION_ENTERPRISE, \Configuration\License\License::EDITION_ENTERPRISE_TRIAL, \Configuration\License\License::EDITION_DEVELOPER_ENTERPRISE);
		if ($this->getEdition()->isCluster() && ! in_array($licenseData->getEdition(), $clusterEditions)) {
			$this->error(self::LICENSE_NOT_CLUSTER);
			return false;
		}
	
		return true;
	}
	
	/**
	 * @return Wrapper
	 */
	private function getLicenseWrapper() {
		if (is_null($this->utilsWrapper)) {
			$this->utilsWrapper = new Wrapper();
		}
	
		return $this->utilsWrapper;
	}
	/**
	 * @param Wrapper $utilsWrapper
	 * @return LicenseValidator
	 */
	public function setUtilsWrapper($utilsWrapper) {
		$this->utilsWrapper = $utilsWrapper;
		return $this;
	}
	
	/**
	 * @return the $edition
	 */
	private function getEdition() {
		if (! ($this->edition instanceof Edition)) {
			$this->edition = new Edition();
		}
		return $this->edition;
	}

	/**
	 * @param \ZendServer\Edition $edition
	 * @return LicenseValidator
	 */
	public function setEdition($edition) {
		$this->edition = $edition;
		return $this;
	}

	
}