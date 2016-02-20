<?php

namespace WebAPI;

use WebAPI\Db\Mapper;

interface WebapiRequestCreatorInterface {
	/**
	 * @param Mapper $keyMapper
	 */
	public function setWebapiKeyMapper(Mapper $keyMapper);
}

