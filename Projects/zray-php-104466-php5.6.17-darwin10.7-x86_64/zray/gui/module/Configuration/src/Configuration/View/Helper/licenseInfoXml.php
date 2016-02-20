<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Configuration\License\License;

class licenseInfoXml extends AbstractHelper {
	
	/**
	 * @param \Configuration\License\License $container
	 * @param integer $numberOfNodes
	 * @return string
	 */
	public function __invoke(License $container, $numberOfNodes) {
		$status 	= $this->getStatus($container, $numberOfNodes);
		$order 		= $container->getUserName();
		$expiration =  $this->getView()->webapiDate($container->getExpiration());// TODO - V1.2: Sun, 08 Sep 2019 21:00:00 GMT
		$nodeLimit 	= $container->getNumOfServers();
		$edition 	= $container->getEdition();
		$evaluation = $container->isEvaluation() ? 'true' : 'false';
		
		return <<<XML
		
				<status>{$status}</status>
				<orderNumber>{$order}</orderNumber>
				<validUntil>{$expiration}</validUntil>
				<nodeLimit>{$nodeLimit}</nodeLimit>
			    <edition>{$edition}</edition>
			    <evaluation>{$evaluation}</evaluation>
XML;
	}
	
	/**
	 * @param \Configuration\License\License $container
	 * @param integer $numberOfNodes
	 * @return string
	 */	
	protected function getStatus($container, $numberOfNodes) {
		if (! $container->isSignatureValid()) {
			return 'invalid';
		}
		
		if ($container->isLicenseExpired()) {
			return 'expired';
		}
		
		if ($container->getNumOfServers() < $numberOfNodes) {
			return 'serverLimitExceeded';
		}
				
		return 'OK';
	}
}

