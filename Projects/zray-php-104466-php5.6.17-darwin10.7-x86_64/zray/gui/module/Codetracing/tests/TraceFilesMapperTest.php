<?php
namespace Codetracing;

use Deployment\IdentityFilterSimple;

use Codetracing\TraceFilesMapper;

use 
Configuration\MapperAbstractTest;

require_once 'tests/bootstrap.php';
require_once 'module/Configuration/tests/MapperAbstractTest.php';

class TraceFilesMapperTest extends MapperAbstractTest {
	
	protected $testedTable = 'trace_files';
	/**
	 * @var IdentityFilterMock
	 */
	protected $identityFilter;
	
	public function testFindCodetracesByIds() {
		$result = $this->getTestedMapper()->findCodetracesByIds(array('0.4622.2', '0.4622.3'));
		self::assertInstanceOf('ZendServer\Set', $result);
		self::assertArrayHasKeys(array('0.4622.2', '0.4622.3'), $result->toArray());
	}
	
	public function testFindCodetracesByIdsOneIsMissing() {
		$result = $this->getTestedMapper()->findCodetracesByIds(array('0.4622.2', '0.4622.5'));
		self::assertInstanceOf('ZendServer\Set', $result);
		self::assertArrayHasKeys(array('0.4622.2'), $result->toArray());
	}
	
	public function testSelectAllFileTraces() {
		$this->assertEquals(4, $this->getTestedMapper()->selectAllFileTraces()->count());
		
		$filters = array('applications' => array(20));
		$this->assertEquals(2, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		$filters = array('applications' => array(20,22));
		$this->assertEquals(3, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		$filters = array('applications' => array(-1));
		$this->assertEquals(1, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		
		$filters = array('type' => 1);
		$this->assertEquals(1, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		
		$filters = array('freetext' => 'mtrig');
		$this->assertEquals(4, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		$filters = array('freetext' => 'mtrig1');
		$this->assertEquals(2, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		$filters = array('freetext' => 'mtrig12');
		$this->assertEquals(1, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		
		$filters = array('freetext' => '0.4622');
		$this->assertEquals(4, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		$filters = array('freetext' => '0.4622.0');
		$this->assertEquals(2, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
		$filters = array('freetext' => '0.4622.01');
		$this->assertEquals(1, $this->getTestedMapper()->selectAllFileTraces($filters)->count());
	}
	
	public function testGetTraceCount() {
		$this->assertEquals(4, $this->getTestedMapper()->getTraceCount());

		$filters = array('applications' => array(20));
		$this->assertEquals(2, $this->getTestedMapper()->getTraceCount($filters));
		$filters = array('applications' => array(20,22));
		$this->assertEquals(3, $this->getTestedMapper()->getTraceCount($filters));
		$filters = array('applications' => array(-1));
		$this->assertEquals(1, $this->getTestedMapper()->getTraceCount($filters));
		
		$filters = array('type' => 1);
		$this->assertEquals(1, $this->getTestedMapper()->getTraceCount($filters));
		
		$filters = array('freetext' => 'mtrig');
		$this->assertEquals(4, $this->getTestedMapper()->getTraceCount($filters));
		$filters = array('freetext' => 'mtrig1');
		$this->assertEquals(2, $this->getTestedMapper()->getTraceCount($filters));
		$filters = array('freetext' => 'mtrig12');
		$this->assertEquals(1, $this->getTestedMapper()->getTraceCount($filters));
		
		$filters = array('freetext' => '0.4622');
		$this->assertEquals(4, $this->getTestedMapper()->getTraceCount($filters));
		$filters = array('freetext' => '0.4622.0');
		$this->assertEquals(2, $this->getTestedMapper()->getTraceCount($filters));
		$filters = array('freetext' => '0.4622.01');
		$this->assertEquals(1, $this->getTestedMapper()->getTraceCount($filters));
	}


	protected function sqlGetContents() {
		return file_get_contents("{$this->getZendInstallDir()}/share/create_monitor_tracing_db.sql");
	}
	
	/**
	 * @return TraceFilesMapper
	 */
	protected function getTestedMapper() {
		if ($this->testedMapper) return $this->testedMapper;
			
		$this->testedMapper = new TraceFilesMapper();
		$identityFilter = new IdentityFilterSimple();
		$deploymentMapper = $this->getMock('Deployment\Model');
		$deploymentMapper->expects($this->any())
			->method('getAllApplicationIds')->withAnyParameters()->will($this->returnValue(array(20,21,22)));
		
		$identityFilter->setDeploymentMapper($deploymentMapper);
		$this->testedMapper->setIdentityFilter($identityFilter);
		return $this->testedMapper;
	}
	
	protected function getRows() {
		return array(
			// RELYING ON STRUCT FOUND IN getTableColumns()
			'trace1'=>"1,'0.4622.0','/usr/local/zend/var/codetracing/dump.0.4622.1','localhost','/mtrig/mtrig1.php?all&dump_data=1','/mtrig/mtrig.php',13010,1,1345041491,0,-1",
			'trace2'=>"2,'0.4622.01','/usr/local/zend/var/codetracing/dump.0.4622.1','localhost','/mtrig/mtrig12.php?all&dump_data=1','/mtrig/mtrig.php',13010,2,1345041491,0,20",
			'trace3'=>"3,'0.4622.2','/usr/local/zend/var/codetracing/dump.0.4622.2','localhost','/mtrig/mtrig3.php?all&dump_data=1','/mtrig/mtrig.php',13010,3,1345041491,0,21",
			'trace4'=>"4,'0.4622.3','/usr/local/zend/var/codetracing/dump.0.4622.2','localhost','/mtrig/mtrig4.php?all&dump_data=1','/mtrig/mtrig.php',13010,4,1345041491,0,22",
		);
	}

	protected function getTableColumns() {
		return "id, trace_id, filepath, host, originating_url, final_url, trace_size, reason, trace_time, node_id, app_id";
	}

}
