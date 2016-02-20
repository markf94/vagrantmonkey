<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class Filter extends AbstractHelper {
	
        protected $widgetClassName = 'filter';
    
	/**
	 * @param string $container
	 * @param array $gridStructure
	 * @param array $params
	 * @return string
	 */
	public function __invoke($container, $internalFilters, $externalFilters, $existingFilters, $name, $uniqueFilterTypes, $defaultFilterId) {
		$basePath = $this->getView()->basePath();
		
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/filter.css');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/filter.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/searchField.js');
		
		$jsonInternalFilters = json_encode($internalFilters);
		$jsonExternalFilters = json_encode($externalFilters);
		$jsonExistingFilters = json_encode($existingFilters);
		$uniqueFilterTypes = json_encode($uniqueFilterTypes);
		
		return <<<CHART
new {$this->widgetClassName}('{$container}', {$jsonInternalFilters} ,{$jsonExternalFilters}, {$jsonExistingFilters}, '{$name}', {$uniqueFilterTypes}, '{$defaultFilterId}');
CHART;
	}
}

