<?php
namespace DevBar;

use ZendServer\Log\Log;
use Acl\License\Exception;
class RequestContainer {
	/**
	 * @var array
	 */
	protected $request;
	
	/**
	 * @param array $eventsGroup
	 */
	public function __construct(array $request, $key=null) {
		$this->request = $request;
	}
	
	public function toArray() {
		return $this->request;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->request['id']) ? (integer)$this->request['id'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getPageId() {
		return (isset($this->request['page_id']) ? $this->request['page_id'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return (isset($this->request['url']) ? $this->request['url'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getStatusCode() {
		return (isset($this->request['status_code']) ? $this->request['status_code'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getMethod() {
		return (isset($this->request['method']) ? $this->request['method'] : '');
	}
	
	/**
	 * @return double
	 */
	public function getStartTime() {
		return (isset($this->request['start_time']) ? (double)$this->request['start_time'] : 0);
	}
	
	/**
	 * @return integer
	 */
	public function getRunTime() {
		return (isset($this->request['request_time']) ? (integer)$this->request['request_time'] : 0);
	}
	
	/**
	 * @return boolean
	 */
	public function isPrimaryPage() {
		return (isset($this->request['is_primary_page']) && $this->request['is_primary_page'] == '1');
	}
	
	/**
	 * @return integer
	 */
	public function getPeakMemory() {
		return isset($this->request['peak_memory_usage']) ? (integer)$this->request['peak_memory_usage'] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getMemoryLimit() {
		return isset($this->request['memory_limit']) ? (integer)$this->request['memory_limit'] : 0;
	}
	
	/**
	 * @return integer
	 */
	public function getUrlId() {
		return isset($this->request['url_id']) ? (integer)$this->request['url_id'] : 0;
	}
	
	/**
	 * @return string
	 */
	public function getHttpRawPostData() {
	    return isset($this->request['http_raw_post_data']) ? $this->request['http_raw_post_data'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getRawOutput() {
	    return isset($this->request['raw_output']) ? $this->request['raw_output'] : '';
	}

	/**
	 * @return string
	 */
	public function getRequestHeaders() {
	    return isset($this->request['request_headers']) ? $this->request['request_headers'] : '';
	}
	
	/**
	 * @return string
	 */
	public function getResponseHeaders() {
	    return isset($this->request['response_headers']) ? $this->request['response_headers'] : '';
	}
}