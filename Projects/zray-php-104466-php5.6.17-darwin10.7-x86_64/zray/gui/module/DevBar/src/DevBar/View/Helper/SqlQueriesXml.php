<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class SqlQueriesXml extends AbstractHelper {
	
	public function __invoke($sqlQueries) {
		$queries = array();
		foreach ($sqlQueries as $sqlQuery) {
			$queries[] = $this->sqlQueryInfo($sqlQuery);
		}

		return implode(PHP_EOL, $queries);
	}
	
	/**
	 * @param SqlQueryContainer $sqlQuery
	 * @return string
	 */
	private function sqlQueryInfo(SqlQueryContainer $sqlQuery) {
		return <<<XML
<sqlQuery>
	<id>{$sqlQuery->getId()}</id>
	<transactionId>{$sqlQuery->getTransactionId()}</transactionId>
	<fileName>{$sqlQuery->getFileName()}</fileName>
	<lineNumber>{$sqlQuery->getLineNumber()}</lineNumber>
	<success>{$this->getView()->sqlQueriesStatus($sqlQuery->getStatus())}</success>
	<affectedRows>{$sqlQuery->getAffectedRows()}</affectedRows>
	<string><![CDATA[{$this->getView()->SqlQueryFormat($sqlQuery->getResolvedStatement())}]]></string>
	<queryTime>{$sqlQuery->getQueryTime()}</queryTime>
	<errorString><![CDATA[{$sqlQuery->getErrorMessage()}]]></errorString>
	<backtraceId>{$sqlQuery->getBacktraceId()}</backtraceId>
</sqlQuery>
XML;
	}
}