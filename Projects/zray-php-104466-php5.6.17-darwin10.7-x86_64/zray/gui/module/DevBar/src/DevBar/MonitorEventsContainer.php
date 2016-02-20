<?php
namespace DevBar;

class MonitorEventsContainer {
	/**
	 * @var array
	 */
	protected $monitorEvents;
	
	/**
	 * @param array $monitorEvents
	 */
	public function __construct(array $monitorEvents) {
		$this->monitorEvents = $monitorEvents;
	}
	
	public function toArray() {
		return $this->monitorEvents;
	}	
	
	/**
	 * @return integer
	 */
	public function getId() {
		return (isset($this->monitorEvents['id']) ? $this->monitorEvents['id'] : '');
	}
	
	/**
	 * @return integer
	 */
	public function getRequestId() {
		return (isset($this->monitorEvents['request_id']) ? $this->monitorEvents['request_id'] : '');
	}
	
	/**
	 * @return string
	 */
	public function getAggregationKey() {
		return (isset($this->monitorEvents['agg_key']) ? $this->monitorEvents['agg_key'] : '');
	}
}