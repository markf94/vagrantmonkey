<?php
namespace DevBar;

class LogEntryContainer {
	/**
	 * @var array
	 */
	protected $logEntry;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $logEntry, $key=null) {
		$this->logEntry = $logEntry;
	}
	
	public function toArray() {
		return $this->logEntry;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->logEntry['id']) ? $this->logEntry['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getRequestId() {
		return (isset($this->logEntry['request_id']) ? (integer)$this->logEntry['request_id'] : 0);
	}
	
	/**
	 * @return string
	 */
	public function getType() {
		return (isset($this->logEntry['type']) ? $this->logEntry['type'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getMessage() {
		return (isset($this->logEntry['message']) ? $this->logEntry['message'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getLine() {
		return (isset($this->logEntry['line']) ? $this->logEntry['line'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getFilename() {
		return (isset($this->logEntry['filename']) ? $this->logEntry['filename'] : '');
	}
	
	/**
	 * @return double
	 */
	public function getTimestamp() {
		return (isset($this->logEntry['entry_time']) ? (double)$this->logEntry['entry_time'] : 0);
	}
	
	/**
	 * @return bool integer (1 or 0) 
	 */
	public function getSilenced() {
		return (isset($this->logEntry['silenced']) && $this->logEntry['silenced']) ? 1 : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getBacktraceId() {
		return (isset($this->logEntry['backtrace_id']) ? (integer)$this->logEntry['backtrace_id'] : 0);
	}
	
	// serial number of the log entry. log entries and exceptions are dispayed together
	public function getSequenceId() {
		return (isset($this->logEntry['sequence_id']) ? (integer)$this->logEntry['sequence_id'] : 0);
	}
		
}