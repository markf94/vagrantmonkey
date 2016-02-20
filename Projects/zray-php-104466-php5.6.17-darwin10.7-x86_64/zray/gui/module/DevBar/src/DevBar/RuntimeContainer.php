<?php
namespace DevBar;

class RuntimeContainer {
	/**
	 * @var array
	 */
	protected $runtime;
	
	/**
	 * @param array $runtime
	 */
	public function __construct(array $runtime, $key=null) {
		$this->runtime = $runtime;
	}
	/**
	 * @return array
	 */
	public function toArray() {
		return $this->runtime;
	}	
	
	/**
	 * @return integer
	 */
	public function getRequestId() {
		return (integer)(isset($this->runtime['request_id']) ? $this->runtime['request_id'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getDatabaseTime() {
		return (integer)(isset($this->runtime['database_time']) ? $this->runtime['database_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getNetworkTime() {
		return (integer)(isset($this->runtime['network_time']) ? $this->runtime['network_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getLocalTime() {
		return (integer)(isset($this->runtime['local_time']) ? $this->runtime['local_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getPhpTime() {
		return (integer)(isset($this->runtime['php_time']) ? $this->runtime['php_time'] : 0);
	}
	
}