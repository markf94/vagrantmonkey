<?php
namespace MonitorRules\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGridMonitorRules extends AbstractHelper {
	
	    protected $widgetClassName = 'monitorRules';
    
	/**
	 * @param string $container
	 * @param array $gridStructure
	 * @param array $params
	 * @return string
	 */
	public function __invoke($container, $gridStructure, $options = array()) {
		$basePath = $this->getView()->basePath();
		
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/zgrid.css');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zgrid2.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/monitorRules.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zmenu.js');
		
		$jsonOptions = json_encode($options);
		
		return <<<CHART
new {$this->widgetClassName}('{$container}', {$gridStructure} ,{$jsonOptions});
CHART;
	}
}

