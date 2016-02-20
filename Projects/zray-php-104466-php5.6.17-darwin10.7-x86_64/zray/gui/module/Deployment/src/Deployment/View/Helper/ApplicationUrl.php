<?php
namespace Deployment\View\Helper;

use Application\Module;

use Zend\View\Helper\AbstractHelper,
Deployment\Application,
ZendServer\Log\Log,
Deployment\Model;

class ApplicationUrl extends AbstractHelper {
	
	/**
	 * @var string
	 */
	private $defaultServer;
	
    public function __invoke($baseUrl) {
    	
    	if ($this->getDefaultServer() == '<default-server>') { // nothing to do
    		return $baseUrl;
    	} elseif (strstr($baseUrl,'<default-server>')) {  // check if the user's defaultServer contains schema or port, don't add the dublication if exists
    		$baseUrlArr = parse_url($baseUrl);
    		$defaultServerArr = parse_url($this->getDefaultServer());

    		if ($this->getHost($this->getDefaultServer())) {
    			$baseUrlArr['host'] = $this->getHost($this->getDefaultServer());
    		}
    		if (isset($defaultServerArr['scheme'])) {
    			$baseUrlArr['scheme'] = $defaultServerArr['scheme'];
    		}
    		if (isset($defaultServerArr['port'])) {
    			$baseUrlArr['port'] = $defaultServerArr['port'];
    		}
    		if (isset($defaultServerArr['path'])) {
    			$defaultServerArr['path'] = str_replace($baseUrlArr['host'], '', $defaultServerArr['path']);
    		}
    		
    		$baseUrlStr = $baseUrlArr['host'];
    		if (isset($baseUrlArr['scheme'])) {
    			$baseUrlStr = $baseUrlArr['scheme'] . '://' . $baseUrlStr;
    		}
    		if (isset($baseUrlArr['port'])) {
    			$baseUrlStr .= ':' . $baseUrlArr['port'];
    		}
    		if (isset($defaultServerArr['path'])) {
    			$baseUrlStr .= $defaultServerArr['path'] . $baseUrlArr['path'];
    		} else {
    			$baseUrlStr .= $baseUrlArr['path'];
    		}
    		return $baseUrlStr;
    	} else {
    		return $baseUrl;
    	}
    	
    	//return preg_replace('#\<default\-server\>(.+)#', "{$this->getDefaultServer()}$1", $baseUrl);
    	 
    }
    
	/**
	 * @return string $defaultServer
	 */
	public function getDefaultServer() {
		return $this->defaultServer;
	}

	/**
	 * @param string $defaultServer
	 * @return ApplicationUrl
	 */
	public function setDefaultServer($defaultServer) {
		$this->defaultServer = $defaultServer;
		return $this;
	}

	// some odd reason, parse_url returns the host (ex. example.com or 10.1.2.3) as the path when no scheme is provided in the input url
	private function getHost($address) {
		$parseUrl = parse_url(trim($address));
		if (isset($parseUrl['path'])) {
			$explodedArray = explode('/', $parseUrl['path'], 2);
		}
		return trim((isset($parseUrl['host']) && $parseUrl['host']) ? $parseUrl['host'] : array_shift($explodedArray));
	}
}