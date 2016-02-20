<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module;

class ZGrid extends AbstractHelper {
	
        protected $widgetClassName = 'zGrid';
    
	/**
	 * @param string $container
	 * @param array $gridStructure
	 * @param array $params
	 * @return string
	 */
	public function __invoke($container, $gridStructure, $params = array()) {
		$basePath = Module::config()->baseUrl;
		
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/zgrid.css');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zgrid.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/zmenu.js');
		
		$cmu = json_encode($gridStructure);
		
		list($pagerInit, $pagerAttach) = $this->getPager($params);
		list($detailsInit, $detailsAttach) = $this->getDetails($params);
		
		$url = (isset($params['url'])) ? $params['url'] : '';
		$idColumn = (isset($params['idColumn'])) ? $params['idColumn'] : 'id';
		$perPage = (isset($params['perPage'])) ? $params['perPage'] : '';
		$dataStructure = (isset($params['dataStructure'])) ? $params['dataStructure'] : '';
		$descHandler = (isset($params['details'])) ? $params['details'] : 'null';
		$requestParams = (isset($params['params'])) ? json_encode($params['params']) : '{}';
		$direction = (isset($params['direction'])) ? $params['direction'] : 'asc';
		$sortedBy = (isset($params['sortedBy'])) ? $params['sortedBy'] : '';
		
		return <<<CHART
{$pagerInit}
{$detailsInit}
zgrid = new {$this->widgetClassName}('{$container}', {
	columnModel: {$cmu},
	url: "{$url}",
	filterId: 'test',
	limit: {$perPage},
	descHandler: {$descHandler},
	dataStructure: '{$dataStructure}',
	idColumn: '{$idColumn}',
	direction: '{$direction}',
	sortedBy: '{$sortedBy}',
	params: {$requestParams}
});
{$pagerAttach}
{$detailsAttach}
CHART;
	}
	
	/**
	 * @param array $params
	 * @return array 
	 */
	private function getPager($params) {
		if (! isset($params['pager'])) {
			return array('', '');
		}
		
		$init = "zpager = {$params['pager']}";
		
		$attach = <<<ATTACH
zgrid.addEvent('onLoadData',function(params) {
	zpager.reloadData(params);
});
ATTACH;
		
		return array($init, $attach);
	}
	
   /**
	* @param array $params
	* @return array
	*/
	private function getDetails($params) {
		if (! isset($params['details'])) {
			return array('', '');
		}
	
		$init = "rowDetails = {$params['details']}";

		$attach = <<<ATTACH
zgrid.addEvent('onDescriptionOpen',function(params) {
	rowDetails.loadData(params);
});
ATTACH;

		return array($init, $attach);
	}
}

