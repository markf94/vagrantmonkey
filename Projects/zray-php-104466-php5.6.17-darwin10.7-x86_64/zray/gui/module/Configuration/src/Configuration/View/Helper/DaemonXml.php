<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper,
Configuration\DaemonContainer,
Messages\View\Helper\ServerErrorMessageXml;
use ZendServer\Log\Log;
use Messages\MessageContainer;

class DaemonXml extends AbstractHelper {
	/**
	 * @param \Configuration\DaemonContainer $daemon
	 * @return string
	 */
	public function __invoke($daemon) {
	
		return <<<XML

<daemon>
	<name>{$this->getView()->escapeHtml($daemon->getName())}</name>
	<status>{$daemon->getStatus()}</status>
	<restartRequired>{$daemon->getRestartRequired()}</restartRequired>
	<shortDescription><![CDATA[{$daemon->getShortDescription()}]]></shortDescription>
	<longDescription><![CDATA[{$daemon->getLongDescription()}]]></longDescription>
	<messageList>{$this->getMessageList($daemon)}</messageList>
</daemon>


XML;
	}	

	/**
	 *
	 * @param DaemonContainer $container
	 */
	protected function getMessageList($container) {
		$extensionErrorMessageXml = new ExtensionErrorMessageXml();
		$extensionErrorMessageXml->setView($this->getView());
		return $extensionErrorMessageXml->getMessageList($container->getMessageList());
	}	
}


class ExtensionErrorMessageXml extends ServerErrorMessageXml {

	protected $nodeName;

	/**
	 * @param array $serversMessageList - where nodeName are they keys
	 * @return string
	 */
	public function getMessageList($serversMessageList) {
		$messagesXml = '';

		foreach($serversMessageList as $nodeName=>$messages) {
			$this->node_name = $nodeName;
			foreach ($messages as $message) { /* @var $message MessageContainer */
				Log::debug('Message ' . $this->getMessageText($message));
				$messagesXml .= $this->getMessageText($message) . "\n";
			}
		}

		return $messagesXml;
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
	 * @param string $severity
	 * @return string
	 */
	protected function createMessage($message, $severity) {
		$nodeName = ($this->node_name) ? "Node {$this->node_name}: " : "";
		return "<{$severity}><![CDATA[{$nodeName}{$message}]]></{$severity}>";
	}

}