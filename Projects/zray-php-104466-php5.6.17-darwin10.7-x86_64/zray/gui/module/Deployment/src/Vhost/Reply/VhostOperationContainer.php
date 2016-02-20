<?php

namespace Vhost\Reply;

use Configuration\ReplyContainer;
use Zend\Json\Json;
use ZendServer\Log\Log;
class VhostOperationContainer extends ReplyContainer {
	
	const REPLY_ERROR = 0;
	const REPLY_SUCCESS = 1;
	const REPLY_CANT_VALIDATE = 2;
	const REPLY_NAME_CHANGED = 3;
	const REPLY_PORT_CHANGED = 4;
	const REPLY_CANT_ACCESS_FILE = 5;
	const REPLY_FILE_NOT_FOUND = 6;
	const REPLY_SSL_NOT_AVAILABLE = 7;
	
	public function __construct($reply) {
		parent::__construct($reply);
		if ($this->hasReply()) {
			$this->reply['REPLY_BODY'] = Json::decode($this->reply['REPLY_BODY']);
		}
	}
	
	public function getSuccessCode() {
		$reply = $this->getReply();
		return isset($reply->success) ? $reply->success : false;
	}
	
	public function isSuccess() {
		$reply = $this->getReply();
		return isset($reply->success) && (in_array($reply->success, array(self::REPLY_SUCCESS, self::REPLY_CANT_VALIDATE, self::REPLY_CANT_ACCESS_FILE))) ? true : false;
	}
	
	public function getMessage() {
		$reply = $this->getReply();
		return isset($reply->message) ? $reply->message : '';
	}
}

