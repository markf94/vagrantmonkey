<?php

namespace WebAPI\Authentication\Adapter;

use Application\Module;

use ZendServer\Authentication\Adapter\Ldap;

use ZendServer\Exception;

use ZendServer\Log\Log;

use WebAPI\Authentication\Result;

use Users\Identity;

use Zend\Authentication\Storage\NonPersistent;
use Zend\Authentication\AuthenticationService;
use WebAPI\Db\ApiKeyContainer;

use Zend\Http\PhpEnvironment\Request;
use WebAPI\Db\Mapper;
use WebAPI\SignatureGenerator;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Ldap as ZendLdap;
use Zend\Http\Header\Exception\InvalidArgumentException;

abstract class SignatureAbstract implements AdapterInterface {
	
    const SIGNATURE_ALLOWED_TIMESKEW = 360;
	const SIGNATURE_TIMESKEW_CHECK_DISABLE = -1;
	
	/**
	 * @var Request
	 */
	private $request;
	
	/**
	 * @var Mapper
	 */
	private $webApiMapper;
	/**
	 * @var AuthenticationService
	 */
	private $authService;
	/**
	 * @var integer
	 */
	private $timeskew = self::SIGNATURE_ALLOWED_TIMESKEW;
	
	/* (non-PHPdoc)
	 * @see \Zend\Authentication\Adapter\AdapterInterface::authenticate()
	*/
	public function authenticate() {
		$request = $this->getRequest();
		$headers = $request->getHeaders();
		$originalHeaders = $headers->toArray();
		
		$requestTimestamp = $request->getServer('REQUEST_TIME');
		
		$signatureGenerator = new SignatureGenerator();
		if ($headers->has('X-Zend-Signature')) {
			// workaround for non english localized dates handling - php cannot parse localized dates
			try {
				$date = $headers->get('Date')->getFieldValue();
				$signatureGenerator->setDate($date);
			} catch (InvalidArgumentException $ex) {
				return new Result(Result::FAILURE_UNCATEGORIZED, new Identity(_t('Unknown')), array(_t('Date header cannot be parsed: %s. Are you using a localized date header?', array($originalHeaders['Date'])))); 
			}

			if ($this->getTimeskew() != self::SIGNATURE_TIMESKEW_CHECK_DISABLE && abs($requestTimestamp - strtotime($date)) > $this->getTimeskew()) {
				return new Result(Result::FAILURE_SIGNATURE_TIMESKEW, new Identity(_t('Unknown')));
			}
			
			list($keyName, $remoteSignature) = explode(';', $headers->get('X-Zend-Signature')->getFieldValue());
			$remoteSignature = trim($remoteSignature);
			$keyMapper = $this->getWebApiMapper();
			$key = $keyMapper->findKeyByName($keyName);
			
			$signatureGenerator->setHost($headers->get('host')->getFieldValue());
			$signatureGenerator->setRequestUri($request->getUri()->getPath());
			$signatureGenerator->setUserAgent($headers->get('useragent')->getFieldValue());
		} else {
			return new Result(Result::FAILURE, new Identity(_t('Unknown')));
		}
		
		$identity = new Identity($keyName);
		if (! $key->getId()) {
			return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, $identity, array(_t('Unknown key name requested')));
		}
		
		if($signatureGenerator->generate($key->getHash()) != $remoteSignature) {
			return new Result(Result::FAILURE_CREDENTIAL_INVALID, $identity, array(_t('Signature comparison does not match')));
		}
		
		$authService = $this->getAuthService();
		$identity->setUsername($key->getUsername());
		/*
		 * @see https://il-jira.zend.net/browse/ZSRV-7691
		 */
		if ($key->getName() == 'zend-zsd') {
			$identity->setRole(Module::ACL_ROLE_ADMINISTRATOR);
		} elseif (isZrayStandaloneEnv()) {
			// statically assign a role in case of Z-Ray standalone
			$identity->setRole(Module::ACL_ROLE_ADMINISTRATOR);
		} else {
			$identity = $this->collectGroups($identity);
		}
		
		$storage = new NonPersistent();
		$storage->write($identity);
		$authService->setStorage($storage);
			
		return new Result(Result::SUCCESS, $identity);
	}
	
	/**
	 * 
	 * @param Identity $identity
	 * @return Identity
	 */
	abstract protected function collectGroups(Identity $identity);
	
	/**
	 * @return \Zend\Http\PhpEnvironment\Request $request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @return \WebAPI\Db\Mapper $webApiMapper
	 */
	public function getWebApiMapper() {
		return $this->webApiMapper;
	}

	/**
	 * @return \Zend\Authentication\AuthenticationService $authService
	 */
	public function getAuthService() {
		return $this->authService;
	}

	/**
	 * @return integer
	 */
	public function getTimeskew() {
		return $this->timeskew;
	}

	/**
	 * @param number $timeskew
	 */
	public function setTimeskew($timeskew) {
		$this->timeskew = $timeskew;
	}

	/**
	 * @param \Zend\Http\PhpEnvironment\Request $request
	 * @return WebAPISignature
	 */
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}

	/**
	 * @param \WebAPI\Db\Mapper $webApiMapper
	 * @return WebAPISignature
	 */
	public function setWebApiMapper($webApiMapper) {
		$this->webApiMapper = $webApiMapper;
		return $this;
	}

	/**
	 * @param \Zend\Authentication\AuthenticationService $authService
	 * @return WebAPISignature
	 */
	public function setAuthService($authService) {
		$this->authService = $authService;
		return $this;
	}


}
