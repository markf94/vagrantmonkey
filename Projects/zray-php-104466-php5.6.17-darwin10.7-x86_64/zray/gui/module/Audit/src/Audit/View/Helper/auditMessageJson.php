<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Audit\Container;

class auditMessageJson extends AbstractHelper {

	public function __invoke(Container $message) {

		$auditMessages = array(
				'id'				=> $message->getAuditId(),
				'username'			=> $message->getUsername(),
				'requestInterface'	=> $message->getRequestInterface(),
				'remoteAddr'		=> $message->getRemoteAddr(),
				'auditType' 		=> $message->getAuditType(),
				'auditTypeTranslated' => $this->getView()->auditType($message->getAuditType()),
				'baseUrl' 			=> $message->getBaseUrl(),				
				'creationTime' 		=> $this->getView()->webapidate($message->getCreationTime()),
				'creationTimeTimestamp'	=> $message->getCreationTime(),
				'extraData' 		=> $this->parseExtraData($message->getExtraData()),
				'outcome' 			=> $message->getOutcome(),
		);
		
		return $this->getView()->json($auditMessages, array());
	}
	
	protected function parseExtraData($extraData) {
		$messages = array();

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