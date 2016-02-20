<?php

namespace ZendServer\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Navigation\Navigation;

class OverridePageLabel extends AbstractHelper {
	
	/**
	 * @var Navigation
	 */
	private $navigation;
	
	public function __invoke($propertyName, $propertyValue, $labeloverride) {
		$page = $this->getNavigation()->findOneBy($propertyName, $propertyValue);
		$page->setLabel($labeloverride);
	}
	
	/**
	 * @return Navigation
	 */
	public function getNavigation() {
		return $this->navigation;
	}

	/**
	 * @param \Zend\Navigation\Navigation $navigation
	 */
	public function setNavigation($navigation) {
		$this->navigation = $navigation;
	}


}

