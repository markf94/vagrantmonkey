<?php
namespace Messages\View\Helper;

use Messages\View\Helper\ServerErrorMessageAbstract;

class ServerErrorMessageJson extends ServerErrorMessageAbstract {	
	/**
	 * @param array|Set $messagesList
	 * @return string
	 */
	public function __invoke($messagesList) {
		$allMessages = $messages = array();
		foreach ($messagesList as $message) {
			$messageWithSeverity = $this->getMessageText($message); // such as array('Info' => 'PHP Requires a restart')
			if (!isset($allMessages[current($messageWithSeverity)])) {  // we might for instance have multiple restart messages - we don't want duplicates
				$allMessages[current($messageWithSeverity)] = true;
				$messages[] = $messageWithSeverity;
			}			
		}
		
		return $messages;
	}

	/**
	 * @param string $message
	 * @return string
	 */
	protected function createInfoMessage($message) {
		return array(self::JSON_INFO => $message);
	}
			
	/**
	 * @param string $message
	 * @return string
	 */
	protected function createWarningMessage($message) {
		return array(self::JSON_WARNING => $message);
	}
	
	/**
	 * @param string $message
	 * @return string
	 */
	protected function createErrorMessage($message) {
		return array(self::JSON_ERROR => $message);
	}
}