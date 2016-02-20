<?php
namespace JobQueue\Filter;

class Translator {

	
	/**
	 * \JobQueue\Filter\Filter
	 * @var $filter
	 */
	protected $filter;
	
	public function __construct(\JobQueue\Filter\Filter $filter) {
		$this->filter = $filter;
	}
	
	/**
	 * @return \JobQueue\Filter\Filter
	 */
	public function getFilter() {
		return $this->filter;
	}
	
	public function translate() {
		$translation = array();
		$dictionary = $this->getFilter()->getDictionary();
	
		if (is_array($this->getFilter()->getApplicationIds()) && count($this->getFilter()->getApplicationIds())) {
			$translation[Dictionary::FILTER_COLUMN_APP_IDS] = $this->getFilter()->getApplicationIds();
		}

		if ($this->getFilter()->getName()) {
			$translation[Dictionary::COLUMN_NAME] = $this->getFilter()->getName();
		}

		if ($this->getFilter()->getScript()) {
			$translation[Dictionary::COLUMN_SCRIPT] = $this->getFilter()->getScript();
		}

		if ($this->getFilter()->getPriority()) {
			$translation[Dictionary::COLUMN_PRIORITY] = $dictionary->prioritiesToDbValues($this->getFilter()->getPriority());
		}
		
		if ($this->getFilter()->getStatuses()) {			
			$translation[Dictionary::COLUMN_STATUS] = $dictionary->statusesToDbValues($this->getFilter()->getStatuses());
		}

		if ($this->getFilter()->getRuleIds()) {
			$translation[Dictionary::FILTER_COLUMN_RULE_IDS] = $this->getFilter()->getRuleIds();
		}

		if ($this->getFilter()->getQueueIds()) {
			$translation[Dictionary::FILTER_COLUMN_QUEUE_IDS] = $this->getFilter()->getQueueIds();
		}

		if ($this->getFilter()->getScheduledBefore()) {
			$translation[Dictionary::FILTER_COLUMN_SCHEDULED_BEFORE] = $this->translateTimestamp($this->getFilter()->getScheduledBefore());
		}

		if ($this->getFilter()->getScheduledAfter()) {
			$translation[Dictionary::FILTER_COLUMN_SCHEDULED_AFTER] = $this->translateTimestamp($this->getFilter()->getScheduledAfter());
		}
		
		if ($this->getFilter()->getExecutedBefore()) {
			$translation[Dictionary::FILTER_COLUMN_EXECUTED_BEFORE] = $this->translateTimestamp($this->getFilter()->getExecutedBefore());
		}
		
		if ($this->getFilter()->getExecutedAfter()) {
			$translation[Dictionary::FILTER_COLUMN_EXECUTED_AFTER] = $this->translateTimestamp($this->getFilter()->getExecutedAfter());
		}	

		if ($this->getFilter()->getFreeText()) {
			$translation[Dictionary::FILTER_COLUMN_FREE_TEXT] = $this->getFilter()->getFreeText();
		}
		
		return $translation;
	}
	
	protected function translateTimestamp($timestamp) {
		$timestamp = intval($timestamp);		
		$date = new \DateTime("@{$timestamp}");
		$date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		return $date->format('Y-m-d H:i:s');
	}
}