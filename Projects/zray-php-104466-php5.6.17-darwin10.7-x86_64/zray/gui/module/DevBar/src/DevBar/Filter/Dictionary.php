<?php

namespace DevBar\Filter;
use \ZendServer\Exception;

class Dictionary {	

		
	const RESPONSE_2xx	= '2xx';
	const RESPONSE_4xx	= '4xx';
	const RESPONSE_5xx	= '5xx';
	
	const SORT_DESC	= 'DESC';
	const SORT_ASC	= 'ASC';	

	
	/**
	 * @return array
	 */
	public function getSeverityDictionary() {
		return array(
		    'normal'    => 'normal',
			'critical' 	=> 'critical',
			'warning'	=> 'warning',
		);
	}
	
	/**
	 * @return array
	 */
	public function getMethodDictionaryReversed() {
		return array(
			'GET'  => 0,
			'POST' => 10,
			'CLI'  => 20,
		);
	}
	
	/**
	 * @return array
	 */
	public function getMethodDictionaryForFiltering() {
		$arrayForFiltering = array();
		foreach (array_keys($this->getMethodDictionaryReversed()) as $key) {
			$arrayForFiltering[$key] = $key;
		}
	
		return $arrayForFiltering;
	}
			
	/**
	 * @return array
	 */
	static public function getResponseDictionary() {
		return array(
				self::RESPONSE_2xx	=> self::RESPONSE_2xx,
				self::RESPONSE_4xx	=> self::RESPONSE_4xx,
				self::RESPONSE_5xx	=> self::RESPONSE_5xx,
		);
	}
	

}