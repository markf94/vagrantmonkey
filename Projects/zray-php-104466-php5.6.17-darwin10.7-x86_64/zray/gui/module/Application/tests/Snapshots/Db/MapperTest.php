<?php
namespace Snapshots\Db;

use Snapshots\Db\Mapper;
use Configuration\MapperAbstractTest;
use Zend\Db\TableGateway\TableGateway;
use ZendServer\FS\FS;

require_once 'tests/bootstrap.php';

class MapperTest extends MapperAbstractTest
{

	protected $testedTable = 'GUI_SNAPSHOTS';
	
	public function testFindAllEnabledLogFiles() {
		$snapshots = $this->getTestedMapper()->findSnapshotByName(Mapper::SNAPSHOT_SYSTEM_BOOT);
		self::assertInstanceOf('Snapshots\Db\SnapshotContainer', $snapshots);
		self::assertEquals(Mapper::SNAPSHOT_SYSTEM_BOOT, $snapshots->getName());
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
		
		$this->testedMapper = new Mapper();
		$this->testedMapper->setTableGateway(new TableGateway($this->testedTable, $this->getDbAdapter()));
		return $this->testedMapper;
	}
	
	protected function getRows() {
		$this->getDbAdapter()->query('DELETE FROM GUI_SNAPSHOTS')->execute();
		/* $this->getDbAdapter()
			->query('INSERT INTO GUI_SNAPSHOTS (ID, NAME,TYPE,DATA)
					VALUES(NULL, "'. Mapper::SNAPSHOT_SYSTEM_BOOT .'", "'. Mapper::SNAPSHOT_TYPE_SYSTEM .'","data")')
			->execute(); */
		return array(
					'snapshot' => "NULL, '".Mapper::SNAPSHOT_SYSTEM_BOOT."', '". Mapper::SNAPSHOT_TYPE_SYSTEM ."','data'",
				);
	}
	
	protected function getTableColumns() {
		/// disable fixtures
		return "ID, NAME,TYPE,DATA";
	}

}