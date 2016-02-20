<?php
namespace Configuration\View\Helper;

use Configuration\View\Helper\DaemonJson,
	Configuration\ExtensionContainer;

/**
 * @uses \ZendServer\View\Helper\PhpErrorType
 */
class ExtensionJson extends DaemonJson {
		
	/**
	 * @param \Configuration\ExtensionContainer $extension
	 * @return string
	 */
	public function __invoke($extension) {
		$status = $extension->getStatus();
		$messageList = $this->getMessageList($extension);
		
		if (! $this->getView()->isAllowed('data:components', $extension->getName())) {
			$status = 'Unsupported';
			$messageList = array(array('message' => 'Unsupported in current edition', 'nodeName' => '', 'type' => 'Info'));
		}
		
		// @todo escaping values
		$extensionArray = array(
				'name' => $extension->getName(),
				'version' => $extension->getVersion(),
				'type' => $extension->getType(),				
				'status' => $status,
				'loaded' => $extension->getIsLoaded(),
				'installed' => $extension->getIsInstalled(),
				'builtIn' => $extension->getBuiltIn(),
				'dummy' => $extension->getDummy(),
				'restartRequired' => $extension->getRestartRequired(),
				'shortDescription' =>  $extension->getShortDescription(),
				'longDescription' =>  $extension->getLongDescription(),
				'messageList' =>  $messageList
				);
				
		return $this->getView()->json($extensionArray, array());
	}	
}