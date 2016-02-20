<?php
namespace Servers\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Servers\Container;

class serverInfoXml extends AbstractHelper {
	/**
	 * @param integer $severity
	 * @return string
	 */
	public function __invoke(Container $server) {
		
		$status = $this->getView()->ServerStatus($server->getStatusCode());
		$messages = $this->getView()->serverErrorMessageXml($server->getMessageList());
		$debugModeEnabled = $server->isDebugModeEnabled() ? 'true' : 'false';
		
		return <<<XML
			
<serverInfo>
		       <id>{$server->getNodeId()}</id>
		       <name><![CDATA[{$server->getNodeName()}]]></name>
		       <address>{$server->getNodeIp()}</address>
		       <status>{$status}</status>
		       <messageList>{$messages}</messageList>
		       <debugModeEnabled>{$debugModeEnabled}</debugModeEnabled>
     </serverInfo>
XML;
	}
}

