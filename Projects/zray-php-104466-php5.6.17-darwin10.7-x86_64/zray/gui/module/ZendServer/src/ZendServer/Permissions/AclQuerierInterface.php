<?php

namespace ZendServer\Permissions;

use ZendServer\Permissions\AclQuery;

interface AclQuerierInterface {
	/**
	 * @param AclQuery $acl
	 * @return AclQuerierInterface
	 */
	public function setAcl(AclQuery $acl);
}

