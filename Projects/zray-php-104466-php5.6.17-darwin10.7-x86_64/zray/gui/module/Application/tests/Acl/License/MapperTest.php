<?php
namespace Acl\License;

use ZendServer\PHPUnit\TestCase;
use ZendServer\Permissions\AclQuery;
use Zend\Permissions\Acl\Acl;
use Configuration\License\License;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;
use Users\Identity;

require_once 'tests/bootstrap.php';

class LicenseTest extends TestCase
{
	/**
	 * @var Mapper
	 */
	private $mapper;
	
	/**
	 * @var Acl
	 */
	private $aclEdition;
	
	public function testIsValidUnlimited() {
		$this->aclEdition->allow(null, 'dataRentention:timelimit', array('unlimited'));
		self::assertTrue($this->mapper->isValid(strtotime('1980')));
		self::assertTrue($this->mapper->isValid(strtotime('-3 months')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 weeks')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 hours')));
	}
	
	public function testIsValid3months() {
		$this->aclEdition->allow(null, 'dataRentention:timelimit', array('3month'));
		self::assertFalse($this->mapper->isValid(strtotime('1980')));
		self::assertTrue($this->mapper->isValid(strtotime('-3 months')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 weeks')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 hours')));
	}
	
	public function testIsValid2weeks() {
		$this->aclEdition->allow(null, 'dataRentention:timelimit', array('2weeks'));
		self::assertFalse($this->mapper->isValid(strtotime('1980')));
		self::assertFalse($this->mapper->isValid(strtotime('-3 months')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 weeks')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 hours')));
	}
	
	public function testIsValid2hours() {
		self::assertFalse($this->mapper->isValid(strtotime('1980')));
		self::assertFalse($this->mapper->isValid(strtotime('-3 months')));
		self::assertFalse($this->mapper->isValid(strtotime('-2 weeks')));
		self::assertTrue($this->mapper->isValid(strtotime('-2 hours')));
	}
	
	protected function setUp() {
		parent::setUp();
		
		$acl = new Acl();
		$acl->addResource('dataRentention:timelimit');
		$acl->addRole('role');
		$acl->allow();
		
		$this->aclEdition = new Acl();
		$this->aclEdition->addRole('edition:EMPTY');
		$this->aclEdition->addResource('dataRentention:timelimit');
		$this->aclEdition->deny();
		
		$storage = new NonPersistent();
		$storage->write(new Identity('user', 'role'));
		
		$auth = new AuthenticationService();
		$auth->setStorage($storage);
		
		$aclQuery = new AclQuery();
		$aclQuery->setAcl($acl);
		$aclQuery->setAuthService($auth);
		$aclQuery->setEditionAcl($this->aclEdition);
		$aclQuery->setLicense(new License(array(
			'edition' => License::EDITION_EMPTY,
			'serial_number' => ''
		)));
		
		$this->mapper = new Mapper();
		$this->mapper->setAcl($aclQuery);
	}
}