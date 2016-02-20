<?php

namespace Configuration\License;

use ZendServer\Log\Log,
ZendServer\Exception as ZSException;
use ZendServer\FS\FS;
use Zend\Config\Reader\Ini;
use Configuration\License\License;
use Configuration\License\Wrapper;

class ZemUtilsWrapper {
	
	/**
	 * @var License
	 */
	private $license;
	/**
	 * @param string $serialNumber
	 * @param string $userName
	 * @return License
	 * @throws ZendServer\Exception
	 */
	
	/**
	 * @var Wrapper
	 */
	private $wrapper;
	/**
	 * @var string
	 */
	private $licenseUser;
	/**
	 * @var string
	 */
	private $licenseSerial;
	
	/**
	 * @return \Configuration\License\License
	 */
	public function getLicenseInfo() {
		if ($this->license) return $this->license;
		
		// in case of Aazure - set custom license
		if (isAzureEnv()) {
		    $licenseInfo = array(
		        'user_name' => 'azure',
		        'serial_number' => 'stub',
		        'edition' => 2, // ENTERPRISE
		        'expiration_date' => strtotime('+1 year'),
		        'num_of_nodes' => 100,
		        'signature_invalid' => false,
		        'license_ok' => true,
		        'is_first_license' => false,
		        'evaluation' => false,
		        'license_expired' => false,
		        'date_lock' => false,
		        'is_cloud' => true,
		    );
		    return $this->license = new License($licenseInfo);
		}
		
		// in case of standalone zray - set custom license
		if (isZrayStandaloneEnv()) {
		    $licenseInfo = array(
		        'user_name' => 'zray_standalone',
		        'serial_number' => 'stub',
		        'edition' => 2, // ENTERPRISE
		        'expiration_date' => strtotime('+1 year'),
		        'num_of_nodes' => 100,
		        'signature_invalid' => false,
		        'license_ok' => true,
		        'is_first_license' => false,
		        'evaluation' => false,
		        'license_expired' => false,
		        'date_lock' => false,
		        'is_cloud' => true,
		    );
		    return $this->license = new License($licenseInfo);
		}
		
		if ($this->hasLicenseDetails()) {
			$licenseDetails = array($this->getLicenseSerial(), $this->getLicenseUser());
		} else {
		    if (file_exists(FS::getGlobalDirectivesFile())) {
    			$config = new Ini();
    			$ZGconfig = $config->fromFile(FS::getGlobalDirectivesFile());
    			if (isset($ZGconfig['Zend']['zend'])) {
    				$licenseDetails = array($ZGconfig['Zend']['zend']['serial_number'], $ZGconfig['Zend']['zend']['user_name']);
    			} else {
    				$licenseDetails = array($ZGconfig['zend']['serial_number'], $ZGconfig['zend']['user_name']);
    			}
    			Log::notice('License information retrieved from file');
		    } else {
		        Log::notice('License information cannot be retrieved from file, file does not exists');
		    }
		}

		if (!isset($licenseDetails[1])) {
			Log::err("Failed retrieving license directives from ZS blueprint");
			throw new ZSException("Failed retrieving license directives from ZS blueprint");
		}
		
		
		list($serialNumber, $userName) = $licenseDetails;
		
		return $this->license = $this->getWrapper()->getSerialNumberInfo($serialNumber, $userName);
	}

	public function isLicenseValid() {
		$licenseInfo = $this->getLicenseInfo();
	
		return $licenseInfo->isLicenseOk() && $licenseInfo->isSignatureValid() && $licenseInfo->isLicenseKnown();
	}	
	
	public function getLicenseFormattedExpiryDate() {
		$licenseInfo = $this->getLicenseInfo();
		
		return strftime("%d/%m/%Y", $licenseInfo->getExpiration());
	}
	
	public function getLicenseEvaluation() {
		return $this->getLicenseInfo()->isEvaluation();
	}
	
	public function getLicenseExpirationDaysNum($ignoreNotificationTime = false) {
		$notificationTime = \Application\Module::config('notifications', 'zend_gui', 'longNotificationTime');
		$daysToExpired = ceil(($this->getLicenseInfo()->getExpiration() - time()) / (60*60*24)); // using ceil, as license should be calculated till the end of the last day (rather than start)
		
		if ($daysToExpired <= $notificationTime || $ignoreNotificationTime) {
			return intval($daysToExpired);
		}
		
		return false; // not in the notification time range
	}
	
	public function getLicenseType() {
		$licenseInfo = $this->getLicenseInfo();
		return $licenseInfo->getEdition();
	}
	/**
	 * @return Wrapper
	 */
	public function getWrapper() {
		if (is_null($this->wrapper)) {
			$this->wrapper = new Wrapper();
		}
		return $this->wrapper;
	}

	/**
	 * @return boolean
	 */
	public function hasLicenseDetails() {
		return (! is_null($this->licenseUser)) && (! is_null($this->licenseSerial)); 
	}
	
	/**
	 * @return string
	 */
	public function getLicenseUser() {
		return $this->licenseUser;
	}

	/**
	 * @return string
	 */
	public function getLicenseSerial() {
		return $this->licenseSerial;
	}

	/**
	 * @param string $licenseUser
	 */
	public function setLicenseUser($licenseUser) {
		$this->licenseUser = $licenseUser;
	}

	/**
	 * @param string $licenseSerial
	 */
	public function setLicenseSerial($licenseSerial) {
		$this->licenseSerial = $licenseSerial;
	}

	/**
	 * @param \Configuration\License\Wrapper $wrapper
	 */
	public function setWrapper($wrapper) {
		$this->wrapper = $wrapper;
	}

	
}