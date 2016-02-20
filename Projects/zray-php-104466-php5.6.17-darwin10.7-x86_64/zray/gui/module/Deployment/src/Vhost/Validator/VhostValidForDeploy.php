<?php

namespace Vhost\Validator;

use Zend\Validator\AbstractValidator;
use Vhost\Entity\Vhost;
use ZendServer\Log\Log;

class VhostValidForDeploy extends AbstractValidator {
	const VIRTUAL_HOST_MISSING = 'virtualHostMissing';
	const VIRTUAL_HOST_NOT_ZEND_DEFINED = 'virtualHostNotZendDefined';
	const VIRTUAL_HOST_IN_ERROR = 'virtualHostInError';
	
	/**
	 * @var \Vhost\Mapper\Vhost
	 */
	private $vhostMapper;
	
	public function __construct($options = null) {
		parent::__construct($options);
		
		$this->abstractOptions['messageTemplates'] = array(
			self::VIRTUAL_HOST_IN_ERROR 		=> "Virtual host configuration error detected", 
			self::VIRTUAL_HOST_MISSING 			=> "Virtual host '%value%' does not exist", 
			self::VIRTUAL_HOST_NOT_ZEND_DEFINED => "Virtual host is manually defined and cannot be managed by Zend Server",
		);
		
	}
	
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

		if (! $vhost->isManagedByZend()) {
			$this->error(self::VIRTUAL_HOST_NOT_ZEND_DEFINED);
			return false;
		}
		
		$vhostId = $vhost->getId();
		$vhostNodes = $this->getVhostMapper()->getSingleVhostNodes($vhostId);
		foreach ($vhostNodes as $vhostNode) { /* @var $vhostNode VhostNode */
			if ($vhostNode->getStatus() == Vhost::STATUS_ERROR) {
				$this->error(self::VIRTUAL_HOST_IN_ERROR);
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @return \Vhost\Mapper\Vhost
	 */
	public function getVhostMapper() {
		return $this->vhostMapper;
	}

	/**
	 * @param \Vhost\Mapper\Vhost $vhostMapper
	 */
	public function setVhostMapper($vhostMapper) {
		$this->vhostMapper = $vhostMapper;
	}
}

