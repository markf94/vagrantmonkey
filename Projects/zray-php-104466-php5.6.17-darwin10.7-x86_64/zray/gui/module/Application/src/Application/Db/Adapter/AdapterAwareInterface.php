<?php

namespace Application\Db\Adapter;

use Zend\Db\Adapter\AdapterAwareInterface as baseInterface;

interface AdapterAwareInterface extends baseInterface {
	/**
	 * Returns the name of the db adapter to retrieve. These should be one of the Connector constants
	 * @return string
	 */
	public function getAdapterDb();
}

