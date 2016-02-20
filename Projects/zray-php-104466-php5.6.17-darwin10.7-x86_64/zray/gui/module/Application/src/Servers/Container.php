<?php
namespace Servers;

use Servers\View\Helper\ServerStatus;

class Container {
	/**
	 * @var array
	 */
	protected $server;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $server, $key=null) {
		$this->server = $server;
	}
	
	public function toArray() {
		return $this->server;
	}	
	
	/**
	 * @return integer
	 */
	public function getNodeId() {
		return (isset($this->server['NODE_ID']) ? $this->server['NODE_ID'] : '');
	}
	
	/**
	 * 
	 * @param int $id
	 */
	public function setNodeId($id) {
		$this->server['NODE_ID'] = $id;
	}
	
	/**
	 * @return string
	 */
	public function getNodeName() {
		return (isset($this->server['NODE_NAME']) ? trim($this->server['NODE_NAME']) : '');
	}

	/**
	 * @param string $name
	 */
	public function setNodeName($name) {
		$this->server['NODE_NAME'] = $name;
	}
		
	/**
	 * @return string
	 */
	public function getNodeIp() {
		return (isset($this->server['NODE_IP']) ? $this->server['NODE_IP'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getStatusCode() {
		return (isset($this->server['STATUS_CODE']) ? $this->server['STATUS_CODE'] : '');
	}
	
	public function setStatusCode($code) {
		$this->server['STATUS_CODE'] = $code;
	}
	
	public function isStatusError() {		
		return (integer) $this->getStatusCode() === ServerStatus::STATUS_ERROR;
	}

	public function isDisabled() {
		return (integer) $this->getStatusCode() === ServerStatus::STATUS_DISABLED;
	}

	public function isPendingRestart() {
		return (integer) $this->getStatusCode() === ServerStatus::STATUS_RESTART_REQUIRED;
	}
	
	public function isPendingRemoval() {
		return (integer) $this->getStatusCode() === ServerStatus::STATUS_SERVER_PENDING_REMOVAL;
	}
	
	/**
	 * @return string
	 */
	public function getReasonString() {
		return (isset($this->server['REASON_STRING']) ? $this->server['REASON_STRING'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getLastUpdate() {
		return (isset($this->server['LAST_UPDATED']) ? $this->server['LAST_UPDATED'] : '');
	}

	/**
	 * @return array
	 */
	public function getMessageList() {
		return (isset($this->server['MESSAGES']) ? $this->server['MESSAGES'] : array());
	}
	
	/**
	 * @return boolean
	 */
	public function isDebugModeEnabled() {
		return $this->server['SERVER_FLAGS'] == 1;
	}
		
	/**
	 * @return array
	 */
	public function setMessageList($messages) {
		$this->server['MESSAGES'] = $messages;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getIsDeleted() {
		return (isset($this->server['IS_DELETED']) ? $this->server['IS_DELETED'] : 0);
	}
	
	public function isDeleted() {
		return (integer) $this->getIsDeleted() === 1;
	}
}