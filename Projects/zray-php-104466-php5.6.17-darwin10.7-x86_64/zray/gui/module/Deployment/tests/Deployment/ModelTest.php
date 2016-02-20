<?php
namespace Deployment;
use ZendServer\PHPUnit\TestCase;


use ZendServer\Set;
use Servers\Db\Mapper;
use ArrayObject as AO;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\Adapter\Platform\PlatformInterface;
use ZendServer\Db\Adapter\Platform\NullPlatform;
require_once 'tests/bootstrap.php';

class ModelTest extends TestCase
{

	/**
	 * @var Model
	 */
	private $deploymentModel;
	
	public function testGetServersStatusByAppIds() {
		
		$deploymentModel = $this->deploymentModel;
		$tableGateway = $deploymentModel->getServersMapper()->getTableGateway();
		$tableGateway->expects($this->once())
		->method('selectWith')
		->will($this->returnValue(array(new AO(array('NODE_ID' => 1)), new AO(array('NODE_ID' => 3)))));
		
		$packageMeta = new \ZendDeployment_PackageMetaData();
		
		$AppMocked = new \ZendDeployment_Application();
		$AppMocked->setAppId(1);
		
		$deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
		$deploymentManagerMock->expects($this->once())
		->method('getApplicationsByIds')
		->with(array(1))
		->will($this->returnValue(array(1 => array(1 => $AppMocked, 3 => $AppMocked))));
		$deploymentModel->setManager($deploymentManagerMock);
		
		$result = $deploymentModel->getServersStatusByAppIds(array(1));
		
		self::assertInternalType('array', $result);
		self::assertEquals(1, count($result), '1 application');
		self::assertArrayHasKeys(array(1,3), $result[1], '2 servers');
		self::assertArrayHasKeys(array('NODE_ID', 'status', 'healthStatus', 'version', 'messages'), $result[1][1]);
		
	}
    
        public function testGetMasterApplicationsByIdsEmptyArray() {
            $deploymentModel = $this->deploymentModel;
		
            $edition = $this->getMock('ZendServer\Edition');
            
            $edition->expects($this->any())
            ->method('isClusterMember')
            ->will($this->returnValue(false));
            
            $deploymentModel->setEdition($edition);
            $deploymentModel->getServersMapper()->setEdition($edition);
            
            $tableGateway = $deploymentModel->getServersMapper()->getTableGateway();
            $tableGateway->expects($this->once())
            ->method('selectWith')
            ->will($this->returnValue(array(new AO(array('NODE_ID' => 0)))));
            
            $deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
            $deploymentManagerMock->expects($this->any())
                        ->method('getMasterApplications')
                        ->with(array(0))
                        ->will($this->returnValue(array(new \ZendDeployment_Application())));

            
            $deploymentModel->setManager($deploymentManagerMock);
            $result = $deploymentModel->getMasterApplicationsByIds(array());
            self::assertTrue($result instanceof Set);
            self::assertEquals(1, $result->count());
            
        }
    
        public function testGetMasterApplicationsByIds() {
            $deploymentModel = $this->deploymentModel;
            
            $edition = $this->getMock('ZendServer\Edition');
            
            $edition->expects($this->any())
            ->method('isClusterMember')
            ->will($this->returnValue(false));
            
            $deploymentModel->setEdition($edition);
            $deploymentModel->getServersMapper()->setEdition($edition);
            
            $tableGateway = $deploymentModel->getServersMapper()->getTableGateway();
            $tableGateway->expects($this->never())->method('selectWith');
        	
            $deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
            $deploymentManagerMock->expects($this->never())->method('getMasterApplication');

            $deploymentModel->setManager($deploymentManagerMock);
            $result = $deploymentModel->getMasterApplicationsByIds(array(1));
            $result->setHydrateClass(null);
            
            self::assertTrue($result instanceof Set);
            self::assertTrue($result->current() instanceof \ZendDeployment_Application);
        }
        
	public function testGetAllApplicationIds() {
		 $deploymentModel = $this->deploymentModel;
            
            $edition = $this->getMock('ZendServer\Edition');
            
            $edition->expects($this->any())
            ->method('isClusterMember')
            ->will($this->returnValue(false));
            
            $deploymentModel->setEdition($edition);
            $deploymentModel->getServersMapper()->setEdition($edition);
            
            $tableGateway = $deploymentModel->getServersMapper()->getTableGateway();
            $tableGateway->expects($this->once())
            ->method('selectWith')
            ->will($this->returnValue(array(new AO(array('NODE_ID' => 0)))));
		
		$deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
		$deploymentManagerMock->expects($this->any())
			 ->method('getAllApplicationsInfo')
			 ->with(array(0))
			 ->will($this->returnValue(array(2 => array(), 3 => array(), 100 => array())));
		
		$deploymentModel->setManager($deploymentManagerMock);
		$result = $deploymentModel->getAllApplicationIds();
                self::assertEquals(array(2,3,100), $result);
	}
        
	public function testGetAllApplicationsInfo() {
		$deploymentModel = $this->deploymentModel;
            
            $edition = $this->getMock('ZendServer\Edition');
            
            $edition->expects($this->any())
            ->method('isClusterMember')
            ->will($this->returnValue(false));
            
            $deploymentModel->setEdition($edition);
            $deploymentModel->getServersMapper()->setEdition($edition);
            
            $tableGateway = $deploymentModel->getServersMapper()->getTableGateway();
            $tableGateway->expects($this->once())
            ->method('selectWith')
            ->will($this->returnValue(array(new AO(array('NODE_ID' => 0)))));
		
		$deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
		$deploymentManagerMock->expects($this->any())
			 ->method('getAllApplicationsInfo')
			 ->with(array(0))
			 ->will($this->returnValue(array()));
		
		$deploymentModel->setManager($deploymentManagerMock);
		$deploymentModel->getAllApplicationsInfo();
	}
	
	protected function setUp() {
		parent::setUp();
		
		$this->deploymentModel = new Model();
		$this->deploymentModel->setDeploySupportedByWebserver(true);
		
		$tableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array(), array(), '', false);
		
		$mapper = new Mapper();
		$mapper->setTableGateway($tableGateway);
		$mapper->setPlatform(new NullPlatform());
		$this->deploymentModel->setServersMapper($mapper);
	}
}