<?php
namespace WebAPI\View\Helper;

use Zend\View\Helper\AbstractHelper;

class WebapiDate extends AbstractHelper {
	/**
	 * @param integer $timestamp
	 * @param string $format
	 * @return string
	 */
	public function __invoke($timestamp, $format = 'c') {
		if ($timestamp) {
			return date($format, $timestamp);
		}
		return '';
	}
}

