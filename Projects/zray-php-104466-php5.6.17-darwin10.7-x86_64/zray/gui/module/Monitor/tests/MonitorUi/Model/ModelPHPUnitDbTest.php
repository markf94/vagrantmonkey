<?php
namespace MonitorUi\Model;

if (! class_exists('PHPUnit_Extensions_Database_TestCase')) {
	return;
}

use ZendServer\PHPUnit\DbUnit\TestCase;

use PHPUnit_Framework_TestCase;
use Issue\Db\Mapper;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Adapter\Platform\Sqlite;

require_once 'tests/bootstrap.php';

class ModelPHPUnitDbTest extends TestCase
{
	/**
	 * @var Model
	 */
	private $model;
	
	/**
	 * @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 */
	private $db;
	
	public function testGetIssuesCountDb() {
		self::assertEquals(1, $this->model->getIssuesCount(array()));
	}
	
	public function testGetIssues() {
	
		$expectedLimit = array(
				ZM_LIMITS_FIRST_ITEM	=> 2,
				ZM_LIMITS_NUM_OF_ITEMS	=> 7
		);
	
		$expectedSortby = array(
				ZM_SORTBY_DATA		=> ZM_DATA_LAST_TIMESTAMP,
				ZM_SORTBY_ORDER_ASC	=> true
		);
	
		// ok if no exception thrown
		$this->model->getIssues(array(), 7, 2, 'date', 'ASC');
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getConnection()
	 */
    public function getConnection()
    {
    	if (is_null($this->db)) {
	        $pdo = new \PDO('sqlite::memory:');
	        $monitorSql = file_get_contents($this->getZendInstallDir() . DIRECTORY_SEPARATOR . "share/create_monitor_db.sql");
	        $pdo->exec($monitorSql);
	        $this->db = $this->createDefaultDBConnection($pdo, ':memory:');
    	}
    	return $this->db;
    }
    
    public function getAdapter() {
    	$pdo = $this->db->getConnection();
    	$driver = new Pdo($pdo);
    	return new Adapter($driver, new Sqlite($driver));
    }
 
    /* (non-PHPdoc)
     * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
     */
    public function getDataSet()
    {
        return new ArrayDataSet(array(
        	'events' => array(
	        	array('event_id' => 1, 'issue_id' => 1, 'request_id' => 1, 'script_id' => 1, 'event_type' => 1,
	        			'severity' => 1, 'agg_key' => 'key', 'repeats' => 1, 'first_timestamp' => time(), 'last_timestamp' => time(),
	        			'cluster_issue_id' => 1, 'app_id' => -1
        		)
	        ),
        	'issues' => array(
	        	array('id' => 1, 'cluster_issue_id' => 1, 'agg_key' => 'key', 'rule_name' => 'rule', 'event_type' => 1, 'severity' => 1,
	        			'repeats' => 1, 'first_timestamp' => time(), 'last_timestamp' => time(), 'full_url' => 'http://full_url',
	        			'status' => 2
        		)
	        ),
        	'matched_rules' => array(
	        	array('id' => 1, 'event_id' => 1)
	        ),
        ));
    }
	
	protected function setUp() {
		parent::setUp();
		$this->model = new Model();
		
		$identityFilter = $this->getMock('Deployment\IdentityFilterSimple');
		$identityFilter->expects($this->once())->method('filterAppIds')->will($this->returnValue(array(-1)));
		$identityFilter->expects($this->once())->method('setAddGlobalAppId');
		
		$mapper = new Mapper(new TableGateway('events', $this->getAdapter()));
		$mapper->setIdentityFilter($identityFilter);
		$this->model->setIssueMapper($mapper);
	}
	
}