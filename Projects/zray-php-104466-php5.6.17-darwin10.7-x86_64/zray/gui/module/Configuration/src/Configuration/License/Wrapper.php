<?php

namespace Configuration\License;
use ZendServer\Exception as ZSException;
use ZendServer\Log\Log;

class Wrapper {

	public function getSerialNumberInfo($serialNumber, $userName) {
		$method = 'zem_serial_number_info';
	
		try {
			$this->validateMethod($method);
			$licenseInfo = $method($serialNumber, $userName);
			if (!is_array($licenseInfo)) {
				throw new ZSException('unexpected response received');
			}
		} catch (\Exception $e) {
			Log::err("method {$method} invocation failed with the following error: " . $e->getMessage());
			throw new ZSException("method {$method} invocation failed with the following error: " . $e->getMessage());
		}
	
		return new License($licenseInfo);
	}

	private function validateMethod($method) {
		if (!function_exists($method)) {
			throw new ZSException("method does not exist - 'zend utils' not loaded?");
		}
	}
}

