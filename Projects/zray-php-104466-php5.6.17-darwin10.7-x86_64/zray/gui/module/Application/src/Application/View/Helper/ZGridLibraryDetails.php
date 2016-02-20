<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGridLibraryDetails extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke() {
		$basePath = Module::config()->baseUrl;
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.js');
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.Extra.js');
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/zgridLibraryDetails.js');
		
		return <<<CHART
new zgridLibraryDetails()
CHART;
	}
}

