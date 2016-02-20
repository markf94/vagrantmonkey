<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use ZendServer\FS\FS;
use ZendServer\Configuration\Manager;

class FreeEditionString extends AbstractHelper {
	/**
	 * @param string $osType
	 * @return string
	 */
	public function __invoke() {
		$manager = new Manager();
		$osType = $manager->getOsType();
		$freeStr = 'Free';
		if($osType == \ZendServer\Configuration\Manager::OS_TYPE_IBMI) {
			$freeStr = 'Basic';
		}
		
		return $freeStr;
	}
	
}

