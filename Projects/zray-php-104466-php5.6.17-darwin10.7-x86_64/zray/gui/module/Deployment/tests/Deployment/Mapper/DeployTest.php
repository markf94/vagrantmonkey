<?php
namespace Deployment;
use ZendServer\PHPUnit\TestCase;

use Deployment\Mapper\Deploy;
require_once 'tests/bootstrap.php';

class DeployTest extends TestCase
{
	
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
        }
}