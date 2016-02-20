<?php

namespace Application\Db;
use ZendServer\FS\FS;

use Zend\Db\Adapter\Adapter;
use Zend\EventManager\Event;
use ZendServer\Log\Log;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Bootstrap\Mapper;
use Application\ConfigAwareInterface;
use Zend\ServiceManager\Exception\InvalidArgumentException;
use Zend\EventManager\EventsCapableInterface;
use Zend\EventManager\EventManager;


// service manager aware is required here - during creation of this class, we cannot produce db dependent classes, they will all attempt to build the db
abstract class Connector  {
	
	const DB_CONTEXT_GUI = 'guidbadapter';
	const DB_CONTEXT_MONITOR = 'monitordbadapter';
	const DB_CONTEXT_STATS = 'statsdbadapter';
	const DB_CONTEXT_CODETRACING = 'codetracingdbadapter';
	const DB_CONTEXT_JOBQUEUE = 'jobqueuedbadapter';
	const DB_CONTEXT_ZSD = 'zsddbadapter';
	const DB_CONTEXT_ZDD = 'zddadapter';
	const DB_CONTEXT_DEVBAR = 'devbaradapter';
	const DB_CONTEXT_UrlInsight = 'urlinsightadapter';
	
	const DB_TYPE_SQLITE = 'SQLITE';
	
	/**
	 * @var array[\Zend\Db\Adapter\Adapter]
	 */
	protected static $dbs;
	
	/**
	 * @var array
	 */
	protected $dsns = array(
		self::DB_CONTEXT_GUI => 'gui.db',
		self::DB_CONTEXT_MONITOR => 'monitor.db',
		self::DB_CONTEXT_STATS => 'statistics.db',
		self::DB_CONTEXT_CODETRACING => 'codetracing.db',
		self::DB_CONTEXT_JOBQUEUE => 'jobqueue.db',
		self::DB_CONTEXT_ZSD => 'zsd.db',
		self::DB_CONTEXT_ZDD => 'deployment.db',
		self::DB_CONTEXT_DEVBAR => 'devbar.db',
		self::DB_CONTEXT_UrlInsight => 'urlinsight.db',
	);
	
	/**
	 * @var array
	 */
	protected $dbConfig;

	/**
	 * @param string $name
	 * @return Adapter
	 */
	public function createDbAdapter($name) {
		$pdoConfig = array();
		if ($this->dbConfig['type'] == self::DB_TYPE_SQLITE) {
			return $this->createSqliteAdapter($name);
		} else {
			return $this->createMysqlAdapter($name);
		}
	}

	/**
	 * @param string $name
	 * @return \Zend\Db\Adapter\Adapter
	 */
	private function createMysqlAdapter($name) {
		if (! isset(static::$dbs['mysql'])) {
			$conn = strtolower($this->dbConfig['type']). ":host={$this->dbConfig['host_name']};port={$this->dbConfig['port']}; dbname={$this->dbConfig['name']}";
			$pdoConfig = array(
					'driver'         => 'Pdo',
					'dsn'            => $conn,
					'username'       =>$this->dbConfig['user'],
					'password'      =>$this->dbConfig['password'],
					'driver_options' => array(
					),
			);
			static::$dbs['mysql'] = new Adapter($pdoConfig);
		}
		return static::$dbs['mysql'];
	}
	
	
	/**
	 * @param string $name
	 * @return \Zend\Db\Adapter\Adapter
	 */
	private function createSqliteAdapter($name) {
		if (! isset($this->dsns[strtolower($name)])) {
			throw new InvalidArgumentException('Unknown database name');
		}
		
		if (! isset(static::$dbs[$name])) {
			
			$path = FS::createPath(getCfgVar('zend.data_dir'), 'db', $this->dsns[strtolower($name)]);
			$pdoConfig = array(
					'driver'         => 'Pdo',
					'dsn'            => "sqlite:{$path}",
					'username'       =>'',
					'password'      =>'',
					'driver_options' => array(
					),
			);
			
			$adapter = new Adapter($pdoConfig);
			$adapter->query("PRAGMA busy_timeout=6000");
			
			// set journal mode from the config if it was set, the default behaviur is PRAGMA journal_mode=='DELETE' in purpose to solve #ZSRV-14461
			if (isset($this->dbConfig['sqlite_journal_mode'])) {
			    $sqliteJournalMode = $this->dbConfig['sqlite_journal_mode'];
			    $adapter->query("PRAGMA journal_mode={$sqliteJournalMode}");
			}
			
			static::$dbs[$name] = $adapter;
			
			if (stripos($name, 'gui') !== false ) {
				try {
					$counterObj = $adapter->query('SELECT COUNT(*) counter FROM sqlite_master WHERE type=\'table\' AND name=\'GUI_METADATA\'', Adapter::QUERY_MODE_EXECUTE)->current();
					if ((! isset($counterObj->counter)) || (! $counterObj->counter)) {
						$this->getEventManager()->trigger('missingMetadata', null, array('adapter' => $adapter));
					}
				} catch (\Exception $ex) {
					Log::emerg($ex->getMessage());
				}
			}
			
		}
		return static::$dbs[$name];
	}

}
