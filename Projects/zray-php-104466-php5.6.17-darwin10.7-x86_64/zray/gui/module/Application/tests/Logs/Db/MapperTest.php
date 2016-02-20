<?php
namespace Logs\Db;

use Zend\Db\TableGateway\TableGateway;

use Configuration\MapperDirectives;

use Logs\Db\Mapper;

use ZendServer\FS\FS;

use Configuration\MapperAbstractTest;

require_once 'tests/bootstrap.php';

class MapperTest extends MapperAbstractTest
{

	protected $testedTable = 'GUI_AVAILABLE_LOGS';
	
	public function testFindAllEnabledLogFiles() {
		$logs = $this->getTestedMapper()->findAllEnabledLogFiles();
		self::assertInternalType('array', $logs);
		self::assertArrayHasKeys(array('logfile1', 'logfile2'), $logs);
		self::assertArrayValues(array('/path/to/logfile1.log', '/usr/local/zend/var/log/php.log'), $logs);
	}

	protected function sqlGetContents() {
		$basepath = __DIR__;
		return file_get_contents(FS::createPath(ZEND_SERVER_GUI_PATH,'utils','sqls','gui_sqlite_create_database.sql'))
		.PHP_EOL.file_get_contents("{$this->getZendInstallDir()}/share/zsd_sqlite_create_schema.sql");
	}
	
	/**
	 * @return Mapper
	 */
	protected function getTestedMapper() {
		if ($this->testedMapper) return $this->testedMapper;
		
		$directives = new MapperDirectives();
		$directives->setTableGateway(new TableGateway('ZSD_DIRECTIVES', $this->getDbAdapter()));
		$this->testedMapper = new Mapper();
		$this->testedMapper->setDirectivesMapper($directives);
		return $this->testedMapper;
	}
	
	protected function getRows() {
		$this->getDbAdapter()->query('DELETE FROM GUI_AVAILABLE_LOGS')->execute();
		$this->getDbAdapter()
			->query('INSERT INTO ZSD_DIRECTIVES (NAME,TYPE,MEMORY_VALUE,DISK_VALUE,EXTENSION,DAEMON,INI_FILE)
					VALUES("error_log","1","","/usr/local/zend/var/log/php.log","global","","/usr/local/zend/etc/php.ini")')
			->execute();
		return array(
					'log1' => "'logfile1', '/path/to/logfile1.log',NULL, 1",
					'log2' => "'logfile2', NULL, 'error_log', 1",
					'log3' => "'logfile3', '/path/to/logfile3.log',NULL, 0",
				);
	}
	
	protected function getTableColumns() {
		/// disable fixtures
		return "NAME, FILEPATH, DIRECTIVE, ENABLED";
	}

}