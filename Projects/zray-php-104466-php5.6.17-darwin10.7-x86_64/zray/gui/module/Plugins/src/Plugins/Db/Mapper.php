<?php

namespace Plugins\Db;

use ZendServer\Log\Log,
    ZendServer\Set,
    Zend\Db\TableGateway\TableGateway,
    \Configuration\MapperAbstract,
    Zend\Db\Sql\Select,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Predicate\Predicate;

class Mapper extends MapperAbstract {
	
	protected $defaultField = 'AUDIT_ID';
	
	protected $setClass = '\Plugins\PluginContainer';
	
	const ID = 'id';
	const NAME = 'name';
	const UNIQUE_PLUGIN_ID = 'unique_plugin_id';
	const VERSION = 'version';
	const LOGO = 'logo';
	const MESSAGE = 'message';
	const DESCRIPTION = 'description';
	const CREATION_TIME = 'creation_time';
	
	public function findAuditMessage($auditId) {
		return $this->select(array('plugin_id'=>$auditId));
	}
		
	/**
	 * 
	 * @param string $orderBy
	 * @param string $direction
	 * @param array $filters
	 * @return Ambigous <\ZendServer\Set, multitype:>
	 */
	public function findAllPlugins($orderBy = self::ID, $direction = 'Desc',  $filters = array()) {
		$table = $this->getTableGateway()->getTable();
		$select = new Select($table);
		$select->join('deployment_plugins_versions', 'deployment_plugins.plugin_id = deployment_plugins_versions.plugin_id', Select::SQL_STAR);
		$select->group('deployment_plugins_versions.plugin_id');
		$filters = $this->getFilters($filters); /* @var $filters Where */
		$select->where($filters);
		$select->order($orderBy . ' ' . $direction);
		return $this->selectWith($select);
	}
	
	/**
	 * @return array
	 */
	public function getPluginTypeDictionary() {
	    return array(
	        1 => _t('Z-Ray'),
	        2 => _t('ROUTE'),
	        3 => _t('GUI'),
	    );
	}
	
	/**
	 * @return array
	 */
	public function getDictionaryType() {
	    return array(
	        1 => 'type_zray',
	        2 => 'type_route',
	        3 => 'type_zs_ui',
	    );
	}
	
	/**
	 * @param array $filters
	 * @return Where
	 */
	private function getFilters(array $filters = array()) {
	    $where = new Where();
	    
	    if (isset($filters['type']) && $filters['type']) {
            $predicate = new Predicate(null, Predicate::OP_OR);
            foreach ($filters['type'] as $type) {
        	    $predicate->equalTo("deployment_plugins_versions.$type", '1');
	        }
            $where->addPredicate($predicate);
	    }
	
	    if (isset($filters['freeText']) && $filters['freeText']) {
	       
	        $predicate = new Predicate(null, Predicate::OP_OR);
	        $predicate->like("deployment_plugins_versions.message", "%" . $filters['freeText'] . "%");
	        $predicate->like("deployment_plugins.name", "%" . $filters['freeText'] . "%");
	        $where->addPredicate($predicate);
	        
	    }
	    
	    return $where;
	}
	
}
