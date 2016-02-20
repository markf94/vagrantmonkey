<?php
namespace Configuration\View\Helper;

use Configuration\License\LicenseChangeContainer;

use Zend\View\Helper\AbstractHelper;


class licenseChangeJson extends AbstractHelper {

	/**
	 * @param \Configuration\License\LicenseChangeContainer $container
	 * @param Boolean $isCurrent
	 * @return string
	 */
	public function __invoke(LicenseChangeContainer $container, $isCurrent) {
		if ($isCurrent) {
			$edition = $container->getCurrentEdition();
			$evaluation = $container->getCurrentEvaluation() ? 'true' : 'false';
		} else {
			$edition = $container->getNewEdition();
			$evaluation = $container->getNewEvaluation() ? 'true' : 'false';
		}
		
		$licenseArray = array(
							'edition'		=> $edition,
							'evaluation'	=> $evaluation,
						);
				
		return $this->getView()->json($licenseArray, array());
	}
}