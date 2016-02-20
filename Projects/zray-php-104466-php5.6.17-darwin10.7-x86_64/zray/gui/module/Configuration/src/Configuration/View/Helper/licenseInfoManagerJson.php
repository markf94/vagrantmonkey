<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper;


class licenseInfoManagerJson extends AbstractHelper {

	/**
	 * @return string
	 */
	public function __invoke() {
		$licenseArray = array(				
				'status'		=> 'notRequired',				
				'orderNumber'	=> '',				
				'validUntil'	=> '',				
				'nodeLimit'		=> '',				
				'edition'		=> '',
				'evaluation'	=> '',
			);
				
		return $this->getView()->json($licenseArray, array());
	}
}