<?php

namespace Application\Db;

use ZendServer\FS\FS;
use ZendServer\Log\Log;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Adapter;
use ZendServer\Exception;

class Creator {
	const CONNECTION_TESTING_TIMEOUT = 3;
	const ZEND_DB_USER_NAME = 'zend';
	const DBSCHEMA_FILENAME_ZSD = 'zsd_mysql_create_schema.sql';
	const DBSCHEMA_FILENAME_MONITOR = 'mysql_create_monitor_db.sql';
	const DBSCHEMA_FILENAME_MONITOR_RULES = 'mysql_create_monitor_rules_db.sql';
	const DBSCHEMA_FILENAME_PAGECACHE_RULES = 'mysql_create_pagecache_rules_db.sql';
	const DBSCHEMA_FILENAME_MONITOR_RULES_FIXTURES = 'create_monitor_rules_fixtures.sql';
	const DBSCHEMA_FILENAME_DEPLOYMENT = 'deployment_mysql_create_database.sql';
	const DBSCHEMA_FILENAME_UrlInsight = 'urlinsight_mysql_create_database.sql';
	const DBSCHEMA_FILENAME_JOBQUEUE = 'jobqueue_mysql_create_database.sql';
	const DBSCHEMA_FILENAME_STATISTICS = 'statistics_mysql_create_database.sql';
	const DBSCHEMA_FILENAME_GEOGRAPHIC = 'stats_mysql_fixtures.sql';
	const DBSCHEMA_FILENAME_MESSAGE_CENTER = 'mysql_create_message_center_db.sql';
	const DBSCHEMA_FILENAME_GUI = 'gui_mysql_create_database.sql';
	const DBSCHEMA_FILENAME_ACL_FIXTURES_GUI = 'gui_mysql_acl_fixtures.sql';
	const DBSCHEMA_FILENAME_DEVBAR = 'devbar_mysql_create_database.sql';
	const DBSCHEMA_FILENAME_DEVBAR_FIXTURES_GUI = 'devbar_fixtures.sql';
	const CREATE_DB_LOCK = 'CREATE_DB_LOCK';
	
	/**
	 *
	 * @var Adapter
	 */
	private $dbAdapter;
	private $schema;
	private $lock = false;
	public function __construct($dsn, $user, $pass, $schema) {
		
		if (! extension_loaded('pdo_mysql')) {
			throw new Exception(_t('Database connection failed, pdo_mysql driver must be loaded'), Exception::DATABASE_MISSING_DRIVER);
		}
		set_time_limit(120); /// Arbitrarily extend time limit for save action in case of locks or slow response
		$this->dbAdapter = new Adapter ( new Pdo ( new \PDO ( $dsn, $user, $pass, array (
				\PDO::ATTR_TIMEOUT => self::CONNECTION_TESTING_TIMEOUT 
		) ) ) );
		
		$this->schema = $schema;
	}
	public function zendUserExists() {
		$stmt = $this->dbAdapter->query ( 'SELECT COUNT(*) as counted_users FROM mysql.user where user = ?' );
		/* @var $stmt \Zend\Db\Adapter\Driver\Pdo\Statement */
		$result = $stmt->execute ( array (
				self::ZEND_DB_USER_NAME 
		) );
		/* @var $stmt \Zend\Db\Adapter\Driver\Pdo\Result */
		$result = $result->current ();
		if ($result ['counted_users']) {
			return true;
		}
		return false;
	}
	public function getAdapter() {
		try {
			$this->dbAdapter->query ( 'use ' . $this->schema, Adapter::QUERY_MODE_EXECUTE );
		} catch ( \Exception $e ) {
			Log::notice ( "Cannot use database {$this->schema} for cluster profile check. " . $e->getMessage () );
			return null;
		}
		
		return $this->dbAdapter;
	}
	
	public function hasLock() {
		return $this->lock;
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function getLock() {
		$result = $this->dbAdapter->query ( 'SELECT GET_LOCK("' . self::CREATE_DB_LOCK . '", 1) as lockObtained', Adapter::QUERY_MODE_EXECUTE )->current ();
		$this->lock = $result ['lockObtained'] === '1';
		return $this->lock;
	}
	
	public function releaseLock() {
		$this->lock = false;
		return $this->dbAdapter->query ( 'SELECT RELEASE_LOCK("' . self::CREATE_DB_LOCK . '")' );
	}
	
	/**
	 *
	 * @return array information about the credentials that were created
	 */
	public function createZendUser() {
		$password = $this->createRandomePassword ();
		// unset old_passwords variable for drizzle support (ZSRV-1599)
		$this->dbAdapter->query ( 'set @old = (select @@old_passwords) ;', Adapter::QUERY_MODE_EXECUTE );
		$this->dbAdapter->query ( 'set @@old_passwords = 0;' );
		
		$this->dbAdapter->query ( 'CREATE USER \'' . self::ZEND_DB_USER_NAME . '\'@\'%\' IDENTIFIED BY \'' . $password . '\'', Adapter::QUERY_MODE_EXECUTE );
		$this->dbAdapter->query ( 'CREATE USER \'' . self::ZEND_DB_USER_NAME . '\'@\'localhost\' IDENTIFIED BY \'' . $password . '\'', Adapter::QUERY_MODE_EXECUTE );
		
		// reset old_passwords variable
		$this->dbAdapter->query ( 'set @@old_passwords = @old ;', Adapter::QUERY_MODE_EXECUTE );
		
		$this->dbAdapter->query ( "FLUSH PRIVILEGES", Adapter::QUERY_MODE_EXECUTE );
		
		return array (
				'username' => self::ZEND_DB_USER_NAME,
				'password' => $password 
		);
	}
	
	/**
	 *
	 * @return string
	 */
	private function createRandomePassword() {
		// / truncate to 16 characters to avoid drizzle limitations for password lengths
		return substr ( base64_encode ( \Zend\Crypt\Hash::compute ( 'sha1', mt_rand ( 100000000, 999999999 ), true ) ), 0, 16 );
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function schemaExists() {
		try {
			$this->dbAdapter->query ( 'use ' . $this->schema, Adapter::QUERY_MODE_EXECUTE );
			$result = $this->dbAdapter->query ( 'SELECT * FROM schema_properties limit 1', Adapter::QUERY_MODE_EXECUTE );
		} catch ( \Exception $e ) {
			return false;
		}
		
		if (! $result || ! sizeof ( $result )) {
			return false;
		}
		
		return true;
	}
	public function createSchema() {
		Log::debug ( "Creating schema {$this->schema}" );
		$this->dbAdapter->query ( 'CREATE DATABASE IF NOT EXISTS ' . $this->schema, Adapter::QUERY_MODE_EXECUTE );
		$this->dbAdapter->query ( 'use ' . $this->schema, Adapter::QUERY_MODE_EXECUTE );
		
		$installDir = get_cfg_var ( 'zend.install_dir' );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_MONITOR );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_DEPLOYMENT );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_UrlInsight );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_JOBQUEUE );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_ZSD );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_MONITOR_RULES );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_MONITOR_RULES_FIXTURES );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_PAGECACHE_RULES );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_STATISTICS );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_GEOGRAPHIC );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_MESSAGE_CENTER );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_GUI );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_ACL_FIXTURES_GUI );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_DEVBAR );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		$schemaPath = FS::createPath ( $installDir, 'share', self::DBSCHEMA_FILENAME_DEVBAR_FIXTURES_GUI );
		$schemaFiles [] = FS::getFileObject ( $schemaPath );
		
		try {
			foreach ( $schemaFiles as $schemaFile ) { /* @var $schemaFile \SplFileobject */
				Log::debug ( 'Executing queries from ' . $schemaFile->getFilename () );
				$this->dbAdapter->query ( $schemaFile->readAll(), Adapter::QUERY_MODE_EXECUTE );
			}
		} catch ( \Exception $e) {
			Log::err ( "query failed with the following error: " . $e->getMessage () );
			throw new \ZendServer\Exception ( "Failed query during database creation", null, $e );
		}
		
		Log::info ( 'Schema created successfully' );
	}
	public function grantPermissions($schema, $user) {
		$privileges = array (
				'Create_priv' => 'CREATE',
				'Drop_priv' => 'DROP',
				'Alter_priv' => 'ALTER',
				'Delete_priv' => 'DELETE',
				'Index_priv' => 'INDEX',
				'Insert_priv' => 'INSERT',
				'Select_priv' => 'SELECT',
				'Update_priv' => 'UPDATE',
				'Create_tmp_table_priv' => 'CREATE TEMPORARY TABLES',
				'Lock_Tables_priv' => 'LOCK TABLES',
				'Create_view_priv' => 'CREATE VIEW',
				'Show_view_priv' => 'SHOW VIEW',
				'Alter_routine_priv' => 'ALTER ROUTINE',
				'Create_routine_priv' => 'CREATE ROUTINE',
				'Execute_priv' => 'EXECUTE' 
		);
		
		$privilegesList = implode ( ',', $privileges );
		
		$query = "GRANT {$privilegesList} ON `$schema`.* TO '" . $user . "'@'%'";
		$this->dbAdapter->query ( $query, Adapter::QUERY_MODE_EXECUTE );
		$query = "GRANT {$privilegesList} ON `$schema`.* TO '" . $user . "'@'localhost'";
		$this->dbAdapter->query ( $query, Adapter::QUERY_MODE_EXECUTE );
		Log::debug ( "granting permissions to '{$user}' user: {$query}" );
	}
	
	public function cleanUpZend($schema) {
		Log::info('DB Cleanup initiated');
		/// Removed cleanup code - overkill
		Log::warn ("Could not clean up after zend schema creation process. Cleanup should be performed manually" );
	}
}
