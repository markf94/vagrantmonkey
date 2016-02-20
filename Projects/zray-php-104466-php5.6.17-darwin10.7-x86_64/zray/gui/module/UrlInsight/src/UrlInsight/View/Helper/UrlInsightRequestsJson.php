<?php
namespace UrlInsight\View\Helper;

use Zend\View\Helper\AbstractHelper;

class UrlInsightRequestsJson extends AbstractHelper {
	
	public function __invoke($requests = array()) {
		$requestsList = array();
		foreach ($requests as $request) {
			$requestsList[] = $this->getView()->urlinsightRequestJson($request);
		}
		return implode(',', $requestsList);
	}
}