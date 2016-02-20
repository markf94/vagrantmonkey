<?php

namespace Notifications\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Notifications\NotificationContainer;
use Configuration\License\ZemUtilsWrapper;

class NotificationDescription extends AbstractHelper {

	private $descriptions = array(
			NotificationContainer::TYPE_SERVER_OFFLINE => 'The server is offline.',
			NotificationContainer::TYPE_RESTART_REQUIRED => 'To apply changes, you must perform a restart.',
			NotificationContainer::TYPE_DAEMON_OFFLINE => 'The daemon is offline.',
			NotificationContainer::TYPE_PHP_EXT_DIRECTIVE_MISSMATCH => 'The values for this extension directive contradict corresponding values in the cluster.',
			NotificationContainer::TYPE_PHP_EXT_NOT_LOADED => 'To configure the directives for this PHP extension and begin working with its functionality, the extension needs to be enabled.',
			NotificationContainer::TYPE_PHP_EXT_NOT_INSTALLED => 'To configure the directives for this PHP extension and begin working with its functionality, the extension needs to be installed and enabled.',
			NotificationContainer::TYPE_ZEND_EXT_DIRECTIVE_MISSMATCH => 'The values for this component directive contradict corresponding values in the cluster.',
			NotificationContainer::TYPE_ZEND_EXT_NOT_LOADED => 'To configure the directives for this component and begin working with its functionality, the component needs to be enabled.',
			NotificationContainer::TYPE_ZEND_EXT_NOT_INSTALLED => 'To configure the directives for this component and begin working with its functionality, the component needs to be installed and enabled.',
			NotificationContainer::TYPE_DEPLOYMENT_FAILURE => 'The application package you attempted to deploy could not be deployed.',
			NotificationContainer::TYPE_DEPLOYMENT_UPDATE_FAILURE => 'The application you attempted to update could not be updated.',
			NotificationContainer::TYPE_DEPLOYMENT_REDEPLOY_FAILURE => 'The application you attempted to redeploy could not be redeployed.',
			NotificationContainer::TYPE_DEPLOYMENT_HEALTHCHECK_FAILURE => 'The application you attempted to health check could not be health checked.',
			NotificationContainer::TYPE_DEPLOYMENT_REMOVE_FAILURE => 'The application you attempted to remove could not be removed.',
			NotificationContainer::TYPE_DEPLOYMENT_ROLLBACK_FAILURE => 'The deployment you attempted to rollback failed to roll back.',
			NotificationContainer::TYPE_DEPLOYMENT_DEFINE_APP_FAILURE => 'Your new application could not be defined.',
			NotificationContainer::TYPE_SERVER_ADD_ERROR => 'The server you attempted to add to the cluster could not be added.',
			NotificationContainer::TYPE_SERVER_REMOVE_ERROR => 'The server you attempted to remove from the cluster could not be removed',
			NotificationContainer::TYPE_SERVER_ENABLE_ERROR => 'The server you attempted to enable could not be enabled.',
			NotificationContainer::TYPE_SERVER_DISABLE_ERROR => 'The server you attempted to enable could not be enabled.',
			NotificationContainer::TYPE_SERVER_FORCE_REMOVE_ERROR => 'The server you attempted to force remove could not be removed',
			NotificationContainer::TYPE_JOBQUEUE_HIGH_CONCURRENCY => 'The Job Queue daemon has reached a high concurrency level',
			NotificationContainer::TYPE_SC_SESSION_HANDLER_FILES => 'Session Clustering is disabled since the \'session.save_handler\' directive is not set to \'cluster\'',
			NotificationContainer::TYPE_SC_NO_BACKUP => 'Session Clustering failed to locate a backup server for sessions in server/s %s',
			NotificationContainer::TYPE_LICENSE_INVALID => 'The Zend Server license you are currently using has expired or is invalid',
			NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE => 'The Zend Server license you are currently using is about to expire in %s day(s)',
			NotificationContainer::TYPE_WEBSERVER_NOT_RESPONDING => 'The webserver is not responding',
			NotificationContainer::TYPE_MAX_SERVERS_IN_CLUSTER => 'The number of servers in the cluster has reached the limit defined in the license',
			NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45 => 'The Zend Server license you are currently using is about to expire in %s day(s)',
			NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15 => 'The Zend Server license you are currently using is about to expire in %s day(s)',
			NotificationContainer::TYPE_LICENSE_EXPIRES_TODAY => 'The Zend Server license you are currently using will expire today',
			NotificationContainer::TYPE_LICENSE_EXPIRES_TOMORROW => 'The Zend Server license you are currently using will expire tomorrow',
			NotificationContainer::TYPE_DATABASE_CONNECTION_RESTORED => 'Zend Server has restored the database connection for server \'%s\'',
			NotificationContainer::TYPE_SCD_STDBY_MODE => 'The \'session.save_handler\' directive is set to \'cluster\', but the Session Clustering daemon is inactive. PHP might not be able to track user sessions.',
			NotificationContainer::TYPE_ZSD_OFFLINE => 'The zend server daemon is offline.',
			NotificationContainer::TYPE_MAIL_SETTINGS_NOT_SET => 'Zend Server mail settings have not been defined yet. To receive email notifications from Zend Server, configure your email server on the Settings page.',
			NotificationContainer::TYPE_NO_SUPPORT => 'The Zend Server license you are currently using does not include support',
			NotificationContainer::TYPE_LIBRARY_UPDATE_AVAILABLE => 'There is a new library update available and ready for download',
			NotificationContainer::TYPE_PLUGIN_UPDATE_AVAILABLE => 'There is a new plugin update available and ready for download',
			NotificationContainer::TYPE_PLUGIN_BROKEN_DEPENDENCY => 'There is a plugin with broken dependencies',
			NotificationContainer::TYPE_SCD_SHUTDOWN_ERROR => 'The Session Clustering daemon (SCD) has failed to perform a graceful shutdown. Restart it to restore session data',
			NotificationContainer::TYPE_LIBRARY_SET_DEFAULT_ERROR => 'Zend Server failed to define the library version you selected as the default library version',
			NotificationContainer::TYPE_LIBRARY_DEPLOY_ERROR => 'The library package you attempted to deploy could not be deployed.',
			NotificationContainer::TYPE_LIBRARY_REMOVE_ERROR => 'The library you attempted to remove could not be removed.',
			NotificationContainer::TYPE_VHOST_MISSING => 'Zend Server failed to locate a virtual host.', 
			NotificationContainer::TYPE_VHOST_MODIFIED => 'Configuration changes to virtual host were detected.', 
			NotificationContainer::TYPE_VHOST_CONFIG_FAILED => 'Zend Server has identified a Web server configuration error. This may be the result of missing directories or a syntax error in the Web server\'s configuration file.',
	        NotificationContainer::TYPE_RSS_NEWS_AVAILABLE => '%s',
	);
	
	private $singleDescriptions = array(
			NotificationContainer::TYPE_PHP_EXT_DIRECTIVE_MISSMATCH => 'The values for an extension directive have been changed, and contradict corresponding values in the database.',
			NotificationContainer::TYPE_ZEND_EXT_DIRECTIVE_MISSMATCH => 'The values for a component directive have been changed, and contradict corresponding values in the database.',
			NotificationContainer::TYPE_SERVER_ADD_ERROR => 'The server you attempted to add to the cluster could not be added.',
	);
	
	/**
	 * @var ZemUtilsWrapper
	 */
	private $utilsWrapper;
	
	public function __invoke(NotificationContainer $notification) {
		$type = $notification->getType();
		$description = $notification->getDescription();
		
		if ($type >= NotificationContainer::TYPE_RSS_NEWS_AVAILABLE && $type <= NotificationContainer::TYPE_RSS_NEWS_AVAILABLE + 10) {
		    $type = NotificationContainer::TYPE_RSS_NEWS_AVAILABLE;
		}
		
		if (isset($this->descriptions[$type])) {
			$extraData = $notification->getExtraData();
			if (isset($extraData['title'])) {
			    unset($extraData['title']); 
			}
			if ($type == NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE || $type == NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_15 || $type == NotificationContainer::TYPE_LICENSE_ABOUT_TO_EXPIRE_45) {
				$numofDays = 0;
		
				$utilsWrapper = $this->getUtilsWrapper();
				if (! $utilsWrapper->isLicenseValid()) {
					return array('Non Valid License!', '');
				}
		
				$numofDays = $utilsWrapper->getLicenseExpirationDaysNum(true);
				$extraData = array($numofDays);
		
				if ($numofDays == 1) {
					$type = NotificationContainer::TYPE_LICENSE_EXPIRES_TOMORROW;
				} elseif ($numofDays == 0) {
					$type = NotificationContainer::TYPE_LICENSE_EXPIRES_TODAY;
				}
			}
		
			if (! is_array($extraData)) {
				$extraData = array($extraData);
			}
			
			// single server with specific message
			if (\Application\Module::isSingleServer() && isset($this->singleDescriptions[$type])) {
				$description = sprintf(_t($this->singleDescriptions[$type], $extraData));
			} else {
				$description = sprintf(_t($this->descriptions[$type], $extraData));
			}
		} else {
			$description = '';
		}
		return $description;
	}
	
	/**
	 * @return ZemUtilsWrapper
	 */
	public function getUtilsWrapper() {
		return $this->utilsWrapper;
	}

	/**
	 * @param \Configuration\License\ZemUtilsWrapper $utilsWrapper
	 */
	public function setUtilsWrapper($utilsWrapper) {
		$this->utilsWrapper = $utilsWrapper;
	}

}

