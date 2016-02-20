<?php
namespace DevBar;

class SqlQueryContainer {
	/**
	 * @var array
	 */
	protected $sqlQuery;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $sqlQuery, $key=null) {
		$this->sqlQuery = $sqlQuery;
	}
	
	public function toArray() {
		return $this->request;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->sqlQuery['id']) ? $this->sqlQuery['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getRequestId() {
		return (isset($this->sqlQuery['request_id']) ? $this->sqlQuery['request_id'] : '');
	}
	
	/**
	 * @return int
	 */
	public function getPreparedStatement() {
		return (isset($this->sqlQuery['prepared_statement_id']) ? $this->sqlQuery['prepared_statement_id'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getResolvedStatement() {
		return (isset($this->sqlQuery['resolved_statement']) ? $this->sqlQuery['resolved_statement'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getStatus() {
		return (isset($this->sqlQuery['status']) ? $this->sqlQuery['status'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getExplain() {
		return (isset($this->sqlQuery['explain']) ? $this->sqlQuery['explain'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getAffectedRows() {
		return (isset($this->sqlQuery['rows_affected']) ? (integer)$this->sqlQuery['rows_affected'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getQueryTime() {
		return (isset($this->sqlQuery['query_time']) ? (integer)$this->sqlQuery['query_time'] : 0);
	}
	
	/**
	 * @return string
	 */
	public function getErrorMessage() {
		return (isset($this->sqlQuery['error_message']) ? $this->sqlQuery['error_message'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getTransactionId() {
		return (isset($this->sqlQuery['transaction_id']) ? (integer)$this->sqlQuery['transaction_id'] : -1);
	}
	
	/**
	 * @return integer
	 */
	public function getLineNumber() {
		return (isset($this->sqlQuery['line_number']) ? (integer)$this->sqlQuery['line_number'] : -1);
	}
	
	/**
	 * @return string
	 */
	public function getFileName() {
		return (isset($this->sqlQuery['file_name']) && !empty($this->sqlQuery['file_name']) ? $this->sqlQuery['file_name'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function isTransactionQuery() {
		return (isset($this->sqlQuery['transaction_id']) && $this->sqlQuery['transaction_id'] == -1);
	}
	
	/**
	 * @return integer
	 */
	public function getBacktraceId() {
		return (isset($this->sqlQuery['backtrace_id']) ? (integer)$this->sqlQuery['backtrace_id'] : 0);
	}
}