<?php
namespace Messages\View\Helper;

use Messages\View\Helper\ServerErrorMessageAbstract;
use Messages\MessageContainer;
use ZendServer\Log\Log;

class ServerErrorMessageXml extends ServerErrorMessageAbstract {	
	/**
	 * @param array $messagesList
	 * @return string
	 */
	public function __invoke($messagesList) {
		$allMessages = array();
		$messages = '';
		foreach ($messagesList as $message) {
			$messageString = $this->getMessageText($message); // such as <info><![CDATA[PHP Requires a restart]]></info>		
			if (!isset($allMessages[$messageString])) {  // we might for instance have multiple restart messages - we don't want duplicates
				$allMessages[$messageString] = true;
				$messages .= $this->getMessageText($message) . "\n";
			}
		}
		
		return $messages;
	}

	protected function getMessageText(MessageContainer $message) {
		try {
			$messageArray = $this->extractMessage($message);
			return $messageArray;
		} 
		catch (\ZendServer\Exception $e) {
			Log::logException("Error in extracting message data", $e);
			return $this->createErrorMessage("Unknown Error");
		}
	}
	
	/**
	 * @param string $message
	 * @return string
	 */
	protected function createInfoMessage($message) {
		return $this->createMessage($message, self::XML_INFO);
	}

	/**
	 * @param string $message
	 * @return string
	 */
	protected function createWarningMessage($message) {
		return $this->createMessage($message, self::XML_WARNING);
	}
	
	/**
	 * @param string $message
	 * @return string
	 */
	protected function createErrorMessage($message) {
		return $this->createMessage($message, self::XML_ERROR);
	}

	/**
	 * @param string $message
	 * @param string $severity
	 * @return string
	 */
	protected function createMessage($message, $severity) {
		return "<{$severity}><![CDATA[{$message}]]></{$severity}>";
	}	
	
}

