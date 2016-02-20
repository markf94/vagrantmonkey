<?php

namespace Bootstrap;

use Application\Module;

use Configuration\License\Validator\LicenseValidator;

use ZendServer\FS\FS,
ZendServer\Log\Log,
Notifications\Db\NotificationsActionsMapper,
GuiConfiguration\Mapper\Configuration,
ZendServer\Ini\IniWriter;
use WebAPI\Db\ApiKeyContainer;
use Configuration\Task\ConfigurationPackage;
use Servers\Db\ServersAwareInterface;
use Snapshots\Db\Mapper as SnapshotMapper;

class Mapper implements ServersAwareInterface {

	/**
	 * @var string
	 */
	private $licenseUser;
	/**
	 * @var string
	 */
	private $licenseKey;
	/**
	 * @var string
	 */
	private $adminPassword;
	/**
	 * @var string
	 */
	private $developerPassword;
	/**
	 * @var string
	 */
	private $production;
	/**
	 * @var string
	 */
	private $applicationUrl;
	/**
	 * @var string
	 */
	private $adminEmail;
	/**
	 * @var \Users\Forms\ChangePassword
	 */
	private $changePassword;
	/**
	 * @var \Configuration\MapperDirectives
	 */
	private $directivesMapper;
	/**
	 * @var \Users\Db\Mapper
	 */
	private $usersMapper;
	/**
	 * @var \Servers\Db\Mapper
	 */
	private $serversMapper;
	/**
	 * @var \WebAPI\Db\Mapper
	 */
	private $webapiKeysMapper;
	
	/**
	 * @var \Snapshots\Mapper\Profile
	 */
	private $profilesMapper;
	
	/**
	 * @var \GuiConfiguration\Mapper\Configuration
	 */
	private $guiConfiguration;
	/**
	 * @var \Notifications\Db\NotificationsActionsMapper
	 */
	private $notificationsActionsMapper;
	
	/**
	 * @var ConfigurationPackage
	 */
	private $configurationPackage;
	
	public function bootstrapSingleServer() {
		
		if (! $this->adminPassword) {
			throw new Exception(_t('Missing password field for saving'));
		}
	
		$passwordEntry = $this->changePassword;
		$passwordEntry->setData(array('newPassword' => $this->adminPassword));
		if (! $passwordEntry->isValid()) {
			throw new Exception(_t('Administrator password is invalid: %s', array(current($passwordEntry->getMessages('newPassword')))));
		}
		 
		$passwordEntry->setData(array('newPassword' => $this->developerPassword));
		if (! $passwordEntry->isValid()) {
			throw new Exception(_t('Developer password is invalid: %s', array(current($passwordEntry->getMessages('newPassword')))));
		}

		if ($this->licenseUser) {
			$licenseValidator = new LicenseValidator($this->licenseUser);
			if (! $licenseValidator->isValid($this->licenseKey)) {
				throw new Exception(_t('License key is invalid'));
			}
			if (Module::isClusterManager()) { // single server will already have this directives
				$this->getDirectivesMapper()->insertLicenseDetails($this->licenseKey, $this->licenseUser);
				$this->getDirectivesMapper()->writeLicenseDirectivesToIni($this->licenseKey, $this->licenseUser);
			}
			if (0 < $this->serversMapper->countAllServers()) {
				// if we have any servers, push using storeDirectives so that all synchronisation takes place
				$this->getDirectivesMapper()->setDirectives(array('zend.serial_number' => $this->licenseKey, 'zend.user_name' => $this->licenseUser));
			}
		}

		$this->setProfileDirectives();

		$this->getUsersMapper()->setUser(Module::config('user', 'adminUser'), $this->adminPassword, Module::ACL_ROLE_ADMINISTRATOR);
		if ($this->developerPassword) {
			$this->getUsersMapper()->setUser(Module::config('user', 'devUser'), $this->developerPassword, Module::ACL_ROLE_DEVELOPER);
		} else { /// create the developer with an empty password
			$this->getUsersMapper()->setUser(Module::config('user', 'devUser'), '', Module::ACL_ROLE_DEVELOPER);
		}

		if (! $this->applicationUrl) {
			$defaultServer = '<default-server>';
		} else {
			//remove "http://" on begining if user added
			$defaultServer = preg_replace('#^https?://#', '', $this->applicationUrl);
		}

		$adminKey = $this->webapiKeysMapper->findKeyByName(\WebAPI\Db\Mapper::ADMIN_KEY_NAME);
		if ((! ($adminKey instanceof ApiKeyContainer) || (! $adminKey->getId()))) {
			$adminKey = $this->webapiKeysMapper->addAdminKey(\WebAPI\Db\Mapper::ADMIN_KEY_NAME);
		}
		
		$this->setDefaultServer($defaultServer);
		$this->setNotificationMail($this->adminEmail);
		$this->setBootStrapCompleted();
		$this->setServerUniqueId();
		$this->setServerTimezone();
		$this->getConfigurationPackage()->exportConfiguration(SnapshotMapper::SNAPSHOT_SYSTEM_BOOT);
		return array('key' => $adminKey);
	}
	
	/**
	 * @return boolean
	 */
	public function isBootstrapNeeded() {
		$users = $this->getUsersMapper()->getUsers()->toArray();
		return count($users) == 0;
	}
	
    /**
	 * @return \Configuration\MapperDirectives $directivesMapper
	 */
	public function getDirectivesMapper() {
		return $this->directivesMapper;
	}

	/**
	 * @return \Users\Db\Mapper $usersMapper
	 */
	public function getUsersMapper() {
		return $this->usersMapper;
	}

	/**
	 * @param \Users\Db\Mapper $usersMapper
	 * @return Mapper
	 */
	public function setUsersMapper($usersMapper) {
		$this->usersMapper = $usersMapper;
		return $this;
	}

	/**
	 * @param \Configuration\MapperDirectives $directivesMapper
	 * @return Mapper
	 */
	public function setDirectivesMapper($directivesMapper) {
		$this->directivesMapper = $directivesMapper;
		return $this;
	}

	/**
	 * @param \Users\Forms\ChangePassword $changePassword
	 * @return Mapper
	 */
	public function setChangePassword($changePassword) {
		$this->changePassword = $changePassword;
		return $this;
	}

	/**
	 * @return string $licenseUser
	 */
	public function getLicenseUser() {
		return $this->licenseUser;
	}

	/**
	 * @return string $licenseKey
	 */
	public function getLicenseKey() {
		return $this->licenseKey;
	}

	/**
	 * @return string $adminPassword
	 */
	public function getAdminPassword() {
		return $this->adminPassword;
	}

	/**
	 * @return string $developerPassword
	 */
	public function getDeveloperPassword() {
		return $this->developerPassword;
	}

	/**
	 * @return boolean $production
	 */
	public function getProduction() {
		return $this->production;
	}

	/**
	 * @return string $applicationUrl
	 */
	public function getApplicationUrl() {
		return $this->applicationUrl;
	}

	/**
	 * @return string $adminEmail
	 */
	public function getAdminEmail() {
		return $this->adminEmail;
	}

	/**
	 * @return \WebAPI\Db\Mapper $webapiKeysMapper
	 */
	public function getWebapiKeysMapper() {
		return $this->webapiKeysMapper;
	}

	/**
	 * @return \Snapshots\Mapper\Profile
	 */
	public function getProfilesMapper() {
		return $this->profilesMapper;
	}

	/**
	 * @return NotificationsActionsMapper
	 */
	public function getNotificationsActionsMapper() {
		return $this->notificationsActionsMapper;
	}

	/**
	 * @return ConfigurationPackage
	 */
	public function getConfigurationPackage() {
		return $this->configurationPackage;
	}

	/**
	 * @param \Configuration\Task\ConfigurationPackage $configurationPackage
	 */
	public function setConfigurationPackage($configurationPackage) {
		$this->configurationPackage = $configurationPackage;
	}

	/**
	 * @param Configuration $guiConfiguration
	 */
	public function setGuiConfiguration($guiConfiguration) {
		$this->guiConfiguration = $guiConfiguration;
	}

	/**
	 * @param NotificationsActionsMapper $notificationsActionsMapper
	 */
	public function setNotificationsActionsMapper($notificationsActionsMapper) {
		$this->notificationsActionsMapper = $notificationsActionsMapper;
	}

	/**
	 * @param \Snapshots\Mapper\Profile $profilesMapper
	 */
	public function setProfilesMapper($profilesMapper) {
		$this->profilesMapper = $profilesMapper;
	}

	/**
	 * @param \WebAPI\Db\Mapper $webapiKeysMapper
	 * @return Mapper
	 */
	public function setWebapiKeysMapper($webapiKeysMapper) {
		$this->webapiKeysMapper = $webapiKeysMapper;
		return $this;
	}

	/**
	 * @param \Servers\Db\Mapper $serversMapper
	 * @return Mapper
	 */
	public function setServersMapper($serversMapper) {
		$this->serversMapper = $serversMapper;
		return $this;
	}

	/**
	 * @param string $adminEmail
	 * @return Mapper
	 */
	public function setAdminEmail($adminEmail) {
		$this->adminEmail = $adminEmail;
		return $this;
	}

	/**
	 * @param string $licenseUser
	 * @return Mapper
	 */
	public function setLicenseUser($licenseUser) {
		$this->licenseUser = $licenseUser;
		return $this;
	}

	/**
	 * @param string $licenseKey
	 * @return Mapper
	 */
	public function setLicenseKey($licenseKey) {
		$this->licenseKey = $licenseKey;
		return $this;
	}

	/**
	 * @param string $adminPassword
	 * @return Mapper
	 */
	public function setAdminPassword($adminPassword) {
		$this->adminPassword = $adminPassword;
		return $this;
	}

	/**
	 * @param string $developerPassword
	 * @return Mapper
	 */
	public function setDeveloperPassword($developerPassword) {
		$this->developerPassword = $developerPassword;
		return $this;
	}

	/**
	 * @param string $production
	 * @return Mapper
	 */
	public function setProduction($production) {
		$this->production = $production;
		return $this;
	}

	/**
	 * @param string $applicationUrl
	 * @return Mapper
	 */
	public function setApplicationUrl($applicationUrl) {
		$this->applicationUrl = $applicationUrl;
		return $this;
	}

	public function setServerUniqueId() {
		Log::debug("setting server unique id");
		
		$uniqueId = sha1(uniqid('' ,true));
		return $this->getGuiConfigurationMapper()->setGuiDirectives(array('uniqueId' => $uniqueId));
	}
	
	public function setServerTimezone() {
		Log::debug("setting server timezone");
	
		// get timezone
		$tz = @date_default_timezone_get();
		
		Log::debug("timezone detected is " . $tz);
		
		// set timezone to both apache and gui
		$this->getDirectivesMapper()->setDirectives(array('date.timezone' => $tz));
		return $this->getGuiConfigurationMapper()->setGuiDirectives(array('timezone' => $tz));
	}
	
	/**
	 * sets bootstrap[completed] = true in zs_ui_user.ini
	 */
	public function setBootStrapCompleted() {
		Log::debug("setting bootstrap completed to true");
		return $this->getGuiConfigurationMapper()->setGuiDirectives(array('completed' => "true"));
	}
	
	public function resetBootstrap() {
		Log::debug("setting bootstrap completed to false");
		return $this->getGuiConfigurationMapper()->setGuiDirectives(array('completed' => "false"));
	}

	/**
	 * 
	 * @return boolean flag if the set profile success
	 */
	public function setProfileDirectives() {		
		$profilesManager = $this->getProfilesMapper();
		
		$profiles = array('production'=>'productionDirectives', 'development'=>'developmentDirectives', 'cluster' => 'clusterDirectives');
		if (isset($profiles[$this->production])) {
			try {
				$profilesManager->activateProfile($profiles[$this->production]);
				
				// @link ZSRV-14095
				if (strtolower($this->production) == 'development') {
				    $this->directivesMapper->setDirectives(array('zend_codetracing.max_disk_space' => \Codetracing\Controller\IndexController::DEVELOPMENT_MAX_DISK));
				} else {
				    $this->directivesMapper->setDirectives(array('zend_codetracing.max_disk_space' => \Codetracing\Controller\IndexController::PRODUCTION_MAX_DISK));
				}
			} catch (Exception $ex) {
				Log::err("Profile {$this->production} not activated: {$ex->getMessage()}");
				return false;
			}
			return true;
		} else {
		    return false;
		}
	}
	
    /**
     * sets bootstrap[completed] = true in zs_ui_user.ini
     */
    private function setDefaultServer($defaultServer) {
    	Log::debug("setting bootstrap default server to true");
    	return $this->getGuiConfigurationMapper()->setGuiDirectives(array('defaultServer' => $defaultServer));
    }

    /**
     *
     * @return \GuiConfiguration\Mapper\Configuration
     */
    private function getGuiConfigurationMapper() {
    	return $this->guiConfiguration;
    }
    
    /**
     * Update the email address in the notification table
     */
    private function setNotificationMail($mail) {
    	Log::debug("setting notification mail to {$mail}");
    	return $this->getNotificationsActionsMapper()->updateTypesEmail($mail);
    }
}
 