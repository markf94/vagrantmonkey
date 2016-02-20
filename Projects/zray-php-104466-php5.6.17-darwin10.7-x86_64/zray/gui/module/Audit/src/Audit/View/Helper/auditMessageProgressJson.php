<?php
namespace Audit\View\Helper;

use ZendServer\Log\Log;

use Zend\View\Helper\AbstractHelper,
	Audit\ProgressContainer;

class auditMessageProgressJson extends AbstractHelper {

	public function __invoke(ProgressContainer $messageProgress) {

		$auditProgressMessage = array(
				'progressId'	=> $messageProgress->getId(),
				'auditId'		=> $messageProgress->getAuditId(),
				'serverId'		=> $messageProgress->getNodeId(),
				'serverIp'		=> $messageProgress->getNodeIp(),
				'serverName' 	=> $messageProgress->getNodeName(),
				'creationTime' 	=> $this->getView()->webapidate($messageProgress->getCreationTime()),
				'creationTimeTimestamp' => $messageProgress->getCreationTime(),
				'progress' 		=> $messageProgress->getProgress(),
				'extraData' 	=> $this->parseExtraData($messageProgress->getExtraData()),
		);
		
		return $this->getView()->json($auditProgressMessage, array());
	}
	
	protected function parseExtraData($extraData) {
		$messages = array();
		if (! $extraData || (! is_array($extraData))) { // covering also the case where we get NULL
			return $messages;
		}		

		foreach ($extraData as $idx=>$paramater) {
			if (is_array($paramater) || is_object($paramater)) {
				foreach ($paramater as $key=>$value) {
					$messages[$idx][] = array('name' => $key, 'value' => $value);
				}
			}
		}
		return $messages;
	}
}