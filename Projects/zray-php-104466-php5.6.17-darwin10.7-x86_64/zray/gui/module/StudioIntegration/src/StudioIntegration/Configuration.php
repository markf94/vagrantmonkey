<?php
namespace StudioIntegration;

use Zend\Db\Sql\Ddl\Column\Integer;
class Configuration {

	/**
	 * @var Configuration
	 */
	private static $instance;
	
	/**
	 * @var boolean
	 */
	private $autoDetect;
	
	/**
	 * @var Integer
	 */
	private $autoDetectionPort;
	
	/**
	 * @var boolean
	 */
	private $browserDetect;
	
	/**
	 * @var boolean
	 */
	private $useSsl;
	
	/**
	 * @var boolean
	 */
	private $useRemote;

	/**
	 * @var boolean
	 */
	private $breakOnFirstLine;
	
	/**
	 * @var integer
	 */
	private $port;
	
	/**
	 * @var string
	 */
	private $host;
	
	/**
	 * @var string
	 */
	private $brwoserHost;
	
	/**
	 * @var integer
	 */
	private $timeout;
	
	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}
	
	/**
	 * @return string
	 */
	public function getBrowserHost() {
		return (string)$this->brwoserHost;
	}
	
	/**
	 * @return string
	 */
	public function getCurrentHost() {
		if (! $this->autoDetect) {
			return ($this->browserDetect) ? (string)$this->brwoserHost : (string)$this->host;
		}
		return (string)$this->host;
	}
	
	/**
	 * @return boolean
	 */
	public function getAutoDetect() {
		return (boolean)$this->autoDetect;
	}
	
	/**
	 * @return boolean
	 */
	public function getAutoDetectionPort() {
		return $this->autoDetectionPort;
	}
	
	/**
	 * @return boolean
	 */
	public function getBrowserDetect() {
		return (boolean)$this->browserDetect;
	}
	
	/**
	 * @param string $browserHost
	 */
	public function setBrowserHost($browserHost) {
		$this->brwoserHost = $browserHost;
	}
	
	/**
	 * @return integer
	 */
	public function getPort() {
		return (integer)$this->port;
	}
	
	/**
	 * @return boolean
	 */
	public function getSsl() {
		return (boolean)$this->useSsl;
	}
	
	/**
	 * @return boolean
	 */
	public function getUseRemote() {
		return (boolean)$this->useRemote;
	}
	
	/**
	 * @return boolean
	 */
	public function getBrakeOnFirstLine() {
		return (boolean)$this->breakOnFirstLine;
	}
	
	/**
	 * @return the $timeout
	 */
	public function getTimeout() {
		return $this->timeout;
	}

	/**
	 * @param number $timeout
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	/**
	 * @param boolean $autoDetect
	 * @return Configuration
	 */
	public function setAutoDetect($autoDetect) {
		$this->autoDetect = $autoDetect;
		return $this;
	}
	
	/**
	 * @param boolean $autoDetectionPort
	 * @return Configuration
	 */
	public function setAutoDetectionPort($autoDetectionPort) {
		$this->autoDetectionPort = $autoDetectionPort;
		return $this;
	}
	
	/**
	 * @param boolean $browserDetect
	 * @return Configuration
	 */
	public function setBrowserDetect($browserDetect) {
		$this->browserDetect = $browserDetect;
		return $this;
	}
	
	/**
	 * @param string $host
	 * @param integer $port
	 * @param boolean $useSsl
	 */
	public function setConfiguration($host = null, $port = null, $useSsl = null, $breakOnFirstLine = null, $useRemote = null) {
		if (! empty($host)) {
			$this->host = (string)$host;
		}
		if (! empty($port)) {
			$this->port = (integer)$port;
		}
		if (('0' === $useSsl) || (! empty($useSsl))) {
			$this->useSsl = (boolean)$useSsl;
		}
		if (('0' === $breakOnFirstLine) || (! empty($breakOnFirstLine))) {
			$this->breakOnFirstLine = (boolean)$breakOnFirstLine;
		}
		if (('0' === $useRemote) || (! empty($useRemote))) {
			$this->useRemote = (boolean)$useRemote;
		}
	}
	
	public static function importZscmInstance(Configuration $suggestedConfiguration) {
		self::$instance = new Configuration();
		self::$instance->setBrowserHost($suggestedConfiguration->getBrowserHost());
		return self::$instance;
	}
	
	/**
	 * Singleton factory method
	 * Build a "Configuration" object base on arguments or data from configuration file
	 *
	 * @return Configuration
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new Configuration();
		}
		return self::$instance;
	}
	
	/**
	 * Resets all object properties of the singleton instance
	 * @return void
	 */
	public static function resetInstance() {
		self::$instance = null;
	}
	
	/**
	 * @return string
	 */
	public static function getBrowserRemoterAddress() {
		if (!isset($_SERVER['REMOTE_ADDR'])) {
			return '127.0.0.1';
		}
		
		$browserRemoteAddress = $_SERVER['REMOTE_ADDR'];
		// Special case of IPv6 local loopback
		if ('::1' == $browserRemoteAddress) {
			$browserRemoteAddress = '127.0.0.1';
		}
		// Special case of IPv6 which holds an IPv4 address (e.g. ::ffff:10.1.1.1) bug #30319
		if (0 === strpos($browserRemoteAddress, '::ffff:')) {
			$browserRemoteAddress = substr($browserRemoteAddress, 7);
		}
		return $browserRemoteAddress;
	}
	
	public function __construct() {
		$this->brwoserHost		= Configuration::getBrowserRemoterAddress();
	}
	
	
}