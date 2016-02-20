<?php
namespace Configuration;
use ZendServer\Exception as ZSException;

class ReplyContainer {
	/**
	 * @var array
	 */
	protected $reply;
	
	/**
	 * @param array $reply
	 */
	public function __construct(array $reply) {
		$this->reply = $reply;
	}
	
	public function toArray() {
		return $this->reply;
	}
	
	/**
	 * @return string
	 */
	public function getReply() {
		return $this->reply['REPLY_BODY'];
	}
	
	/**
	 * @return boolean
	 */
	public function hasReply() {
		return isset($this->reply['REPLY_BODY']);
	}
	
}