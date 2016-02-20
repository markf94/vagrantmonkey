<?php

namespace ZendServer\Filter;
use Configuration\MapperAbstract;
use ZendServer\Log\Log;
use Zend\Json\Json;

class Mapper extends MapperAbstract {
    
    /**
     * 
     * @param string $type
     * @throws \Exception
     * @return \ZendServer\Filter\FilterList
     */
    public function getByType($type) {
        if (!in_array($type, Filter::$types)) {
            throw new \Exception('Filters of type ' . $type . " does not exists");
        }
        
        $select = new \Zend\Db\Sql\Select();
        $select->from($this->getTableGateway()->getTable());
        $select->where(array("filter_type = ?" => $type));
        $select->order(array('custom', 'name'));
        return new FilterList($this->selectWith($select));
    }
    
    /**
     * 
     * @param int $id
     * @return \ZendServer\Filter\FilterList
     */
    public function getById($id) {
        $select = new \Zend\Db\Sql\Select();
        $select->from($this->getTableGateway()->getTable());
        $select->where(array("id = ?" => $id));
        return new FilterList($this->selectWith($select));
    }
    
    /**
     * @param string $name
     * @return integer
     */
    public function deleteFilterByName($name) {
    	return $this->delete(array('name = ?' => $name));
    }
    
    /**
     * 
     * @param string $name
     * @return \ZendServer\Filter\FilterList
     */
    public function getByName($name) {
        $select = new \Zend\Db\Sql\Select();
        $select->from($this->getTableGateway()->getTable());
        $select->where(array("name = ?" => $name));       
        
        return new FilterList($this->selectWith($select));
    }
    

    /**
     *
     * @param string $type
     * @param string $name
     * @return \ZendServer\Filter\FilterList
     */
    public function getByTypeAndName($type, $name) {
    	if (!in_array($type, Filter::$types)) {
    		throw new \Exception('Filters of type ' . $type . " does not exists");
    	}
    	
    	$select = new \Zend\Db\Sql\Select();
    	$select->from($this->getTableGateway()->getTable());
    	$select->where(array("filter_type = ?" => $type));
    	$select->where(array("name = ?" => $name));

    	return new FilterList($this->selectWith($select));
    }
    
    /**
     * Update/Insert new row
     * @param Filter $filter
     * @return number
     */
    public function upsert(Filter $filter) {
        $params = array (
            'filter_type' => $filter->getType(),
            'name' => $filter->getName(),
            'data' => Json::encode($filter->getData(), Json::TYPE_ARRAY),
            'custom' => 1,
        );
        if ($filter->getId()) {
            $this->update($params, array('id = ?' => $filter->getId()));
            return $filter->getId();
        }
        return $this->insert($params);
    }
}