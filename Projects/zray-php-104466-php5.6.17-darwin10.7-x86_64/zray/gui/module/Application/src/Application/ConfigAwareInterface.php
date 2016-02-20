<?php

namespace Application;

use Zend\Config\Config;
interface ConfigAwareInterface {
	/**
	 * @return array
	 */
	public function getAwareNamespace();
	/**
	 * @param Config $config
	 */
	public function setConfig($config);
}

