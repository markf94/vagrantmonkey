<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGridJobDetails extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url) {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zgridJobDetails.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/TabPane.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/TabPane.Extra.js');
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/appList.css');
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/prereq.css');
		
		return <<<CHART
new zgridJobDetails({ url: "{$url}" })
CHART;
	}
}

