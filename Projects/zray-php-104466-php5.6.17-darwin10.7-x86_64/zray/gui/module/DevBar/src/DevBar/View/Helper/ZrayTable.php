<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class ZrayTable extends AbstractHelper {
	
	private $params = array();
	
	public function __invoke($params) {
		$this->params = $params;
		
		return $content = $this->getTable();
	}
	
	private function getTable() {
	    $tableWidth = (isset($this->params['tableWidth'])) ? $this->params['tableWidth'] : (isset($this->params['summaryTableWidth']) ? '3-wide' : '4');
	    $summaryTable = $this->getSummaryTable();
	    
		return <<<TABLE
    	<div class="zdb-row zdb-panel zsb-called-observers-panel">
    	    {$summaryTable}
    	
    		<!-- main panel: called-observers -->
    		<div id="{$this->params['tableId']}" class="zdb-col-{$tableWidth} zdb-entries-table-wrapper zdb-adaptive-height"></div>
    	</div>
TABLE;
	}
	
	private function getSummaryTable() {
	    if (! isset($this->params['summaryTableId'])) {
	        return '';
	    }
	    $summaryTableWidth = (isset($this->params['summaryTableWidth'])) ? $this->params['summaryTableWidth'] : '1-narrow';
	    
	    return <<<TABLE
	    <!-- Left panel: summary -->
        <div id="{$this->params['summaryTableId']}" class="zdb-col-{$summaryTableWidth} zdb-adaptive-height zdb-summary-table-wrapper"></div>
TABLE;
	}
}