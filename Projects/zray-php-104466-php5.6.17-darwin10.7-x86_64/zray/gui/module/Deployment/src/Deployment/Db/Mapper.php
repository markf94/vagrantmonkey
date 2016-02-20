<?php

namespace Deployment\Db;

use ZendServer\Exception;

use Zend\Db\TableGateway\TableGateway;

class Mapper {
	const STATUS_INITIALIZED 	= 0;
	const STATUS_DOWNLOADING 	= 1;
	const STATUS_ERROR			= 2;
	const STATUS_OK				= 3;
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $tableGateway;

	public function __construct(TableGateway $tableGateway = null) {
		$this->tableGateway = $tableGateway;
	}
	
	/**
	 * @param integet $libraryId
	 * @return array
	 */
	public function findByLibrary($libraryId) {
		$this->cleanOld();
		
		$library = $this->tableGateway->select(array('lib_id' => $libraryId))->current();
		if ($library instanceof \ArrayObject) {
			return $library->getArrayCopy();
		}
		throw new Exception(_t('Library \'%s\' was not found', array($libraryId)));
	}
	
	public function findByUrl($url) {
		$this->cleanOld();
		
		$library = $this->tableGateway->select(array('url' => $url))->current();
		if ($library instanceof \ArrayObject) {
			return $library->getArrayCopy();
		}
		throw new Exception(_t('Package \'%s\' was not found', array($url)));
	}
	
	/**
	 * @brief temporary function to update the download row. 
	 * Used when downloading the file directly from the GUI
	 * @param array $data 
	 * @return bool
	 */
	public function updateDownloadRow(array $data) {
		if (!isset($data['id'])) {
			return false;
		}
		
		return $this->tableGateway->update($data, array('id' => $data['id']));
	}
	
	public function deleteByUrl($url) {
		return $this->tableGateway->delete(array('url' => $url));
	}
	
	public function deleteById($id) {
		return $this->tableGateway->delete(array('id' => $id));
	}
	
	public function deleteByLibraryId($libraryId) {
		return $this->tableGateway->delete(array('lib_id' => $libraryId));
	}
	
	// clean records older than 1 day
	private function cleanOld() {
		$timestamp = strtotime('-1 day');
		$library = $this->tableGateway->delete('start_time < ' . $timestamp);
	}
}
