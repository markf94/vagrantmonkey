<?php
namespace WebAPI\View\Helper;

use Zend\View\Helper\AbstractHelper,
	WebAPI\Db\ApiKeyContainer;


class apiKeyXml extends AbstractHelper {

	public function __invoke(ApiKeyContainer $apiKey, $keyname=null) {
		if ($keyname === null) $keyname = 'apiKey';
		
		return <<<XML
			
<{$keyname}>
		    <id>{$apiKey->getId()}</id>
		    <username>{$apiKey->getUsername()}</username>
		    <name><![CDATA[{$apiKey->getName()}]]></name>
		    <hash><![CDATA[{$apiKey->getHash()}]]></hash>
			<creationTime>{$this->getView()->webapidate($apiKey->getCreationTime())}</creationTime>
			<creationTimeTimestamp>{$apiKey->getCreationTime()}</creationTimeTimestamp>
</{$keyname}>
XML;
	}

}

