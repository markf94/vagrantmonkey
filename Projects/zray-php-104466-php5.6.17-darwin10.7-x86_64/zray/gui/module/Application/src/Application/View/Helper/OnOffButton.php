<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class OnOffButton extends AbstractHelper {
	
	protected $widgetClassName = 'onOffButton';
    
	public function __invoke() {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/onOffButton.js');
	}
}

