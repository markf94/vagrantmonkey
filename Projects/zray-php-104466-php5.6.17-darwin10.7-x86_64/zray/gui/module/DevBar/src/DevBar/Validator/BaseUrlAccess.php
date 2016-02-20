<?php

namespace DevBar\Validator;

use Zend\Validator\AbstractValidator;
use ZendServer\Validator\IpRange;
use ZendServer\Log\Log;
use Zend\Uri\UriFactory;
use ZendServer\Exception;
use Zend\Uri\Exception\InvalidArgumentException;
use Zend\Validator\Uri;
use Zend\Uri\Http;

class BaseUrlAccess extends AbstractValidator {
	
	const NOT_ALLOWED_DOMAIN = 'NOT_ALLOWED_DOMAIN';
	const INVALID_URL = 'INVALID_URL';
	
	protected $messageTemplates = array(
		self::NOT_ALLOWED_DOMAIN => 'Requested URL \'%value%\' is not allowed for this token',
		self::INVALID_URL => '\'%value%\' is not an absolute URL',
	);
	
	/**
	 * @param Uri
	 */
	protected $uri;
	/**
	 * @var Http
	 */
	protected $baseUrl;
	
	/**
	 * @param array $options
	 */
	public function __construct($options = null) {
		$this->uri = new Uri(array('allowAbsolute' => true, 'allowRelative' => false));
		if (isset($options['baseUrl'])) {
			if (! $this->uri->isValid($options['baseUrl'])) {
				$message = current($this->uri->getMessages());
				throw new Exception("baseUrl parameter must be an absolute URL ({$message})");
			}
			$this->baseUrl = clone $this->uri->getUriHandler();
		} else {
			throw new Exception("baseUrl parameter must be an absolute URL");
		}
		parent::__construct($options);
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Validator\ValidatorInterface::isValid()
	 */
	public function isValid($value) {
		$this->setValue($value);
		if (! $this->uri->isValid($value)) {
			$this->error(self::INVALID_URL);
			return false;
		}
		
		$uri = $this->uri->getUriHandler();
		$baseUrl = $this->baseUrl;
		
		if ($baseUrl->getHost() != $uri->getHost()) {
			$this->error(self::NOT_ALLOWED_DOMAIN);
			return false;
		}

		$uriPath = $uri->getPath();
		$baseUrlPath = $baseUrl->getPath();
		if (empty($baseUrlPath) || empty($uriPath)) {
			$uriPath .= '/';
			$baseUrlPath .= '/';
		}
		/// if uri does not start with our baseurl
		if ($uriPath !== $baseUrlPath && strpos($uriPath, $baseUrlPath) !== 0) {
			$this->error(self::NOT_ALLOWED_DOMAIN);
			return false;
		}
		
		return true;
	}
}