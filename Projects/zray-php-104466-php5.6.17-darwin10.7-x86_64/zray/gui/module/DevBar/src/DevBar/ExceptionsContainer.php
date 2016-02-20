<?php
namespace DevBar;

class ExceptionsContainer {
	/**
	 * @var array
	 */
	protected $exception;
	
	/**
	 * @param array $exception
	 */
	public function __construct(array $exception, $key=null) {
		$this->exception = $exception;
	}

	public function toArray() {
		return $this->exception;
	}	
	
	public function getId() {
		return $this->exception['id'];
	}
	
	public function getRequestId() {
		return $this->exception['request_id'];
	}
	
	public function getExceptionText() {
		return $this->exception['exception_text'];
	}
	
	public function getExceptionClass() {
		return isset($this->exception['class_type']) ? $this->exception['class_type'] : '';
	}
	
	public function getExceptionCode() {
		return isset($this->exception['exception_code']) ? $this->exception['exception_code'] : 0;
	}
	
	public function getFileName() {
		return isset($this->exception['file_name']) ? $this->exception['file_name'] : '';
	}
	
	public function getLineNumber() {
		return isset($this->exception['line_number']) ? $this->exception['line_number'] : '';
	}
	
	public function getCreatedAt() {
		return isset($this->exception['created_at']) ? $this->exception['created_at'] : time();
	}
	
	/**
	 * @return integer
	 */
	public function getBacktraceId() {
		return (isset($this->exception['backtrace_id']) ? (integer)$this->exception['backtrace_id'] : 0);
	}
	
	// serial number of the log entry. log entries and exceptions are dispayed together
	public function getSequenceId() {
		return (isset($this->exception['sequence_id']) ? (integer)$this->exception['sequence_id'] : 0);
	}
	
}