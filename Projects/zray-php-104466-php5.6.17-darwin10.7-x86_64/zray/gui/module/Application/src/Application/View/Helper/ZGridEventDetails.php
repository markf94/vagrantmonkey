<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGridEventDetails extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url) {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zgridEventDetails.js');
		
		return <<<CHART
new zgridEventDetails({ url: "{$url}" })
CHART;
	}
}

