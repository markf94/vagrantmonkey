<?php

namespace Notifications\Db;

use ZendServer\Log\Log,
ZendServer\Set,
Configuration\MapperAbstract;

class NotificationsActionsMapper extends MapperAbstract {
	
	protected $setClass = '\Notifications\NotificationActionContainer';

	/**
	 * @return Set
	 */
	public function findAll() {
		return $this->select();
	}

	/**
	 * @param string $name
	 * @return \Notifications\NotificationActionContainer
	 */
	public function getNotification($name) {
		return $this->select('NAME = "' . $name . '"');
	}

	/**
	 * @return Set[MessageContainer]
	 */
	public function updateTypes($email, $customAction) {
		$this->tableGateway->update(array('EMAIL' => $email, 'CUSTOM_ACTION' => $customAction), "TYPE >= 0");
	}	
	
	/**
	 * @return Set[MessageContainer]
	 */
	public function updateTypesEmail($email) {
		$this->tableGateway->update(array('EMAIL' => $email), "TYPE >= 0");
	}
	
	/**
	 * @return Set[MessageContainer]
	 */
	public function updateTypesCustomAction($customAction) {
		$this->tableGateway->update(array('CUSTOM_ACTION' => $customAction), "TYPE >= 0");
	}
}