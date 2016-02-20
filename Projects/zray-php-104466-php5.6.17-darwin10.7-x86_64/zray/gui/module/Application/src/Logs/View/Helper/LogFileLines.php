<?php
namespace Logs\View\Helper;

use Zend\View\Helper\AbstractHelper;

class LogFileLines extends AbstractHelper {
	
	/**
	 * @param string $lines
	 * @return string
	 */
	public function __invoke($lines) {
		return "<![CDATA[{$lines}]]>";		
	}
}