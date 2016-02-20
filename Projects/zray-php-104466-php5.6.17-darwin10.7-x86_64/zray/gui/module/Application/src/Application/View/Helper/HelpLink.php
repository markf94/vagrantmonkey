<?php

namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Uri\UriFactory;
use ZendServer\FS\FS;
use ZendServer\Log\Log;
class HelpLink extends AbstractHelper {
	const DOCS_VERSION = '8.5';
	const OS_SUFFIX_IBMi = 'IBMi';
	
	/**
	 * @brief exceptions for the top navigation help link button.
	 * (!) keep keys lowercase
	 * @var array
	 */
	private $exceptions = array(
		'queues_import' => 'creating_a_queue',
	);
	
    /**
     * @var string
     */
    private $requestUri;

    /**
     * @var RouteMatch
     */
    private $routeMatch;
    
	public function __invoke($helpHash = null) {
		$osSuffix = FS::isAix() ? '-'.self::OS_SUFFIX_IBMi : '';
		if (isAzureEnv()) {
		    $helpBase = 'http://files.zend.com/help/Z-Ray-Azure/Content/';
		} elseif (isZrayStandaloneEnv()) {
		    $helpBase = 'http://files.zend.com/help/Z-Ray/content/';
		} else {
			$helpBase = 'http://files.zend.com/help/Zend-Server-' . self::DOCS_VERSION . $osSuffix . '/Content/';
		}

		if (! is_null($helpHash)) {
			if (empty($helpHash)) {
				return $this->redirectUrl('', $helpBase);
			}
			return $this->redirectUrl($helpHash, $helpBase . $helpHash . '.htm');
		}
		
		$path = UriFactory::factory($this->getRequestUri())->getPath();
		if (preg_match('/^index/i', basename($path))) { // in some cases the basename of the path is starting with Index, in which case we will ignore that part
			$path = dirname($path);
		}

		$firstName = basename(dirname($path));
		$baseName = basename($path);
		if ($firstName == 'ZendServer') {
			$pageName = $baseName;
		} else {
			$pageName = $firstName.'_'.$baseName;
		}
		
		if ($pageName == '_ZendServer') {
			$pageName = '';
		}

		$routeMatch = $this->getRouteMatch();
		if ($routeMatch instanceof \Zend\Mvc\Router\Http\RouteMatch && (strpos($routeMatch->getMatchedRouteName(), 'home') !== false)) {
			if (strtolower($this->getRouteMatch()->getParam('controller')) == 'index') {
				$pageName = 'dashboard';
			} else {
				$pageName = $this->getRouteMatch()->getParam('controller');
			}
		}

        $helpPageName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $pageName));
		if (in_array(strtolower($helpPageName), array_keys($this->exceptions))) {
			$helpPageName = $this->exceptions[$helpPageName];
		}
        if (isAzureEnv()) {
            $helpPageName = $this->getAzureLink($helpPageName);
        }
        if (isZrayStandaloneEnv()) {
            $helpPageName = $this->getZrayStandaloneLink($helpPageName);
        }
        $helpLink = $this->redirectUrl($helpPageName, $helpBase . $helpPageName . '.htm');

		return $helpLink;
	}
	
    /**
     * @param \Zend\Mvc\Router\Http\RouteMatch $routeMatch
     */
    public function setRouteMatch($routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    /**
     * @return \Zend\Mvc\Router\Http\RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        if (is_null($this->requestUri)) {
            $this->requestUri = $_SERVER["REQUEST_URI"];
        }
        return $this->requestUri;
    }

    /**
     * @param string $requestUri
     */
    public function setRequestUri($requestUri)
    {
        $this->requestUri = $requestUri;
    }
    
    private function redirectUrl($action = '', $url = '') {
    	return 'http://updates.zend.com/redir/index.php?category=In-Product-Link&label=&action=' . $action . '&url=' . urlencode($url);
    }
    
    private function getAzureLink($helpPageName) {
        $map = array(
            'guide_page'        => 'home',
            'zray_live'         => 'z-ray-live',
            'z-ray_mode'        => 'enabling_disabling_z-ray',
            'z-ray_tokens'      => 'enabling_disabling_z-ray',
            'z-ray_advanced'    => 'configuring_z-ray',
            'z-ray_concept'     => '',
        );
        
        if (isset($map[$helpPageName])) {
            return $map[$helpPageName];
        }
        return $helpPageName;
    }
    
    private function getZrayStandaloneLink($helpPageName) {
        $map = array(
            'guide_page'        => 'home',
            'zray_live'         => 'z-ray-live',
            'z-ray_access_mode'	=> 'enabling_disabling_z-ray',
            'zray_history'		=> 'history',
            'z-ray_advanced'	=> 'configuring_z-ray',
        );
        
        if (isset($map[$helpPageName])) {
            return $map[$helpPageName];
        }
        return $helpPageName;
    }
}

