<?php

namespace Application\Db;

use ZendServer\FS\FS;
use Zend\Config\Reader\Ini;
use ZendServer\Exception;
class DirectivesFileConnector extends Connector {
	/**
	 * 
	 * @var string
	 */
	private $filepath;
	
	public function __construct() {
		$this->filepath = FS::createPath(getCfgVar('zend.conf_dir'), DIRECTORY_SEPARATOR, 'zend_database.ini');
		$this->retrieveFileDirectives();
	}

	/**
	 * @return boolean
	 */
	public function isReady() {
		return (! is_null($this->dbConfig));
	}
	/**
	 * @return boolean
	 */
	public function isSqlite() {
		if (! $this->isReady()) {
			throw new Exception(_t('Connector object is not initialized'));
		}
		return ($this->dbConfig['type'] === Connector::DB_TYPE_SQLITE);
	}
	
	public function retrieveFileDirectives() {
		$config = new Ini();
		$dbDirectives = $config->fromFile($this->filepath);
		$this->dbConfig = $dbDirectives['zend']['database'];
	}
}

