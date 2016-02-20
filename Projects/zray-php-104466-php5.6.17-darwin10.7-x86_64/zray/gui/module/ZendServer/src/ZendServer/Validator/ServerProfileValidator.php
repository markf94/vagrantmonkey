<?php

namespace ZendServer\Validator;

use ZendServer\Log\Log,
	Zend\Db\Adapter\Adapter;
use Zend\Validator\Db\AbstractDb;

class ServerProfileValidator extends AbstractDb {
	
	const NO_CLUSTER_PROFILE = 'noClusterProfile';
	const INVALID_ARCH = 'invalidArch';
	const INVALID_PHP = 'invalidPhp';
	const INVALID_OS = 'invalidOs';
	const INVALID_WEBSERVER = 'invalidWebserver';
	
	private $targetDbAdapter = null;
	
	private $currProfile = null;
	
	/**
	 * @var array
	 */
	protected $messageTemplates = array(
			self::NO_CLUSTER_PROFILE  => "Cannot find cluster profile to match the server: %value%",
			self::INVALID_ARCH  => "The server's architecture '%value%' does not match the cluster configuration",
			self::INVALID_OS  => "The server's operating system '%value%' does not match the cluster configuration",
			self::INVALID_PHP  => "The server's PHP version '%value%' does not match the cluster configuration",
			self::INVALID_WEBSERVER  => "The server's web server '%value%' does not match the cluster configuration",
	);
	
	public function __construct($options) {
	    $this->currProfile = array_change_key_case($options['currProfile'], CASE_LOWER);
	    
		$options['field'] = '*';
		$options['table'] = 'ZSD_NODES_PROFILE';
		parent::__construct($options);
	}
	
	public function isValid($value) {
		
	    $version = phpversion();
	    $pieces = explode(".", $version);
	    if (is_array($pieces) && count($pieces) >= 2) {
	       $version = $pieces[0] . '.' . $pieces[1];
	    }
	    
		// changing the below requires a matching change in zsd_node_profile.php
	    $currentProfile = array(
	        'os' => $this->currProfile['os'],
	        'phpversion' => $this->currProfile['phpversion'],
	        'arch' => $this->currProfile['arch'],
	        'webserver' => $this->currProfile['webserver'],
	    );
		Log::debug("Server profile: " . var_export($currentProfile, true));
		
		$select = $this->getSelect();
		$select->reset('where');
		try {
			$result = $this->getAdapter()->query($select->getSqlString($this->getAdapter()->getPlatform()), Adapter::QUERY_MODE_EXECUTE)->current();
			if (! ($result instanceof \ArrayObject)) {
				Log::debug("First node joining - not checking profile (table missing)");
				return true;
			}
			$clusterProfile = array_change_key_case($result->getArrayCopy(), CASE_LOWER);
			Log::debug("Cluster profile: " . var_export($clusterProfile, true));
		} catch (\Exception $e) {
			$this->error(self::NO_CLUSTER_PROFILE, $e->getMessage());
			return false;
		}
		
		if (!$clusterProfile) {
			$this->error(self::NO_CLUSTER_PROFILE);
			return false;
		}		
		
		if (current($clusterProfile) === false) {
			Log::debug("First node joining - not checking profile");
			return true;
		}
		
		if ($currentProfile != $clusterProfile) {
			$diff = array_diff($currentProfile, $clusterProfile);
			if (count($diff) ==  0) {
				return true;
			}
			
			Log::debug("Diff is : " . var_export($diff, true));
			if (isset($diff['os'])) {
				$this->error(self::INVALID_OS, $diff['os']);
			}
			if (isset($diff['phpversion'])) {
				$this->error(self::INVALID_PHP, $diff['phpversion']);
			}
			if (isset($diff['arch'])) {
				$this->error(self::INVALID_ARCH, $diff['arch']);
			}
			if (isset($diff['webserver'])) {
				$this->error(self::INVALID_WEBSERVER, $diff['webserver']);
			}
			
			return false;
		}
		
		return true;	
		
	}
}

?>