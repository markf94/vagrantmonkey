<?php
namespace Vhost\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ZGridVhostInfo extends AbstractHelper {
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function __invoke() {
		$basePath = $this->getView()->basePath();
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/zgridVhostInfo.js');
		//Include the javascript files for tabs
        $this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.js');
        $this->getView()->plugin('headScript')->appendFile($basePath . '/js/TabPane.Extra.js');
		$this->getView()->plugin('headScript')->appendFile($basePath . '/js/general.js');
		
		$defaultServer = \Application\Module::config('deployment', 'defaultServer');
		$requestHost = $_SERVER['SERVER_NAME'];
                
		return <<<CHART
new zgridVhostInfo({url: "{$this->getView()->url('vhostGetDetails')}", host: "{$requestHost}" });
CHART;
	}
}

