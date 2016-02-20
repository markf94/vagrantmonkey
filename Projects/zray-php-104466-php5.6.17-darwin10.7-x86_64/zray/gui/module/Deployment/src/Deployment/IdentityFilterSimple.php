<?php

namespace Deployment;

use ZendServer\Log\Log;

class IdentityFilterSimple implements IdentityFilterInterface {
	
	/**
	 * @var Model
	 */
	private $deploymentMapper;
	
	/**
	 * @var boolean
	 */
	private $addGlobalAppId = true;
	
	/* (non-PHPdoc)
	 * @see \Deployment\IdentityFilterInterface::filterAppIds()
	 */
	public function filterAppIds(array $applicationIds, $emptyIsAll = false) {
        $result = array();
		if ((! $applicationIds) && (! $emptyIsAll)) {
			if ($this->isAddGlobalAppId()) {
                $result = array(-1);
			} else {
                $result = array();
			}
		} else {
            if ($emptyIsAll && (! $applicationIds)) {
                $result = $this->deploymentMapper->getAllApplicationIds();
            } else {
                $result = array_values(array_intersect($this->deploymentMapper->getAllApplicationIds(), $applicationIds));
            }

            if ($this->isAddGlobalAppId()) {
                $result[] = -1;
            }
        }

        if (0 == count($result)) {
            throw new IdentityFilterException(_t('Applications array is empty'), IdentityFilterException::EMPTY_APPLICATIONS_ARRAY);
        }

        return $result;
	}
	
	/**
	 * @return boolean $addGlobalAppId
	 */
	public function isAddGlobalAppId() {
		return $this->addGlobalAppId;
	}

	/**
	 * @param boolean $addGlobalAppId
	 * @return IdentityFilterInterface
	 */
	public function setAddGlobalAppId($addGlobalAppId = true) {
		$this->addGlobalAppId = $addGlobalAppId;
		return $this;
	}

	/**
	 * @param \Deployment\Model $deploymentMapper
	 * @return IdentityFilterInterface
	 */
	public function setDeploymentMapper($deploymentMapper) {
		$this->deploymentMapper = $deploymentMapper;
		return $this;
	}


	
}

