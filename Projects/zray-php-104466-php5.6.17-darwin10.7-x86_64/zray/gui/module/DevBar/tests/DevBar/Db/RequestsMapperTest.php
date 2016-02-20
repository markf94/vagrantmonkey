<?php
namespace DevBar\Db;

use ZendServer\PHPUnit\DbUnit\TestCase;
use ZendServer\PHPUnit\DbUnit\ArrayDataSet;
use Zend\Db\TableGateway\TableGateway;

require_once 'tests/bootstrap.php';

class RequestsMapperTest extends TestCase
{
	/**
	 * @var RequestsMapper
	 */
	private $mapper;

	public function testGetFirstRequests() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_requests' => array(
				array('page_id' => '1@111@0', 'url_id' => '1', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0, 'is_primary_page' => '1'),
				array('page_id' => '1@111@0', 'url_id' => '2', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0, 'is_primary_page' => '1'),
				array('page_id' => '2@111@0', 'url_id' => '2', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0, 'is_primary_page' => '0'),
				array('page_id' => '2@111@0', 'url_id' => '3', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0, 'is_primary_page' => '1'),
			),
			'devbar_requests_urls' => array(
				array('id' => '1', 'url' => 'http://url', 'url_hash' => sha1('http://url')),
				array('id' => '2', 'url' => 'http://url2', 'url_hash' => sha1('http://url2')),
				array('id' => '3', 'url' => 'http://url3', 'url_hash' => sha1('http://url3')),
			),
		)));
		
		$request = $this->mapper->getFirstRequests('1@111@0');
		self::assertEquals('1', $request->getId());
		$request = $this->mapper->getFirstRequests('2@111@0');
		self::assertEquals('4', $request->getId());
	}
	
	public function testGetRequest() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_requests' => array(
				array('page_id' => '1@111@0', 'url_id' => '1', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0),
				array('page_id' => '1@111@0', 'url_id' => '2', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0),
				array('page_id' => '2@111@0', 'url_id' => '2', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0),
				array('page_id' => '2@111@0', 'url_id' => '3', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c'), 'request_time' => 0),
			),
			'devbar_requests_urls' => array(
				array('id' => '1', 'url' => 'http://url', 'url_hash' => sha1('http://url')),
				array('id' => '2', 'url' => 'http://url2', 'url_hash' => sha1('http://url2')),
				array('id' => '3', 'url' => 'http://url3', 'url_hash' => sha1('http://url3')),
			),
		)));
		
		$request = $this->mapper->getRequest(1);
		self::assertEquals('1', $request->getId());
		$request = $this->mapper->getRequest(2);
		self::assertEquals('2', $request->getId());
	}
	
	public function testGetRequests() {
		$this->updateDataSet(new ArrayDataSet(array(
			'devbar_requests' => array(
				array('page_id' => '1@111@0', 'url_id' => '1', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c', 0), 'request_time' => 0),
				array('page_id' => '1@111@0', 'url_id' => '2', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c', 1), 'request_time' => 0),
				array('page_id' => '1@111@0', 'url_id' => '2', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c', 2), 'request_time' => 0),
				array('page_id' => '1@111@0', 'url_id' => '3', 'status_code' => '200', 'method' => 'GET', 'start_time' => date('c', 3), 'request_time' => 0),
			),
			'devbar_requests_urls' => array(
				array('id' => '1', 'url' => 'http://url', 'url_hash' => sha1('http://url')),
				array('id' => '2', 'url' => 'http://url2', 'url_hash' => sha1('http://url2')),
				array('id' => '3', 'url' => 'http://url3', 'url_hash' => sha1('http://url3')),
			),
		)));
		
		$requests = $this->mapper->getRequests('1@111@0');
		self::assertEquals(4, $requests->count());
		foreach ($requests as $request) {/* @var $request \DevBar\RequestContainer */
			self::assertEquals('1@111@0', $request->getPageId());
		}
		
		$requests = $this->mapper->getRequests('1@111@0', '3');
		self::assertEquals(1, $requests->count());
		foreach ($requests as $request) {/* @var $request \DevBar\RequestContainer */
			self::assertGreaterThan('3', $request->getId());
		}
		
		$requests = $this->mapper->getRequests('1@111@0', '2');
		self::assertEquals(2, $requests->count());
		foreach ($requests as $request) {/* @var $request \DevBar\RequestContainer */
			self::assertGreaterThan('2', $request->getId());
		}
		
		$requests = $this->mapper->getRequests('1@111@0', '2', 1);
		self::assertEquals(1, $requests->count());
		foreach ($requests as $request) {/* @var $request \DevBar\RequestContainer */
			self::assertGreaterThan('2', $request->getId());
		}
		
		$requests = $this->mapper->getRequests('1@111@0', '2', 1);
		self::assertEquals(1, $requests->count());
		foreach ($requests as $request) {/* @var $request \DevBar\RequestContainer */
			self::assertGreaterThan('2', $request->getId());
		}
	}
	
	protected function setUp() {
		parent::setUp();
		$this->mapper = new RequestsMapper();
		$this->mapper->setTableGateway(new TableGateway('devbar_requests', $this->getAdapter()));
	}
	
	/* (non-PHPdoc)
	 * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
	 */
	protected function getDataSet() {
		return new ArrayDataSet(array(
			'devbar_requests' => array(),
			'devbar_requests_urls' => array(),
		));
	}
}