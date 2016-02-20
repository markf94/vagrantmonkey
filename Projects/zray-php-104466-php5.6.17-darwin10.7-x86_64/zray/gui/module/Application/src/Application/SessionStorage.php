<?php
namespace Application;

use \Zend\Session\SessionManager,
	\ZendServer\Exception;

class SessionStorage {
	
	/**
	 * @var string
	 */
	const NS = 'ZEND_SERVER_SETTINGS';
	
	/**
	 * @var SessionManager
	 */
	private $manager = null;
	
	public function __construct() {	
		$this->manager = new SessionManager();
		$this->manager->start();
	}
	
	/**
	 * @param string $tz
	 */
	public function setTimezone($tz) {
		$this->setValue('timezone', $tz);
	}
	
	/**
	 * @return string 
	 */
	public function getTimezone() {
		return $this->getValue('timezone');
	}
	
	public function setRemoteAddr() {
	    $this->setValue('remoteAddr', $this->getClientIp());
	}
	
	/**
	 * @return string
	 */
	public function getRemoteAddr() {
	    return $this->getValue('remoteAddr');
	}
	
	/**
	 * @return bool
	 */
	public function hasRemoteAddr() {
	    return $this->hasValue('remoteAddr');
	}
	
	/**
	 * @return bool
	 */
	public function hasTimezone() {
		return $this->hasValue('timezone');
	}
	
	public function isExists() {
		return ($this->manager->getStorage()->offsetExists(self::NS) &&	! is_null($this->getStorage()));
	}
	
	public function clear() {
		$this->manager->getStorage()->clear(self::NS);
	}
	
	
	/**
	 * @param string $key
	 * @param string $value
	 */
	private function setValue($key, $value) {
		$data = $this->getStorage();
		$data[$key] = $value;
		$this->setData($data);
	}
	
	/**
	 * @param string $key
	 * @return string
	 * @throws Exception
	 */
	private function getValue($key) {
		$data = $this->getStorage();
		
		if (! isset($data[$key])) {
			throw new Exception('Could not retrieve value from key ' . $key);
// 					Zwas_Translate::_('Could not retrieve value from key %s'),
// 					array((string)$key)
// 			));
		}
	
		return $data[$key];
	}
	
	/**
	 * @param string $key
	 * @return string
	 * @throws Zwas_Exception
	 */
	private function hasValue($key) {
		$data = $this->getStorage();
		
		return isset($data[$key]);
	}
	
	/**
	 * @param array $data
	 */
	private function setData($data) {
		$namespace = self::NS;
		$this->manager->getStorage()->$namespace = $data;
	}
	
	/**
	 * @return array
	 */
	public function getStorage() {
		$namespace = self::NS;
		$data = isset($this->manager->getStorage()->$namespace) ? $this->manager->getStorage()->$namespace : null;
		
		if (! is_array($data)) {
			return array();
		}
		
		return $data;
	}
	
    // Function to get the client IP address
	public static function getClientIp() {

        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED'])  && $_SERVER['HTTP_X_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR'])  && $_SERVER['HTTP_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED'])  && $_SERVER['HTTP_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR'])  && $_SERVER['REMOTE_ADDR'])
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        
        return $ipaddress;
    }
}