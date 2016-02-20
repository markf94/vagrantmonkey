<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\RuntimeContainer;

class RuntimeXml extends AbstractHelper {
	
	public function __invoke(RuntimeContainer $runtime) {
		return <<<XML
		<requestRuntime>
			<requestId>{$runtime->getRequestId()}</requestId>
			<php>{$runtime->getPhpTime()}</php>
			<database>{$runtime->getDatabaseTime()}</database>
			<io>{$runtime->getLocalTime()}</io>
			<network>{$runtime->getNetworkTime()}</network>
		</requestRuntime>
XML;
	}
}