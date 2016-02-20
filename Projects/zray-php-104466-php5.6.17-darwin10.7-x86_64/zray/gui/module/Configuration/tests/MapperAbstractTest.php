<?php
namespace Configuration;
use ZendServer\PHPUnit\TestCase;

use Zend\Db\Adapter\Adapter;

use Zend\Db\Adapter\Driver\Pdo\Pdo;

use Zend\Db\TableGateway\TableGateway;


use PHPUnit_Framework_TestCase,
ZendServer\Exception;

abstract class MapperAbstractTest extends TestCase {
	
	private $pdo = null; // only instantiate pdo once for test clean-up/fixture load
	private $dbAdapter = null;
	private $dbCreated=false;	

	protected $testedMapper;	
	protected $testedTable;
	/**
	 * @return \PDO
	 */
	public function getConnection()	{
		if ($this->pdo) return $this->pdo;
		return $this->pdo = new \PDO($this->getDbConn());
	}
	
	public function getTableName() {
		return $this->testedTable;
	}
	
	abstract protected function getTestedMapper();

	protected function getDbConn() {
		return 'sqlite::memory:';
	}

	protected function tearDown() {
		$this->pdo = null;
		$this->dbAdapter = null;
		unset($this->pdo);
		unset($this->dbAdapter);
		$this->dbCreated = false;
		unset($this->testedMapper);
		$this->testedMapper = null;
		parent::tearDown();
	}
	
	protected function setUp() {
		parent::setup();
			
		$this->getTestedMapper()->setTableGateway(new TableGateway($this->getTableName(), $this->getDbAdapter()));
		
		$this->createDB();
	}
	
	protected function getDbAdapter() {
		if ($this->dbAdapter instanceof Adapter) {
			return $this->dbAdapter;
		}
		$this->dbAdapter = new Adapter(new Pdo($this->getConnection()));
		return $this->dbAdapter;
	}

	protected function sqlGetContents() {
		return file_get_contents("{$this->getZendInstallDir()}/share/zsd_sqlite_create_schema.sql");
	}
	
	private function createDB() {
		if (!$this->dbCreated) {
			$this->execSqlFile();	
			$this->dbCreated = true;			
		}	
		
		$this->insertData();
	}
	
	private function execSqlFile() {
		$queries = explode (";\n", $this->sqlGetContents());

		foreach ($queries as $query) {
			if (!$query) continue;
			$this->getConnection()->exec($query);
			if ($this->getConnection()->errorCode() !== '00000') {
				self::fail("Invalid query [$query]: " . $this->getConnection()->errorCode());
			}
		}

	}
	
	private function insertData() {
		$table = $this->getTableName();
		$columns = $this->getTableColumns();
		
		foreach($this->getRows() as $idx=>$row) {
			$this->getConnection()->exec("INSERT INTO $table ($columns) VALUES ($row);");	
			if ($this->getConnection()->errorCode() !== '23000' && $this->getConnection()->errorCode() !== '00000') {
				self::fail("Invalid INSERT query [$idx]: " . print_r($this->getConnection()->errorInfo(), true));
			}
		}
	}
	
	abstract protected function getRows();
	abstract protected function getTableColumns();

}
