<?php
namespace StudioIntegration;

use ZendServer\Exception;

use Zend\Validator\Regex;

use StudioIntegration\Client;
use Zend\Session\SessionManager,
Zend\Uri\Uri,
ZendServer\Log\Log,
StudioIntegration\Debugger\Wrapper,
ZendServer\Validator\IpRange as IpRangeValidator,
Application\Module as AppModule;
use Zend\Config\Config;
use Application\ConfigAwareInterface;
use Configuration\MapperExtensions;
use Zend\Http\HeaderLoader;

class Model implements ConfigAwareInterface {

	const DEBUGGER_DEFAULT_PORT	= 10137;

	const REQUEST_TIME_LIMIT	= 1800;	// time limit of the GUI request in seconds

	/**
	 * 
	 * @var string
	 */
	private $alternateDebugServer = "";
	
	/**
	 * @var \StudioIntegration\Client
	 */
	private $client;
	
	/**
	 * @var \StudioIntegration\Debugger\Wrapper
	 */
	private $debuggerWrapper = null;
	
	/**
	 * @var \StudioIntegration\MonitorIssueGroupData
	 */
	private $issueGroupDetails;

	/**
	 * @var Config
	 */
	private $config;
	
	/**
	 * @var Mapper
	 */
	private $studioMapper;
	
	/**
	 * @var MapperExtensions
	 */
	private $extensionsMapper;
	
	public function __construct() {
		$this->checkIfOperationsAllowed();
	}

	
	/**
	 * @param \StudioIntegration\Debugger\Wrapper $wrapper
	 * @return \StudioIntegration\Debugger\Wrapper
	 */
	public function setDebuggerWrapper($wrapper) {
		$this->debuggerWrapper = $wrapper;
		return $this;
	}
	
	/**
	 * @return \StudioIntegration\Debugger\Wrapper
	 */
	private function getDebuggerWrapper() {
		if (is_null($this->debuggerWrapper)) {
			$this->debuggerWrapper = new Wrapper();
		}
	
		return $this->debuggerWrapper;
	}
	
	/**
	 * @param \StudioIntegration\MonitorIssueGroupData
	 * @return \StudioIntegration\ClientDebug
	 */
	public function getDebugClient(\StudioIntegration\MonitorIssueGroupData $data) {
		$client = new Client\Debug($data->getFileName(), $data->getLine());
		$client->setMapper($this->getStudioMapper());
		$client->addEventGroupData(
				$data->getGet(),
				$data->getPost(),
				$data->getRawPostData(),
				$data->getCookies(),
				$data->getHeaders()
		);
		$fullUrl = $this->encodeUri($data->getFullUrl());
		$timeout = AppModule::config('studioIntegration', 'zend_gui', 'studioClientTimeout');
		$client->setOptions(array('timeout' => $timeout));
		$client->addCommonAttributes($this->getDebugHostUrl($fullUrl), $fullUrl);
		return $client;
	}

	/**
	 * @param \StudioIntegration\MonitorIssueGroupData $data
	 * @return \StudioIntegration\ClientProfile
	 */
	public function getProfileClient(\StudioIntegration\MonitorIssueGroupData $data) {		
		$client = new Client\Profile();
		$client->setMapper($this->getStudioMapper());
		$client->addEventGroupData(
				$data->getGet(),
				$data->getPost(),
				$data->getRawPostData(),
				$data->getCookies(),
				$data->getHeaders()
		);
		
		$fullUrl = $this->encodeUri($data->getFullUrl());
		$timeout = AppModule::config('studioIntegration', 'zend_gui', 'studioClientTimeout');
		$client->setOptions(array('timeout' => $timeout));
		$client->addCommonAttributes($this->getDebugHostUrl($fullUrl), $fullUrl);
		return $client;
	}

	/**
	 * get show source client by event group data
	 * @param \StudioIntegration\MonitorIssueGroupData $data
	 * @return \StudioIntegration\ClientSource
	 */
	public function getShowSourceClientByEventGroup(\StudioIntegration\MonitorIssueGroupData $data) {
	    return $this->getShowSourceClient($data->getFileName(), $data->getLine(), $data->getFullUrl());
	}
	
	/**
	 * 
	 * @param unknown $fileName
	 * @param unknown $line
	 * @param unknown $fullUrl
	 * @return \StudioIntegration\Client\Source
	 */
	public function getShowSourceClient($fileName, $line, $fullUrl) {
	    $client = new Client\Source($fileName, $line);
	    $client->setMapper($this->getStudioMapper());
	    $fullUrl = $this->encodeUri($fullUrl);
	    $timeout = AppModule::config('studioIntegration', 'zend_gui', 'studioClientTimeout');
	    $client->setOptions(array('timeout' => $timeout));
	    $client->addCommonAttributes($this->getDebugHostUrl($fullUrl), $fullUrl);
	    return $client;
	}
	
	/*
	$client = new Client\Source($data->getFileName(), $data->getLine());
	$client->setMapper($this->getStudioMapper());
	$fullUrl = $this->encodeUri($data->getFullUrl());
	$timeout = AppModule::config('studioIntegration', 'zend_gui', 'studioClientTimeout');
	$client->setOptions(array('timeout' => $timeout));
	$client->addCommonAttributes($this->getDebugHostUrl($fullUrl), $fullUrl);
	 */

	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::getAwareNamespace()
	 */
	public function getAwareNamespace() {
		return array('installation');
	}

	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::setConfigNamespace()
	 */
	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 * Perform a request to an http client and control the error handling of the http_client and the connection to the debugger
	 * whether or not it receive a successful response from the debugger
	 * @param \StudioIntegration\Client $client
	 * @throws \ZendServer\Exception
	 */
	public function connect(\StudioIntegration\Client\StudioClient $client) {

		try {
			// NOTE: as the user may leave the Studio Integration page, the session needs to be released
			$sessionManager = new SessionManager();			
			$sessionManager->writeClose();
			
			Log::info('Connect to studio');
			Log::debug('Target: ', array($client->getUri()->toString()));
			Log::debug('Query: ', $client->getRequest()->getQuery());
			Log::debug('Cookies: ', $client->getCookies());

			HeaderLoader::addStaticMap(array('setcookie' => 'ZendServer\Http\Header\SetCookie'));
			
			$response	= $client->send();
			$uri		= $client->getUri();
			if ($response->isClientError()) {
				$this->checkResponseError($response, (string)$uri);
			}
			elseif ($response->isSuccess()) {
				$this->checkResponseSuccessful($response, (string)$uri);
			}
			$sessionManager->start();
			return;

		} catch (Exception $e) {
				throw $e;
		} catch (\Exception $e) { // hardcoded http://www.zend.com/support-center
			$logMessage		= _t('Failed to create the HTTP request on %s: %s', array($client->getUri(true), $e->getMessage()));
			$errorMessage	= _t('An internal application error has occurred.  If this problem persists, go to the Zend Support Center at: http://www.zend.com/support-center');
		}
		
		$sessionManager->start();
		Log::warn($logMessage);
		throw new \ZendServer\Exception($errorMessage, 0, $e);
	}

	public function setAlternateDebugServer($hostname) {
		$this->alternateDebugServer = $hostname;
	}
	
	/**
	 * @throws \ZendServer\Exception if operation (debug/profile/view) is not allowed
	 */
	protected function checkIfOperationsAllowed() {
		$studioConfiguration = Configuration::getInstance();
		$debugHost = $studioConfiguration->getCurrentHost();

		if ($this->alternateDebugServer) {
			// Debug session will not work on alternate server with a local Studio Client IP (127.*.*.*)
			$ipRangeValidator = new IpRangeValidator;
			
			if ($ipRangeValidator->isValid($debugHost)) {
				throw new \ZendServer\Exception(_t('The alternate server cannot communicate with a local Studio Client IP (i.e. 127.0.0.1). Change the Zend Debugger configuration in the Zend Components page'));
			}				
		} else {// Check if the IDE Client IP is allowed/denied on the Debugger, this check can be made only local server			
			// @todo - validate allowed/denied hosts, - requires a translated debuggerModel
		}
	}

	/**
	 * Get URL of web server with debugger on which the debug session should happen
	 * @param string $fullUrl
	 * @return string
	 */
	protected function getDebugHostUrl($fullUrl) {
		if (empty($this->alternateDebugServer)) {
			return $fullUrl;
		}
		$uri = new Uri($fullUrl);
		$alternateUriParts = explode(':', $this->alternateDebugServer);

		$uri->setHost($alternateUriParts[0]);
		if (isset($alternateUriParts[1])) {
			$uri->setPort($alternateUriParts[1]);
		} else {
			$uri->setPort(null);
		}
		return (string)$uri;
	}

	/**
	 * Check the response and throw an Exception base on the error occurred
	 * @param Zend_Http_Response $response
	 * @param string $url
	 * @return void
	 * @throws \ZendServer\Exception
	 */
	private function checkResponseError(\Zend\Http\Response $response, $url) {
		$responseStatusCode = $response->getStatusCode();
		Log::err("Request to debugger on URL '$url' failed: {$response->getReasonPhrase()} (with code {$responseStatusCode})");

		switch ($responseStatusCode) {
			case 403:	// Forbidden
				$errorMsg = _t('The event\'s URL \'%s\' is forbidden (response code 403)', array($url));
				break;

			case 404:	// Not Found
				$errorMsg = _t('The event\'s URL \'%s\' was not found (response code 404)', array($url));
				break;

			case 500:	// Internal Server Error
				if ('OK' == $response->getHeader('X-zend-debug-server')) {
					// If the response hold a successfull Debugger header,
					//	it means the Internal Server Error was the actual result of the debug session
					//	(i.e. fatal error on fastcgi when display errors is set to off)
					return true;
				}
				// We do not have break because we want a fallback for the default message

			default:
				$errorMsg = _t('The event\'s URL \'%s\' returned \'%s\' (response code %s)',
				array($url, $response->getReasonPhrase(), $responseStatusCode));
		}
		throw new \ZendServer\Exception(_t('Failed to communicate with Zend Debugger. %s', array($errorMsg)));
	}

	private function encodeUri($uri) {
		return str_replace(' ', '%20', $uri);// @todo - move this METHOD - used to be Zwas_Uri::encode()
	}
	
	/**
	 * Check the response after success request and check if the response is valid
	 * @param \Zend\Http\Response $response
	 * @param string $url
	 * @return void
	 * @throws \ZendServer\Exception on invalid response
	 */
	private function checkResponseSuccessful(\Zend\Http\Response $response, $url) {	
		$headers = $response->getHeaders();
		if (! is_object($headers->get('X-zend-debug-server'))) {
			$debugResponse = '';
		} else {
			$debugResponse = $headers->get('X-zend-debug-server')->getFieldValue();
		}
		
		if ('OK' == $debugResponse) {
			return;
		}
		
		$studioClientIP		= Configuration::getInstance()->getCurrentHost();

		if ('' == $debugResponse) {
			if ($this->alternateDebugServer) {
				$logMessage		= "Failed to communicate with Zend Debugger on alternate server '{$this->alternateDebugServer}'. No debugger header in the response.\nStudio client IP was '{$studioClientIP}'";
				$errorMessage	= _t('Failed to communicate with IDE. See the Online Help \'Troubleshoot\' section to find out how to fix the connection.');

			} else {
				if ($this->getExtensionsMapper()->isExtensionLoaded('Zend Debugger')) {
					$logMessage		= "Failed to communicate with Zend Debugger on originating server ('$url'). Debugger is loaded but there was no debugger header in the response.\nStudio Client IP was '{$studioClientIP}'"; 
					$errorMessage	= _t('Failed to communicate with IDE. Make sure IDE is running on \'%s\' according to the Zend Debugger\'s configuration on the Zend Components page', array($studioClientIP));
				} else {
					$logMessage		= 'Failed to communicate with IDE, Zend Debugger is not loaded.';
					$errorMessage	= _t('Failed to communicate with IDE, Zend Debugger is not loaded. To turn it on, go to the Zend Components page');
				}
			}
		} else {
			if (0 < preg_match('#^Host \'(?P<host>.+)\' is not allowed to open debug sessions#', $debugResponse, $matches)) {
				$logMessage		= "Zend Debugger failed to communicate with studio, host '{$studioClientIP}' is not in the debugger allowed hosts list";
				$errorMessage	= _t('Zend Debugger failed to communicate with studio, host \'%s\' is not in the debugger allowed hosts list', array($studioClientIP));
			} else {
				if ($this->alternateDebugServer) {
					$logMessage		= "Failed to communicate with Zend Debugger on alternate server '{$this->alternateDebugServer}', debugger header in the response was: {$debugResponse}.\nStudio Client IP was '{$studioClientIP}'";
					$errorMessage	= _t('Failed to communicate with IDE. Go to the Online Help\'s \'Troubleshoot\' section to find out how to fix the connection');
				} else {
					$logMessage		= "Failed to communicate with Zend Debugger on originating server ('$url'), debugger header in the response was: {$debugResponse}.\nStudio Client IP was '{$studioClientIP}'";
					$errorMessage	= _t('Failed to communicate with IDE. Make sure IDE is running on \'%s\' according to the Zend Debugger\'s configuration on the Zend Components page', array($studioClientIP));
				}
			}
		}
		Log::err($logMessage);
		throw new \ZendServer\Exception($errorMessage);
	}

	/**
	 * Start debugger debug mode
	 * @param array $options list of debugging options (e.g. 'debugger_host=127.0.0.1', 'debug_port=10137', etc.)
	 * @param array $filters list of url filters
	 * @throws Exception
	 */
	public function debuggerStartDebugMode($options, $filters) {
		foreach($filters as $filter) {
			$ports = $this->config['defaultPort'] . '|' . $this->config['securedPort'] . '|' . $this->config['enginePort'];
			if (preg_match('#.+:(' . $ports .')#', $filter, $matches)) {
				throw new Exception("Starting debug mode on port {$matches[1]} is not allowed", Exception::ASSERT);
			}
		}
		$this->debuggerWrapper->debugModeStart($options, $filters);
	}
	
	/**
	 * stop debugger debug mode
	 */
	public function debuggerStopDebugMode() {
		$this->debuggerWrapper->debugModeStop();
	}
	
	public function debuggerIsDebugModeEnabled() {
		return $this->debuggerWrapper->isDebugModeEnabled();
	}
	
	/**
	 * @return MapperExtensions
	 */
	public function getExtensionsMapper() {
		return $this->extensionsMapper;
	}

	/**
	 * @return Mapper
	 */
	public function getStudioMapper() {
		return $this->studioMapper;
	}

	/**
	 * @param \StudioIntegration\Mapper $studioMapper
	 */
	public function setStudioMapper($studioMapper) {
		$this->studioMapper = $studioMapper;
	}

	/**
	 * @param \Configuration\MapperExtensions $extensionsMapper
	 */
	public function setExtensionsMapper($extensionsMapper) {
		$this->extensionsMapper = $extensionsMapper;
	}

	
}