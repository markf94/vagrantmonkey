<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ZGridApplicationInfo extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke($url) {
		$basePath = $this->getView()->basePath();
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/zgridApplicationInfo.js');
		//Include the javascript files for tabs
        $this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.js');
        $this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.Extra.js');
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/general.js');
		
		$defaultServer = \Application\Module::config('deployment', 'defaultServer');
		$requestHost = $_SERVER['SERVER_NAME'];
	
		return <<<CHART
new zgridApplicationInfo({url: "{$url}", host: "{$requestHost}" });
CHART;
	}
}

