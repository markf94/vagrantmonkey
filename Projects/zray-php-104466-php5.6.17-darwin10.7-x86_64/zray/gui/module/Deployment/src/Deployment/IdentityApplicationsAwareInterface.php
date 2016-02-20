<?php

namespace Deployment;

interface IdentityApplicationsAwareInterface {
	/**
	 * @param IdentityFilterInterface $filter
	 */
	public function setIdentityFilter(IdentityFilterInterface $filter);
}

