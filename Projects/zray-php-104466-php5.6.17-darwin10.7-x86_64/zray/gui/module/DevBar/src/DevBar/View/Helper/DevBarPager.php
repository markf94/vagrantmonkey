<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class DevBarPager extends AbstractHelper {
	
	public function __invoke() {
		return <<<PAGER
		<div class="pager zdb-pager">
			<form>
				<div class="first zdb-first"></div> <div class="prev zdb-prev"></div>
				<div class="next zdb-next"></div> <div class="last zdb-last"></div> 
				<span class="pagedisplay zdb-pagedisplay"></span>
				<select class="pagesize zdb-pagesize pagelimit zdb-pagelimit">
					<option value="20">20</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="200">200</option>
				</select>
			</form>
		</div>
PAGER;
	}
}