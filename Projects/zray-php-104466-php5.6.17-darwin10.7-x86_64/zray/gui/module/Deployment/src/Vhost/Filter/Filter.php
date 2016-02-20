<?php

namespace Vhost\Filter;
use ZendServer\Exception;


use ZendServer\Filter\FilterInterface;

use Zend\Json\Json;

use ZendServer\Container\Structure;
use ZendServer\Filter\Filter as BaseFilter;

class Filter extends BaseFilter implements FilterInterface {
	
	
	const VHOST_FILTER_TYPE = 'vhost';

	/**
	 * @var Dictionary
	 */
	protected $dictionary;
		
	protected $ssl;
	protected $type;
	protected $deployment;
	protected $freeText;
	protected $port;
	
	protected $serializableProperties = array('ssl', 'type', 'deployment', 'freeText');
	
	function getType() {
        return self::VHOST_FILTER_TYPE;
    }
    
	public function __construct($data) {	
		if (isset($data['ssl'])) {
			$this->setSsl($data['ssl']);
		}
		
		if (isset($data['type'])) {
			$this->setVhostType($data['type']);
		}
		
		if (isset($data['deployment'])) {
			$this->setDeployment($data['deployment']);
		}
		
		if (isset($data['freeText'])) {
			$this->setFreeText($data['freeText']);
		}
		
		if (isset($data['port'])) {
			$this->setPort($data['port']);
		}
	}
   
	public function setFreeText($freeText) {
		return $this->freeText = $freeText;
	}

	public function getFreeText() {
		return $this->freeText;
	}
	
	public function setSsl($ssl) {
		return $this->ssl = $ssl;
	}
	
	public function getSsl() {
		return $this->ssl;
	}
	
	public function setPort($port) {
		return $this->port = $port;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function setVhostType($type) {
		return $this->type = $type;
	}
	
	public function getVhostType() {
		return $this->type;
	}
	
	public function setDeployment($deployment) {
		return $this->deployment = $deployment;
	}
	
	public function getDeployment() {
		return $this->deployment;
	}
	
	public function serialize() {
	    $reflect = new \ReflectionClass($this);
	    $props = $reflect->getProperties();
	    $serializable = array();
	    foreach ($props as $prop) { /* @var $prop \ReflectionProperty */
	        if (!in_array($prop->getName(), $this->serializableProperties)) {
	            $prop->setAccessible(true);
	            $serializable[$prop->getName()] = $prop->getValue($this);
	        }
	    }
	    return Json::encode($serializable);
	}
	

	public function getDictionary() {
		if ($this->dictionary) {
			return $this->dictionary;
		}
	
		return $this->dictionary = new Dictionary();
	}
}