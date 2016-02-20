<?php

function ZERROR($msg) {
	ZendDeployment_Logger::ERROR ( $msg );
}

function ZWARNING($msg) {
	ZendDeployment_Logger::WARNING ( $msg );
}

function ZNOTICE($msg) {
	ZendDeployment_Logger::NOTICE ( $msg );
}

function ZDBG1($msg) {
	ZendDeployment_Logger::DBG1 ( $msg );
}

function ZDBG2($msg) {
	ZendDeployment_Logger::DBG2 ( $msg );
}

function ZDBG3($msg) {
	ZendDeployment_Logger::DBG3 ( $msg );
}

class ZendDeployment_Logger {
	
	private static $_logFile = NULL;
	private static $_isInited = false;
	private static $_logLevel = 0;
	private static $_logRotationSize = 10;
	
	const TIME_FORMAT = 'd.m.Y H:i:s';
	
	public static function ERROR($msg) {
		self::initLog ();
		$msg = "ERROR: " . $msg . PHP_EOL;
		self::writeMsg ( $msg );
	}
	
	public static function WARNING($msg) {
		self::initLog ();
		if (self::$_logLevel < 1) {
			return;
		}
		$msg = "WARNING: " . $msg . PHP_EOL;
		self::writeMsg ( $msg );
	}
	
	public static function NOTICE($msg) {
		self::initLog ();
		if (self::$_logLevel < 2) {
			return;
		}
		$msg = "NOTICE: " . $msg . PHP_EOL;
		self::writeMsg ( $msg );
	}
	
	public static function DBG1($msg) {
		self::initLog ();
		if (self::$_logLevel < 3) {
			return;
		}
		$msg = "DBG1: " . $msg . PHP_EOL;
		self::writeMsg ( $msg );
	}
	
	public static function DBG2($msg) {
		self::initLog ();
		if (self::$_logLevel < 4) {
			return;
		}
		$msg = "DBG2: " . $msg . PHP_EOL;
		self::writeMsg ( $msg );
	}
	
	public static function DBG3($msg) {
		self::initLog ();
		if (self::$_logLevel < 5) {
			return;
		}
		$msg = "DBG3: " . $msg . PHP_EOL;
		self::writeMsg ( $msg );
	}
	
	public static function initLogNull() {
		self::$_isInited = true;
		self::$_logFile = fopen ( 'php://stderr', "a+" );
		self::$_logLevel = 0;
	}
	
	private static function initLog() {
		if (! self::$_logFile && ! self::$_isInited) {
			
			self::$_isInited = true;
			
			$zendLogDir = NULL;
			if (function_exists ( 'zend_get_cfg_var' )) {
				$zendLogDir = zend_get_cfg_var ( "zend.log_dir" );
				
				$zendDir = get_cfg_var("zend.install_dir");
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					$iniFile = "$zendDir/etc/cfg/deployment.ini";
				} elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'AIX') {
					$iniFile = "$zendDir/etc/conf.d/deployment.ini";
				} else {
					$iniFile = "$zendDir/gui/lighttpd/etc/conf.d/deployment.ini";
				}
				
				$ini = parse_ini_file($iniFile);
				
				if (isset($ini["zend_deployment.log_verbosity_level"])) {
					$verbose = $ini["zend_deployment.log_verbosity_level"];
				} else {
					$verbose = 0;
				}
				if (isset($ini["zend_deployment.log_rotation_size"])) {
					self::$_logRotationSize = (int) $ini["zend_deployment.log_rotation_size"];
				}  else {
					$verbose = 10;
				}
				
				self::$_logLevel = ( int ) $verbose;
			}
			
			if ($zendLogDir) {
				$logPath = $zendLogDir . "/deployment.log";
							
				if (self::$_logRotationSize && file_exists($logPath) &&
						(filesize($logPath) > (self::$_logRotationSize * 1024 * 1024) ) ) {
					$path_parts = pathinfo($logPath);
						
					$timestamp = "_";
					$timestamp .= strftime("%d%m%y");
					$timestamp .= "_";
					$timestamp .= strftime("%H%M%S");
					
					$rotateName = $path_parts['dirname'] . "/" . $path_parts['filename'] . $timestamp . "." . $path_parts['extension'];
					@rename($logPath,  $rotateName );
				}
				
				self::$_logFile = fopen ( $logPath, "a+" );
			}
		}
	
	}
	
	private static function writeMsg($msg) {
		
		$msg = "[" . date ( self::TIME_FORMAT ) . "] (" . getmypid () . ") " . $msg;
		if (self::$_logFile) {
			fwrite ( self::$_logFile, $msg );
		} else {
			// Printing on screen is freezed to avoid zend_get_cfg_var binary string PHP7 bug: #ZSRV-15636
			// echo PHP_EOL . $msg;
		}
	}
}
