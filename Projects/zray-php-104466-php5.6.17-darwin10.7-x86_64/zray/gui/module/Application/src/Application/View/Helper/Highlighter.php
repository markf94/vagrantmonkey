<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class Highlighter extends AbstractHelper {
	
	/**
	 * @param string $container
	 * @return string
	 */
	public function __invoke($elements) {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/highlighter.js');
		//$this->view->plugin('headLink')->appendStylesheet($basePath . '/js/datepicker/datepicker_vista/datepicker_vista.css');
		$elements = isset($elements) ? $elements : 'body';
		
		return <<<CHART
new Highlighter({
	    elements: '{$elements}',
	    className: 'highlight1',
	    autoUnhighlight: false
	  });
CHART;
	}
}

