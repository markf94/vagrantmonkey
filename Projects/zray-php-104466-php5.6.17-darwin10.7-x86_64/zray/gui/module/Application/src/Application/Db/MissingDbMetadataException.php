<?php

namespace Application\Db;

use ZendServer\Exception;
use Zend\Db\Adapter\Adapter;
class MissingDbMetadataException extends Exception {
	/**
	 * @var Adapter
	 */
	private $adapter;
	
	/**
	 * @return Adapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}

	/**
	 * @param \Zend\Db\Adapter\Adapter $adapter
	 */
	public function setAdapter($adapter) {
		$this->adapter = $adapter;
	}

}

