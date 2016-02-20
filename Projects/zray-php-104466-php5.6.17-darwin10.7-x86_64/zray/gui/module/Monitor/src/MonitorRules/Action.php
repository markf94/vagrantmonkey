<?php

namespace MonitorRules;

class Action {
	const TYPE_MAIL = 0;
	const TYPE_TRACING_IMMEDIATE = 1;
	const TYPE_TRACING_LATENT = 2;
	const TYPE_CALLBACK = 3;
	/**
	 * @var string
	 */
	private $type;
	
	/**
	 * @var string
	 */
	private $url;
	
	/**
	 * @var string
	 */
	private $sendToAddress;

	/**
	 * @var string
	 */
	private $tracingDuration;
		
	public function __construct(array $action) {
		$this->setType($action['ACTION_TYPE']);		
		$this->setUrl($action['ACTION_URL']);
		$this->setSendToAddress($action['SEND_TO']);
		$this->setTracingDuration($action['TRACING_DURATION']);
	}
	
	/**
	 * Get the $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * Get the $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * Get the $sendToAddress
	 */
	public function getSendToAddress() {
		return $this->sendToAddress;
	}
	
	/**
	 * @param string $mode
	 */
	public function setSendToAddress($sendToAddress) {
		$this->sendToAddress = $sendToAddress;
	}	

	/**
	 * Get the $tracingDuration
	 */
	public function getTracingDuration() {
		return $this->tracingDuration;
	}
	
	/**
	 * @param string $mode
	 */
	public function setTracingDuration($tracingDuration) {
		$this->tracingDuration = $tracingDuration;
	}	
}