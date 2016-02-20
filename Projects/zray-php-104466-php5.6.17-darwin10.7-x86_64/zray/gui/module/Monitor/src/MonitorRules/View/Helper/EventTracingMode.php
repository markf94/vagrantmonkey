<?php
namespace MonitorRules\View\Helper;

use Zend\View\Helper\AbstractHelper;

class EventTracingMode extends AbstractHelper {
	/**
	 * @param string $rule
	 * @return string
	 */
	public function __invoke($eventTracingMode) {
		switch ($eventTracingMode) {
			case 0:
				return _t('Disabled');
				break;
			case 1:
				return _t('Active');
				break;
			case 2:
				return _t('Enabled');
				break;
			default:
				return _t('Unknown');
		}
	}
}

