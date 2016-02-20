<?php

namespace Configuration;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;
use ZendServer\Log\Log;
use Zend\Db\Sql\Sql;

class MapperExtensions extends MapperAbstract {

	protected $setClass='\Configuration\ExtensionContainer';
	
	const NAME = 'NAME';
	const IS_ZEND_COMPONENT = 'IS_ZEND_EXTENSION';
	const IS_INSTALLED = 'IS_INSTALLED';
	const IS_LOADED = 'IS_LOADED';	

	
	/**
	 * @return \ZendServer\Set[\Configuration\ExtensionContainer]
	 */
	public function selectAllExtensions() {
		return $this->select();		
	}	


	/**
	 * @return array sql queries
	 */
	public function getExportData() {		
		$data = array();		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$res = $this->selectWith($select)->toArray();		
		$intVals =  array("IS_INSTALLED", "IS_LOADED");
		
		foreach ($res as $row) {			
			foreach ($row as $key => $val) {
				if (in_array($key, $intVals)) {
					$row[$key] = intval($row[$key]);
				} else {
					$row[$key] = "'" . $row[$key] . "'";
				}
			}		
			$line = "REPLACE INTO " . (string) $this->getTableGateway()->getTable() . " (" . implode(",", array_keys($row)) . ') VALUES (' . implode(",", array_values($row)) . ");";
			$data[] = $line; 
		}		
		
		return $data; 
	}
	

	/**
	 * @return \ZendServer\Set[\Configuration\ExtensionContainer]
	 */
	public function selectAllZendExtensions() {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$where = new Where();
		
		$where->equalTo(self::IS_ZEND_COMPONENT, 1);
		
		$select->where($where);
		$select->order(array(self::NAME => 'ASC')); // A-Z
		return $this->selectWith($select);
	}
	
	/**
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function selectAllPHPExtensions() {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$where = new Where();
		
		$where->equalTo(self::IS_ZEND_COMPONENT, 0);
		
		$select->where($where);
		return $this->selectWith($select);
	}
	
	/**
	 * Check if the extension installed
	 * @param string $extName
	 * @return bool
	 */
	public function isExtensionInstalled($extName) {
	    $installedExtensions = $this->selectAllPHPExtensionsInstalled();
	    foreach ($installedExtensions as $extensionData) {
	        if (strcasecmp($extensionData->getName(), $extName) == 0) {
	            return true;
	        }
	    }
	    return false;
	}
	
	/**
	 * @return \ZendServer\Set[\Configuration\ExtensionContainer]
	 */
	public function selectAllPHPExtensionsInstalled() {
		
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$where = new Where();
		
		$newRecord = new Predicate();
		$newRecord->equalTo(self::IS_ZEND_COMPONENT, 0);
		
		$loadedOrInstalled = new Predicate(array(), Where::COMBINED_BY_OR);
		$loadedOrInstalled->equalTo(self::IS_LOADED, 1);
		$loadedOrInstalled->equalTo(self::IS_INSTALLED, 1);
		
		$where->addPredicate($newRecord);
		$where->addPredicate($loadedOrInstalled);
		
		$select->where($where);
		return $this->selectWith($select);
	}
	
	/**
	 * @return \ZendServer\Set[\Configuration\ExtensionContainer]
	 */
	public function selectExtensions(array $extensions) {
		/// the IN predicate forces the identifier to be specifically quoted, ruining LOWER(NAME)
		$predicate = 'LOWER('.self::NAME.') IN ("' . implode('","', array_map('strtolower', $extensions)).'")';
		return $this->select(array($predicate)); // in values, should look like: ("bcmath","bz2")
	}	
	/**
	 * @return \Configuration\ExtensionContainer
	 */
	public function selectExtension($extension) {		
		return $this->selectExtensions(array($extension))->current(); // in values, should look like: ("bcmath","bz2")
	}
	
	/**
	 * @param string $extension
	 * @return boolean
	 */
	public function isExtensionLoaded($extension) {
		return (Boolean) $this->selectExtension($extension)->isLoaded(); // whether the array is empty or not
	}
}
