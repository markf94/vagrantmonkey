<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;
use ZendServer\Log\Log,
	DeploymentLibrary\Mapper;

class NormalizeUpdateUrl extends AbstractHelper {
	
	/**
	 * @param string
	 * @return string
	 */
	public function __invoke($versions) {
		$current = current($versions);
		$latestVersion = $current['version'];
		$url = $current['updateUrl'];
		foreach ($versions as $version) {
			if (version_compare($version['version'], $latestVersion) > 0) {
				$latestVersion = $version['version'];
				$url = $version['updateUrl'];
			}
		}
	
		return $url;
	}
	
}

