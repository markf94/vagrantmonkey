<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Breadcrumbs extends AbstractHelper {
	/**
	 * @var boolean
	 */
	private $rendered;
	
	public function __invoke() {
		return $this;
	}
	
	public function render() {
		if (! $this->isRendered()) {
			$this->setRendered();
			$partial = array('application/navigation/crumbs.phtml', 'default');
			$this->getView()->navigation()->breadcrumbs()->setPartial($partial);
			return $this->getView()->navigation()->breadcrumbs()->render('Breadcrumbs');
		}
		return '';
	}
	/**
	 * @return boolean
	 */
	public function isRendered() {
		return $this->rendered;
	}

	/**
	 * @param boolean $rendered
	 */
	public function setRendered($rendered = true) {
		$this->rendered = $rendered;
	}

}

