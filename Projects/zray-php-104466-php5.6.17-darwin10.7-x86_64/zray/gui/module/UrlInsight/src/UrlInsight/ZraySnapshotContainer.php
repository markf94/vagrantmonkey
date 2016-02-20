<?php
namespace UrlInsight;

class ZraySnapshotContainer {
	/**
	 * @var array
	 */
	protected $snapshot;
	
	/**
	 * @param array $request
	 */
	public function __construct(array $snapshot) {
		$this->snapshot = $snapshot;
	}
	
	public function toArray() {
		return $this->snapshot;
	}
	
	/**
	 * @return string
	 */
	public function getPageId() {
		return (isset($this->snapshot['pageId']) ? $this->snapshot['pageId'] : '');
	}
	
	/**
	 * @return integer timestamp
	 */
	public function getRequestTime() {
		return (isset($this->snapshot['requestTime']) ? $this->snapshot['requestTime'] : 0);
	}
	
}