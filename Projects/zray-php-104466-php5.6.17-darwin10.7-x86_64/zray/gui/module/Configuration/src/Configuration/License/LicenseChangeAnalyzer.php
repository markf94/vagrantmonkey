<?php

namespace Configuration\License;

use Configuration\License\ZemUtilsWrapper;

use Configuration\License\License;

use \Configuration\License\LicenseChangeContainer;

use ZendServer\Exception;


class LicenseChangeAnalyzer {
	
	/**
	 * @var ZemUtilsWrapper
	 */
	private $zendServerUtils;

	/**
	 * @var integer
	 */
	private $evaluation;

	/**
	 * @var integer
	 */
	private $editionChange;

	/**
	 * @var \Configuration\License\LicenseChangeContainer
	 */
	private $LicenseChangeContainer;	

	/**
	 * @param string $newLicenseSerial
	 * @param string $newLicenseUser
	 * @param string $currentLicenseSerial
	 * @param string $currentLicenseName
	 * @return LicenseChangeContainer
	 */
	public function analyzeLicenseChange($newLicenseSerial, $newLicenseUser, $currentLicenseSerial, $currentLicenseName) {
		$this->LicenseChangeContainer = new LicenseChangeContainer();
		$newLicense = $this->zendServerUtils->getSerialNumberInfo($newLicenseSerial, $newLicenseUser);
		if ($currentLicenseSerial) {
			$currentLicense = $this->zendServerUtils->getSerialNumberInfo($currentLicenseSerial, $currentLicenseName);
		} else {
			$currentLicense = new EmptyLicense();
		}
		
		$this->compareEvaluations($newLicense, $currentLicense);
		$this->compareEditions($newLicense, $currentLicense);
		
		return $this->LicenseChangeContainer;
	}
	
	/**
	 * @param \Configuration\License\ZemUtilsWrapper $zendServerUtils
	 * @return ChangeListener
	 */
	public function setZendServerUtils($zendServerUtils) {
		$this->zendServerUtils = $zendServerUtils;
		return $this;
	}

	/**
	 * @param \Configuration\License\License $newLicense
	 * @param \Configuration\License\License $currentLicense
	 */
	private function compareEvaluations($newLicense, $currentLicense) {
		$this->LicenseChangeContainer->setNewEvaluation($newLicense->isEvaluation());
		$this->LicenseChangeContainer->setCurrentEvaluation($currentLicense->isEvaluation());
		
		if ($currentLicense->isEvaluation()) {
			if ($newLicense->isEvaluation()) {
				return $this->LicenseChangeContainer->setEvaluationChange(LicenseChangeContainer::EVALUATION_TO_EVALUATION);
			} else {
				return $this->LicenseChangeContainer->setEvaluationChange(LicenseChangeContainer::EVALUATION_TO_COMMERCIAL);
			}
		}
		
		if ($newLicense->isEvaluation()) {
			return $this->LicenseChangeContainer->setEvaluationChange(LicenseChangeContainer::COMMERCIAL_TO_EVALUATION);
		} else {
			return $this->LicenseChangeContainer->setEvaluationChange(LicenseChangeContainer::COMMERCIAL_TO_COMMERCIAL);
		}
	}
	
	/**
	 * Compares licenses by edition difference
	 * If the new license is greater, return 1
	 * If the current license is greater, return -1
	 * Otherwise, if both instances are equal, return 0
	 * @param License $license
	 * @return integer
	 */
	private function compareEditions(License $newLicense, License $currentLicense) {		
		$this->LicenseChangeContainer->setNewEdition($newEdition = $newLicense->getEdition());
		$this->LicenseChangeContainer->setCurrentEdition($currentEdition = $currentLicense->getEdition());
		
		if ($newEdition == $currentEdition) {
			return $this->LicenseChangeContainer->setEditionChange(LicenseChangeContainer::EDITION_NO_CHANGE);
		}
		
		$ordinalEditions = array(
			License::EDITION_EMPTY, License::EDITION_FREE, License::EDITION_DEVELOPER, License::EDITION_BASIC, License::EDITION_PROFESSIONAL, License::EDITION_ENTERPRISE
		);
		
		$newEditionOrder = array_search($newEdition, $ordinalEditions);
		$currentEditionOrder = array_search($currentEdition, $ordinalEditions);
		
		if ($newEditionOrder > $currentEditionOrder) {
			return $this->LicenseChangeContainer->setEditionChange(LicenseChangeContainer::EDITION_UPGRADE);
		} else {
			return $this->LicenseChangeContainer->setEditionChange(LicenseChangeContainer::EDITION_DOWNGRADE);
		}
	}
	
}

