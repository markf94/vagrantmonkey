<?php

namespace DevBar;

use DevBar\ModuleManager\Feature\DevBarProducerProviderInterface;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Loader\StandardAutoloader;
use Magento\Producer\Magento;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

abstract class ZRayModule implements DevBarProducerProviderInterface, AutoloaderProviderInterface, ConfigProviderInterface {
    
    const PLUGIN_CURRENT_VERSION = '1.0.3';
    
    private $parentDir = null;
    private $parentNS = null;
    private $extensionName = null;
    
	public function getConfig() {
		$config = $this->config();
		$panels = isset($config['panels']) ? $config['panels'] : array();
		
		$this->extensionName = (isset($config['extension']['name'])) ? $config['extension']['name'] : $this->parentNS;
		$this->extensionName = strtolower($this->extensionName);
		
		$templateMap = array();
		foreach ($panels as $panelName => $panelParams) {
			$templateMap[$this->extensionName . '/' . $panelName] = $this->parentDir . DIRECTORY_SEPARATOR . 'Panels' . DIRECTORY_SEPARATOR . $panelName . '.phtml';
		}
		
		return array(
				'view_manager' => array(
						'template_map' => $templateMap,
				)
		);
	}
	
	/* (non-PHPdoc)
	 * @see \Zend\ModuleManager\Feature\AutoloaderProviderInterface::getAutoloaderConfig()
	*/
	public function getAutoloaderConfig() {
		$object = new \ReflectionObject($this);
		$this->parentDir = dirname($object->getFileName());
		$this->parentNS = $object->getNamespaceName();
		
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						StandardAutoloader::LOAD_NS => array(
							$this->parentNS => $this->parentDir,
						),
				),
		);
	}
	
	/* (non-PHPdoc)
	 * @see \DevBar\ModuleManager\Feature\DevBarProducerProviderInterface::getDevBarProducers()
	*/
	public function getDevBarProducers(EventInterface $e) {
	    if (isAzureEnv()) {    	    
    	    if (! $this->allowedByLicense()) {
    	        return array();
    	    }
	    }
	    
		// fetch renderer
		$renderer = $e->getApplication()->getServiceManager()->get('Zend\View\Renderer\PhpRenderer');
		
		$config = $this->config();
		$panels = isset($config['panels']) ? $config['panels'] : array();
		if (isset($config['defaultPanels'])) {
			foreach ($config['defaultPanels'] as $panelName => $displayPanel) {
				if (! $displayPanel) {
					$panels[$panelName] = array('display' => false);
				}
			}
		}
		
		$list = array();
		foreach ($panels as $panelName => $panelParams) {
			if ($panelParams instanceof \DevBar\Listener\AbstractDevBarProducer) {
				$list[] = $panelParams;
			} else {
				$list[] = $this->getProducer($panelName, $panelParams, $config, $renderer);
			}
		}
			
		return $list;
	}
	
	public function config() {
		return array();
	}
	
	private function getProducer($name, $params, $globalParams, $renderer) {
	    $globalParams['extensionName'] = (isset($globalParams['extension']['name'])) ? $globalParams['extension']['name'] : $this->parentNS;
		
		if (isset($params['logo']) && ! empty($params['logo'])) {				
			$params['logo'] = $this->getLogo($params['logo']);
		}
		
		if (isset($params['resources']) && count($params['resources']) > 0) {
		    $params['resources'] = $this->getResources($params['resources']);
		}
		
		return new \DevBar\Producer\External($name, $params, $globalParams, $renderer);
	}
	
	private function getLogo($logoPath) {
	    $fileContent = $this->getExtensionFile($logoPath);
	    if (is_null($fileContent)) {
	        return $fileContent;
	    }
	    
		return base64_encode($fileContent);
	}
	
	private function getResources($resources) {
	    $contents = array();
	    foreach ($resources as $key => $resource) {
	        $contents[$key] = $this->getExtensionFile($resource);
	    }
	    
	    return $contents;
	}
	
	private function getExtensionFile($filepath) {
	    $filepath = realpath($this->parentDir . DIRECTORY_SEPARATOR . $filepath);
	    $pos = strpos(strtolower($filepath), strtolower($this->parentDir));
	    if ($pos === false || $pos > 0) {
	        return null;
	    }
	    
	    if (! file_exists($filepath)) {
	        return null;
	    }
	    
	    return file_get_contents($filepath);
	}
	
	private function allowedByLicense() {
	    $azureLicense = getAzureLicense();
	    if (is_null($azureLicense)) {
	        return false;
	    }
	    
	    if ($azureLicense == 'basic' || $azureLicense == 'free') {
	        $builtInExtensions = array('apigility', 'composer', 'drupal', 'magento', 'samples', 'symfony', 'wordpress', 'xmltoolkit', 'zf1', 'zf2', 'laravel');
	        return in_array(strtolower($this->extensionName), $builtInExtensions);
	    } elseif ($azureLicense == 'standard') {
	        return true;
	    }
	    
	    return false;
	}
}