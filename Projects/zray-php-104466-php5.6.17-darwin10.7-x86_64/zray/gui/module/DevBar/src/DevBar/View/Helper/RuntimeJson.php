<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\RuntimeContainer;

class RuntimeJson extends AbstractHelper {
	
	public function __invoke(RuntimeContainer $runtime) {
		return $this->getView()->json(array(
	        'requestId' => $runtime->getRequestId(),
			'database' => $runtime->getDatabaseTime(),
		    'php' => $runtime->getPhpTime(),
	    	'io' => $runtime->getLocalTime(),
		    'network' => $runtime->getNetworkTime(),
		));
	}
}