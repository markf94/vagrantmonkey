<?php
namespace Servers\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGridServerDetails extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url) {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zgridServerDetails.js');
		
		return <<<CHART
new zgridServerDetails({ url: "{$url}" })
CHART;
	}
}

