<?php
namespace Messages;

use Zend\XmlRpc\Value\Integer,
Messages\Db\MessageMapper,
ZendServer\Log\Log;
use Zend\Json\Json;
use Zend\Json\Exception\RuntimeException;

class MessageContainer {

	
	
	/**
	 * @var array
	 */
	protected $message;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $message) {
		$this->message = $message;
	}
	
	public function toArray() {
		return $this->message;
	}
	
	/**
	 * @return integer
	 */
	public function getMessageId() {
		return (integer) $this->message['MSG_ID'];
	}

	
	/**
	 * @return integer
	 */
	public function getMessageNodeId() {
		return (integer) $this->message['NODE_ID'];	
	}
	
	/**
	 * @return string
	 */
	public function getMessageContext() {
		if (isset($this->message['CONTEXT']) && is_numeric($this->message['CONTEXT'])) {
			return (integer) $this->message['CONTEXT'];
		}
		
		return '';		
	}

	/**
	 * @return string
	 */
	public function getMessageKey() {		
		return $this->message['MSG_KEY'];
	}

	/**
	 * @return string
	 */
	public function getMessageType() {
		if (isset($this->message['TYPE']) && is_numeric($this->message['TYPE'])) {
			return (integer) $this->message['TYPE'];
		}
		
		return '';
	}
		
	/**
	 * @return string
	 */
	public function getMessageDetails() {
		if (isset($this->message['DETAILS']) && $this->message['DETAILS']) {
			try {
				return (array) Json::decode($this->message['DETAILS'], true);
			} catch (RuntimeException $ex) {
				Log::warn("Could not decode message details: {$ex->getMessage()}");
				return array();
			}
		}
		
		return '';
	}	

	/**
	 * @return string
	 */
	public function getMessageSeverity() {
		if (isset($this->message['MSG_SEVERITY']) && is_numeric($this->message['MSG_SEVERITY'])) {
			return (integer) $this->message['MSG_SEVERITY'];
		}
		
		return '';
	}

	/**
	 * @return boolean
	 */
	public function isInfo() {		
		return $this->getMessageSeverity() === MessageMapper::SEVERITY_INFO;
	}
	
	/**
	 * @return boolean
	 */
	public function isWarning() {
		return $this->getMessageSeverity() === MessageMapper::SEVERITY_WARNING;
	}	
	
	/**
	 * @return boolean
	 */
	public function isError() {
		return $this->getMessageSeverity() === MessageMapper::SEVERITY_ERROR;
	}	

	/**
	 * @return boolean
	 */
	public function isExtension() {
		return $this->getMessageContext() === MessageMapper::CONTEXT_EXTENSION;
	}	

	/**
	 * @return boolean
	 */
	public function isExtensionEnabled() {
		return $this->getMessageType() === MessageMapper::TYPE_EXTENSION_ENABLED;
	}
	
	/**
	 * @return boolean
	 */
	public function isDirective() {
		return $this->getMessageContext() === MessageMapper::CONTEXT_DIRECTIVE;
	}

	/**
	 * @return boolean
	 */
	public function isDaemon() {
		return $this->getMessageContext() === MessageMapper::CONTEXT_DAEMON;
	}		
}