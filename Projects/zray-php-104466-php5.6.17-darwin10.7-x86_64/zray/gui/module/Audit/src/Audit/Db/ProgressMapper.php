<?php

namespace Audit\Db;
use Zend\Json\Json;


use Audit\ProgressContainer,
\Configuration\MapperAbstract,
ZendServer\Log\Log,
ZendServer\Set,
Zend\Db\TableGateway\TableGateway,
Zend\Db\Sql\Select;

class ProgressMapper extends MapperAbstract {
	
	const AUDIT_NO_PROGRESS = "AUDIT_NO_PROGRESS";
	
	const AUDIT_PROGRESS_REQUESTED = 'AUDIT_PROGRESS_REQUESTED';
	const AUDIT_PROGRESS_STARTED = 'AUDIT_PROGRESS_STARTED';
	const AUDIT_PROGRESS_ENDED_SUCCESFULLY = 'AUDIT_PROGRESS_ENDED_SUCCESFULLY';
	const AUDIT_PROGRESS_ENDED_FAILED = 'AUDIT_PROGRESS_ENDED_FAILED';
	
	protected $setClass = '\Audit\ProgressContainer';
	
	protected $tableColumns = array(
									'AUDIT_PROGRESS_ID',
									'AUDIT_ID',
									'NODE_ID',
									'NODE_IP',
									'NODE_NAME',
									'CREATION_TIME',
									'PROGRESS',
									'EXTRA_DATA'
							);

	protected $progressStrings = array(
			0 => 'AUDIT_PROGRESS_REQUESTED',
			1 => 'AUDIT_PROGRESS_STARTED',
			2 => 'AUDIT_PROGRESS_ENDED_SUCCESFULLY',
			3 => 'AUDIT_PROGRESS_ENDED_FAILED',
	);	
	
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findMessageDetails($auditId) {
		return $this->select(array('AUDIT_ID'=>$auditId));
	}
	
	/**
	 * @return \ZendServer\Set
	 */
	public function findMessageDetailsErrorOnly($auditId) {
		/// retrieve all progress rows that are not successful
		$flippedProgress = array_flip($this->progressStrings);
		return $this->select(array('AUDIT_ID' => $auditId, 'PROGRESS' => $flippedProgress[self::AUDIT_PROGRESS_ENDED_FAILED]));
	}
	
	public function findMessagesProgressData(array $auditIds) {
		$predicate = 'AUDIT_ID IN ("' . implode('","', $auditIds) . '")';
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->columns(array('AUDIT_ID', 'PROGRESS'));
		$select->where($predicate);
		
		$shortRows = $this->selectWith($select, false);		
		$progressDataWithKeys = array();
		foreach ($shortRows as $shortRow) {
			$progressDataWithKeys[$shortRow['AUDIT_ID']][] = $shortRow['PROGRESS'];
		}
		
		return $progressDataWithKeys;
	}
	
	public function addAuditProgress(ProgressContainer $progress) {
		$progressRecord = $progress->toArray();
		$progressRecord['progress'] = array_search($progressRecord['progress'], $this->progressStrings);
		$progressRecord['extraData'] = Json::encode($progressRecord['extraData']);
		
		$this->getTableGateway()->insert(array_combine($this->tableColumns, $progressRecord));
	}
	
	public function getProgressStrings() {
		return array(
			0 => 'In Progress',
			2 => 'Ok',
			3 => 'Failed');
	}

	/**
	 *
	 * @param \Zend\Db\ResultSet\ResultSet $resultSet
	 * @return Array
	 */
	protected function resultSetToArray($resultSet) {
		$responseData = $resultSet->toArray();
	
		foreach($responseData as $idx=>&$auditProgressMessage) {
			if (isset($auditProgressMessage['PROGRESS']) && is_numeric($auditProgressMessage['PROGRESS'])) {
				$auditProgressMessage['PROGRESS'] = $this->getProgressString($auditProgressMessage['PROGRESS']);
			}
			if (isset($auditProgressMessage['EXTRA_DATA']) && strlen($auditProgressMessage['EXTRA_DATA'])) {
				$auditProgressMessage['EXTRA_DATA'] = Json::decode($auditProgressMessage['EXTRA_DATA']);
			}
		}
		return $responseData;
	}
	
	/**
	 * 
	 * @param integer $progress
	 * @throws \ZendServer\Exception
	 * @return string
	 */
	protected function getProgressString($progress) {
		if (isset($this->progressStrings[$progress])) {
			return $this->progressStrings[$progress];
		}
	
		throw new \ZendServer\Exception("Unkown audit type {$progress}");
	}	
}
