<?php

namespace JobQueue\Model;


class Statistics {
	/**
	 * @var integer
	 */
	private $waiting;
	/**
	 * @var integer
	 */
	private $waitingPredecessor;
	/**
	 * @var integer
	 */
	private $inProgress;
	/**
	 * @var integer
	 */
	private $completed;
	/**
	 * @var integer
	 */
	private $failed;
	/**
	 * @var integer
	 */
	private $logicallyFailed;
	/**
	 * @var integer
	 */
	private $scheduled;
	/**
	 * @var integer
	 */
	private $avgWait;
	/**
	 * @var integer
	 */
	private $avgRun;
	/**
	 * @var integer
	 */
	private $added;
	/**
	 * @var integer
	 */
	private $served;
	/**
	 * @var integer
	 */
	private $startupTime;

	/**
	 * @param array $statistics
	 * @return Statistics
	 */
	public static function fromArray(array $statistics) {
		$self = new self();
		$self->setWaiting($statistics['waiting']);
		$self->setWaitingPredecessor($statistics['waiting_predecessor']);
		$self->setInProgress($statistics['in_progress']);
		$self->setCompleted($statistics['completed']);
		$self->setFailed($statistics['failed']);
		$self->setLogicallyFailed($statistics['logically_failed']);
		$self->setScheduled($statistics['scheduled']);
		$self->setAvgWait($statistics['avg_wait']);
		$self->setAvgRun($statistics['avg_run']);
		$self->setAdded($statistics['added']);
		$self->setServed($statistics['served']);
		$self->setStartupTime($statistics['startup_time']);
		return $self;
	}
	/**
	 * @return number $waiting
	 */
	public function getWaiting() {
		return $this->waiting;
	}

	/**
	 * @return number $waitingPredecessor
	 */
	public function getWaitingPredecessor() {
		return $this->waitingPredecessor;
	}

	/**
	 * @return number $inProgress
	 */
	public function getInProgress() {
		return $this->inProgress;
	}

	/**
	 * @return number $completed
	 */
	public function getCompleted() {
		return $this->completed;
	}

	/**
	 * @return number $failed
	 */
	public function getFailed() {
		return $this->failed;
	}

	/**
	 * @return number $logicallyFailed
	 */
	public function getLogicallyFailed() {
		return $this->logicallyFailed;
	}

	/**
	 * @return number $scheduled
	 */
	public function getScheduled() {
		return $this->scheduled;
	}

	/**
	 * @return number $avgWait
	 */
	public function getAvgWait() {
		return $this->avgWait;
	}

	/**
	 * @return number $avgRun
	 */
	public function getAvgRun() {
		return $this->avgRun;
	}

	/**
	 * @return number $added
	 */
	public function getAdded() {
		return $this->added;
	}

	/**
	 * @return number $served
	 */
	public function getServed() {
		return $this->served;
	}

	/**
	 * @return number $startupTime
	 */
	public function getStartupTime() {
		return $this->startupTime;
	}

	/**
	 * @param number $waiting
	 * @return Statistics
	 */
	public function setWaiting($waiting) {
		$this->waiting = $waiting;
		return $this;
	}

	/**
	 * @param number $waitingPredecessor
	 * @return Statistics
	 */
	public function setWaitingPredecessor($waitingPredecessor) {
		$this->waitingPredecessor = $waitingPredecessor;
		return $this;
	}

	/**
	 * @param number $inProgress
	 * @return Statistics
	 */
	public function setInProgress($inProgress) {
		$this->inProgress = $inProgress;
		return $this;
	}

	/**
	 * @param number $completed
	 * @return Statistics
	 */
	public function setCompleted($completed) {
		$this->completed = $completed;
		return $this;
	}

	/**
	 * @param number $failed
	 * @return Statistics
	 */
	public function setFailed($failed) {
		$this->failed = $failed;
		return $this;
	}

	/**
	 * @param number $logicallyFailed
	 * @return Statistics
	 */
	public function setLogicallyFailed($logicallyFailed) {
		$this->logicallyFailed = $logicallyFailed;
		return $this;
	}

	/**
	 * @param number $scheduled
	 * @return Statistics
	 */
	public function setScheduled($scheduled) {
		$this->scheduled = $scheduled;
		return $this;
	}

	/**
	 * @param number $avgWait
	 * @return Statistics
	 */
	public function setAvgWait($avgWait) {
		$this->avgWait = $avgWait;
		return $this;
	}

	/**
	 * @param number $avgRun
	 * @return Statistics
	 */
	public function setAvgRun($avgRun) {
		$this->avgRun = $avgRun;
		return $this;
	}

	/**
	 * @param number $added
	 * @return Statistics
	 */
	public function setAdded($added) {
		$this->added = $added;
		return $this;
	}

	/**
	 * @param number $served
	 * @return Statistics
	 */
	public function setServed($served) {
		$this->served = $served;
		return $this;
	}

	/**
	 * @param number $startupTime
	 * @return Statistics
	 */
	public function setStartupTime($startupTime) {
		$this->startupTime = $startupTime;
		return $this;
	}

	
	
}

