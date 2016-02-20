<?php

namespace ZendServer\PHPUnit\DbUnit;

class ArrayDataSet extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet {
	/**
     * @var array
     */
    protected $tables = array();
 
    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data AS $tableName => $rows) {
            $columns = array();
            if (isset($rows[0])) {
                $columns = array_keys($rows[0]);
            }
 
            $metaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $columns);
            $table = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);
 
            foreach ($rows AS $row) {
                $table->addRow($row);
            }
            $this->tables[$tableName] = $table;
        }
    }
 
    /**
     * @param boolean $reverse
     * @return \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator
     */
    protected function createIterator($reverse = FALSE)
    {
        return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
    }
 
    /**
     * @param string $tableName
     * @throws \InvalidArgumentException
     * @return \PHPUnit_Extensions_Database_DataSet_DefaultTable
     */
    public function getTable($tableName)
    {
        if (!isset($this->tables[$tableName])) {
            throw new \InvalidArgumentException("$tableName is not a table in the current database.");
        }
 
        return $this->tables[$tableName];
    }
}

