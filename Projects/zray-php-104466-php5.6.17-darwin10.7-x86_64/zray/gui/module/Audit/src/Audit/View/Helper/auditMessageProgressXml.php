<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Audit\ProgressContainer;

class auditMessageProgressXml extends AbstractHelper {

	public function __invoke(ProgressContainer $messageProgress) {
		
		return <<<XML
			
<auditProgress>				
		       <progressId>{$messageProgress->getId()}</progressId>
		       <auditId>{$messageProgress->getAuditId()}</auditId>
		       <serverId>{$messageProgress->getNodeId()}</serverId>		       
		       <serverIp>{$messageProgress->getNodeIp()}</serverIp>			       
		       <serverName><![CDATA[{$this->getView()->escapeHtml($messageProgress->getNodeName())}]]></serverName>	
		       <creationTime>{$this->getView()->webapidate($messageProgress->getCreationTime())}</creationTime>		       		       		       
		       <creationTimeTimestamp>{$messageProgress->getCreationTime()}</creationTimeTimestamp>
		       <progress><![CDATA[{$this->getView()->escapeHtml($messageProgress->getProgress())}]]></progress>   		
		       <extraData>{$this->parseExtraData($messageProgress->getextraData())}</extraData>       		
     </auditProgress>
XML;
	}
	
	protected function parseExtraData($extraData) {
		$messages = '';
		
		foreach ($extraData as $paramater) {
			$messages .= "<extraDataMessage>";
			foreach ($paramater as $key=>$value) {
				$messages .= "<parameter>";
				$messages .= "<name>{$key}</name>";
				$messages .= "<value>{$value}</value>";
				$messages .= "</parameter>";
			}
			$messages .= "</extraDataMessage>";
		}
		
		return $messages;
	}
}

