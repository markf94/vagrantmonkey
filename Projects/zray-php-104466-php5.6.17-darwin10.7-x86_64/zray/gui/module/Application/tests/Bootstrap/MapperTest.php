<?php
namespace Bootstrap;

if (! class_exists('PHPUnit_Extensions_Database_TestCase')) {
	return;
}

use ZendServer\PHPUnit\DbUnit\TestCase;

use PHPUnit_Framework_TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Users\Db\Mapper as UsersMapper;
use Zend\Db\TableGateway\TableGateway;
use Users\Forms\ChangePassword;
use Application\Module;
use Zend\Config\Config;
use WebAPI\Db\Mapper as WebAPIMapper;
use Configuration\MapperDirectives;
use GuiConfiguration\Mapper\Configuration;
use Zsd\Db\TasksMapper;
use Notifications\Db\NotificationsActionsMapper;
use Zend\EventManager\EventManager;
use Configuration\Task\ConfigurationPackage;
use Snapshots\Mapper\Profile;
use Zend\Crypt\Hash;

require_once 'tests/bootstrap.php';

class MapperTest extends TestCase
{
	/**
	 * @var Mapper
	 */
	private $mapper;
	
	public function testIsBootstrapNeeded() {
		/// table has entries, bootstrap is not needed
		self::assertTrue($this->mapper->isBootstrapNeeded());
		
		$this->updateDataSet(new ArrayDataSet(array(
    			'GUI_USERS' => array(
    				array('ID' => '1', 'NAME' => 'admin', 'PASSWORD' => Hash::compute('sha256', 'password'), 'ROLE' => 'administrator'),
    				array('ID' => '2', 'NAME' => 'developer', 'PASSWORD' => '', 'ROLE' => 'developer'),
    			),
    	)));
		/// table has no entries, bootstrap is needed
		self::assertFalse($this->mapper->isBootstrapNeeded());
	}
	
	public function testBootstrapSingleServerNoAdminPassword() {
		self::setExpectedException('ZendServer\Exception');
		$this->mapper->bootstrapSingleServer();
	}
	
	public function testBootstrapSingleServerMinimal() {
		$this->mapper->setAdminEmail('email');
		$this->mapper->setAdminPassword('password');
		$this->mapper->bootstrapSingleServer();
		
		$queryTable = $this->getConnection()->createQueryTable('GUI_USERS', 'SELECT * FROM GUI_USERS');
		$expected = new ArrayDataSet(array(
    			'ZSD_NOTIFICATIONS_ACTIONS' => array(
    				array('TYPE' => 1, 'NAME' => 'admin', 'EMAIL' => 'email', 'CUSTOM_ACTION' => '')
    			),
    			'GUI_USERS' => array(
    				array('ID' => '1', 'NAME' => 'admin', 'PASSWORD' => Hash::compute('sha256', 'password'), 'ROLE' => 'administrator'),
    				array('ID' => '2', 'NAME' => 'developer', 'PASSWORD' => '', 'ROLE' => 'developer'),
    			),
    			'ZSD_TASKS' => array( /// only comapre task id's -> some of the task extra data are generated randomly and will change for every execution
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '47'),
    			),
    	));

		self::assertTablesEqual($expected->getTable('GUI_USERS'), $queryTable);
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT TASK_ID, EXTRA_DATA FROM ZSD_TASKS');
		self::assertEquals($expected->getTable('ZSD_TASKS')->getRowCount(), $queryTable->getRowCount());
		
		/// test individual rows, some rows will have random values which will change and fail tests
		self::assertEquals('[{"name":"zend_gui.defaultServer","value":"\u003Cdefault-server\u003E"}]', $queryTable->getValue(0, 'EXTRA_DATA'));
		
		/// test individual rows, some rows will have random values which will change and fail tests
		self::assertTableContains(array(
			'TASK_ID' => '2', 'EXTRA_DATA' => '[{"name":"zend_gui.completed","value":"true"}]'
		), $queryTable);
		self::assertTableContains(array(
			'TASK_ID' => '2', 'EXTRA_DATA' => '[{"name":"zend_gui.defaultServer","value":"\u003Cdefault-server\u003E"}]'
		), $queryTable);
		self::assertTableContains(array(
			'TASK_ID' => '2', 'EXTRA_DATA' => '[{"name":"zend_gui.timezone","value":"Asia\/Jerusalem"}]'
		), $queryTable);
		self::assertTableContains(array(
			'TASK_ID' => '47', 'EXTRA_DATA' => '{"snapshotType":1,"directivesBlacklist":[],"snapshotName":"SystemBoot"}'
		), $queryTable);
		
		self::markTestIncomplete('Getting a surprising result from notifications actions table');
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_NOTIFICATIONS_ACTIONS', 'SELECT * FROM ZSD_NOTIFICATIONS_ACTIONS');
		self::assertEquals($expected->getTable('ZSD_NOTIFICATIONS_ACTIONS')->getRowCount(), $queryTable->getRowCount());
		
	}
	
	public function testBootstrapSingleServer() {
		$this->mapper->setAdminEmail('email');
		$this->mapper->setAdminPassword('password');
		$this->mapper->setApplicationUrl('url');
		$this->mapper->setDeveloperPassword('devpass');
		$this->mapper->setLicenseKey('SA12A010C01G21EE11CB594B3FAE83D4');
		$this->mapper->setLicenseUser('user');
		
		$this->mapper->bootstrapSingleServer();
		
		$queryTable = $this->getConnection()->createQueryTable('GUI_USERS', 'SELECT * FROM GUI_USERS');
		$expected = $this->getFullDataSet()->getTable('GUI_USERS');
		self::assertTablesEqual($expected, $queryTable);
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT TASK_ID FROM ZSD_TASKS');
		$expected = $this->getFullDataSet()->getTable('ZSD_TASKS');
		self::assertTablesEqual($expected, $queryTable);
		
	}
	
	public function testBootstrapSingleServerDoNotProduceKey() {
		
		$this->updateDataSet(new ArrayDataSet(array(
        		'ZSD_NOTIFICATIONS_ACTIONS' => array(),
	        	'GUI_USERS' => array(),
	        	'ZSD_TASKS' => array(),
        		'ZSD_NODES' => array(),
				'GUI_WEBAPI_KEYS' => array(
					array('ID' => '1', 'NAME' => 'admin', 'HASH' => '', 'USERNAME' => 'admin', 'CREATION_TIME' => 0)
				)
	        )));
		
		$this->mapper->setAdminEmail('email');
		$this->mapper->setAdminPassword('password');
		
		$this->mapper->bootstrapSingleServer();
		
		$queryTable = $this->getConnection()->createQueryTAble('GUI_WEBAPI_KEYS', 'SELECT * FROM GUI_WEBAPI_KEYS');
		self::assertEquals(1, $queryTable->getRowCount());
		self::assertTableContains(array(
				'ID' => '1', 'NAME' => 'admin', 'HASH' => '', 'USERNAME' => 'admin', 'CREATION_TIME' => 0
		), $queryTable);
		
	}
	
	public function testBootstrapSingleServerProduceKey() {
		
		$this->mapper->getWebapiKeysMapper()->setGeneratedHash('hash');
		
		$this->updateDataSet(new ArrayDataSet(array(
        		'ZSD_NOTIFICATIONS_ACTIONS' => array(),
	        	'GUI_USERS' => array(),
	        	'ZSD_TASKS' => array(),
        		'ZSD_NODES' => array(),
				'GUI_WEBAPI_KEYS' => array(
						/// no keys!
				)
	        )));
		
		$this->mapper->setAdminEmail('email');
		$this->mapper->setAdminPassword('password');
		
		$this->mapper->bootstrapSingleServer();
		
		$queryTable = $this->getConnection()->createQueryTAble('GUI_WEBAPI_KEYS', 'SELECT * FROM GUI_WEBAPI_KEYS');
		self::assertEquals(1, $queryTable->getRowCount());
		self::assertTableContains(array(
			'ID' => '2', 'NAME' => 'admin', 'HASH' => 'hash', 'USERNAME' => 'admin', 'CREATION_TIME' => (string)time()
		), $queryTable);
		
	}
	
	public function testBootstrapSingleServerNoNodes() {
		
		$this->updateDataSet(new ArrayDataSet(array(
        		'ZSD_NOTIFICATIONS_ACTIONS' => array(),
	        	'GUI_USERS' => array(),
	        	'ZSD_TASKS' => array(),
        		'ZSD_NODES' => array(),
				'GUI_WEBAPI_KEYS' => array(
				)
	        )));
		
		$this->mapper->setAdminEmail('email');
		$this->mapper->setAdminPassword('password');
		$this->mapper->setApplicationUrl('url');
		$this->mapper->setLicenseKey('SA12A010C01G21EE11CB594B3FAE83D4');
		$this->mapper->setLicenseUser('user');
		
		$this->mapper->bootstrapSingleServer();
		
		$expected = new ArrayDataSet(array(
    			'ZSD_TASKS' => array( /// only comapre task id's -> some of the task extra data are generated randomly and will change for every execution
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '47'),
    			),
    	));
		
		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT TASK_ID FROM ZSD_TASKS');
		self::assertTablesEqual($expected->getTable('ZSD_TASKS'), $queryTable);
		
	}
	
	public function testBootstrapSingleServerProduction() {
		$this->mapper->setAdminEmail('email');
		$this->mapper->setAdminPassword('password');
		$this->mapper->setProduction('development');
		
		$this->mapper->bootstrapSingleServer();

		$queryTable = $this->getConnection()->createQueryTable('ZSD_TASKS', 'SELECT TASK_ID,EXTRA_DATA FROM ZSD_TASKS');
		/// test individual rows, some rows will have random values which will change and fail tests
		self::assertEquals('[{"name":"zend_codetracing.buffer_size","value":"5M"}]', $queryTable->getValue(0, 'EXTRA_DATA'));
		
	}
	
    /* (non-PHPdoc)
     * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
     */
    public function getDataSet()
    {
        return new ArrayDataSet(array(
        		'ZSD_NOTIFICATIONS_ACTIONS' => array(),
	        	'GUI_USERS' => array(),
	        	'ZSD_TASKS' => array(),
        		'ZSD_NODES' => array(
        				array('NODE_ID' => '0')
        		)
	        ));
    }
    
    public function getFullDataSet() {
    	return new ArrayDataSet(array(
    			'ZSD_NOTIFICATIONS_ACTIONS' => array(
    				array('TYPE' => 1, 'NAME' => 'admin', 'EMAIL' => 'email', 'CUSTOM_ACTION' => '')
    			),
    			'GUI_USERS' => array(
    				array('ID' => '1', 'NAME' => 'admin', 'PASSWORD' => Hash::compute('sha256', 'password'), 'ROLE' => 'administrator'),
    				array('ID' => '2', 'NAME' => 'developer', 'PASSWORD' => Hash::compute('sha256', 'devpass'), 'ROLE' => 'developer'),
    			),
    			'ZSD_TASKS' => array( /// only comapre task id's -> some of the task extra data are generated randomly and will change for every execution
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '2'),
    				array('TASK_ID' => '47'),
    			),
    	));
    }
    
	protected function setUp() {
		parent::setUp();
		$this->mapper = new Mapper();
		$this->mapper->setUsersMapper(new UsersMapper(new TableGateway('GUI_USERS', $this->getAdapter())));
		$this->mapper->setChangePassword(new ChangePassword());
		$this->mapper->setWebapiKeysMapper(new WebAPIMapper(new TableGateway('GUI_WEBAPI_KEYS', $this->getAdapter())));
		$this->mapper->setNotificationsActionsMapper(new NotificationsActionsMapper(new TableGateway('ZSD_NOTIFICATIONS_ACTIONS', $this->getAdapter())));
		
		$tasksMapper = new TasksMapper(new TableGateway('ZSD_TASKS', $this->getAdapter()));
		
		$configuration = new Configuration();
		$configuration->setTasksMapper($tasksMapper);
		$this->mapper->setGuiConfiguration($configuration);
		
		$directives = new MapperDirectives(new TableGateway('ZSD_DIRECTIVES', $this->getAdapter()));
		$directives->setTasksMapper($tasksMapper);
		$directives->setEventManager(new EventManager());
		$this->mapper->setDirectivesMapper($directives);
		
		$package = new ConfigurationPackage();
		$package->setTasksMapper($tasksMapper);
		$this->mapper->setConfigurationPackage($package);
		
		$profile = new Profile();
		$profile->setDirectivesMapper($directives);
		$profile->setGuiConfigurationMapper($configuration);
		$profile->setProfiles(array(
					'productionDirectives'=>array(
							'ZEND' 		=> array('zend_debugger.allow_hosts'=>'127.0.0.0/8'),
					),
					'developmentDirectives'=>array(
							'ZEND' 		=> array('zend_codetracing.buffer_size'=>'5M'),
					),
				));
		$this->mapper->setProfilesMapper($profile);
		
		$serversMapper = new \Servers\Db\Mapper(new TableGateway('ZSD_NODES', $this->getAdapter()));
		$this->mapper->setServersMapper($serversMapper);
		
		Module::setConfig(new Config(array(
			'user' => array('zend_gui' => array('adminUser' => 'admin','devUser' => 'developer')),
			'package' => array('zend_gui' => array('edition' => 'zs'))
			)
		));
		
	}
	
}