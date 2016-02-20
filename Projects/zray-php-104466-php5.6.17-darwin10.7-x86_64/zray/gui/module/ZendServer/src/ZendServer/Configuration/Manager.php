<?php
namespace ZendServer\Configuration;

class Manager {
	const OS_TYPE_NIX		= 1;
	const OS_TYPE_WINDOWS	= 2;
	const OS_TYPE_IBMI		= 3;
	const OS_TYPE_MAC		= 4;
	
	/**
	 * build => marketing name
	 */
	private static $windowsOsTable = array(	528 	=> 'Windows NT',
			807 	=> 'Windows NT',
			1057 	=> 'Windows NT',
			1381	=> 'Windows NT',
	
			2195	=> 'Windows 2000',
			2600	=> 'Windows XP',
			3790	=> 'Windows XP or Windows Server 2003',
			6000	=> 'Windows Vista',
			6001	=> 'Windows Vista or Windows Server 2008',
			6002	=> 'Windows Vista or Windows Server 2008');
	
	/**
	 * @return string
	 */
	public function getPhpVersion() {
		return PHP_VERSION;
	}

	/**
	 * @return integer
	 */
	public function getOsType() {
		if (DIRECTORY_SEPARATOR == '\\') {
			return self::OS_TYPE_WINDOWS;
		} else {
			if (in_array(PHP_OS, array('AIX', 'OS400'))) {
				return self::OS_TYPE_IBMI;
			} elseif (PHP_OS == 'Darwin') {
				return self::OS_TYPE_MAC;
			} else {
				return self::OS_TYPE_NIX;
			}
		}
	}
	
	/**
	 * @return integer
	 */
	public function getOsName() {
		$osType = $this->getOsType();
		switch ($osType) {
			case self::OS_TYPE_WINDOWS:
				return $this->getWindowsOsName();
				break;
			case self::OS_TYPE_IBMI:
				return 'Ibmi';
				break;
			case self::OS_TYPE_MAC:
				return 'Mac';
				break;
			case self::OS_TYPE_NIX:
				return 'Linux';
				break;
		}
	}
	
	/**
	 * @return string
	 */
	public function getPhpIniFileLocation() {
		return trim(getCfgVar('cfg_file_path'));
	}
	
	public function getDefaultListenPort() {
		$osType = $this->getOsType();
		switch ($osType) {
			case self::OS_TYPE_IBMI:
			case self::OS_TYPE_MAC:
				return 10088;

			case self::OS_TYPE_NIX:
			case self::OS_TYPE_WINDOWS:
			default:
				return 80;
		}
	}
	
	/**
	 * @return string
	 */
	private function getWindowsOsName() {
		list( , $build) = explode(" ", php_uname('v'));
		if (array_key_exists($build, self::$windowsOsTable)) {
			return self::$windowsOsTable[$build];
		}
		return 'Windows';
	}
}