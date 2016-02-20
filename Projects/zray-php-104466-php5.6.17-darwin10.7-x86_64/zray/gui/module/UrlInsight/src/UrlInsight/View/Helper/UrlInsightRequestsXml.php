<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class UrlInsightRequestsXml extends AbstractHelper {
	
	public function __invoke($requests = array()) {
		$requestsList = array();
		foreach ($requests as $request) {
			$requestsList[] = $this->getView()->urlinsightRequestXml($request);
		}
		return implode(PHP_EOL, $requestsList);
	}
}