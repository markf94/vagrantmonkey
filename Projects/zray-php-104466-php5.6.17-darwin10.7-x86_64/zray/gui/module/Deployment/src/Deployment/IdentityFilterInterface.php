<?php

namespace Deployment;

interface IdentityFilterInterface {
	/**
	 * @param array $applicationIds
     * @param boolean $emptyIsAll If no applications are passed to be filtered, will populate output with all applications
     * @throws IdentityFilterException
	 * @return array
	 */
	public function filterAppIds(array $applicationIds, $emptyIsAll = false);
	/**
	 * @param boolean $addGlobalAppId
	 * @return IdentityFilterInterface
	 */
	public function setAddGlobalAppId($addGlobalAppId = true);
}

