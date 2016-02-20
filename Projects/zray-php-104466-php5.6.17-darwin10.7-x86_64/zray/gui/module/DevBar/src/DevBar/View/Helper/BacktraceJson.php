<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class BacktraceJson extends AbstractHelper {
	
	public function __invoke(BacktraceContainer $backtrace) {
		$json = json_decode($backtrace->getBacktrace(), true);
		
		$res = array();
		foreach ($json as $trace) {
			$file = $trace['file'];
			if (empty($file)) {
				$file = '<builtin>';
			}
			
			$res[] = array(
				'name' => $trace['name'],
				'scope' => $trace['scope'],
				'file' => $file,
				'cline' => $trace['cline'],
				'args' => $trace['args'],
			);
		}

		return $this->getView()->json($res);
	}
}