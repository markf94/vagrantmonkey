<?php

namespace ZendServer\Log\Formatter;

use Zend\Log\Formatter\Simple as baseSimple;

class Simple extends baseSimple {
	
	/**
	 * @var string
	 */
	private $uri = '';
	
	public function format($event) {
		$output = parent::format($event);
		$output = preg_replace('/\n$/', "", $output);
		$output = preg_replace('/\n(.+)/', "\n{$this->uri}\t$1", $output);
		return str_replace('%uri%', $this->uri, $output);
	}
	
	/**
	 * @param string $uri
	 * @return Simple
	 */
	public function setUri($uri) {
		$this->uri = $uri;
		return $this;
	}

}

