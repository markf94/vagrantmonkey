<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Zend\Date\Date,
	ZendServer\Exception;

class DateFormat extends AbstractHelper {
	const DATE_FORMAT_SHORT 	= 'short';
	const DATE_FORMAT_MEDIUM	= 'medium';
	const DATE_FORMAT_LONG 		= 'long';
	
	/**
	 * @param integer $timestamp
	 * @param string $format
	 * @return string
	 */
	public function __invoke($timestamp, $dateFormat = 'long') {
		return date($this->getFormat($dateFormat), $timestamp);
	}
	
	/**
	 * @param string $format
	 * @return string a date format matching date() function's definitions
	 */
	private function getFormat($format) {
		switch ($format) {
			case self::DATE_FORMAT_SHORT:
				return "d-M-Y";
			case self::DATE_FORMAT_MEDIUM:
				return "d-M H:i";
			case self::DATE_FORMAT_LONG:
				return "d-M-Y H:i";
			default:
				throw new Exception('Invalid date format requested');
		}
	}
}

