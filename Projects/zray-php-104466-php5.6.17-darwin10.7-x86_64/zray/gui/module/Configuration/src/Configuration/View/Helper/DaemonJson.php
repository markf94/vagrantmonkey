<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Configuration\ExtensionContainer,
	Messages\View\Helper\ServerErrorMessageJson;

/**
 * @uses \ZendServer\View\Helper\PhpErrorType
 */
class DaemonJson extends AbstractHelper {
		
	/**
	 * @param \Configuration\DaemonContainer $daemon
	 * @return string
	 */
	public function __invoke($daemon) {
		
		// @todo escaping values
		$daemonArray = array(
				'name' => $daemon->getName(),
				'status' => $daemon->getStatus(),
				'restartRequired' => $daemon->getRestartRequired(),
				'shortDescription' => $daemon->getShortDescription(),
				'longDescription' => $daemon->getLongDescription(),
				'messageList' =>  $this->getMessageList($daemon)
				);
				
		return $this->getView()->json($daemonArray, array());
	}

	protected function getMessageList($container) {
		$extensionErrorMessageJson = new ExtensionErrorMessageJson();
		$extensionErrorMessageJson->setView($this->getView());
		return $extensionErrorMessageJson->getMessageList($container->getMessageList());
	}
	
	
}

class ExtensionErrorMessageJson extends ServerErrorMessageJson {
	
	protected $nodeName;
	
	/**
	 * @param array $serversMessageList - where nodeName are they keys
	 * @return array
	 */
	public function getMessageList($serversMessageList) {
		$messageList = array();
	
		foreach($serversMessageList as $nodeName=>$messages) {
			$this->node_name = $nodeName;
			foreach ($messages as $message) { /* @var $message MessageContainer */			
				$messageList[] = $this->getMessageText($message);
			}
		}
	
		return $messageList;
	}

	/**
	 * @param string $message
	 * @return string
	 */
	protected function createInfoMessage($message) {
		return array('type'=>self::JSON_INFO, 'message'=>$message, 'nodeName'=>$this->node_name);
	}
			
	/**
	 * @param string $message
	 * @return string
	 */
	protected function createWarningMessage($message) {
		return array('type'=>self::JSON_WARNING, 'message'=>$message, 'nodeName'=>$this->node_name);
	}
	
	/**
	 * @param string $message
	 * @return string
	 */
	protected function createErrorMessage($message) {
		return array('type'=>self::JSON_ERROR, 'message'=>$message, 'nodeName'=>$this->node_name);
	}

}

