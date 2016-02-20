<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DevBar\BacktraceContainer;

class UrlInsightUrlsXml extends AbstractHelper {
	
	public function __invoke($requests = array()) {
		$requestsList = array();
		foreach ($requests as $request) {
			$requestsList[] = $this->getView()->urlinsightUrlXml($request);
		}
		return implode(PHP_EOL, $requestsList);
	}
}