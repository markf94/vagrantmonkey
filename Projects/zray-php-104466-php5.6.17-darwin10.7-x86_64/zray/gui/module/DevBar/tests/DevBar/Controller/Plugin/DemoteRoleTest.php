<?php
namespace DevBar\Controller\Plugin;

use ZendServer\PHPUnit\TestCase;
use Users\Identity;
use Application\Module;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;

require_once 'tests/bootstrap.php';

class DemoteRoleTest extends TestCase {
	public function test__invokeRoleDevbar() {
		$plugin = new DemoteRole();
		$plugin->setIdentity(new Identity('Identity', 'devbar'));
		$authService = new AuthenticationService();
		$authService->setStorage(new NonPersistent());
		$plugin->setAuthService($authService);
		$identity = $plugin->__invoke();

		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals(Module::ACL_ROLE_GUEST, $identity->getRole());
		
		/// identity was written into session
		$sessionIdentity = $plugin->getAuthService()->getStorage()->read(); /* @var $sessionIdentity Identity */
		self::assertInstanceOf('Users\Identity', $sessionIdentity);
		self::assertEquals(Module::ACL_ROLE_GUEST, $sessionIdentity->getRole());
	}
	
	public function test__invokeRoleGuest() {
		$plugin = new DemoteRole();
		$plugin->setIdentity(new Identity('Identity', Module::ACL_ROLE_GUEST));
		$identity = $plugin->__invoke();
		
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals(Module::ACL_ROLE_GUEST, $identity->getRole());
	}
	
	public function test__invokeRoleBootstrap() {
		$plugin = new DemoteRole();
		$plugin->setIdentity(new Identity('Identity', Module::ACL_ROLE_BOOTSTRAP));
		$identity = $plugin->__invoke();
		
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals(Module::ACL_ROLE_BOOTSTRAP, $identity->getRole());
	}
	
	public function test__invokeRoleAdmin() {
		$plugin = new DemoteRole();
		$plugin->setIdentity(new Identity('Identity', Module::ACL_ROLE_ADMINISTRATOR));
		$identity = $plugin->__invoke();
		
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals(Module::ACL_ROLE_ADMINISTRATOR, $identity->getRole());
	}
}