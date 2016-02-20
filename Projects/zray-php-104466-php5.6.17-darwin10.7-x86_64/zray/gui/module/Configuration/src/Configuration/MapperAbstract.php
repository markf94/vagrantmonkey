<?php

namespace Configuration;

use Zend\Db\Sql\Select;

use ZendServer\Log\Log,
ZendServer\Set,
Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Platform\PlatformInterface;

abstract class MapperAbstract {
	
	protected $count_field;
	
	protected $setClass;

	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	protected $tableGateway;
	
	/**
	 * @var PlatformInterface
	 */
	protected $platform;
	
	public function __construct(TableGateway $tableGateway = null) {
		$this->setTableGateway($tableGateway);
	}

	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->getTableGateway()->getTable();
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $tableGateway
	 */
	public function getTableGateway() {
		return $this->tableGateway;
	}
	
	/**
	 * @param \Zend\Db\TableGateway\TableGateway $tableGateway
	 * @return \Configuration\MapperAbstract
	 */
	public function setTableGateway($tableGateway) {
		$this->tableGateway = $tableGateway;
		return $this;
	}

	/**
	 * @param string $field
	 * @param string $where
	 * @return integer
	 */
	public function count($field='*', $where=null) {
		// in case of Z-Ray standalone there's only one server
		if (isZrayStandaloneEnv()) {
			return 1;
		}
		
		try {
			$table = $this->getTableName();
			$select = new Select($table);
			$select->columns(array(new \Zend\Db\Sql\Expression("COUNT($field)")));
			if ($where) {
			    $select->where($where);
			}
		} catch (\Exception $e) {
			throw new \ZendServer\Exception("db count query preparation failed with the following error: " . $e->getMessage());
		}
		
		$resultSet = $this->selectWith($select, false, true)->toArray();
		
		if (!isset($resultSet[0])) {
			Log::warn("db COUNT query '{$select->getSqlString($this->getPlatform())}' did not return expected response");
			return 0;
		}

		return (integer) current($resultSet[0]);
	}
		
	/**
	 * @param Where|\Closure|string|array $where
	 * @return \Zend\Db\ResultSet\ResultSet|\ZendServer\Set|Array
	 */
	protected function select($where = null, $returnSet=true) {		
		$table = $this->getTableName();
		$select = new Select($table);
		if (! is_null($where)) {
		    $select->where($where);
		}
		return $this->selectWith($select, $returnSet);
	}
	
	/**
	 * 
	 * @param \Zend\Db\ResultSet\ResultSet $resultSet
	 * @return Array
	 */
	protected function resultSetToArray($resultSet) {
		if (is_array($resultSet)) {
			return $resultSet;
		}
		return $resultSet->toArray(); // placeholder where inheriting mappers can play with the ResultSet->toArray conversion
	}
	
	/**
	 * @param Sql\Select $select
	 * @return \ZendServer\Set|Array
	 * @throws \RuntimeException
	 */
	protected function selectWith(Select $select, $returnSet=true, $returnRaw=false) {
		$sql = $select->getSqlString($this->getPlatform());

	    try {
			$resultSet = $this->getTableGateway()->selectWith($select); /* @var $rowset \Zend\Db\ResultSet\ResultSet */
			Log::debug("query: '{$sql}' executed OK");
		}
		catch (\Exception $e) {
			$msg = "db SELECT query '{$sql}' failed with the following error: " . $e->getMessage();
			Log::err($msg);
			throw new \ZendServer\Exception($msg);
		}	

		if ($returnRaw) {
			return $resultSet;
		}
		
		if ($this->setClass && $returnSet) {
			return new Set($this->resultSetToArray($resultSet), $this->setClass);
		}
		
		return $this->resultSetToArray($resultSet);
	}
	
	/**
	 * @brief Same as "selectWith", but it retries to execute it several times in case of exception
	 * @param \Select $select 
	 * @param bool $returnSet 
	 * @param bool $returnRaw 
	 * @return  
	 */
	protected function selectWithRetries(Select $select, $returnSet=true, $returnRaw=false) {
		$queryResult = array();
		$that = $this;
		$retriesResult = $this->executeWithRetries(function() use ($that, $select, $returnSet, $returnRaw, &$queryResult) {
			$queryResult = $that->selectWith($select, $returnSet, $returnRaw);
		});
		
		if ($retriesResult === false) {
			throw new \WebAPI\Exception(_t('Error executing the query. Probably DB is locked'), \WebAPI\Exception::DATABASE_LOCKED);
		}
		
		return $queryResult;
	}
	
	/**
	 * @brief Execute the callback, if it throws an exception, retry to execute it {$maxNumberOfAttempts} times.
	 * @param \callable $callbackFn 
	 * @param array $callbackParameters
	 * @param int $maxNumberOfAttempts 
	 * @return  
	 */
	protected function executeWithRetries(callable $callbackFn, $callbackParameters = array(), $maxNumberOfAttempts = 20) {
		$attemptsSoFar = 0;
		while ($attemptsSoFar++ < $maxNumberOfAttempts) {
			try {
				call_user_func_array($callbackFn, $callbackParameters);
				break;
			} catch (\Exception $e) {
				// wait 100ms, maybe there will be a miracle, and the DB will be unlocked
				usleep(100 * 1000);
				
				// avoid flooding the log file - log the exception once
				$exceptionMessage = $attemptsSoFar == 1 ? $e->getMessage() : '';
				Log::notice('The requested method threw an exception. Retrying. ('.__FILE__.':'.__LINE__.')'."\n".$exceptionMessage);
			}
		}
		
		// reached the attempts limit and still the DB is locked - throw an exception
		if ($attemptsSoFar >= $maxNumberOfAttempts) {
			return false;
		}
		
		return true;
	}

	/**
	 * @param array $set
	 * @param Where|\Closure|string|array $where
	 * @return integer affectedRowsCount
	 */
	protected function update(array $set, $where = null) {
		try {
			$affectedRowsCount = $this->getTableGateway()->update($set, $where); /* @var $rowset \Zend\Db\ResultSet\ResultSet */
		}
		catch (\Exception $e) {
			throw new \ZendServer\Exception("db UPDATE query failed with the following error: " . $e->getMessage());
		}
	
		Log::debug("db UPDATE statement successfully executed on {$affectedRowsCount} rows");
		return $affectedRowsCount;
	}
	
	/**
	 * @param array $set
	 * @return integer LastInsertValue
	 */
	protected function insert(array $set) {
		try {
			$this->getTableGateway()->insert($set);
		}
		catch (\Exception $e) {
			throw new \ZendServer\Exception("db INSERT failed with the following error: " . $e->getMessage(), 0, $e->getPrevious());
		}		
		
		$value = $this->getTableGateway()->getLastInsertValue();
		Log::debug("db INSERT statement successfully executed, with lastValue {$value}");
		return $value;
	}

	/**
	 * @param Where|\Closure|string|array $where
	 * @return integer affectedRowsCount
	 */
	protected function delete($where) {
		try {
			$affectedRowsCount = $this->getTableGateway()->delete($where); /* @var $rowset \Zend\Db\ResultSet\ResultSet */
		}
		catch (\Exception $e) {
			throw new \ZendServer\Exception("db DELETE failed with the following error: " . $e->getMessage(), 0, $e);
		}
	
		Log::debug("db DELETE statement successfully executed on {$affectedRowsCount} rows");
		return $affectedRowsCount;
	}
		
	protected function isSqlite() {
		return $this->getPlatform()->getName() === 'SQLite';
	}
	
	protected function getSqlInStatement($field, $invalues) {
		return $field . ' IN ("' . implode('","', $invalues) . '")';
	}
	
	/**
	 * @return PlatformInterface
	 */
	protected function getPlatform() {
		if (is_null($this->platform)) {
			$this->platform = $this->getTableGateway()->getAdapter()->getPlatform();
		}
		return $this->platform;
	}

	/**
	 * @param PlatformInterface $platform
	 */
	public function setPlatform($platform) {
		$this->platform = $platform;
	}

}

