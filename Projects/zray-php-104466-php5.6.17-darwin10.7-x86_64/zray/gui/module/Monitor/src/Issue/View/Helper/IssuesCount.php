<?php
namespace Issue\View\Helper;

use Zend\View\Helper\AbstractHelper;

class IssuesCount extends AbstractHelper {
	/**
	 * @param integer $timestamp
	 * @param string $format
	 * @return string
	 */
	public function __invoke($count) {
		if ($count > 1000) {
			return floor($count / 1000) . "k";
		}
		
		return $count;
	}
}

