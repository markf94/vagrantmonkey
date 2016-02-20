<?php

namespace Servers\Db;

interface ServersAwareInterface {
	/**
	 * @param Mapper $serversMapper
	 */
	public function setServersMapper($serversMapper);
}

