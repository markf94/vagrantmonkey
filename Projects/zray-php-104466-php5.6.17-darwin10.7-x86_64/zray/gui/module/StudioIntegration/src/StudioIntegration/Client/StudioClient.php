<?php
namespace StudioIntegration\Client;

use Zend\Http\Headers;

use Zend\Http\Request;

use StudioIntegration\Configuration;
use \ZendServer\Log\Log;
use StudioIntegration\Mapper;
use Zend\Uri\Http;

class StudioClient extends \Zend\Http\Client {

	/**
	 * @var Mapper
	 */
	private $mapper;
	
	const POST		= 'POST';
	const GET		= 'GET';
	const COOKIE	= 'cookie';
	const HEADER	= 'header';

	/**
	 'start_debug' 			=> 1<<0,
	 'start_profile' 		=> 1<<1,
	 'debug_stop' 			=> 1<<4,
	 'send_sess_end' 		=> 1<<6,
	 'debug_stop_error'		=> 1<<7,
	 'debug_jit'				=> 1<<8,
	 'no_remote'				=> 1<<9,
	 'use_ssl'				=> 1<<10,
	 'send_debug_header'		=> 1<<11,
	 'debug_fastfile'		=> 1<<13,
	 'debug_coverage'		=> 1<<14,

	 * @var array
	 */
	protected static $bitmasks = array(
			'start_debug'		=> 1,
			'start_profile'		=> 2,
			'debug_stop'		=> 16,
			'send_sess_end'		=> 64,
			'debug_stop_error'	=> 128,
			'debug_jit'			=> 256,
			'no_remote'			=> 512,
			'use_ssl'			=> 1024,
			'send_debug_header'	=> 2048,
			'debug_fastfile'	=> 8192,
			'debug_coverage'	=> 16384
	);

	/**
	 * Define which parameter of the debugger goes where in the request object (EGPC)
	 * @var array
	 */
	protected static $paramsAssociation = array(
			'_bm'					=> self::COOKIE,
			'debug_port'			=> self::COOKIE,
			'debug_host'			=> self::COOKIE,
			'original_url'			=> self::COOKIE,
			'debug_file_bp'			=> self::COOKIE,
			'debug_line_bp'			=> self::COOKIE,
			'debug_session_id'		=> self::COOKIE,
			'get_file_content'		=> self::GET,
			'line_number'			=> self::GET,
			'ZEND_MONITOR_DISABLE'	=> self::GET,
	);

	/**
	 * @var integer
	 */
	protected $debuggerBitmask = 0;

	public function __construct() {
		$this->setHeaders(array('User-Agent' => 'Zend Server'));
		
		$adapter = new \Zend\Http\Client\Adapter\Curl();
		$adapter = $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST,false);
		$adapter = $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER,false);
		
		$this->setAdapter($adapter);
	}

	public function addCommonAttributes($debugHostUrl, $fullUrl) {
		// collect the Issue data
		$this->setUri($debugHostUrl);
		
		$uri = new \Zend\Uri\Http($debugHostUrl);		
		$this->setHeaders(array('Host' => $uri->getHost()));

		$config = $this->getMapper()->getConfiguration();
		// collect Zend Server Debugger data
		$this->addDebuggerParam('debug_host',			$config->getCurrentHost());
		$this->addDebuggerParam('debug_port',			$config->getPort());
		$this->addDebuggerParam('use_ssl', 				$config->getSsl());
		$this->addDebuggerParam('debug_session_id', 	rand(3000, 1000000));
		$this->addDebuggerParam('no_remote',			0);
		$this->addDebuggerParam('start_debug',			1);
		$this->addDebuggerParam('send_sess_end',		1);
		$this->addDebuggerParam('debug_jit',			1);
		$this->addDebuggerParam('send_debug_header',	1);
		$this->addDebuggerParam('original_url',			$fullUrl);

		// disable Zend Server Monitor in order not to recatch the event
		$this->addDebuggerParam('ZEND_MONITOR_DISABLE',	1);

		// add the bitmask collected from the previous added params to the system
		$this->addDebuggerParam('_bm', $this->debuggerBitmask);
	}

	/*
	 * (non-PHPdoc)
	 * @see \Zend\Http\Client::doRequest()
	 */
	public function doRequest(Http $uri, $method, $secure = false, array $header = array(), $body = '') {
		return parent::doRequest($uri, $method, $secure, $header, $body);
	}
	
	/**
	 * Add data from the content of the event group to the Http client
	 */
	public function addEventGroupData($get, $post, $raw, $cookies, $headers) {
		$this->setParameterGet($get);
		$this->setParameterPost($post);
		if ($raw) {
			$this->setRawBody($raw);
		}

		foreach($cookies as $name => $value) {
			$this->addCookie($name, $value);
		}

		// Special header case, set the request METHOD as it was in the event's request
		if (isset($headers['REQUEST_METHOD'])) {
			try {
				$this->setMethod($headers['REQUEST_METHOD']);
			} catch (\Exception $e) {
				// @link https://il-jira.zend.net/browse/ZSRV-1448
				Log::notice(_t('Invalid REQUEST_METHOD provided: %s'), array($headers['REQUEST_METHOD']));
			}
		}

		// Special header case, set HTTP Authentication, if exists
		if (isset($headers['PHP_AUTH_USER']) && isset($headers['PHP_AUTH_PW'])) {
			$this->setAuth($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']);
		}

		if ($headers) {
			$filteredHeaders = array();
			foreach($headers as $key => $value) {
				if (($newKey = $this->filterHeader($key)) !== false) {
					$filteredHeaders[$newKey] = $value;
				}
			}
			
			$this->setHeaders($filteredHeaders);
		}
	}



	/**
	 * Add a debugger parameter to the relevant request section
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws Zwas_Exception
	 */
	public function addDebuggerParam($name, $value) {
		// Bitmask parameters have precendece
		if (isset(self::$bitmasks[$name])) {
			if ($value) {
				$this->debuggerBitmask |= self::$bitmasks[$name];
			} else {
				$this->debuggerBitmask &= (~ self::$bitmasks[$name]);
			}
			return;
		}

		if (isset(self::$paramsAssociation[$name])) {
			switch (self::$paramsAssociation[$name]) {
				case self::GET:
					$this->getRequest()->getQuery()->set($name, $value);
					break;
				case self::POST:
					$this->getRequest()->getPost()->set($name, $value);
					break;
				case self::COOKIE:
					$this->addCookie($name, $value);
					break;
				case self::HEADER:
					$this->getRequest()->getHeaders()->addHeader(Headers::fromString("$name: $value"));
					break;
				default:
					throw new \ZendServer\Exception(_t('Unidentified association type %s for %s', array(self::$paramsAssociation[$name], $name)));
					break;
			}
			return;
		}
		throw new \ZendServer\Exception(_t('Unidentified debugger parameter %s', array($name)));
	}

	/**
	 * @return the $mapper
	 */
	public function getMapper() {
		return $this->mapper;
	}

	/**
	 * @param \StudioIntegration\Mapper $mapper
	 */
	public function setMapper($mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * filtering headers from those that should be ignored
	 * @param string $name
	 */
	private function filterHeader($name) {
		static $ignoreHeaders = array(
				'HTTP_ACCEPT_ENCODING',	// to avoid compression in the debugging
				'CONNECTION',			// make sure it's not a keep-alive connection
				'CONTENT_LENGTH',		// content length may have changed as we've added info
				'HTTP_COOKIE'			// cookies already exist in _COOKIE so no need to add them twice
		);

		// skip ignored keys
		if (in_array($name, $ignoreHeaders)) {
			return false;
		}

		// we add only headers starting with HTTP_ or CONTENT_, which are not in the ignore list
		$headerKey = null;
		if (0 === strpos(strtoupper($name), 'CONTENT_')) {
			$headerKey = $name;

		} elseif (0 === strpos(strtoupper($name), 'HTTP_')) {
			$headerKey = substr($name, 5);

		} else {
			// This is not one of the headers that should be added to the request
			return false;
		}

		// make sure the $headerKey follows the standard of lowercase-Letters-With-Dashes
		return str_replace('_', '-', strtolower($headerKey));
	}
}