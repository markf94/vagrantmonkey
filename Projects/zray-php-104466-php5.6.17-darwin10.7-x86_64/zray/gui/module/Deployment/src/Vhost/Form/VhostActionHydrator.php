<?php

namespace Vhost\Form;

use Zend\Stdlib\Hydrator\HydratorInterface;
use ZendServer\Log\Log;
use Vhost\Mapper\AddVhost;

class VhostActionHydrator implements HydratorInterface {
	
	/* (non-PHPdoc)
	 * @see \Zend\Stdlib\Hydrator\HydratorInterface::extract()
	 */
	public function extract($object) {
		$data = array();
		if ($object instanceof \Vhost\Mapper\AbstractVhostAction) {
			$data['name'] 						= $object->getVhostName();
			$data['port'] 						= $object->getPort();
			$data['template'] 					= $object->getTemplate();
			$data['sslEnabled'] 				= intval($object->getSsl());
			$data['sslCertificateChainPath']	= '';
			$data['sslCertificateKeyPath'] 		= '';
			$data['sslCertificatePath'] 		= '';
			$data['sslAppName'] 				= '';
		}
		
		if ($object instanceof \Vhost\Mapper\EditVhost) {
			$data['vhostId'] = $object->getVhostId();
		}
		
		if ($object instanceof AddVhost) {
			$data['forceCreate'] = $object->isForceCreate();
		}
		return $data;
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\Stdlib\Hydrator\HydratorInterface::hydrate()
	 */
	public function hydrate(array $data, $object) {
		if ($object instanceof \Vhost\Mapper\AbstractVhostAction) {
			if (isset($data['port'])) {
				$object->setPort($data['port']);
			}
			if (isset($data['sslEnabled'])) {
				$object->setSsl((boolean)$data['sslEnabled']);
			}
			if (isset($data['sslAppName'])) {
				$object->setSslAppName($data['sslAppName']);
			}
			if (isset($data['sslCertificateChainPath'])) {
				$object->setSslCertificateChainPath($data['sslCertificateChainPath']);
			}
			if (isset($data['sslCertificateKeyPath'])) {
				$object->setSslCertificateKeyPath($data['sslCertificateKeyPath']);
			}
			if (isset($data['sslCertificatePath'])) {
				$object->setSslCertificatePath($data['sslCertificatePath']);
			}
			if (isset($data['template'])) {
				$object->setTemplate($data['template']);
			}
			$object->setVhostName($data['name']);
		}
		
		if ($object instanceof \Vhost\Mapper\EditVhost) {
			$object->setVhostId($data['vhostId']);
		}
		
		if (isset($data['forceCreate']) && $object instanceof AddVhost) {
			$object->setForceCreate($data['forceCreate']);
		}
		return $object;
	}
}