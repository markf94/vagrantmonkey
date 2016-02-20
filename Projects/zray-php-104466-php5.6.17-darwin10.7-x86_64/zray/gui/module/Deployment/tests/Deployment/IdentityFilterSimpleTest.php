<?php
namespace Deployment;
use ZendServer\PHPUnit\TestCase;


use ZendServer\Set;
use Servers\Db\Mapper;
use ArrayObject as AO;
require_once 'tests/bootstrap.php';

class IdentityFilterSimpleTest extends TestCase
{
	public function testFilterAppIdsNoApplications() {
		$filter = new IdentityFilterSimple();
		
		$filter->setAddGlobalAppId(true);
		$appIds = $filter->filterAppIds(array(), false);
		self::assertEquals(array(-1), $appIds, 'Empty array should get the global application');
	}
	
	public function testFilterAppIdsCannotReturnCompletelyEmptySet() {
		$filter = new IdentityFilterSimple();
		$filter->setAddGlobalAppId(false);
		self::setExpectedException('Deployment\IdentityFilterException', null, IdentityFilterException::EMPTY_APPLICATIONS_ARRAY);
		$appIds = $filter->filterAppIds(array(), false);
	}
	
	public function testFilterAppIdsNoApplicationsDeployedAndNoApplicationsRequested() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array()));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(true);
		
		$appIds = $filter->filterAppIds(array(), true);
		self::assertEquals(array(-1), $appIds, 'Empty array should get the global application');
	}
	
	public function testFilterAppIdsNoApplicationsDeployedAndNoApplicationsRequestedNoGlobalApp() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array()));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(false);
		self::setExpectedException('Deployment\IdentityFilterException', null, IdentityFilterException::EMPTY_APPLICATIONS_ARRAY);
		
		$appIds = $filter->filterAppIds(array(), true);
	}
	
	public function testFilterAppIdsOneApplicationsDeployedAndNoApplicationsRequested() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array(1)));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(true);
		
		$appIds = $filter->filterAppIds(array(), true);
		self::assertEquals(array(1, -1), $appIds, 'Empty array should get the global application and available application');
	}
	
	public function testFilterAppIdsOneApplicationsDeployedAndOneApplicationsRequestedWithGlobal() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array(1)));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(true);
		
		$appIds = $filter->filterAppIds(array(1), false);
		self::assertEquals(array(1, -1), $appIds, 'Empty array should get the global application and available application');
	}
	
	public function testFilterAppIdsOneApplicationsDeployedAndOneApplicationsRequestedNoGlobal() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array(1)));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(false);
		
		$appIds = $filter->filterAppIds(array(1), false);
		self::assertEquals(array(1), $appIds, 'Empty array should get the global application and available application');
	}
	
	public function testFilterAppIdsOneApplicationsDeployedAndTwoApplicationsRequestedWithGlobal() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array(1)));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(true);
		
		$appIds = $filter->filterAppIds(array(2), false);
		self::assertEquals(array(-1), $appIds, 'Empty array should get the global application and available application');
	}
	
	public function testFilterAppIdsOneApplicationsDeployedAndTwoApplicationsRequestedNoGlobal() {
		$filter = new IdentityFilterSimple();
		
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->once())->method('getAllApplicationIds')->will($this->returnValue(array(1)));
		$filter->setDeploymentMapper($deploymentMapper);
		$filter->setAddGlobalAppId(false);
		self::setExpectedException('Deployment\IdentityFilterException', null, IdentityFilterException::EMPTY_APPLICATIONS_ARRAY);
		
		$appIds = $filter->filterAppIds(array(2), false);
	}
}