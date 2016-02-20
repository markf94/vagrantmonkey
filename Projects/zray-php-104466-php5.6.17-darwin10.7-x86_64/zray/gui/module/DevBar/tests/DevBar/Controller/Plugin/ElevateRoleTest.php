<?php
namespace DevBar\Controller\Plugin;

use ZendServer\PHPUnit\TestCase;
use Users\Identity;
use Application\Module;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;

require_once 'tests/bootstrap.php';

class ElevateRoleTest extends TestCase {
	public function test__invokeRoleGuest() {
		$plugin = new ElevateRole();
		$plugin->setIdentity(new Identity('Identity', Module::ACL_ROLE_GUEST));
		$authService = new AuthenticationService();
		$authService->setStorage(new NonPersistent());
		$plugin->setAuthService($authService);
		$result = $plugin->__invoke();
		
		$identity = $plugin->getIdentity();

		self::assertTrue($result);
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals('devbar', $identity->getRole());
		
		/// identity was written into session
		$sessionIdentity = $plugin->getAuthService()->getStorage()->read(); /* @var $sessionIdentity Identity */
		self::assertInstanceOf('Users\Identity', $sessionIdentity);
		self::assertEquals('devbar', $sessionIdentity->getRole());
	}
	
	public function test__invokeRoleBootstrap() {
		$plugin = new ElevateRole();
		$plugin->setIdentity(new Identity('Identity', Module::ACL_ROLE_BOOTSTRAP));
		$result = $plugin->__invoke();
		
		$identity = $plugin->getIdentity();

		self::assertFalse($result);
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals(Module::ACL_ROLE_BOOTSTRAP, $identity->getRole());
	}
	
	public function test__invokeRoleAdmin() {
		$plugin = new ElevateRole();
		$plugin->setIdentity(new Identity('Identity', Module::ACL_ROLE_ADMINISTRATOR));
		$result = $plugin->__invoke();
		
		$identity = $plugin->getIdentity();

		self::assertFalse($result);
		self::assertInstanceOf('Users\Identity', $identity);
		self::assertEquals(Module::ACL_ROLE_ADMINISTRATOR, $identity->getRole());
	}
}