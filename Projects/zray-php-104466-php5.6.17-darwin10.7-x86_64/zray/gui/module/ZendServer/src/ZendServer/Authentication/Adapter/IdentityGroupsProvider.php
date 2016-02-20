<?php

namespace ZendServer\Authentication\Adapter;

interface IdentityGroupsProvider {
	/**
	 * @return array
	 */
	public function getIdentityGroups();
}

