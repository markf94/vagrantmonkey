<?php
namespace Acl\Db;

use ZendServer\FS\FS;

use Configuration\MapperAbstractTest;

require_once 'tests/bootstrap.php';

class MapperGroupsTest extends MapperAbstractTest
{

	protected $testedTable = 'GUI_LDAP_GROUPS';
	
	public function testFindAllMappedRoles() {
		$roles = $this->getTestedMapper()->findAllMappedRoles();
		self::assertInternalType('array', $roles);
		self::assertArrayHasKeys(array('administrator', 'developer'), $roles);
		self::assertArrayValues(array('rnd-il', 'rnd-il-devs'), $roles);
	}
	
	public function testFindAllMappedApplications() {
		$applications = $this->getTestedMapper()->findAllMappedApplications();
		self::assertInternalType('array', $applications);
		self::assertArrayHasKeys(array('app1', 'app2', 'app3'), $applications);
		self::assertArrayValues(array('app-devs', 'app3-devs'), $applications);
	}
	
	public function testSetRoleMapping() {
		self::assertEquals(1, $this->getTestedMapper()->setRoleMapping('unknown-role', 'group'));
		$roles = $this->getTestedMapper()->findAllMappedRoles();
		self::assertArrayHasKeys(array('administrator', 'developer', 'unknown-role'), $roles);
		self::assertArrayValues(array('rnd-il', 'rnd-il-devs', 'group'), $roles);
		self::assertEquals(1, $this->getTestedMapper()->setRoleMapping('unknown-role', 'group2'));
		$roles = $this->getTestedMapper()->findAllMappedRoles();
		self::assertArrayHasKeys(array('administrator', 'developer', 'unknown-role'), $roles);
		self::assertArrayValues(array('rnd-il', 'rnd-il-devs', 'group2'), $roles);
	}
	
	public function testSetAppMapping() {
		self::assertEquals(1, $this->getTestedMapper()->setApplicationMapping('new-app', 'group'));
		$applications = $this->getTestedMapper()->findAllMappedApplications();
		self::assertArrayHasKeys(array('app1', 'app2', 'app3', 'new-app'), $applications);
		self::assertArrayValues(array('app-devs', 'app3-devs', 'group'), $applications);
		self::assertEquals(1, $this->getTestedMapper()->setRoleMapping('new-app', 'group2'));
		$applications = $this->getTestedMapper()->findAllMappedApplications();
		self::assertArrayHasKeys(array('app1', 'app2', 'app3', 'new-app'), $applications);
		self::assertArrayValues(array('app-devs', 'app3-devs', 'group2'), $applications);
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
	 * @return MapperGroups
	 */
	protected function getTestedMapper() {
		if ($this->testedMapper) return $this->testedMapper;
		
		$this->testedMapper = new MapperGroups();
		return $this->testedMapper;
	}
	
	protected function getRows() {
		/// disable fixtures
		return array(
					'admin' => "'administrator', 'rnd-il',".MapperGroups::LINK_TYPE_ROLE,
					'developer' => "'developer', 'rnd-il-devs',".MapperGroups::LINK_TYPE_ROLE,
					'app1' => "'app1', 'app-devs',".MapperGroups::LINK_TYPE_APPLICATION,
					'app2' => "'app2', 'app-devs',".MapperGroups::LINK_TYPE_APPLICATION,
					'app3' => "'app3', 'app3-devs',".MapperGroups::LINK_TYPE_APPLICATION
				);
	}
	
	protected function getTableColumns() {
		/// disable fixtures
		return "NAME,LDAP_GROUP,LINK_TYPE";
	}

}