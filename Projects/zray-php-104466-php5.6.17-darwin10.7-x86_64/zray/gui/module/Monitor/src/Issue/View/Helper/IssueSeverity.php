<?php
namespace Issue\View\Helper;

use Zend\View\Helper\AbstractHelper;

class IssueSeverity extends AbstractHelper {
	/**
	 * @param integer $severity
	 * @return string
	 */
	public function __invoke($severity) {
		$severities = array(
						ZM_SEVERITY_INFO	=> _t('Info'),
						ZM_SEVERITY_NORMAL	=> _t('Warning'),
						ZM_SEVERITY_SEVERE	=> _t('Critical'),
					);
		return isset($severities[$severity]) ? $severities[$severity] : _t('Unknown');
	}
}

