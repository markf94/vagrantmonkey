<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Audit\Container;

class auditMessageXml extends AbstractHelper {

	public function __invoke(Container $message) {
		return <<<XML
			
<auditMessage>
		       <id>{$message->getAuditId()}</id>
		       <username><![CDATA[{$this->getView()->escapeHtml($message->getUsername())}]]></username>
		       <requestInterface><![CDATA[{$message->getRequestInterface()}]]></requestInterface>
 		       <remoteAddr><![CDATA[{$message->getRemoteAddr()}]]></remoteAddr>      		
		       <auditType>{$message->getAuditType()}</auditType>    		
		       <auditTypeTranslated><![CDATA[{$this->getView()->auditType($message->getAuditType())}]]></auditTypeTranslated>    		
		       <baseUrl><![CDATA[{$this->getView()->escapeHtml($message->getbaseUrl())}]]></baseUrl>       		
		       <creationTime>{$this->getView()->webapidate($message->getCreationTime())}</creationTime>     		
		       <creationTimeTimestamp>{$message->getCreationTime()}</creationTimeTimestamp>
		       <extraData>{$this->parseExtraData($message->getextraData())}</extraData>       		
		       <outcome>{$this->getView()->escapeHtml($message->getOutcome())}</outcome> 
</auditMessage>
XML;
		
	}
	
	protected function parseExtraData($extraData) {
		$messages = '';
		
		foreach ($extraData as $paramater) {
			$messages .= "<extraDataMessage>";
			if (is_array($paramater) || is_object($paramater)) {
				foreach ($paramater as $key=>$value) {
					$messages .= "<parameter>";
					$messages .= "<name>{$key}</name>";
					$messages .= "<value><![CDATA[{$this->parseValue($value)}]]></value>";
					$messages .= "</parameter>";
				}
			}
			$messages .= "</extraDataMessage>";
		}
		
		return $messages;
	}
	
	protected function parseValue($value) {
		$valueStr='';
		if (!is_array($value) && !is_object($value)) {
			$value = array($value);
		}
		
		foreach($value as $singleValue) {
			$valueStr .= $this->getView()->escapeHtml($singleValue) . ',';
		}
		
		return rtrim($valueStr, ',');
	}
}

