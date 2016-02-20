<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class SearchField extends AbstractHelper {
	
	protected $widgetClassName = 'searchField';
    
	public function __invoke() {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/searchField.js');
	}
}

