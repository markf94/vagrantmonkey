<?php
namespace WebAPI\Authentication\Adapter;

use Application\Module;

use WebAPI\Authentication\Result;

use Zend\Http\Header\Date;

use Users\Identity;

use WebAPI\Db\ApiKeyContainer;

use WebAPI\SignatureGenerator;

use Zend\Http\Headers;

use Zend\Authentication\AuthenticationService;

use Zend\Http\PhpEnvironment\Request;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class SignatureAbstractTest extends TestCase
{
	public function testAuthenticate() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();
		
		$signatureGenerator = new SignatureGenerator();
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		$signatureGenerator->setDate($date)->setHost('http://127.0.0.1')->setRequestUri('/Uri')->setUserAgent('Unit Testing');
		$sig = $signatureGenerator->generate('1234');
		
		$headers->addHeaders(array(
					'Date' => $date,
					'Host' => 'http://127.0.0.1',
					'User-Agent' => 'Unit Testing',
					'X-Zend-Signature' => "test;{$sig}",
				));

		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
		
		$webapiMapper = $this->getMock('WebAPI\Db\Mapper');
		$webapiMapper->expects($this->any())
					->method('findKeyByName')->with('test')
					->will($this->returnValue(new ApiKeyContainer(array('ID' => 1, 'NAME' => 'test', 'HASH' => '1234', 'USERNAME' => 'testuser', 'CREATION_TIME' => time()))));
		$adapter->setWebApiMapper($webapiMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::SUCCESS, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('test', $identity->getIdentity());
		self::assertEquals('testuser', $identity->getUsername());
		
		$authService = $adapter->getAuthService();
		$storage = $authService->getStorage();
		self::assertInstanceOf('Zend\Authentication\Storage\NonPersistent', $storage);
		self::assertSame($identity, $storage->read());
	}
	
	public function testAuthenticateBadDate() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();

		$signatureGenerator = new SignatureGenerator();
		
		if (! setlocale(LC_ALL, 'he_IL.utf8')) {
			self::markTestSkipped('Unable to change locale to he_IL.utf8');
		}

		$date = strftime('%a, %d %b %G %H:%M:%S GMT', time());
		setlocale(LC_ALL, 'en_US.UTF-8');
		
		$signatureGenerator->setDate($date)->setHost('http://127.0.0.1')->setRequestUri('/Uri')->setUserAgent('Unit Testing');
		$sig = $signatureGenerator->generate('1234');
		
		$headers->addHeaders(array(
					'Date' => $date,
					'Host' => 'http://127.0.0.1',
					'User-Agent' => 'Unit Testing',
					'X-Zend-Signature' => "test;{$sig}",
				));

		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
		
		$webapiMapper = $this->getMock('WebAPI\Db\Mapper');
		$webapiMapper->expects($this->any())
					->method('findKeyByName')->with('test')
					->will($this->returnValue(new ApiKeyContainer(array('ID' => 1, 'NAME' => 'test', 'HASH' => '1234', 'USERNAME' => 'testuser', 'CREATION_TIME' => time()))));
		$adapter->setWebApiMapper($webapiMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::FAILURE_UNCATEGORIZED, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('Unknown', $identity->getIdentity());

	}
	
	public function testAuthenticateTimeSkew() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();
		
		$signatureGenerator = new SignatureGenerator();
		$date = gmdate('D, d M Y H:i:s', time() + 2) . ' GMT';
		$signatureGenerator->setDate($date)->setHost('http://127.0.0.1')->setRequestUri('/Uri')->setUserAgent('Unit Testing');
		$sig = $signatureGenerator->generate('1234');
		
		$headers->addHeaders(array(
					'Date' => $date,
					'Host' => 'http://127.0.0.1',
					'User-Agent' => 'Unit Testing',
					'X-Zend-Signature' => "test;{$sig}",
				));

		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
		
		
		$webapiMapper = $this->getMock('WebAPI\Db\Mapper');
		$webapiMapper->expects($this->any())
					->method('findKeyByName')->with('test')
					->will($this->returnValue(new ApiKeyContainer(array('ID' => 1, 'NAME' => 'test', 'HASH' => '1234', 'USERNAME' => 'testuser', 'CREATION_TIME' => time()))));
		$adapter->setWebApiMapper($webapiMapper);
		
		$result = $adapter->authenticate();
		/// default timeskew
		$adapter->setTimeskew(SignatureAbstract::SIGNATURE_ALLOWED_TIMESKEW);
		self::assertEquals(Result::SUCCESS, $result->getCode());

		/// override default timeskew
		$adapter->setTimeskew(1);
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::FAILURE_SIGNATURE_TIMESKEW, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('Unknown', $identity->getIdentity());
	}
	
	public function testAuthenticateMismatch() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();
	
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		$sig = 'badsignature';
	
		$headers->addHeaders(array(
				'Date' => $date,
				'Host' => 'http://127.0.0.1',
				'User-Agent' => 'Unit Testing',
				'X-Zend-Signature' => "test;{$sig}",
		));
	
		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
	
		$webapiMapper = $this->getMock('WebAPI\Db\Mapper');
		$webapiMapper->expects($this->any())
		->method('findKeyByName')->with('test')
		->will($this->returnValue(new ApiKeyContainer(array('ID' => 1, 'NAME' => 'test', 'HASH' => '1234', 'USERNAME' => 'testuser', 'CREATION_TIME' => time()))));
		$adapter->setWebApiMapper($webapiMapper);
	
		$result = $adapter->authenticate();
	
		self::assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('test', $identity->getIdentity());
	}
	
	public function testAuthenticateNoSignature() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();
		
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		
		$headers->addHeaders(array(
					'Date' => $date,
					'Host' => 'http://127.0.0.1',
					'User-Agent' => 'Unit Testing'
				));

		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::FAILURE, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('Unknown', $identity->getIdentity());
	}
	
	public function testAuthenticateKeyNotFound() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();
		
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		$sig = '';
		
		$headers->addHeaders(array(
					'Date' => $date,
					'Host' => 'http://127.0.0.1',
					'User-Agent' => 'Unit Testing',
					'X-Zend-Signature' => "test;{$sig}",
				));

		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
		
		$webapiMapper = $this->getMock('WebAPI\Db\Mapper');
		$webapiMapper->expects($this->any())
		->method('findKeyByName')->with('test')
		->will($this->returnValue(new ApiKeyContainer(array())));
		$adapter->setWebApiMapper($webapiMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('test', $identity->getIdentity());
	}
	
	public function testAuthenticateZendZsd() {
		$adapter = new SignatureFinal();
		$request = new Request();
		$headers = new Headers();
		
		$signatureGenerator = new SignatureGenerator();
		$date = gmdate('D, d M Y H:i:s') . ' GMT';
		$signatureGenerator->setDate($date)->setHost('http://127.0.0.1')->setRequestUri('/Uri')->setUserAgent('Unit Testing');
		$sig = $signatureGenerator->generate('1234');
		
		$headers->addHeaders(array(
					'Date' => $date,
					'Host' => 'http://127.0.0.1',
					'User-Agent' => 'Unit Testing',
					'X-Zend-Signature' => "zend-zsd;{$sig}",
				));

		$request->setHeaders($headers);
		$request->setUri('/Uri');
		$adapter->setRequest($request);
		$adapter->setAuthService(new AuthenticationService());
		
		$webapiMapper = $this->getMock('WebAPI\Db\Mapper');
		$webapiMapper->expects($this->any())
					->method('findKeyByName')->with('zend-zsd')
					->will($this->returnValue(new ApiKeyContainer(array('ID' => 1, 'NAME' => 'zend-zsd', 'HASH' => '1234', 'USERNAME' => 'something', 'CREATION_TIME' => time()))));
		$adapter->setWebApiMapper($webapiMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::SUCCESS, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('zend-zsd', $identity->getIdentity());
		self::assertEquals('something', $identity->getUsername());
		self::assertEquals(Module::ACL_ROLE_ADMINISTRATOR, $identity->getRole());
	}
}

final class SignatureFinal extends SignatureAbstract {
	protected function collectGroups(Identity $identity) {
		return $identity;
	}
}