<?php

namespace MonitorRules\Action;

use Notifications\Db\NotificationsMapper;
use ZendServer\Log\Log;
use Application\Module as appModule;
use Notifications\NotificationContainer;
use MonitorRules\Action;

class MailSettingsListener {
	/**
	 * @var NotificationsMapper
	 */
	private $notificationsMapper;
	
	public function checkMailSettings($event) {
		if ($event->getParam('action_type') == Action::TYPE_MAIL) {
			if ((! appModule::config('mail', 'mail_host'))
				|| (! appModule::config('mail', 'mail_port'))
				|| (! appModule::config('mail', 'authentication_method'))) {
				
				$this->getNotificationsMapper()->insertNotification(NotificationContainer::TYPE_MAIL_SETTINGS_NOT_SET);
			}
		}
	}
	/**
	 * @return NotificationsMapper
	 */
	public function getNotificationsMapper() {
		return $this->notificationsMapper;
	}

	/**
	 * @param \Notifications\Db\NotificationsMapper $notificationsMapper
	 */
	public function setNotificationsMapper($notificationsMapper) {
		$this->notificationsMapper = $notificationsMapper;
		return $this;
	}

}

