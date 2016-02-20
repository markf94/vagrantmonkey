<?php

namespace Application;

use Zend\Mvc\Router\Http\Segment;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\Http\RouteMatch;
use Application\Module;
use ZendServer\Log\Log;

class HomeSwitchRoute extends Segment {
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Mvc\Router\Http\Segment::match()
	 */
	public function match(Request $request, $pathOffset = null, array $options = array()) {
		$match = parent::match($request, $pathOffset, $options);
		if ($match instanceof RouteMatch && is_null($match->getParam('controller', null))) {
			$guidePage = Module::config('package', 'guidePage');
			$controller = $guidePage ? 'GuidePage' : 'Dashboard';
			$match->setParam('controller', $controller);
			$match->setMatchedRouteName(strtolower($controller));
		}
		return $match;
	}
}