<?php

namespace ZendServer\View\Helper;

use Zend\View\Helper\AbstractHelper;

class DaemonName extends AbstractHelper {
	/**
	 * @var array
	 */
	private $daemonDictionary = array(
		'jqd' => 'Job Queue Daemon',
		'zsd' => 'Zend Server Daemon',
		'monitor_node' => 'Monitor Node',
		'scd' => 'Session Clustering Daemon',
		'jb' => 'Java Bridge Daemon',
		'zdd' => 'Deployment Daemon',
	);
	
	/**
	 * @param string $daemon
	 * @return string
	 */
	public function __invoke($daemon) {
		return isset($this->daemonDictionary[$daemon]) ? $this->daemonDictionary[$daemon] : _t('Service');
	}
}

