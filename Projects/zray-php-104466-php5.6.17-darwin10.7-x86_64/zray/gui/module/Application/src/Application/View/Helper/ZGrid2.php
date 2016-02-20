<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGrid2 extends AbstractHelper {
	
        protected $widgetClassName = 'zGrid2';
    
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
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zmenu.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/persistantHeaders.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/FragmentManager.js');
		
		$jsonOptions = json_encode($options);
		
		return <<<CHART
new {$this->widgetClassName}('{$container}', {$gridStructure} ,{$jsonOptions});
persistantHeaders.scroll();
CHART;
	}
	
	/**
	 * @param string $widgetClassName
	 */
	public function setWidgetClassName($widgetClassName) {
		$this->widgetClassName = $widgetClassName;
	}

}

