<?php

namespace Configuration\License;

use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\ListenerAggregateInterface;

use Users\Db\Mapper;

use Zend\Permissions\Acl\Acl;

use ZendServer\Log\Log;

use ZendServer\Exception;

use Configuration\License\LicenseChangeAnalyzer;

use Zend\EventManager\Event;

use \Configuration\License\LicenseChangeContainer;

use Application\Module;

use Snapshots\Mapper\Profile;
use Configuration\MapperDirectives;
use Configuration\Controller\ZendMonitorController;
use GuiConfiguration\Mapper\Configuration;
use Audit\Db\SettingsMapper;

class ChangeListener implements ListenerAggregateInterface {
	
	/**
	 * @var LicenseChangeAnalyzer
	 */
	private $licenseChangeAnalyzer;
	
	/**
	 * @var Acl
	 */
	private $acl;
	
	/**
	 * @var Mapper
	 */
	private $usersMapper;
		
	/**
	 * @var Configuration
	 */
	private $guiConfigurationMapper;	
	
	/**
	 * @var Profile
	 */
	private $profileMapper;
	/**
	 * @var array
	 */
	private $listeners = array();
	
	/**
	 * @var MapperDirectives
	 */
	private $directivesMapper;
	
	/**
	 * @var SettingsMapper
	 */
	private $auditSettingsMapper;
	
	/**
	 * @param Event $event
	 * @return boolean
	 */
	public function licenseChanged(Event $event) {
		$newDirectives = $event->getParam('newDirectives');
		$currentDirectives = $event->getParam('directives');
		/// changed the serial_number?
		if (! isset($newDirectives['zend.serial_number'])) {
			return false;
		}
		
		$this->directivesMapper = $event->getTarget();
		
		$newLicenseSerial = $newDirectives['zend.serial_number'];
		$newLicenseUser = $newDirectives['zend.user_name'];

		try {
			if (isset($currentDirectives['zend.serial_number'])) {
				$currentLicenseSerial = $currentDirectives['zend.serial_number'];
				$currentLicenseName = $currentDirectives['zend.user_name'];
			} else {
				$currentLicenseSerial = $currentLicenseName = null;
			}
			
			$licenseChangeContainer = $this->licenseChangeAnalyzer->analyzeLicenseChange($newLicenseSerial, $newLicenseUser, $currentLicenseSerial, $currentLicenseName);
			$this->checkEvaluationChange($licenseChangeContainer->getEvaluationChange());
			$this->checkEditionChange($licenseChangeContainer->getEditionChange(), $licenseChangeContainer->getNewEdition());
			$this->checkProfileCompliance($licenseChangeContainer);

			$newEdition = strtoupper($licenseChangeContainer->getNewEdition());
			$editionRole = "edition:{$newEdition}";
			$currentRole = strtoupper($licenseChangeContainer->getCurrentEdition());
			$currentRole = "edition:{$currentRole}";
			
			//route:DevBarWebApi
			//data:useZRaySecureMode
			
			// remove the Z-Ray if not even basic activities are allowed
			if ( ! $this->acl->isAllowed($editionRole, 'route:DevBarWebApi', 'devBarGetRequestsInfo')) {
				$this->directivesMapper->setDirectives(array('zray.enable' => '0'));
			} // check if Z-Ray can use secured mode
			elseif ( ! $this->acl->isAllowed($editionRole, 'data:useZRaySecureMode') && 
					$this->directivesMapper->getDirectiveValue('zray.enable') == 2) {

				$this->directivesMapper->setDirectives(array('zray.enable' => '0'));
			}
		} catch (Exception $ex) {
			return Log::warn("License change analysis failed: {$ex->getMessage()}");
		}
	}
	
	/**
	 * @param LicenseChangeContainer $licenseChangeContainer
	 */
	protected function checkProfileCompliance($licenseChangeContainer) {
		$newEdition = $licenseChangeContainer->getNewEdition();
		/// check what's the current profile
		if (strtolower(Module::config('package', 'serverProfile')) != 'development') {
			if (strtolower($newEdition) == 'developer') {
				/// change to developer profile
				$this->getProfileMapper()->activateProfile('developmentDirectives');
				// TODO create a new SystemBoot snapshot
				
				
			} else {
			    $this->directivesMapper->setDirectives(array('zend_monitor.event_tracing_mode' => ZendMonitorController::EVENT_TRACING_MODE_OFF));
			}
		}
	}
	
	/**
	 * @param integer $evaluationChange
	 */
	protected function checkEvaluationChange($evaluationChange) {
		if ($evaluationChange === LicenseChangeContainer::EVALUATION_TO_EVALUATION) {
			return Log::info('Evaluation license renewed');
		} 
		
		if ($evaluationChange === LicenseChangeContainer::EVALUATION_TO_COMMERCIAL) {
			return Log::info('Upgrading from evaluation license to commercial license');
		}

		if ($evaluationChange === LicenseChangeContainer::COMMERCIAL_TO_EVALUATION) {
			return Log::info('Downgrading from commercial license to evaluation license');
		}

		if ($evaluationChange === LicenseChangeContainer::COMMERCIAL_TO_COMMERCIAL) {
			return Log::info('Upgrading from commercial license to commercial license');
		}
					
		throw new Exception("Unknown evaluation change '$evaluationChange' detected!");
	}
		
	/**
	 * @param integer $editionChange
	 */	
	protected function checkEditionChange($editionChange, $newEdition) {
		if ($editionChange === LicenseChangeContainer::EDITION_NO_CHANGE) { // no change
			return Log::info('New license is of the same edition, no changes');
		}

		$newEdition = strtoupper($newEdition);
		$editionRole = "edition:{$newEdition}";
		
		if ($this->acl->isAllowed($editionRole, 'auditTrail:timelimit', 'unlimited')) {
			$this->getAuditSettingsMapper()->setHistory('');
		} elseif ($this->acl->isAllowed($editionRole, 'auditTrail:timelimit', '2hour')) {
			$this->getAuditSettingsMapper()->setHistory(2);
		}
		
		if ($editionChange === LicenseChangeContainer::EDITION_UPGRADE) { // upgrading
			return Log::info('License is upgrading');
		}
		
		Log::info('License is downgrading');// downgrading
		
		$this->editionChange = LicenseChangeContainer::EDITION_DOWNGRADE;
		
		if (! $this->acl->isAllowed($editionRole, 'data:useMultipleUsers')) {
			$this->usersMapper->deleteAllButAdmin();// clean up all users but the admin
			Log::info('Remove gui access users that are not administrators');
		}
		
		if (! $this->acl->isAllowed($editionRole, 'data:collectEventsCodeTrace')) {
			$this->directivesMapper->setDirectives(array('zend_monitor.event_tracing_mode' => ZendMonitorController::EVENT_TRACING_MODE_OFF));
			Log::info('Set zend_monitor.tracing_mode to \'Off\'');
		}
		
	}

	/**
	 * @return Profile
	 */
	public function getProfileMapper() {
		return $this->profileMapper;
	}

	/**
	 * @return SettingsMapper
	 */
	public function getAuditSettingsMapper() {
		return $this->auditSettingsMapper;
	}

	/**
	 * @param \Audit\Db\SettingsMapper $auditSettingsMapper
	 */
	public function setAuditSettingsMapper($auditSettingsMapper) {
		$this->auditSettingsMapper = $auditSettingsMapper;
	}

	/**
	 * @param \Snapshots\Mapper\Profile $profileMapper
	 */
	public function setProfileMapper($profileMapper) {
		$this->profileMapper = $profileMapper;
	}

	/**
	 * @param \Configuration\License\LicenseChangeAnalyzer $licenseChangeAnalyzer
	 * @return ChangeListener
	 */
	public function setlicenseChangeAnalyzer($licenseChangeAnalyzer) {
		$this->licenseChangeAnalyzer = $licenseChangeAnalyzer;
		return $this;
	}

	/**
	 * @param Acl $acl
	 * @return ChangeListener
	 */
	public function setAcl(Acl $acl) {
		$this->acl = $acl;
		return $this;
	}

	/**
	 * @param \Users\Db\Mapper $usersMapper
	 * @return ChangeListener
	 */
	public function setUsersMapper($usersMapper) {
		$this->usersMapper = $usersMapper;
		return $this;
	}
	
	/**
	 * @return \GuiConfiguration\Mapper\Configuration
	 */
	public function getGuiConfigurationMapper() {
		return $this->guiConfigurationMapper;
	}

	/**
	 * @param \GuiConfiguration\Mapper\Configuration $guiConfigurationMapper
	 */
	public function setGuiConfigurationMapper($guiConfigurationMapper) {
		$this->guiConfigurationMapper = $guiConfigurationMapper;
	}

	public function attach(EventManagerInterface $events) {
		$this->listeners[] = $events->attach('setDirectives', array($this, 'licenseChanged'));
	}
	
	public function detach(EventManagerInterface $events) {
		$events->detach(current($this->listeners));
	}	
}
