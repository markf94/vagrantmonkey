<?php
namespace MonitorUi\Model;

use ZendServer\PHPUnit\TestCase;

use PHPUnit_Framework_TestCase,
	MonitorUi\Filter\Container,
	ZendServer\Exception;
use ZendServer\Set;
use Issue\Db\Mapper;
use Deployment\IdentityFilterSimple;
use ZendServer\Db\Adapter\Platform\NullPlatform;

require_once 'tests/bootstrap.php';

class ModelTest extends TestCase
{
	/**
	 * @var Model
	 */
	private $model;
	
	public function testGetIssuesCount() {
		
		$tableGateway = $this->model->getIssueMapper()->getTableGateway();
		$tableGateway->expects($this->any())
		->method('selectWith')
		->will($this->returnValue(array(array('total' => 7))));
		
		$tableGateway->expects($this->any())
		->method('getTable')
		->will($this->returnValue('issues'));
		
		self::assertEquals(7, $this->model->getIssuesCount(array()));
	}
	
	public function testGetIssuesCountException() {
		$tableGateway = $this->model->getIssueMapper()->getTableGateway();
		$tableGateway->expects($this->any())
		->method('selectWith')
		->will($this->throwException(new Exception('This is some exception', 5)));
		
		$tableGateway->expects($this->any())
		->method('getTable')
		->will($this->returnValue('issues'));
		
		try {
			$this->model->getIssuesCount(array());
		} catch (Exception $e) {
			self::assertContains('This is some exception', $e->getMessage());
		}
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
		
		$tableGateway = $this->model->getIssueMapper()->getTableGateway();
		
		$tableGateway->expects($this->any())
		->method('selectWith')
		->will($this->returnValue(array()));
		
		$tableGateway->expects($this->any())
		->method('getTable')
		->will($this->returnValue('issues'));
		
		// ok if no exception thrown
		$this->model->getIssues(array(), 7, 2, 'date', 'ASC');
	}
	
	protected function setUp() {
		$this->model = new Model();
		
		$tableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array(), array(), '', false);
		
		$identityFilter = $this->getMock('Deployment\IdentityFilterSimple');
		$identityFilter->expects($this->once())->method('filterAppIds')->will($this->returnValue(array(-1)));
		$identityFilter->expects($this->once())->method('setAddGlobalAppId');
		
		$mapper = new Mapper($tableGateway);
		$mapper->setIdentityFilter($identityFilter);
		$mapper->setPlatform(new NullPlatform());
		$this->model->setIssueMapper($mapper);
	}
}