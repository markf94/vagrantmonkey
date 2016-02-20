<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Configuration\License\LicenseChangeContainer;

class licenseChangeXml extends AbstractHelper {
	
	/**
	 * @param \Configuration\License\LicenseChangeContainer $container
	 * @param Boolean $isCurrent
	 * @return string
	 */
	public function __invoke(LicenseChangeContainer $container, $isCurrent) {
		if ($isCurrent) {
			$licenseName = 'currentLicense';
			$edition = $container->getCurrentEdition();
			$evaluation = $container->getCurrentEvaluation() ? 'true' : 'false';
		} else {
			$licenseName = 'newLicense';
			$edition = $container->getNewEdition();
			$evaluation = $container->getNewEvaluation() ? 'true' : 'false';
		}
		
		return <<<XML
			
<{$licenseName}>
		       <edition>{$edition}</edition>
		       <evaluation>{$evaluation}</evaluation>
     </{$licenseName}>
XML;
	}

}

