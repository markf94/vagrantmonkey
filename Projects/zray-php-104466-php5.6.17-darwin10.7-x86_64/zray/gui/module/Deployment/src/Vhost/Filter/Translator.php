<?php
namespace Vhost\Filter;

class Translator {

	
	/**
	 * \Vhost\Filter\Filter
	 * @var $filter
	 */
	protected $filter;
	
	public function __construct(\Vhost\Filter\Filter $filter) {
		$this->filter = $filter;
	}
	
	/**
	 * @return \Vhost\Filter\Filter
	 */
	public function getFilter() {
		return $this->filter;
	}
	
	public function translate() {
		$translation = array();
		$dictionary = $this->getFilter()->getDictionary();
	
		if ($this->getFilter()->getSsl()) {
			$translation[Dictionary::FILTER_COLUMN_SSL] = $dictionary->sslToDbValues($this->getFilter()->getSsl());
		}
		
		if ($this->getFilter()->getVhostType()) {
			$translation[Dictionary::FILTER_COLUMN_TYPE] = $dictionary->typeToDbValues($this->getFilter()->getVhostType());
		}
		
		if ($this->getFilter()->getDeployment()) {
			$translation[Dictionary::FILTER_COLUMN_DEPLOYMENT] = $dictionary->deploymentToDbValues($this->getFilter()->getDeployment());
		}
		
		if ($this->getFilter()->getFreeText()) {
			$translation[Dictionary::FILTER_COLUMN_FREE_TEXT] = $this->getFilter()->getFreeText();
		}
		
		if ($this->getFilter()->getPort()) {
			$translation[Dictionary::FILTER_COLUMN_PORT] = $this->getFilter()->getPort();
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