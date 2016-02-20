<?php

namespace Application\Exception;

class DependencyException extends \Exception {
	const CODE_DIRECTIVE = 1000;
	const CODE_EXTENSION = 1001;
	
	/**
	 * @var string
	 */
	private $context;
	
	/**
	 * @return string
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @param string $context
	 */
	public function setContext($context) {
		$this->context = $context;
	}

	
}

