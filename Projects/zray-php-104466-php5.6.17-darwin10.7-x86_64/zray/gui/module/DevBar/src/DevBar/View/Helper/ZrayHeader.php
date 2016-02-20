<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class ZrayHeader extends AbstractHelper {
	
	private $params = array();
	
	private $logo = null;
	
	public function __invoke($params) {
		$this->params = $params;
		
		$button = $this->getButton();
		$header = $this->getHeader();
		
		$extensionName = $params['extensionName'];
		$dataName = 'zrayExtension:' . $extensionName . '/' . $params['name'];
		
		return <<<TEMPLATE
<div class="zdb-toolbar-entry hidden" data-extension="{$extensionName}" data-name="{$dataName}">
	{$button}
	<div class="zdb-toolbar-detail">
		{$header}
TEMPLATE;
	}
	
	private function getButton() {
		$title = (isset($this->params['menuTitle'])) ? $this->params['menuTitle'] : 'Panel';
		
		if (! isset($this->params['logo']) || is_null($this->params['logo'])) {
			$logo = '<div class="zdb-toolbar-icon" style="margin-top: -2px;"></div>';
		} else {
			$logo = '<div class="zdb-toolbar-icon" style="margin-right: 5px; margin-top: -2px; background-image: url(data:image/png;base64,' . $this->params['logo'] . ')"></div>';
		}
		
		// <div class="zdb-toolbar-icon"></div>
		return <<<BUTTON
	<div class="zdb-toolbar-preview" title="{$title}">
        <span class="zdb-toolbar-info">{$logo}{$title}</span>
    </div>
BUTTON;
	}
	
	private function getHeader() {
		$title = (isset($this->params['panelTitle'])) ? $this->params['panelTitle'] : 'Panel';
		$search = $this->getSearch();
		$expandAll = $this->getExpandAll();
		$pagination = $this->getPagination();
		$report = $this->getReport();
		
		$headerLogo = '';
		if (isset($this->params['logo']) && ! is_null($this->params['logo'])) {
			$headerLogo = ' style="background-image: url(data:image/png;base64,' . $this->params['logo'] . ')"';
		}
		
		return <<<HEADER
		<div class="zdb-row zdb-toolbar-detail-header">
    		<div class="zdb-col-1-narrow">
    			<h1{$headerLogo}>{$title}</h1>
    		</div>
    		<div class="zdb-col-3-wide">
    			{$pagination}
    			<div class="zdb-pull-right">
    				<ul class="zdb-toolbar-items zdb-horizontal">
    					<li class="zdb-toolbar-filter">
    						<label for="zdb-toolbar-input-filter-text">Filter by</label>
    						<select>
    							<option value="">Parameter</option>
    						</select>
    						<input type="text" name="zdb-toolbar-input-filter-text" id="zdb-toolbar-input-filter-text" size="6" class="zdb-toolbar-input zdb-toolbar-input-filter-parameter" />
    					</li>
    					{$expandAll}
    					{$search}
    					{$report}
    					<li class="zdb-toolbar-pin">
    						<div class="zdb-popup-pin" onclick="zendDevBar.unpin()"></div>
    					</li>
    				</ul>
    			</div>
    		</div>
    	</div>
HEADER;
	}
	
	private function getSearch() {
		if (! isset($this->params['searchId']) || empty($this->params['searchId'])) {
			return '';
		}
		
		$search = $this->getView()->devBarSearch();
		return '<li id="' . $this->params['searchId'] . '" class="zdb-toolbar-search">' . $search . '</li>';
	}
	
	private function getPagination() {
		if (! isset($this->params['pagerId']) || empty($this->params['pagerId'])) {
			return '';
		}
	
		$pagination = $this->getView()->devBarPager();
		return '<div id="' . $this->params['pagerId'] . '" class="zdb-pull-left">' . $pagination . '</div>';
	}
	
	private function getExpandAll() {
		if (! isset($this->params['expandAll']) || empty($this->params['expandAll'])) {
			return '';
		}
	
		$expandAll = $this->getView()->devBarExpandAll();
		return '<li id="' . $this->params['expandAll'] . '" class="zdb-toolbar-expand-all">' . $expandAll . '</li>';
	}
	
	private function getReport() {
		if (! isset($this->params['showReport']) || $this->params['showReport'] !== true) {
			return '';
		}
	
		$report = $this->getView()->devBarPager();
		return '<li><div class="zdb-export-results-btn" title="Show Report"></div></li>';
	}
}