<?php

namespace WebAPI\View\Resolver;

use Zend\View\Resolver\TemplatePathStack as baseTemplatePathStack;
use WebAPI\Module;
use SplFileInfo;
use Zend\View\Exception;
use Zend\View\Renderer\RendererInterface as Renderer;

use ZendServer\FS\FS;

class TemplatePathStack extends baseTemplatePathStack {
	/**
	 * @var string
	 */
	private $webapiVersion = Module::WEBAPI_CURRENT_VERSION;
	
	
	public function resolve($name, Renderer $renderer = null) {
		$this->lastLookupFailure = false;
		
		if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
			throw new Exception\DomainException(
					'Requested scripts may not include parent directory traversal ("../", "..\\" notation)'
			);
		}
		
		if (!count($this->paths)) {
			$this->lastLookupFailure = static::FAILURE_NO_PATHS;
			return false;
		}
		
		// Ensure we have the expected file extension
		$defaultSuffix = $this->getDefaultSuffix();
		if (pathinfo($name, PATHINFO_EXTENSION) != $defaultSuffix) {;
		$name .= '.' . $defaultSuffix;
		}
		
		foreach ($this->paths as $path) {
			$filepath = $path . $name;
			$filename = basename($filepath);
			$pathonly = dirname($filepath);
			$pathonly = preg_replace('/web-api[\d]+/', 'web-api', $pathonly);//web-api12 -> web-api
			
			$filepath = FS::createPath($pathonly, $this->getWebAPIVersionDirname(), $filename);
			
			$file = new SplFileInfo($filepath);
			if ($file->isReadable()) {
				return $file->getRealPath();
			}
		}
		
		$this->lastLookupFailure = static::FAILURE_NOT_FOUND;
		return false;
	}
	
	/**
	 * @param string $webapiVersion
	 */
	public function setWebapiVersion($webapiVersion) {
		$this->webapiVersion = $webapiVersion;
	}
	/**
	 * 
	 * @return string
	 */
	private function getWebAPIVersionDirname() {
		return str_replace('.', 'x', $this->webapiVersion);
	}
}

