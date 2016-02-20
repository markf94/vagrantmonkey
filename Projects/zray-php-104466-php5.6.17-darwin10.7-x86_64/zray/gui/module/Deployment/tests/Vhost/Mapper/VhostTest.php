<?php
namespace Vhost\Mapper;

use Vhost\Mapper\Vhost;
use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;

require_once 'tests/bootstrap.php';

class VhostTest extends TestCase
{
	/**
	 * @var Vhost
	 */
	private $mapper;
	
	public function testGetVhostsUseOffsetOnlyIfLimitGreaterThan0() {
		$this->updateDataSet(new ArrayDataSet(array(
			'ZSD_VHOSTS' => array(
				array('ID' => '1', 'NAME' => 'vhost1', 'PORT' => '80', 'CREATED_AT' => time(), 'LAST_UPDATED' => time(), 'OWNER' => '0', 'IS_SSL' => '0'),
				array('ID' => '2', 'NAME' => 'vhost2','PORT' => '80', 'CREATED_AT' => time(), 'LAST_UPDATED' => time(), 'OWNER' => '0', 'IS_SSL' => '0'),
				array('ID' => '3', 'NAME' => 'vhost3','PORT' => '80', 'CREATED_AT' => time(), 'LAST_UPDATED' => time(), 'OWNER' => '0', 'IS_SSL' => '0'),
			)
		)));
		
		$this->mapper->getVhosts(array(),array(),0,1);
	}
	
	public function testVhostCount() {
		$this->updateDataSet(new ArrayDataSet(array(
			'ZSD_VHOSTS' => array(
				array('ID' => '1', 'NAME' => 'vhost1', 'PORT' => '80', 'CREATED_AT' => time(), 'LAST_UPDATED' => time(), 'OWNER' => '0', 'IS_SSL' => '0'),
				array('ID' => '2', 'NAME' => 'vhost2','PORT' => '80', 'CREATED_AT' => time(), 'LAST_UPDATED' => time(), 'OWNER' => '0', 'IS_SSL' => '0'),
				array('ID' => '3', 'NAME' => 'vhost3','PORT' => '80', 'CREATED_AT' => time(), 'LAST_UPDATED' => time(), 'OWNER' => '0', 'IS_SSL' => '0'),
			)
		)));
		
		self::assertEquals(3, $this->mapper->countVhosts());
		self::assertEquals(1, $this->mapper->countVhosts(array('freeText' => 'vhost1')));
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new Vhost();
		$this->mapper->setDbAdapter($this->getAdapter());
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'ZSD_VHOSTS' => array(
			)
		));
	}
}