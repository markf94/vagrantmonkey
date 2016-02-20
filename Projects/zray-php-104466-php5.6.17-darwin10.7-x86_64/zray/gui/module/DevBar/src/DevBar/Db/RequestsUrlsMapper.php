<?php

namespace DevBar\Db;

use Configuration\MapperAbstract,
	ZendServer\Log\Log;

class RequestsUrlsMapper extends MapperAbstract {
	
	public function removeDevBarRequests($data) {
		
		$requestsUrlIds = array();
		foreach ($data as $el) {
			$requestsUrlIds[] = $el->getUrlId();
		}
	 	$effected = $this->getTableGateway()->delete(array("id IN (" . implode(",", $requestsUrlIds) . ")"));
	    Log::debug("Deleted $effected rows from devbar_requests_urls");
	}
	
}
