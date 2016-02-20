<?php
namespace UrlInsight;

class RequestContainer {
	/**
	 * @var array
	 */
	protected $request;
	
	/**
	 * @param array $request
	 */
	public function __construct(array $request) {
		$this->request = $request;
	}
	
	public function toArray() {
		return $this->request;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->request['id']) ? (int) $this->request['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getUrlId() {
		return (isset($this->request['resource_id']) ? (int) $this->request['resource_id'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getResourceId() {
	    return (isset($this->request['resource_id']) ? (int) $this->request['resource_id'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getAppId() {
		return (isset($this->request['app_id']) ? (int) $this->request['app_id'] : -1);
	}
	
	/**
	 * @return integer
	 */
	public function getTimeConsumption() {
        return $this->getAvgTime() * $this->getSamples();
	}
	
	/**
	 * @return string
	 */
	public function getUrlTooltip() {
	    if (isset($this->request['resource_string']) && !empty($this->request['resource_string'])) {
	        if (false === ($unserialized = @unserialize($this->request['resource_string']))) {
	            return $this->request['resource_string'];
	        } else {
	            if (is_array($unserialized) && !empty($unserialized)) {
	                
	                // remove empty elements from the array
	                $unserialized = array_filter($unserialized, function($elem) {
	                    return !empty($elem);
	                });
	                
					$tooltip = array();
					array_walk($unserialized, function($item, $key) use (&$tooltip) {
						$tooltip[] = "{$key} = {$item}";
					});
	                return implode("\n", $tooltip);
	            } elseif (is_string($unserialized)) {
	                return $unserialized;
	            } else {
	               return $this->request['resource_string'];
	            }
	        }
	    }
	    
		return '';
	}

	/**
	 * @return string
	 */
	public function getUrl() {
	    if (isset($this->request['resource_string']) && !empty($this->request['resource_string'])) {
	        if (false === ($unserialized = @unserialize($this->request['resource_string']))) {
	            return $this->request['resource_string'];
	        } else {
	            if (is_array($unserialized) && !empty($unserialized)) {
	                
	                // remove empty elements from the array
	                $unserialized = array_filter($unserialized, function($elem) {
	                    return !empty($elem);
	                });
	                
	                return implode(' :: ', $unserialized);
	            } elseif (is_string($unserialized)) {
	                return $unserialized;
	            } else {
	               return $this->request['resource_string'];
	            }
	        }
	    }
	    
		return '';
	}
	
	/**
	 * @return string
	 */
	public function getUrlExample() {
	    return (isset($this->request['resource_string_example']) ? $this->request['resource_string_example'] : $this->getUrl());
	}	
	
	/**
	 * @return string
	 */
	public function getMvc() {
	    $mvcStr = "";
	    if (isset($this->request['resource_string'])) {
	        $mvcArray = @unserialize($this->request['resource_string']);
	        if ($mvcArray && is_array($mvcArray)) {
	           return implode('/', $mvcArray);
	        }
	    }
	    
	    return $mvcStr;
	}
	
	/**
	 * @return integer
	 */
	public function getSamples() {
		return (isset($this->request['samples']) ? (int) $this->request['samples'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getMinTime() {
		return (isset($this->request['min_time']) ? (int) $this->request['min_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getMaxTime() {
		return (isset($this->request['max_time']) ? (int) $this->request['max_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getAvgTime() {
		return (isset($this->request['avg_time']) ? (int) $this->request['avg_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getMaxMemory() {
		return (isset($this->request['max_memory']) ? (int) $this->request['max_memory'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getAvgMemory() {
		return (isset($this->request['avg_memory']) ? (int) $this->request['avg_memory'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getFromTime() {
		return (isset($this->request['from_time']) ? (int) $this->request['from_time'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getUntilTime() {
		return (isset($this->request['until_time']) ? (int) $this->request['until_time'] : '');
	}
}