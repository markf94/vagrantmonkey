<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;

class UrlInsightUrlsJson extends AbstractHelper {
	
	public function __invoke($requests = array()) {
		$requestsList = array();
		foreach ($requests as $request) {
			$requestsList[] = $this->getView()->urlinsightUrlJson($request);
		}
		
		return implode(',', $requestsList);
	}
}