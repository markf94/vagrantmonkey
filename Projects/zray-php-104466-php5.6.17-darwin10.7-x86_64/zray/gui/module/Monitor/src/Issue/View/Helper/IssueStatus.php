<?php
namespace Issue\View\Helper;

use Zend\View\Helper\AbstractHelper;

class IssueStatus extends AbstractHelper {
	/**
	 * @param integer $status
	 * @return string
	 */
	public function __invoke($status) {
		$statusi = array(
						ZM_STATUS_NEW		=> _t('Open'),
						ZM_STATUS_CLOSED	=> _t('Closed'),
						ZM_STATUS_REOPENED	=> _t('Reopened'),
						ZM_STATUS_IGNORED	=> _t('Ignored')
					);
		return isset($statusi[$status]) ? $statusi[$status] : _t('Unknown');
	}
}

