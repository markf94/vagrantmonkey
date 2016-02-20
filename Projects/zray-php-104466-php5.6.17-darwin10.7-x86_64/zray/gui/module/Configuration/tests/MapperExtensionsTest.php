<?php
namespace Configuration;

require_once 'tests/bootstrap.php';
require_once 'MapperAbstractTest.php';

class MapperExtensionsTest extends MapperAbstractTest {
	
	protected $testedTable = 'ZSD_EXTENSIONS';
	
	public function testselectAllExtensions() {		
		
		$this->assertEquals(sizeof($this->getRows()), sizeof($this->getTestedMapper()->selectAllExtensions()), "selectAllExtensionsCount1");
	}

	public function testselectAllZendExtensions() {
	
		$this->assertEquals(3, sizeof($this->getTestedMapper()->selectAllZendExtensions()), "selectAllZendExtensionsCount1");
	}

	public function testselectAllPHPExtensions() {
	
		$this->assertEquals(5, sizeof($this->getTestedMapper()->selectAllPHPExtensions()), "selectAllPHPExtensionsCount1");
	}

	public function testselectAllPHPExtensionsInstalled() {
	
		$this->assertEquals(3, sizeof($this->getTestedMapper()->selectAllPHPExtensionsInstalled()), "selectAllPHPExtensionsCount1");
	}
	

	/**
	 * @return MapperExtensions
	 */
	protected function getTestedMapper() {
		if ($this->testedMapper) return $this->testedMapper;
			
		return $this->testedMapper = new MapperExtensions();
	}
	
	protected function getRows() {
		return array(				
			// RELYING ON STRUCT FOUND IN getTableColumns()
			'OldRecordExtension'=>'"OldRecordExtension","5.3.9-ZS5.6.0","1","1","","0",NULL',
			'OldRecordComponent'=>'"OldRecordComponent","5.3.9-ZS5.6.0","1","1","","1",NULL',
			'Core'=>'"Core","5.3.9-ZS5.6.0","1","1","","0","0"',
			'pdo_sqlite'=>'"pdo_sqlite","1.0.1","1","1","pdo_sqlite.ini","0","0"',
			'ncurses'=>'"ncurses","","0","0","ncurses.ini","0","0"',	
			'Zend Data Cache'=>'"Zend Data Cache","null","1","1","datacache.ini","1","1"',			
			'Zend Extension Manager'=>'"Zend Extension Manager","","1","1","hidden.ini","1","0"',				
			'Zend Java Bridge'=>'"Zend Java Bridge","","0","0","jbridge.ini","1","1"',				
			'Zend extension'=>'"Zend extension","","0","0","jbridge.ini","0","1"',				
			'Zend component not extension'=>'"Zend component not extension","","0","0","jbridge.ini","1","0"',				
		);
	}
	
	protected function getTableColumns() {
		return 'NAME,EXT_VERSION,IS_INSTALLED,IS_LOADED,INI_FILE,IS_ZEND_COMPONENT,IS_ZEND_EXTENSION';		
	}

}
