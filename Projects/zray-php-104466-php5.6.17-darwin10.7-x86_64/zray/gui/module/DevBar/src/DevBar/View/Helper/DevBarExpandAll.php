<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class DevBarExpandAll extends AbstractHelper {
	
	public function __invoke() {
		return <<<EXPANDALL
		<span class="zdb-expand-tree" onclick="zendDevBar.expandTreeTableRows(this)">Expand all</span>
EXPANDALL;
	}
}