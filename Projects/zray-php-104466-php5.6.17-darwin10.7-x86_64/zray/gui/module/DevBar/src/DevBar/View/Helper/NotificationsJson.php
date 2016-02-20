<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Notifications\NotificationContainer;

class NotificationsJson extends AbstractHelper {
	
	public function __invoke($notifications) {
		$entries = array();
		foreach ($notifications as $notification) {
			$entries[] = $this->notification($notification);
		}

		return $this->getView()->json($entries);
	}
	
	private function notification(NotificationContainer $notification) {
		return array(
			"id" => $notification->getId(),
			"severity" => $notification->getSeverity(),
			"creationTime" => $notification->getCreationTime(),
			"type" => $notification->getType(),
			"name" => $notification->getName(),
			"repeats" => $notification->getRepeats(),
			"title" => $notification->getTitle(),
			"description" => $this->getView()->notificationDescription($notification),
			"url" => $notification->getUrl(),
		);
	}
}