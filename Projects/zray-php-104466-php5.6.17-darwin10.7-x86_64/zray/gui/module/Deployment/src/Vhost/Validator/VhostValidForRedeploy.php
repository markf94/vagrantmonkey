<?php

namespace Vhost\Validator;

use Vhost\Entity\Vhost;
use ZendServer\Log\Log;

class VhostValidForRedeploy extends VhostValidForDeploy {
	
	/* (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		
		$vhost = $this->getVhostMapper()->getVhostByName($value);
		if ($vhost === false || is_null($vhost)) {
			$this->error(self::VIRTUAL_HOST_MISSING);
			return false;
		}

		if (! $vhost->isZendDefined()) {
			$this->error(self::VIRTUAL_HOST_NOT_ZEND_DEFINED);
			return false;
		}
		
		return true;
	}

	

}

