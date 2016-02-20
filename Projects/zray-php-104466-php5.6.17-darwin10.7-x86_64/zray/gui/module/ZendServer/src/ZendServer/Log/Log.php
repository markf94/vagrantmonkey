<?php
namespace ZendServer\Log;

use Zend\Log\Logger, Zend\Log\Writer, ZendServer;

class Log {
	
	/**
	 * @var \Zend\Log\Logger
	 */
	private static $logger;
	
	protected static $registeredExceptionHandler = false;
	/**
	 * @param Writer $writer
	 */
	public static function init(Logger $log, $logVerbosity = 'NOTICE') {
		self::clean();
		switch (strtoupper($logVerbosity)) {
			case 'EMERG':
				$verbosity = Logger::EMERG;
				break;
			case 'ALERT':
				$verbosity = Logger::ALERT;
				break;
			case 'ERR':
				$verbosity = Logger::ERR;
				break;
			case 'WARN':
				$verbosity = Logger::WARN;
				break;
			case 'CRIT':
				$verbosity = Logger::CRIT;
				break;
			case 'NOTICE':
				$verbosity = Logger::NOTICE;
				break;
			case 'INFO':
				$verbosity = Logger::INFO;
				break;
			case 'DEBUG':
				$verbosity = Logger::DEBUG;
				break;
			default:
				throw new ZendServer\Exception("Incorrect logVerbosity setting: {$logVerbosity}");
		}
		
		foreach ($log->getWriters()->toArray() as $writer) { /* @var $writer Writer\AbstractWriter */
			$writer->addFilter($verbosity);
		}
		
		static::$logger = $log;
	}
	
	public static function clean() {
		static::$logger = null;
	}
	
	/**
	 * @return \Zend\Log\Logger
	 */
	public static function getLogger() {
		return static::$logger;
	}
	
	/**
	 * Save an exception as an error in the log file
	 * @param string $title
	 * @param Exception $e
	 * @return boolean true
	 */
	public static function logException($title, \Exception $e) {
		$exceptionMessage = (string)$e->getMessage();
	
		$content = 	(string)$title . PHP_EOL .
					'Exception of type \'' . get_class($e) . '\': ' .
					$exceptionMessage;
	
		// need to add check if the log verbosity is info or debug. Look at old code
		self::err($content);
		return true;
	}
	
	/**
	 * Log a debug message for internal use
	 * @param string $message
	 * @param array $extras
	 */
	public static function debug($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->debug($message, $extras);
		}
	}
	
	/**
	 * Log an info message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function info($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->info($message, $extras);
		}
	}
	
	/**
	 * Log a notice message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function notice($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->notice($message, $extras);
		}
	}
	
	/**
	 * Log a warning message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function warn($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->warn($message, $extras);
		}
	}
	
	/**
	 * Log an error message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function err($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->err($message, $extras);
		}
	}
	
	/**
	 * Log a critical message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function crit($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->crit($message, $extras);
		}
	}
	
	/**
	 * Log an alert message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function alert($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->alert($message, $extras);
		}
	}
	
	/**
	 * Log an emergency message for external consumption
	 * @param string $message
	 * @param array $extras
	 */
	public static function emerg($message, $extras = array()) {
		if (self::$logger) {
			self::$logger->emerg($message, $extras);
		}
	}
	
	public static function registerExceptionHandler(Logger $logger)
	{
		// Only register once per instance
		if (self::$registeredExceptionHandler) {
			return false;
		}
	
		set_exception_handler(function ($exception) use ($logger) {
			$logger->log(Logger::ERR, $exception);
		});
		self::$registeredExceptionHandler = true;
		return true;
	}
}

