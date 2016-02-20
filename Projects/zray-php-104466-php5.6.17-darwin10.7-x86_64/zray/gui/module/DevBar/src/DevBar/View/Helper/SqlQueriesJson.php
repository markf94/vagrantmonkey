<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class SqlQueriesJson extends AbstractHelper {
	
	public function __invoke($sqlQueries) {
		$queries = array();
		foreach ($sqlQueries as $sqlQuery) {
			$queries[] = $this->sqlQueryInfo($sqlQuery);
		}

		return $this->getView()->json($queries);
	}
	
	/**
	 * 
	 * @param SqlQueryContainer $sqlQuery
	 * @return array
	 */
	private function sqlQueryInfo(SqlQueryContainer $sqlQuery) {
		return array(
				'id' => $sqlQuery->getId(),
				'transactionId' => $sqlQuery->getTransactionId(),
				'fileName' => $sqlQuery->getFileName(),
				'lineNumber' => $sqlQuery->getLineNumber(),
				'success' => $this->getView()->sqlQueriesStatus($sqlQuery->getStatus()),
				'affectedRows' => $sqlQuery->getAffectedRows(),
				'string' => $this->getView()->sqlQueryFormat($sqlQuery->getResolvedStatement()),
				'queryTime' => $sqlQuery->getQueryTime(),
				'errorMessage' => $sqlQuery->getErrorMessage(),
				'backtraceId' => $sqlQuery->getBacktraceId(),
		);
	}
}