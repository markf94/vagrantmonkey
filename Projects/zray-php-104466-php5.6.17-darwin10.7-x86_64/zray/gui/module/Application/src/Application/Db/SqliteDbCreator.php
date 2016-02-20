<?php

namespace Application\Db;
use ZendServer\FS\FS;
use ZendServer\Log\Log;
use Zend\Db\Adapter\Adapter;

class SqliteDbCreator {
	const DATABASE_GUI_FILENAME = 'gui_sqlite_create_database.sql';
	const DATABASE_GUI_ACL_FIXTURES_FILENAME = 'gui_sqlite_acl_fixtures.sql';
	/**
	 * @var Adapter
	 */
	private $adapter;
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
	}
	
	public function createGuiDb() {
		
		$installDir = getCfgVar('zend.install_dir');
		
		$files = array(self::DATABASE_GUI_FILENAME, self::DATABASE_GUI_ACL_FIXTURES_FILENAME);
		$this->adapter->query('PRAGMA synchronous=OFF', Adapter::QUERY_MODE_EXECUTE);
		$this->adapter->query('pragma temp_store = memory', Adapter::QUERY_MODE_EXECUTE);
		$this->adapter->query('BEGIN TRANSACTION', Adapter::QUERY_MODE_EXECUTE);
		foreach ($files as $filename) {
			$schemaPath = FS::createPath($installDir, 'share', $filename);
			$schemaFile = FS::getFileObject($schemaPath);

			Log::info('Executing queries from ' . $schemaFile->getFilename());
			$queries = explode(';', $schemaFile->readAll());
			foreach ($queries as $query) {
				if ($query = trim($query)) {
					Log::debug("Execute query '{$query}'");
					$this->adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
				}
			}
		}
		$this->adapter->query('COMMIT TRANSACTION', Adapter::QUERY_MODE_EXECUTE);
		
	}
	
}

