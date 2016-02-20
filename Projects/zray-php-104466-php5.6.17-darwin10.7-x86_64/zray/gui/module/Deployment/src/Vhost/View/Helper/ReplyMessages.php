<?php

namespace Vhost\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Vhost\Reply\VhostOperationContainer;

class ReplyMessages extends AbstractHelper {
	
	public function __invoke() {
		return $this;
	}
	
	/**
	 * @param VhostOperationContainer $reply
	 * @return string
	 */
	public function success(VhostOperationContainer $reply) {
		switch ($reply->getSuccessCode()) {
			case VhostOperationContainer::REPLY_NAME_CHANGED:
			case VhostOperationContainer::REPLY_PORT_CHANGED:
			case VhostOperationContainer::REPLY_ERROR:
				$success = 'failure';
				break;
			case VhostOperationContainer::REPLY_SUCCESS:
				$success = 'success';
				break;
			case VhostOperationContainer::REPLY_CANT_VALIDATE:
				$success = 'no-validation';
				break;
			case VhostOperationContainer::REPLY_CANT_ACCESS_FILE:
				$success = 'file-no-access';
				break;
			case VhostOperationContainer::REPLY_FILE_NOT_FOUND:
				$success = 'file-not-found';
				break;
			case VhostOperationContainer::REPLY_SSL_NOT_AVAILABLE:
				$success = 'ssl-not-available';
				break;
			default:
				$success = 'unknown';
		}
		return $success;
	}
	
	/**
	 * @param VhostOperationContainer $reply
	 * @return string
	 */
	public function message(VhostOperationContainer $reply) {
		switch ($reply->getSuccessCode()) {
			case VhostOperationContainer::REPLY_NAME_CHANGED:
				$message = _t('Vhost name does not correspond with the information provided in the configuration template'); 
				break;
			case VhostOperationContainer::REPLY_PORT_CHANGED:
				$message = _t('Vhost port does not correspond with the information provided in the configuration template'); 
				break;
			case VhostOperationContainer::REPLY_ERROR:
				$message = _t('Vhost configuration parsing has failed, an error was detected: %s', array($reply->getMessage())); 
				break;
			case VhostOperationContainer::REPLY_CANT_VALIDATE:
				$message = _t('Template validation is not available on this system. Zend Server is unable to determine if there is any risk in using the configuration you provided.<br />Press \'Continue\' to submit the unvalidated template anyway.'); 
				break;
			case VhostOperationContainer::REPLY_CANT_ACCESS_FILE:
				$message = _t('Zend Server could not find the file \'%s\'. This file is required for this virtual host to operate correctly. This could be because system permissions are too restrictive.<br />If this file is inplace you may press \'Continue\' to proceed with an unvalidated template anyway.', array($reply->getMessage())); 
				break;
			case VhostOperationContainer::REPLY_FILE_NOT_FOUND:
				$message = _t('Zend Server could not find the file \'%s\'. This file is required for this virtual host to operate correctly.', array($reply->getMessage())); 
				break;
			case VhostOperationContainer::REPLY_SSL_NOT_AVAILABLE:
				$message = _t('Web server does not have SSL enabled, SSL virtual hosts cannot be created'); 
				break;
			case VhostOperationContainer::REPLY_SUCCESS:
			default:
				$message = '';
		}
		return $message;
	}
	
	public function rawMessage(VhostOperationContainer $reply) {
		return $reply->getMessage();
	}
}

