<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class SqlQueriesStatus extends AbstractHelper {
	
	public function __invoke($status) {
	switch ($status) {
			case 0:
				return 'error';
			case 1:
				return 'success';
			case 2:
				return 'warning';
			case 3:
				return 'asynchronous';
			default:
				return 'unknown';
		}
	}
}