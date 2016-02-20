<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ZGridAuditInfo extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url) {
		$basePath = $this->getView()->basePath();
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/zgridAuditInfo.js');
		
		return <<<CHART
new zgridAuditInfo({ url: "{$url}" })
CHART;
	}
}

