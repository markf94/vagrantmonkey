<?php
namespace Deployment;
use ZendServer\PHPUnit\DbUnit\TestCase;

use Deployment\Mapper\Deploy;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Vhost\Mapper\Vhost;
use Servers\Db\Mapper;
use Zend\Db\TableGateway\TableGateway;
use Configuration\MapperDirectives;
use Vhost\Mapper\Tasks;
use Zsd\Db\TasksMapper;
use Zend\Stdlib\Hydrator\Reflection;

require_once 'tests/bootstrap.php';

class DeployDbUnitTest extends TestCase
{
	
	/**
	 * @var Deploy
	 */
	private $mapper;
	
	public function testDeployApplicationNewVhostNoCreateVhostFlag() {
		$deploymentModel = $this->mapper;
		$this->updateDataSet(new ArrayDataSet(array(
			'deployment_tasks_descriptors' => array(array(
					'package_id' => 19,
					'base_url' => 'http://newvhost:80/base/url',
					'zend_params' => 'baseUrl;#*http://newvhost:80/base/url;#*userApplicationName;#*mtrig;#*ignoreFailures;#*0;#*removeApplicationData;#*;#*vhostId;#*0;#*createVhost;#*0;#*defaultServer;#*0',
					'status' => 'PENDING',
			)),
			'deployment_packages' => array(array(
				'package_id' => 19,
				'name' => 'minedeploy',
				'monitor_rules' => '',
				'version' => '1.1',
				'pagecache_rules' => '',
			))
		)));
		self::setExpectedException('Vhost\Mapper\Exception');
		
		self::assertTrue($deploymentModel->deployApplication('http://newvhost:80/base/url', 1));
	}
	
	public function testDeployApplicationNewVhost() {
		$deploymentModel = $this->mapper;
		
		$vhost = $deploymentModel->getVhostsMapper();
		$hydrator = new Reflection();
		$newVhost = $hydrator->hydrate(array('ID' => 1), new \Vhost\Entity\Vhost());
		$vhost->expects($this->once())->method('createVhostFromURL')->with('http://newvhost:80/base/url')->
			will($this->returnValue($newVhost));
		
		self::assertTrue($deploymentModel->deployApplication('http://newvhost:80/base/url', 1));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	public function getDataSet() {
		return new ArrayDataSet(array(
			'deployment_tasks_descriptors' => array(array(
					'package_id' => 19,
					'base_url' => 'http://newvhost:80/base/url',
					'zend_params' => 'baseUrl;#*http://newvhost:80/base/url;#*userApplicationName;#*mtrig;#*ignoreFailures;#*0;#*removeApplicationData;#*;#*vhostId;#*0;#*createVhost;#*1;#*defaultServer;#*0',
					'status' => 'PENDING',
			)),
			'deployment_packages' => array(array(
				'package_id' => 19,
				'name' => 'minedeploy',
				'monitor_rules' => '',
				'version' => '1.1',
				'pagecache_rules' => '',
			))
		));
	}
	
	protected function setUp() {
		parent::setUp();
		\ZendDeployment_Logger::initLogNull();
		
		$this->mapper = new Deploy();
		
		$dbcnf = new \ZendDeployment_DB_Config();
		$dbcnf->setDbType('memory');
		
		$manager = new \ZendDeployment_Manager($dbcnf);
		$manager->getRemoteDbHandler()->setDbh($this->getAdapter()->getDriver()->getConnection()->getResource());
		
		$this->mapper->setManager($manager);
		
		$deploymentModel = new Model();
		$deploymentModel->setManager($manager);
		$deploymentModel->setDeploySupportedByWebserver(true);
		
		$serversMapper = new Mapper(new TableGateway('ZSD_NODES', $this->getAdapter()));
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->once())->method('isCluster')->will($this->returnValue(false));
		$edition->expects($this->once())->method('isClusterServer')->will($this->returnValue(false));
		
		$deploymentModel->setEdition($edition);
		$serversMapper->setEdition($edition);
		
		$deploymentModel->setServersMapper($serversMapper);
		
		$this->mapper->setDeploymentMapper($deploymentModel);
		
		$this->mapper->setVhostsMapper($this->getMock('Vhost\Mapper\Vhost'));
		
	}
	
	/* 
        public function testDeployApplicationExistingVhost() {
            $deploymentModel = new Deploy();
            
            $model = $this->getMock('Deployment\Model');
            $deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
            $vhostsMapper = $this->getMock('Vhost\Mapper\Vhost');

            $deploymentModel->setDeploymentMapper($model);
            $deploymentModel->setManager($deploymentManagerMock);
            $deploymentModel->setVhostsMapper($vhostsMapper);
            
            $model->expects($this->once())->method('isDeploySupportedByWebserver')->will($this->returnValue(true));
            $model->expects($this->once())->method('getRespondingServers')->will($this->returnValue(array()));
            
            $packageMeta = new \ZendDeployment_PackageMetaData();
            
            $zendParams = array('defaultServer' => false, 'createVhost' => false);
            $baseUrl = '/base/url';
            
            $pendingDeploymentMocked = new \ZendDeployment_PendingDeployment();
            $pendingDeploymentMocked->setBaseUtl($baseUrl);
            $pendingDeploymentMocked->setId(1);
            $pendingDeploymentMocked->setUserParams(array());
            $pendingDeploymentMocked->setZendParams($zendParams);
            $pendingDeploymentMocked->setDeploymentPackage($packageMeta);
            
            $model->expects($this->once())->method('getPendingDeploymentByBaseUrl')->with($baseUrl)->will($this->returnValue($pendingDeploymentMocked));
            $model->expects($this->once())->method('addAuditIdToZendParams')->with($zendParams)->will($this->returnValue($zendParams + array('auditId' => 1)));
            
            $vhost = $this->getMock('Vhost\Entity\Vhost');
            $vhost->expects($this->once())->method('getId')->will($this->returnValue(1));
            $vhostsMapper->expects($this->once())->method('vhostFromURL')->with($baseUrl)->will($this->returnValue($vhost));
            
            $deploymentManagerMock->expects($this->once())->method('deployApplication')->with(array(), $packageMeta, array(), $zendParams + array('auditId' => 1, 'vhostId' => 1));
            
            self::assertTrue($deploymentModel->deployApplication('/base/url', 1));
        }
	
        public function testDeployApplicationNewVhost() {
            $deploymentModel = new Deploy();
            
            $model = $this->getMock('Deployment\Model');
            $deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
            $vhostsMapper = $this->getMock('Vhost\Mapper\Vhost');

            $deploymentModel->setDeploymentMapper($model);
            $deploymentModel->setManager($deploymentManagerMock);
            $deploymentModel->setVhostsMapper($vhostsMapper);
            
            $model->expects($this->once())->method('isDeploySupportedByWebserver')->will($this->returnValue(true));
            $model->expects($this->once())->method('getRespondingServers')->will($this->returnValue(array()));
            
            $packageMeta = new \ZendDeployment_PackageMetaData();
            
            $zendParams = array('defaultServer' => false, 'createVhost' => true);
            $baseUrl = '/base/url';
            
            $pendingDeploymentMocked = new \ZendDeployment_PendingDeployment();
            $pendingDeploymentMocked->setBaseUtl($baseUrl);
            $pendingDeploymentMocked->setId(1);
            $pendingDeploymentMocked->setUserParams(array());
            $pendingDeploymentMocked->setZendParams($zendParams);
            $pendingDeploymentMocked->setDeploymentPackage($packageMeta);
            
            $model->expects($this->once())->method('getPendingDeploymentByBaseUrl')->with($baseUrl)->will($this->returnValue($pendingDeploymentMocked));
            $model->expects($this->once())->method('addAuditIdToZendParams')->with($zendParams)->will($this->returnValue($zendParams + array('auditId' => 1)));
            
            $vhost = $this->getMock('Vhost\Entity\Vhost');
            $vhost->expects($this->once())->method('getId')->will($this->returnValue(1));
            $vhostsMapper->expects($this->once())->method('createVhostFromURL')->with($baseUrl)->will($this->returnValue($vhost));
            
            $deploymentManagerMock->expects($this->once())->method('deployApplication')->with(array(), $packageMeta, array(), $zendParams + array('auditId' => 1, 'vhostId' => 1));
            
            self::assertTrue($deploymentModel->deployApplication('/base/url', 1));
        }
	
        public function testDeployApplicationDefaultServer() {
            $deploymentModel = new Deploy();
            
            $model = $this->getMock('Deployment\Model');
            $deploymentManagerMock = $this->getMock('ZendDeployment_Manager', array(), array(), '', false);
            $vhostsMapper = $this->getMock('Vhost\Mapper\Vhost');

            $deploymentModel->setDeploymentMapper($model);
            $deploymentModel->setManager($deploymentManagerMock);
            $deploymentModel->setVhostsMapper($vhostsMapper);
            
            $model->expects($this->once())->method('isDeploySupportedByWebserver')->will($this->returnValue(true));
            $model->expects($this->once())->method('getRespondingServers')->will($this->returnValue(array()));
            
            $packageMeta = new \ZendDeployment_PackageMetaData();
            
            $zendParams = array('defaultServer' => true, 'createVhost' => false);
            $baseUrl = '/base/url';
            
            $pendingDeploymentMocked = new \ZendDeployment_PendingDeployment();
            $pendingDeploymentMocked->setBaseUtl($baseUrl);
            $pendingDeploymentMocked->setId(1);
            $pendingDeploymentMocked->setUserParams(array());
            $pendingDeploymentMocked->setZendParams($zendParams);
            $pendingDeploymentMocked->setDeploymentPackage($packageMeta);
            
            $model->expects($this->once())->method('getPendingDeploymentByBaseUrl')->with($baseUrl)->will($this->returnValue($pendingDeploymentMocked));
            $model->expects($this->once())->method('addAuditIdToZendParams')->with($zendParams)->will($this->returnValue($zendParams + array('auditId' => 1)));
            
            $vhost = $this->getMock('Vhost\Entity\Vhost');
            $vhost->expects($this->once())->method('getId')->will($this->returnValue(1));
            $vhostsMapper->expects($this->once())->method('getDefaultServerVhost')->will($this->returnValue($vhost));
            
            $deploymentManagerMock->expects($this->once())->method('deployApplication')->with(array(), $packageMeta, array(), $zendParams + array('auditId' => 1, 'vhostId' => 1));
            
            self::assertTrue($deploymentModel->deployApplication('/base/url', 1));
        } */
}