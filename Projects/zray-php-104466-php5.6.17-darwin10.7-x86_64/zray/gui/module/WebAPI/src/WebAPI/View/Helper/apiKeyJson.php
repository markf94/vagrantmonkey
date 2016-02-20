<?php
namespace WebAPI\View\Helper;

use Zend\View\Helper\AbstractHelper,
	WebAPI\Db\ApiKeyContainer;

class apiKeyJson extends AbstractHelper {

	public function __invoke(ApiKeyContainer $apiKey) {

		$apiKeyArray = array(
				'id'						=> $apiKey->getId(),
				'name'						=> $apiKey->getName(),
				'hash'						=> $apiKey->getHash(),
				'username'					=> $apiKey->getUsername(),
				'creationTime' 				=> $this->getView()->webapidate($apiKey->getCreationTime()),
				'creationTimeTimestamp' 	=> $apiKey->getCreationTime(),
		);
		
		return $this->getView()->json($apiKeyArray, array());
	}
}