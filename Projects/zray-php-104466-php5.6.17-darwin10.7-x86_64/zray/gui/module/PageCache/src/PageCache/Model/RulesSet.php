<?php

namespace PageCache\Model;

use ZendServer\Set;

class RulesSet extends Set{
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
	 * @return RulesSet
	 */
	public function setTotal($total) {
		$this->total = $total;
		return $this;
	}	
}