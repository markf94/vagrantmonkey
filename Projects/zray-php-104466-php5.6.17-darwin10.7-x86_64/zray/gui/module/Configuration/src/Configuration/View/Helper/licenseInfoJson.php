<?php
namespace Configuration\View\Helper;

use Configuration\License\License;

use Zend\View\Helper\AbstractHelper;


class licenseInfoJson extends AbstractHelper {

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
		
		$licenseArray = array(				
				'status'		=> $status,				
				'orderNumber'	=> $order,				
				'validUntil'	=> $expiration,				
				'nodeLimit'		=> $nodeLimit,				
				'edition'		=> $edition,
				'evaluation'	=> $evaluation,
			);
				
		return $this->getView()->json($licenseArray, array());
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