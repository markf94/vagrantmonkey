<?php

namespace DevBar\Db;

use Configuration\MapperAbstract,
	ZendServer\Log\Log;

class SqlStatementsMapper extends MapperAbstract {
	
	public function removeDevBarRequests($data) {
		
		$preparedStatementIds = array();
		foreach ($data as $el) {
			$preparedStatementIds[] = $el->getPreparedStatement();
		}
		$effected = $this->getTableGateway()->delete(array("id IN (" . implode(",", $preparedStatementIds) . ")"));
	   	Log::debug("Deleted $effected rows from devbar_sql_statements");
	}
	
}
