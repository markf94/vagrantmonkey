<?php

namespace JobQueue\Model;

use ZendServer\Set;

class JobsSet extends Set{
	/**
	 * @var integer
	 */
	private $total;
	
	/**
	 * @return number $total
	 */
	public function getTotal() {
		if (is_null($this->total)) {
			return $this->count();
		}
		return $this->total;
	}

	/**
	 * @param number $total
	 * @return JobsSet
	 */
	public function setTotal($total) {
		$this->total = $total;
		return $this;
	}

	
	
}