<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;
use ZendServer\Log\Log,
	DeploymentLibrary\Mapper;

class NormalizeDefaultVersion extends AbstractHelper {
	
	/**
	 * @param string
	 * @return string
	 */
	public function __invoke($versions) {
		$current = current($versions);
		$defaultVersion = $current['version'];
		foreach ($versions as $version) {
			if ($version['default'] == 'true') {
				return $version['version'];
			}
		}
	
		return $defaultVersion;
	}
	
}

