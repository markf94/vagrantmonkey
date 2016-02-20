<?php
namespace Plugins\Db;

use ZendServer\Log\Log,
ZendServer\Set,
Zend\Db\TableGateway\TableGateway;

class UpdatesMapper {
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $tableGateway;

	public function __construct(TableGateway $tableGateway = null) {
		$this->tableGateway = $tableGateway;
	}
	
	/**
	 * @return Set
	 */
	public function getUpdates() {
		$updates = $this->tableGateway->select();
		$updatesByName = array();
		foreach ($updates as $update) {
		    $updatesByName[$update['NAME']] = $update;
		}
		return $updatesByName;
	}
	
	/**
	 * @return Set
	 */
	public function getActiveUpdates() {
	    $updates = $this->tableGateway->select();
	    $updatesActive = array();
	    foreach ($updates as $update) {
	        $extra = json_decode($update['EXTRA_DATA'], true);
	        if ($extra["needs_update"] == 'true') {
	           $updatesActive[$update['NAME']] = $update;
	        }
	    }
	    return $updatesActive;
	}
	
	public function getUpdate($name) {
		return $this->tableGateway->select(array('name' => $name));
	}
	
	public function deleteUpdate($name) {
		$this->tableGateway->delete(array('name' => $name));
	}
	
	public function addUpdate($name, $version, $extraData) {
		$this->tableGateway->insert(
				array(	'NAME'	=> $name,
						'VERSION' => $version,
						'EXTRA_DATA' => $extraData,
				));
	}
	
}
