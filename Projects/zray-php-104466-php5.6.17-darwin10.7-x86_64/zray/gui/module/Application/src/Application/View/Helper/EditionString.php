<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Configuration\License\License;

class EditionString extends AbstractHelper {
	/**
	 * @param string $osType
	 * @return string
	 */
	public function __invoke($edition) {
		
		if ($edition == License::EDITION_FREE) {
			$edition = $this->getView()->freeEditionString();
		} elseif ($edition == License::EDITION_DEVELOPER) {
			$edition = _t('Developer Standard'); 
		} elseif ($edition == License::EDITION_DEVELOPER_ENTERPRISE) {
			$edition = _t('Developer Enterprise');
		}
		
		return ucwords(strtolower($edition));
	}
	
}

