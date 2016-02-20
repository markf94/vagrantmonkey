<?php

namespace Vhost\StdLib\Hydrator;

use Zend\Stdlib\Hydrator\Reflection;
use ZendServer\Set;
use Vhost\Entity\Vhost;
use Deployment\Application\Container;
use Deployment\Model;
use ZendServer\Log\Log;

class VhostApplications extends Reflection {
	/**
	 * @var Set
	 */
	private $applications;
	
	/**
	 * @var Model
	 */
	private $deploymentMapper;
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Stdlib\Hydrator\Reflection::hydrate()
	 */
	public function hydrate(array $data, $object) {
		$object = parent::hydrate($data, $object); /* @var $object Vhost */
		$applications = array();
		
		foreach($this->getApplications()->toArray() as $appId => $application) { /* @var $application \ZendDeployment_Application */
			if ($object->getId() == $application->getVhostId()) {
				$applications[$application->getApplicationId()] = $application;
			}
		}

		$object->setApplications(new Set($applications, 'Deployment\Application\Container'));
		return $object;
	}
	
	/**
	 * @return Set
	 */
	public function getApplications() {
		if (is_null($this->applications)) {
			$this->applications = $this->getDeploymentMapper()->getMasterApplications();
		}
		return $this->applications;
	}

	/**
	 * @param \ZendServer\Set $applications
	 */
	public function setApplications($applications) {
		$this->applications = $applications;
	}
	
	/**
	 * @return Model
	 */
	public function getDeploymentMapper() {
		return $this->deploymentMapper;
	}

	/**
	 * @param \Deployment\Model $deploymentMapper
	 */
	public function setDeploymentMapper($deploymentMapper) {
		$this->deploymentMapper = $deploymentMapper;
	}

	
	

}

