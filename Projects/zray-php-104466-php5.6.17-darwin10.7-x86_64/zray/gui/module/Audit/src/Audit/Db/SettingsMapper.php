<?php

namespace Audit\Db;

use ZendServer\Log\Log;

use ZendServer\Set,
	\Configuration\MapperAbstract;

use Zend\Db\TableGateway\TableGateway;

class SettingsMapper extends MapperAbstract {
	
	/**
	 * @var \Zend\Db\TableGateway\TableGateway
	 */
	private $propertiesTable;
	
	public function getHistory() {
	    $result = $this->getPropertiesTable()->select("PROPERTY_NAME = 'AUDIT_HISTORY'")->toArray();
	    return $result[0]['PROPERTY_VALUE'];
	}
	
	public function getEmail() {
	    $result = $this->getPropertiesTable()->select("PROPERTY_NAME = 'AUDIT_EMAIL'")->toArray();
	    return $result[0]['PROPERTY_VALUE'];
	}
	
	public function getScriptUrl() {
	    $result = $this->getPropertiesTable()->select("PROPERTY_NAME = 'AUDIT_URL'")->toArray();
	    return $result[0]['PROPERTY_VALUE'];
	}
	
	/**
	 *
	 * @param integer $value
	 */
	public function setHistory($value) {
	   $this->setProperty('AUDIT_HISTORY', $value);
	}
	
	/**
	 * 
	 * @param array $data
	 */
	public function setEmail($email) {
	    $this->setProperty('AUDIT_EMAIL', $email);
	}
	
	/**
	 *
	 * @param array $data
	 */
	public function setURL($callbackUrl) {
	    $this->setProperty('AUDIT_URL', $callbackUrl);
	}
	
	/**
	 * @return \Zend\Db\TableGateway\TableGateway $propertiesTable
	 */
	public function getPropertiesTable() {
		return $this->propertiesTable;
	}
	
	/**
	 * @param \Zend\Db\TableGateway\TableGateway $propertiesTable
	 * @return \Audit\Db\Mapper
	 */
	public function setPropertiesTable($propertiesTable) {
		$this->propertiesTable = $propertiesTable;
		return $this;
	}

	protected function setProperty($name, $value) {
		$result = $this->getPropertiesTable()->select("PROPERTY_NAME = '$name'")->toArray();
	    if (! empty($result)) {
	    	$this->getPropertiesTable()->update(array('PROPERTY_VALUE' => $value), array('PROPERTY_NAME' => $name));
	    } else { 
	        $this->getPropertiesTable()->insert(array('PROPERTY_VALUE' => $value, "PROPERTY_NAME" => $name));
	    }
	}
}
