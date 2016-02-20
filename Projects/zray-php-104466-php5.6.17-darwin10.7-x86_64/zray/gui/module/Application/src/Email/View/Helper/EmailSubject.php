<?php
namespace Email\View\Helper;

use Zend\View\Helper\AbstractHelper;

class EmailSubject extends AbstractHelper {
	
	private $storedSubject = '';
	
	/**
	 * @param string $lines
	 * @return string
	 */
	public function __invoke($subject = null) {
		if (is_null($subject)) {
			return $this;
		}
		$this->storedSubject = $subject;
	}
	
	/**
	 * @return string $storedSubject
	 */
	public function getStoredSubject() {
		return $this->storedSubject;
	}

}