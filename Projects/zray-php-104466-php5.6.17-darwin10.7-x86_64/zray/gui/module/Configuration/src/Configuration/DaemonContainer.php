<?php
namespace Configuration;
use Messages\Db\MessageMapper;

use ZendServer\Log\Log;

use ZendServer\Exception as ZSException,
ZendServer\Set,
Messages\MessageContainer;

class DaemonContainer {
	
	/**
	 * @var array
	 */
	protected $data;
	
	/**
	 * @param array $extension
	 */
	public function __construct(array $data) {
		$this->data = $data;
	}
	
	public function toArray() {
		return $this->data;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return isset($this->data['name']) ? $this->data['name'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getStatus() {
		if (($status = $this->getErrorStatus()) !== false) {
			return $this->data['status'] = $status;
		}
		
		if (isset($this->data['status']) && $this->data['status'] === 'None') { // actually, a non-existent daemon
			return $this->data['status'] = 'None';
		}
		
		return $this->data['status'] = 'OK';
	}
	
	/**
	 * @return string
	 */
	public function getShortDescription() {
		return isset($this->data['shortDescription']) ? trim($this->data['shortDescription']) : '';
	}
	
	/**
	 * @return string
	 */
	public function getLongDescription() {
		return isset($this->data['longDescription']) ? trim($this->data['longDescription']) : '';
	}
	
	/**
	 * @return string
	 */
	public function getMessageList() {
		if (!isset($this->data['MessageList'])) {
			return array();
		}
		
		$messageList = array();
		foreach ($this->data['MessageList'] as $nodeName => $msgs) {
			$messageList[$nodeName] = new Set($msgs, '\Messages\MessageContainer');
		}
		
		return $messageList;
	}
	
	/**
	 * @return string
	 */
	public function setMessageList($messageList, $nodeName) {		
		$this->data['MessageList'][$nodeName][] = $messageList;
		return $this;
	}	

	public function getRestartRequired() {
		if ($this->hasRestartMessages()) { // @todo - if the only messages are errors, should we actually return true?
			return 'true';
		}
	
		return 'false';
	}

	/**
	 * @return boolean
	 */
	protected function hasRestartMessages() {
		if (!isset($this->data['MessageList'])) {
			return false;
		}
		foreach ($this->data['MessageList'] as $nodeName => $msgs) {
			foreach ($msgs as $message) {
				if (in_array($message['TYPE'],
						array(
								MessageMapper::TYPE_EXTENSION_ENABLED,
								MessageMapper::TYPE_DIRECTIVE_MODIFIED,
								MessageMapper::TYPE_EXTENSION_DISABLED))) {
					
					return true;
				}
			}
		}
		return false;
	}
	
	protected function getErrorStatus() {
		$found = false;
		foreach ($this->getMessageList() as $nodeId => $messages) {
			foreach ($messages as $message) { /* @var $message MessageContainer */
				if ($message->isError()) {
					return 'Error'; // if error found, then we can stop searching
				}elseif ($message->isWarning()) {
					$found = 'Warning';	// need to finish the search, as to ensure there're aren't any errors
				}
			}
		}

		return $found;
	}	
}