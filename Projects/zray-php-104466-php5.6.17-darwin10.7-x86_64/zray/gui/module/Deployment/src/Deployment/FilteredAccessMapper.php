<?php

namespace Deployment;

use ZendServer\Set;

class FilteredAccessMapper implements IdentityApplicationsAwareInterface {
	/**
	 * @var Model
	 */
	private $model;

	/**
	 * @var IdentityFilterInterface
	 */
	private $identityFilter;
	
	/**
	 * @return \ZendServer\Set
	 */
	public function getAllApplicationsInfo() {
		$appIds = $this->getModel()->getAllApplicationIds();
		$appIds = $this->filterIdentityApplications($appIds);
		$applications = $this->getModel()->getApplicationsInfo($appIds);
		return $applications;
	}
	
	/**
	 * @return array
	 */
	public function getAllApplicationIds() {
		$appIds = $this->getModel()->getAllApplicationIds();
		return $this->filterIdentityApplications($appIds);
	}
	
	/**
	 * @param integer $appId
	 * @param string $baseUrl
	 * @param string $userAppName
	 */
	public function setApplicationName($appId, $baseUrl, $userAppName) {
		$this->getModel()->setApplicationName($appId, $baseUrl, $userAppName);
	}
	
	/**
	 * @param array $ids
	 * @param string $orderDirection
	 * @return \ZendServer\Set
	 */
	public function getMasterApplicationsByIds(array $ids = array(), $orderDirection = 'ASC') {
        $ids = $this->filterIdentityApplications($ids, true);

		if (0 < count($ids)) {
			return $this->getModel()->getMasterApplicationsByIds($ids, $orderDirection);
		} else {
			return new Set(array());
		}
	}
	
	public function getMasterApplications() {
	    return $this->getMasterApplicationsByIds(array());
	}
	
	/* (non-PHPdoc)
	 * @see \Deployment\IdentityApplicationsAwareInterface::setIdentityFilter()
	 */
	public function setIdentityFilter(IdentityFilterInterface $filter) {
		$this->identityFilter = $filter;
		$this->identityFilter->setAddGlobalAppId(false);
		return $this;
	}

	/**
	 * @return \Deployment\Model $model
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @param \Deployment\Model $model
	 * @return IdentityFilterGroups
	 */
	public function setModel($model) {
		$this->model = $model;
		return $this;
	}
	
	/**
	 * @param array $applicationIds
	 * @param boolean $emptyIsAll
	 * @return integer
	 */
	protected function filterIdentityApplications($applicationIds, $emptyIsAll = false) {
		if ($emptyIsAll && (! $applicationIds)) {
			return $this->getAllApplicationIds();
		}

        try {
            $applicationIds = $this->identityFilter->filterAppIds($applicationIds);
        } catch (IdentityFilterException $ex) {
            if (IdentityFilterException::EMPTY_APPLICATIONS_ARRAY == $ex->getCode()) {
                return array();
            }
        }

		return $applicationIds;
	}
}

