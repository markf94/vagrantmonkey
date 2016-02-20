<?php
namespace MonitorUi\Filter;

use ZendServer\Container\Structure;

class Container implements Structure {
	const NO_APPLICATION_VALUE = -1;
	
	/**
	 * @var array
	 */
	private $applicationIds = null;
	
	/**
	 * @param array $applicationIds
	 * @return \MonitorUi\Filter\Container
	 */
	public function setApplicationIds(array $applicationIds) {
		$this->applicationIds = $applicationIds;
		return $this;
	}

	/*
	 * (non-PHPdoc)
	 * @see ZendServer\Container.Structure::toArray()
	 */
	public function toArray() {
		$filter = array();
		
		if (is_array($this->applicationIds)) {
			$filter[ZM_FILTER_APP_ID] = $this->applicationIds;
		}
		
		return $filter;
	}
}

