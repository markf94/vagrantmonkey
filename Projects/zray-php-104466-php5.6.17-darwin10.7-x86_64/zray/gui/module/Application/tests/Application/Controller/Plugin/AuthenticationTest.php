<?php
namespace Application\Controller\Plugin;

use Acl\Db\MapperGroups;

use Users\Identity;

use ZendServer\Authentication\Adapter\IdentityGroupsProvider;

use Zend\Authentication\Adapter\AdapterInterface;

use Zend\Authentication\AuthenticationService;

use Application\Module;

use ZendServer\PHPUnit\TestCase;

use Zend\Authentication\Storage\NonPersistent;

use PHPUnit_Framework_TestCase,
	Application\Controller\Plugin\Authentication,
	Application\Controller\LoginController,
	Zend\Di\Di,
	Zend\Authentication\Result,
	ZendServer\Exception;

require_once 'tests/bootstrap.php';

class AuthenticationTest extends TestCase
{

	private $user = 'USER';
	
	public function testAuthenticateSimpleAdapter() {
		$authentication = $this->getAuthenticationPlugin(true);
		
		self::assertTrue($authentication->authenticate('username', 'password'));
		$identity = $authentication->getIdentity();
		self::assertInstanceOf('Users\Identity', $identity);
	}
	
	public function testAuthenticateSimpleAdapterFailed() {
		$authentication = $this->getAuthenticationPlugin(false);
		
		self::assertFalse($authentication->authenticate('username', 'password'));
		self::assertFalse($authentication->hasIdentity());
	}
	
	public function testAuthenticateSimpleAdapterWithEmptyGroups() {
		$adapter = new MockAuthAdapterGroups();
		$adapter->setGroups(array('group1'));
		$authentication = $this->getAuthenticationPlugin(true, $adapter);
		$mock = $this->getMock('Acl\Db\MapperGroups');
		$mock->expects($this->any())->method('findAllMappedRoles')->will($this->returnValue(array()));
		$mock->expects($this->any())->method('findAllMappedApplications')->will($this->returnValue(array()));
		$authentication->setGroupsMapper($mock);
		
		self::assertTrue($authentication->authenticate('username', 'password'));
		$identity = $authentication->getIdentity();
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals('guest', $identity->getRole());
	}
	
	public function testAuthenticateSimpleAdapterWithAdministratorGroups() {
		$adapter = new MockAuthAdapterGroups();
		$adapter->setGroups(array('group1'));
		$authentication = $this->getAuthenticationPlugin(true, $adapter);
		$mock = $this->getMock('Acl\Db\MapperGroups');
		$mock->expects($this->any())->method('findAllMappedRoles')->will($this->returnValue(array('administrator' => 'group1')));
		$mock->expects($this->any())->method('findAllMappedApplications')->will($this->returnValue(array()));
		$authentication->setGroupsMapper($mock);
		
		self::assertTrue($authentication->authenticate('username', 'password'));
		$identity = $authentication->getIdentity();
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals('administrator', $identity->getRole());
	}
	
	public function testAuthenticateSimpleAdapterWithApplicationGroups() {
		$adapter = new MockAuthAdapterGroups();
		$adapter->setGroups(array('group1'));
		$authentication = $this->getAuthenticationPlugin(true, $adapter);
		$mock = $this->getMock('Acl\Db\MapperGroups');
		$mock->expects($this->any())->method('findAllMappedRoles')->will($this->returnValue(array()));
		$mock->expects($this->any())->method('findAllMappedApplications')->will($this->returnValue(array('app1' => 'group1')));
		$authentication->setGroupsMapper($mock);
		
		self::assertTrue($authentication->authenticate('username', 'password'));
		$identity = $authentication->getIdentity();
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals('developerLimited', $identity->getRole(), 'Application permission implicitly provides developerLimited');
	}
	
	public function testAuthenticateSimpleAdapterWithRolesAndApplicationGroups() {
		$adapter = new MockAuthAdapterGroups();
		$adapter->setGroups(array('group1'));
		$authentication = $this->getAuthenticationPlugin(true, $adapter);
		$mock = $this->getMock('Acl\Db\MapperGroups');
		$mock->expects($this->any())->method('findAllMappedRoles')->will($this->returnValue(array('administrator' => 'group1')));
		$mock->expects($this->any())->method('findAllMappedApplications')->will($this->returnValue(array('app1' => 'group1')));
		$authentication->setGroupsMapper($mock);
		
		self::assertTrue($authentication->authenticate('username', 'password'));
		$identity = $authentication->getIdentity();
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals('administrator', $identity->getRole(), 'Application permission implicitly provides developerLimited');
	}
	
	/**
	 * @param boolean $valid
	 * @return \Application\Controller\Plugin\Authentication
	 */
	private function getAuthenticationPlugin($valid, $adapter = null) {
		$authentication = new Authentication();
		$service = new AuthenticationService();
		$service->setStorage(new NonPersistent());
		$authentication->setAuthService($service);
		if (is_null($adapter)) {
			$adapter = new MockAuthAdapter();
		}
		$adapter->setAuthenticateResult($valid);
		$authentication->setAuthAdapter($adapter);
		return $authentication;
	}
	
	protected function setUp() {
		parent::setUp();
		
		$this->controller = new LoginController();
		$this->roleData = array('administrator' => $this->user, 'developer' => $this->user);
		
		return true;
	}

}

class MockAuthAdapter implements AdapterInterface {
	
	/**
	 * @var boolean
	 */
	private $authenticateResult;
	/* (non-PHPdoc)
	 * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
	 */
	public function authenticate() {
		if ($this->authenticateResult) {
			return new Result(Result::SUCCESS, new Identity());
		} else {
			return new Result(Result::FAILURE, new Identity());
		}
	}

	public function setIdentity() {}
	public function setCredential() {}
	
	/**
	 * @param boolean $authenticateResult
	 * @return MockAuthAdapter
	 */
	public function setAuthenticateResult($authenticateResult) {
		$this->authenticateResult = $authenticateResult;
		return $this;
	}

	
}

class MockAuthAdapterGroups extends MockAuthAdapter implements IdentityGroupsProvider {
	
	/**
	 * @var array
	 */
	private $groups;

	/* (non-PHPdoc)
	 * @see \ZendServer\Authentication\Adapter\IdentityGroupsProvider::getIdentityGroups()
	 */
	public function getIdentityGroups() {
		return $this->groups;
	}

	public function setIdentity() {}
	public function setCredential() {}
	/**
	 * @param array $groups
	 * @return MockAuthAdapterGroups
	 */
	public function setGroups($groups) {
		$this->groups = $groups;
		return $this;
	}

	
}