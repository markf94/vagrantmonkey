<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper;

class licenseInfoManagerXml extends AbstractHelper {
	
	/**
	 * @return string
	 */
	public function __invoke() {
		return <<<XML
		
				<status>notRequired</status>
				<orderNumber></orderNumber>
				<validUntil></validUntil>
				<nodeLimit></nodeLimit>
			    <edition></edition>
			    <evaluation></evaluation>
XML;
	}
}