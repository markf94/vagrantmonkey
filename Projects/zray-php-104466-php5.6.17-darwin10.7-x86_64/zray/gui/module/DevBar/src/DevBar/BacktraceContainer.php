<?php
namespace DevBar;

class BacktraceContainer {
	/**
	 * @var array
	 */
	protected $backtrace;
	
	/**
	 * @param array $backtrace
	 */
	public function __construct(array $backtrace) {
		$this->backtrace = $backtrace;
	}
	
	public function toArray() {
		return $this->backtrace;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->backtrace['id']) ? $this->backtrace['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getBacktrace() {
		return (isset($this->backtrace['backtrace']) ? $this->backtrace['backtrace'] : '');
	}
}