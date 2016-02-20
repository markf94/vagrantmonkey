<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGridDirectives extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url, $validateUrl, $isAllowedToSaveDirectives = true) {
		$basePath = Module::config()->baseUrl;
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/zgridDirectives.js');
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.js');
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.Extra.js');
		
		return <<<CHART
new zgridDirectives({ url: "{$url}", validateUrl: "{$validateUrl}", isAllowedToSaveDirectives: {$isAllowedToSaveDirectives} })
CHART;
	}
}

