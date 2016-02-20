<?php
namespace Zsd;
use ZendServer\PHPUnit\TestCase;
use Zend\Session\SessionManager;
use Zend\Session\Storage\ArrayStorage;
require_once 'tests/bootstrap.php';

/**
 * ZsdHealthChecker test case.
 */
class ZsdHealthCheckerTest extends TestCase {
	
	/**
	 *
	 * @var ZsdHealthChecker
	 */
	private $ZsdHealthChecker;
	
	public function testCheckZsdHealthNotSingleServer() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(false));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		// zsd value doesn't matter
		self::assertTrue($this->ZsdHealthChecker->checkZsdHealth(false));
		self::assertTrue($this->ZsdHealthChecker->checkZsdHealth(true));
	}
	
	public function testCheckZsdHealthZsdLastUpdatedNull() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(true));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		$edition->expects($this->any())
		->method('getServerId')
		->will($this->returnValue(0));
		
		$serversMapper = $this->ZsdHealthChecker->getServersMapper();
		$serversMapper->expects($this->any())
		->method('getZsdLastUpdated')
		->will($this->returnValue(null));
		
		self::assertFalse($this->ZsdHealthChecker->checkZsdHealth(false));
		self::assertFalse($this->ZsdHealthChecker->checkZsdHealth(true));
	}
	
	public function testCheckZsdHealthAllTimestampsNow() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(true));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		$serversMapper = $this->ZsdHealthChecker->getServersMapper();
		$serversMapper->expects($this->any())
		->method('getZsdLastUpdated')
		->will($this->returnValue($now));
		
		$edition->expects($this->any())
		->method('getServerId')
		->will($this->returnValue(0));
		
		$this->ZsdHealthChecker->getSessionStorage()->setPhpTimeStamp($now);
		$this->ZsdHealthChecker->getSessionStorage()->setZsdTimeStamp($now);
		
		self::assertEquals(null, $this->ZsdHealthChecker->checkZsdHealth(false));
		self::assertFalse($this->ZsdHealthChecker->checkZsdHealth(true));
	}
	
	public function testCheckZsdHealthNoStoredTimestampsZsdDown() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(true));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		$serversMapper = $this->ZsdHealthChecker->getServersMapper();
		$serversMapper->expects($this->any())
		->method('getZsdLastUpdated')
		->will($this->returnValue($now - 1));
		
		$edition->expects($this->any())
		->method('getServerId')
		->will($this->returnValue(0));
		
		$this->ZsdHealthChecker->getSessionStorage()->setPhpTimeStamp(0);
		$this->ZsdHealthChecker->getSessionStorage()->setZsdTimeStamp(0);
		
		self::assertEquals(null, $this->ZsdHealthChecker->checkZsdHealth(true));
		
		// stored now into session
		self::assertGreaterThan(0, $this->ZsdHealthChecker->getSessionStorage()->getPhpTimeStamp());
		self::assertGreaterThan(0, $this->ZsdHealthChecker->getSessionStorage()->getZsdTimeStamp());
		
		
	}
	
	public function testCheckZsdHealthNoStoredTimestamps() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(true));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		$serversMapper = $this->ZsdHealthChecker->getServersMapper();
		$serversMapper->expects($this->any())
		->method('getZsdLastUpdated')
		->will($this->returnValue($now - 1));
		
		$edition->expects($this->any())
		->method('getServerId')
		->will($this->returnValue(0));
		
		$this->ZsdHealthChecker->getSessionStorage()->setPhpTimeStamp(0);
		$this->ZsdHealthChecker->getSessionStorage()->setZsdTimeStamp(0);
		
		self::assertEquals(null, $this->ZsdHealthChecker->checkZsdHealth(false));
		
		// stored now into session
		self::assertGreaterThan(0, $this->ZsdHealthChecker->getSessionStorage()->getPhpTimeStamp());
		self::assertGreaterThan(0, $this->ZsdHealthChecker->getSessionStorage()->getZsdTimeStamp());
		
		
	}
	
	public function testCheckZsdHealthLastZsdUpdate1SecondAgo() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(true));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		$serversMapper = $this->ZsdHealthChecker->getServersMapper();
		$serversMapper->expects($this->any())
		->method('getZsdLastUpdated')
		->will($this->returnValue($now - 1));
		
		$edition->expects($this->any())
		->method('getServerId')
		->will($this->returnValue(0));
		
		$this->ZsdHealthChecker->getSessionStorage()->setPhpTimeStamp($now);
		$this->ZsdHealthChecker->getSessionStorage()->setZsdTimeStamp($now);
		
		self::assertEquals(null, $this->ZsdHealthChecker->checkZsdHealth(false));
		self::assertTrue($this->ZsdHealthChecker->checkZsdHealth(true));
	}
	
	public function testCheckZsdHealthLastZsdUpdate11SecondAgo() {
		$now = time();
		
		$edition = $this->getMock('ZendServer\Edition');
		$edition->expects($this->any())
		->method('isSingleServer')
		->will($this->returnValue(true));
		
		$this->ZsdHealthChecker->setEdition($edition);
		
		$serversMapper = $this->ZsdHealthChecker->getServersMapper();
		$serversMapper->expects($this->any())
		->method('getZsdLastUpdated')
		->will($this->returnValue($now - 11));
		
		$edition->expects($this->any())
		->method('getServerId')
		->will($this->returnValue(0));
		
		$this->ZsdHealthChecker->getSessionStorage()->setPhpTimeStamp($now);
		$this->ZsdHealthChecker->getSessionStorage()->setZsdTimeStamp($now);
		
		self::assertEquals(null, $this->ZsdHealthChecker->checkZsdHealth(false));
		self::assertTrue($this->ZsdHealthChecker->checkZsdHealth(true));
	}
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		$this->ZsdHealthChecker = new ZsdHealthChecker();
		$this->ZsdHealthChecker->setServersMapper($this->getMock('Servers\Db\Mapper'));
		$storage = new SessionStorage();
		$storage->setManager(new SessionManager(null, new ArrayStorage()));
		$this->ZsdHealthChecker->setSessionStorage($storage);
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		$this->ZsdHealthChecker = null;
		
		parent::tearDown ();
	}
	
}

