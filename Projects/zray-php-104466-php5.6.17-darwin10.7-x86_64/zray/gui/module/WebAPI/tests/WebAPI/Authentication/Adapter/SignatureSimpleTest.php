<?php
namespace WebAPI\Authentication\Adapter;

use ZendServer\Exception;

use Application\Module;

use Zend\Authentication\Result;

use Zend\Http\Header\Date;

use Users\Identity;

use WebAPI\Db\ApiKeyContainer;

use WebAPI\SignatureGenerator;

use Zend\Http\Headers;

use Zend\Authentication\AuthenticationService;

use Zend\Http\PhpEnvironment\Request;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class SignatureSimpleTest extends TestCase
{
	public function testAuthenticate() {
		$adapter = new SignatureSimple();
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
		
		$usersMapper = $this->getMock('Users\Db\Mapper');
		$usersMapper->expects($this->any())->method('findUserByName')->with('testuser')
					->will($this->returnValue(array('ROLE' => 'testrole')));
		
		$adapter->setUsersMapper($usersMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::SUCCESS, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('test', $identity->getIdentity());
		self::assertEquals('testuser', $identity->getUsername());
		self::assertEquals('testrole', $identity->getRole());
	}
	
	public function testAuthenticateFailedFindUserbyname() {
		$adapter = new SignatureSimple();
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
		
		$usersMapper = $this->getMock('Users\Db\Mapper');
		$usersMapper->expects($this->any())->method('findUserByName')->with('testuser')
					->will($this->throwException(new Exception('test exception')));
		
		$adapter->setUsersMapper($usersMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::SUCCESS, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('test', $identity->getIdentity());
		self::assertEquals('testuser', $identity->getUsername());
		self::assertEquals('guest', $identity->getRole());
	}
	
	public function testAuthenticateZendZsd() {
		$adapter = new SignatureSimple();
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
					->will($this->returnValue(new ApiKeyContainer(array('ID' => 1, 'NAME' => 'zend-zsd', 'HASH' => '1234', 'USERNAME' => 'testuser', 'CREATION_TIME' => time()))));
		$adapter->setWebApiMapper($webapiMapper);
		
		$usersMapper = $this->getMock('Users\Db\Mapper');
		$usersMapper->expects($this->never())->method('findUserByName'); // user should not be retrieved
		
		$adapter->setUsersMapper($usersMapper);
		
		$result = $adapter->authenticate();
		
		self::assertEquals(Result::SUCCESS, $result->getCode());
		$identity = $result->getIdentity(); /* @var $identity Identity */
		self::assertEquals('zend-zsd', $identity->getIdentity());
		self::assertEquals('testuser', $identity->getUsername());
		self::assertEquals('administrator', $identity->getRole());
	}
}
