<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class DevBarSearch extends AbstractHelper {
	
	public function __invoke() {
		return <<<PAGER
		<div class="zdb-search-wrapper">
			<input type="text" name="zdb-toolbar-input-search" id="zdb-toolbar-input-search" class="zdb-toolbar-input zdb-toolbar-input-search" data-column="all" />
			<div class="zdb-search-clear hidden" title="Clear">x</div>
		</div>
PAGER;
	}
}