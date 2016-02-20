<?php

namespace ZendServer\Mvc\View\Http;

use Application\Module;

use Zend\Mvc\View\Http\ViewManager as baseViewManager;
use ZendServer\View\Renderer\PhpRenderer;

class ViewManager extends baseViewManager {
	
	/**
	 * Instantiates and configures the renderer
	 *
	 * @return ViewPhpRenderer
	 */
	public function getRenderer()
	{
	    if ($this->renderer) {
	        return $this->renderer;
	    }
	    
	    $oldRenderer = parent::getRenderer();
	    /// convert and override
	    $this->renderer = PhpRenderer::fromRenderer($oldRenderer);
	
	    $this->services->setAllowOverride(true);
	    $this->services->setService('ViewRenderer', $this->renderer);
	    $this->services->setAllowOverride(false);
	    
	    return $this->renderer;
	}
	
	/*
	 * (non-PHPdoc)
	 * @see \Zend\Mvc\View\Http\ViewManager::getExceptionStrategy()
	 */
	public function getExceptionStrategy()
	{
		if ($this->exceptionStrategy) {
			return $this->exceptionStrategy;
		}
	
		$this->exceptionStrategy = new ExceptionStrategy();
	
		$displayExceptions = Module::config('debugMode', 'debugModeEnabled');
		$exceptionTemplate = 'error';
	
		if (isset($this->config['display_exceptions'])) {
			$displayExceptions = $this->config['display_exceptions'];
		}
		if (isset($this->config['exception_template'])) {
			$exceptionTemplate = $this->config['exception_template'];
		}
		if (isset($this->config['permissions_template'])) {
			$permissionsTemplate = $this->config['permissions_template'];
		}
	
		$this->exceptionStrategy->setDisplayExceptions($displayExceptions);
		$this->exceptionStrategy->setExceptionTemplate($exceptionTemplate);
		$this->exceptionStrategy->setPermissionsTemplate($permissionsTemplate);
	
		$this->services->setService('ExceptionStrategy', $this->exceptionStrategy);
		$this->services->setAlias('Zend\Mvc\View\ExceptionStrategy', 'ExceptionStrategy');
		$this->services->setAlias('Zend\Mvc\View\Http\ExceptionStrategy', 'ExceptionStrategy');
	
		return $this->exceptionStrategy;
	}
}

