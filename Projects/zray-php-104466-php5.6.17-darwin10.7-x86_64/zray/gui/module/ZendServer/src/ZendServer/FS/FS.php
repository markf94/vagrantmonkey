<?php

namespace ZendServer\FS;

use ZendServer\Log\Log,
ZendServer\Exception as ZSException;

class FS {
	
	/**
	 * @param string $filename
	 * @return boolean
	 */
	static public function fileExists($filename) {
		return file_exists($filename);
	}
	
	/**
	 * Concatanate and normalize a path according to the operating system
	 * The result path will start with a directory seperator only if such was given on the first argument.
	 * The result will not end with a directory seperator (in case the last parameter is actually a filename)
	 * @example FS::createPath('/usr/local/zend/', '/platform/', '/etc/') will 
	 * 			return "/usr/local/zend/platform/etc" on a linux machine
	 * @param string $...
	 * @return string
	 */
	static public function createPath() {
		if (0 == func_num_args()) {
			return '';
		}	
	
		$funcArgs = func_get_args();
		$path = rtrim(array_shift($funcArgs), '\\/');
		
		foreach ($funcArgs as $subPath) {
			$subPath = trim($subPath, '\\/');
			$path .= DIRECTORY_SEPARATOR . $subPath;
		}
		
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$path = str_replace('"', '', $path);
		return $path;
	}
	
	/**
	 * Get the temp folder dedicated for the GUI relative to the install path
	 * Fallback for system temp directory
	 * @return string - path to temp directory
	 */	
	static public function getGuiTempDir() {
		$tempPath = self::createPath(getCfgVar('zend.temp_dir'), 'gui');
		if (! is_dir($tempPath)) {
			// TODO: if the mkdir fails, it will keep happening on each request
			if (! @mkdir($tempPath, 0700, true)) {
				return sys_get_temp_dir();
			}
		}
		if (! is_writable($tempPath)) {
			return sys_get_temp_dir();
		}
		return $tempPath;		
	}
	
	/**
	 * @return string
	 */
	static public function getIniScanDirPath() {
		return self::createPath(getCfgVar('zend.conf_dir'), getCfgVar('zend.ini_scandir'));
	}
	
	/**
	 * @return string
	 */
	static public function getLogsDirectory() {
		return self::createPath(getCfgVar('zend.log_dir'));
	}
	
	/**
	 * @return string
	 */
	static public function getZendIniScanDirPath() {
		if (! self::isLlinux()) {
			return self::getIniScanDirPath();
		}
		return self::createPath(getCfgVar('zend.install_dir'), 'gui', 'lighttpd', 'etc', getCfgVar('zend.ini_scandir'));
	}
	
	static public function isOs($osName) {
		return false !== stripos(PHP_OS, $osName);
	}
	
	static public function isLlinux() {
		return (stripos(PHP_OS, 'Linux') === 0);
	}
	
	static public function isWindows() {
		return (stripos(PHP_OS, 'win') === 0);
	}
	
	static public function isAix() {
		return (stripos(PHP_OS, 'AIX') === 0);
	}

	static public function isMac() {
		return (stripos(PHP_OS, 'Darwin') === 0);
	}	

	static public function hasLighttpd() {
		return self::isLlinux() || self::isMac();
	}
	
	static public function getOSAsString() {
		$str = php_uname('s');
		return current(explode(' ', $str)); // Windows NT ==> Windows
	}

	/**
	 * Get the path to the GlobalDirectivesFile
	 * 
	 * @return string - path to the GlobalDirectivesFile
	 */
	static public function getGlobalDirectivesFile() {
		if (self::isWindows()) {
			return self::createPath(getCfgVar('zend.conf_dir'), 'php.ini'); 
		}
		
		return self::createPath(getCfgVar('zend.conf_dir'), 'conf.d', 'ZendGlobalDirectives.ini');
	}
		

	/**
	 * @return string
	 */
	static public function getLogDirPath() {
		if (self::isWindows()) {
			return self::createPath(getCfgVar('zend.install_dir'), 'logs');
		}
		
		return self::createPath(getCfgVar('zend.install_dir'), 'var', 'log');
	}	
	
	
	
	/**
	 * @param string $path
	 * @param string $openMode
	 * @return \SplFileObject
	 */	
	static public function getFileObject($path, $openMode='r') {
		try {
			return new FileObject($path, $openMode);
		} catch (\Exception $e) {
			throw new ZSException("could not open file $path", null, $e);
		}		
		
		Log::debug("created FileObject at $path");
	}

	/**
	 * @param string $path
	 * @param int $openMode
	 * @return ZipArchive
	 */
	static public function getZipArchive($path, $flags=null) {
		if ($flags === null) $flags = \ZipArchive::CREATE | \ZipArchive::OVERWRITE;
		$zip = new ZipArchive(); // using our own ZipArchive
		if (($resultCode = $zip->open($path, $flags)) !== true) {
			throw new ZSException(_t('Failed to retrieve a zip file with error \'%s\'', array($resultCode)));
		}
		
		return $zip;
	}	
	
	
	/**
	 * @param string $filename
	 * @return boolean
	 */	
	static public function unlink($path) {
		Log::debug("unlinking $path");
		
		return unlink($path);
	}

	/**
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	static public function copy($source , $dest) {
		Log::debug("copying $source to $dest");
	
		return copy($source , $dest);
	}	

}

