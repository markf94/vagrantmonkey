<?php
namespace Acl\Db;

use ZendServer\FS\FS;

use Zend\Db\TableGateway\TableGateway;

use Configuration\MapperAbstractTest;

use Application\Module;

use ZendServer\PHPUnit\TestCase;

use Zend\Authentication\Storage\NonPersistent;

use PHPUnit_Framework_TestCase,
	Application\Controller\Plugin\Authentication,
	Application\Controller\LoginController,
	Zend\Di\Di,
	Zend\Authentication\Result,
	ZendServer\Exception;

require_once 'tests/bootstrap.php';

class MapperTest extends MapperAbstractTest
{

	protected $testedTable = 'GUI_ACL_ROLES';
	
	public function testGetRoles() {
		$roles = $this->getTestedMapper()->getRoles();
		self::assertInstanceOf('ZendServer\Set', $roles);
		self::assertInstanceOf('Acl\Role', $roles->current());
		self::assertEquals('guest', $roles[0]->getName());
		self::assertEquals('', $roles[0]->getParentName());
		self::assertEquals('admin', $roles[1]->getName());
		self::assertEquals('guest', $roles[1]->getParentName());
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Configuration\MapperAbstractTest::sqlGetContents()
	 */
	protected function sqlGetContents() {
		$basepath = __DIR__;
		return file_get_contents(FS::createPath(ZEND_SERVER_GUI_PATH,'utils','sqls','gui_sqlite_create_database.sql'));
	}
	
	/**
	 * @return Mapper
	 */
	protected function getTestedMapper() {
		if ($this->testedMapper) return $this->testedMapper;
		
		$this->testedMapper = new Mapper();
		$this->testedMapper->setRolesTable(new TableGateway('GUI_ACL_ROLES', $this->getDbAdapter()));
		$this->testedMapper->setResourcesTable(new TableGateway('GUI_ACL_RESOURCES', $this->getDbAdapter()));
		$this->testedMapper->setPrivilegesTable(new TableGateway('GUI_ACL_PRIVILEGES', $this->getDbAdapter()));
		return $this->testedMapper;
	}
	
	protected function getRows() {
		/// disable fixtures
		return array(
			"1, 'guest', ''",
			"2, 'admin', 1",
			"3, 'superadmin', 2",
		);
	}
	
	protected function getTableColumns() {
		/// disable fixtures
		return "role_id, role_name, role_parent ";
	}

}