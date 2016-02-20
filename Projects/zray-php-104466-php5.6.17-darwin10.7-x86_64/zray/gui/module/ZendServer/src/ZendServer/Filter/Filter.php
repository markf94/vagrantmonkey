<?php

namespace ZendServer\Filter;

use ZendServer\Filter\FilterInterface;

use ZendServer\Log\Log;
use Zend\Json\Json;
use Issue\Filter\Filter as IssueFilter,
JobQueue\Filter\Filter as JobFilter;

class Filter implements FilterInterface {
	
	protected $id;
	
	protected $name;
	
	protected $type;
	
	protected $data;
	
	protected $custom;
	
	const ISSUE_FILTER_TYPE = 'issue';
	const JOB_FILTER_TYPE = 'job';
	const AUDIT_FILTER_TYPE = 'audit';
	const VHOST_FILTER_TYPE = 'vhost';
	const JOB_RULES_FILTER_TYPE = 'job-rule';
	const ZRAYS_FILTER_TYPE = 'zrays';
	
	static $types = array(self::ISSUE_FILTER_TYPE, self::JOB_FILTER_TYPE, self::AUDIT_FILTER_TYPE, self::VHOST_FILTER_TYPE, self::JOB_RULES_FILTER_TYPE, self::ZRAYS_FILTER_TYPE);
	
	public function __construct($data) {
		if (isset($data['id'])) {
			$this->setId($data['id']);
		}
		if (isset($data['name'])) {
			$this->setName($data['name']);
		}
		if (isset($data['data'])) {
			$this->setData($data['data']);
		}
		if (isset($data['filter_type'])) {
			$this->setType($data['filter_type']);
		}
		if (isset($data['custom'])) {
			$this->setCustom($data['custom']);
		}
	}
	
	/**
	 * Get the $custom
	 */
	public function getCustom() {
		return $this->custom;
	}

	/**
	 * @param field_type $custom
	 */
	public function setCustom($custom) {
		$this->custom = $custom;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isCustom() {
		return ($this->getCustom() == 1);	
	}

	/**
	 * Get the $data
	 */
	public function getData() {
		return Json::decode($this->data, Json::TYPE_ARRAY);
	}

	/**
	 * @param field_type $data
	 */
	public function setData($data) {
		$this->data = $data;
	}

	/**
	 * Get the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Get the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * 
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * @param string $type
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * 
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	public function serialize() {
		return Json::encode($this->data);
	}
}