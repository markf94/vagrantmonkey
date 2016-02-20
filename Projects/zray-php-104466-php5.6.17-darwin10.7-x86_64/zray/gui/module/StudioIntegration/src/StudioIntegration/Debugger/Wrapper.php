<?php

namespace StudioIntegration\Debugger;

use \ZendServer\Log\Log;
use \Application\Module;
use Zend\Uri\UriFactory;
use Zend\Uri\Uri;

class Wrapper {
	
		
	public function debugModeStart($options, $filters) {
		
		$str = "";
		foreach ($options as $key => $value) {
			if ($str) {
				$str .= "&";
			}
			$str .= $key . '=' . $value;
		}
		
		$uri = new Uri();
		$uri->setHost($_SERVER['HTTP_HOST']);
		$uri->setScheme((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http');
		$uri->setPath($_SERVER['REQUEST_URI']);
		
		$str .= '&orig_url='.urlencode($uri->toString());
		
		Log::debug("calling debugger_start_debug_mode with $str and " . implode(",", $filters));
		
		if (!function_exists('debugger_start_debug_mode')) {
			throw new \Exception('debugger_start_debug_mode function does not exist');
		}
		
		$defaultPort = Module::config('installation', 'defaultPort');
		$securedPort = Module::config('installation', 'securedPort');
		$enginePort = Module::config('installation', 'enginePort');
		return \debugger_start_debug_mode($str, $filters, array("http://*:{$defaultPort}","http://*:{$securedPort}","https://*:{$defaultPort}","https://*:{$securedPort}","http://127.0.0.1:{$enginePort}"));
	}
	
	
	public function debugModeStop() {
		
		Log::debug('calling debugger_stop_debug_mode');
		
		if (!function_exists('debugger_stop_debug_mode')) {
			throw new \Exception('debugger_stop_debug_mode function does not exist');
		}
		
		if ($this->isDebugModeEnabled()) {
			return \debugger_stop_debug_mode();
		} else {
			return true;
		}
	}
	
	public function isDebugModeEnabled() {
		
		Log::debug('calling debugger_is_debug_mode_enabled');
		
		if (!function_exists('debugger_is_debug_mode_enabled')) {
			throw new \Exception('debugger_is_debug_mode_enabled function does not exist');
		}
		
		return \debugger_is_debug_mode_enabled();
		
		
	}
}

?>
