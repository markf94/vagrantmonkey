<?php
namespace Configuration;

use Zend\Db\TableGateway\TableGateway;

use PHPUnit_Framework_TestCase,
PHPUnit_Extensions_Database_TestCase,
PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection,
Configuration\MapperDirectives,
Configuration\MapperAbstractTest,
ZendServer\Exception;

require_once 'tests/bootstrap.php';
require_once 'MapperAbstractTest.php';

class MapperDirectivesTest extends MapperAbstractTest {
	
	protected $testedTable = 'ZSD_DIRECTIVES';
	
	public function testDirectiveExists() {
		self::assertTrue($this->getTestedMapper()->directiveExists('zend.data_dir'));
		self::assertFalse($this->getTestedMapper()->directiveExists('some_directive.that.does_not_exist'));
	}
	
	public function testselectAllDirectives() {
		$res = $this->getConnection()->query("SELECT * FROM " . $this->getTableName());
		$rows = $res->fetchAll();
		
		$this->assertEquals(sizeof($this->getRows()), $this->getTestedMapper()->selectAllDirectives()->count(), "selectAllDirectivesCount1");
		$this->assertEquals(sizeof($this->getRows()), sizeof($rows), "selectAllDirectivesCount2");
	}

	public function testselectAllExtensionDirectives() {
		$res = $this->getConnection()->query("SELECT * FROM " . $this->getTableName() . ' WHERE "EXTENSION"="Zend Code Tracing"');
		$rows = $res->fetchAll();
	
		$this->assertEquals(1, $this->getTestedMapper()->selectAllExtensionDirectives('Zend Code Tracing')->count(), "selectAllExtensionDirectivesCount1");
		$this->assertEquals(1, sizeof($rows), "selectAllExtensionDirectivesCount2");
	}


	public function testselectAllDaemonDirectives() {
		$res = $this->getConnection()->query("SELECT * FROM " . $this->getTableName() . ' WHERE "DAEMON"="zdd"');
		$rows = $res->fetchAll();
	
		$this->assertEquals(1, $this->getTestedMapper()->selectAllDaemonDirectives('zdd')->count(), "selectAllDaemonDirectivesCount1");
		$this->assertEquals(1, sizeof($rows), "selectAllDaemonDirectivesCount2");
	}	
	

	/**
	 * @return MapperDirectives
	 */
	protected function getTestedMapper() {
		if ($this->testedMapper) return $this->testedMapper;
			
		return $this->testedMapper = new MapperDirectives();
	}
	
	protected function getRows() {
		return array(
			// RELYING ON STRUCT FOUND IN getTableColumns()
			'error_log'=>'"error_log","1","","/usr/local/zend/var/log/php.log","global","","/usr/local/zend/etc/php.ini"',
			'pdo_mysql.default_socket'=>'"pdo_mysql.default_socket","1",""," /var/run/mysqld/mysqld.sock","pdo_mysql","","/usr/local/zend/etc/php.ini"',
			'zend_codetracing.max_string'=>'"zend_codetracing.max_string","1","","48","Zend Code Tracing","","/usr/local/zend/etc/conf.d/codetracing.ini"',
			'zend.data_dir'=>'"zend.data_dir","1","","/usr/local/zend/var","global","","/usr/local/zend/etc/conf.d/ZendGlobalDirectives.ini"',		
			'zend_deployment.webserver.apache.ctl'=>'"zend_deployment.webserver.apache.ctl","0","","/usr/sbin/apache2ctl","","zdd","/usr/local/zend/etc/zdd.ini"',			
		);
	}

	protected function getTableColumns() {
		return "NAME,TYPE,MEMORY_VALUE,DISK_VALUE,EXTENSION,DAEMON,INI_FILE";
	}

}
