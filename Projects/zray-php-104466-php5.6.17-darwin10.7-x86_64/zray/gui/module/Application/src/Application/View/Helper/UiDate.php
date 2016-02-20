<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Zend\Date\Date;

class UiDate extends AbstractHelper {
	/**
	 * @param integer $timestamp
	 * @param string $format
	 * @return string
	 */
	public function __invoke($timestamp, $dateFormat = 'd/M/Y H:i:s') {
		$today = strtotime("now");
		$yesterday = strtotime("-1 day");
		$lastWeek = strtotime("-1 week");
		
		if (date('Y.m.d', $today) == date('Y.m.d', $timestamp)) {
			$hour = date('H:i:s', $timestamp);
			$hour = ($hour == '00:00:00') ? '' : ', '.$hour;
			return _t("Today") . $hour;
		} elseif (date('Y.m.d', $yesterday) == date('Y.m.d', $timestamp)) {
			return _t("Yesterday, ") . date('H:i:s' ,$timestamp);
		} elseif (date('Y.m.d', $lastWeek) == date('Y.m.d', $timestamp)) {
			return date('l, H:i:s');
		}
		
		return date($dateFormat, $timestamp);
	}
}