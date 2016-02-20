<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZPager extends AbstractHelper {
	
	/**
	 * @param string $container
	 * @return string
	 */
	public function __invoke($container, $perPage) {
		$basePath = Module::config()->baseUrl;
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zpager.js');
		
		return <<<CHART
new zPager('{$container}', {$perPage});
CHART;
	}
}

