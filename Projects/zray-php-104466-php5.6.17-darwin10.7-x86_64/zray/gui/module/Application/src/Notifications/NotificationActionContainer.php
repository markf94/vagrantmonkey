<?php
namespace Notifications;

use ZendServer\Log\Log;

class NotificationActionContainer {

	/**
	 * @var array
	 */
	protected $notification;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $notification) {
		$this->notification = $notification;
	}
	
	public function toArray() {
		return $this->notification;
	}
	
	/**
	 * @return integer
	 */
	public function getType() {
		return (integer) $this->notification['TYPE'];
	}
	
	/**
	 * @return integer
	 */
	public function getName() {
		return $this->notification['NAME'];
	}
	
	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->notification['EMAIL'];
	}
	
	/**
	 * @return string
	 */
	public function getCustomAction() {
		return $this->notification['CUSTOM_ACTION'];
	}	
}