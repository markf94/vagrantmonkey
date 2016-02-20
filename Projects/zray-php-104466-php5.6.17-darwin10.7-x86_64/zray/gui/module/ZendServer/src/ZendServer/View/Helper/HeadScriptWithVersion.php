<?php

namespace ZendServer\View\Helper;

use Zend\Uri\UriFactory;
use Zend\Uri\Exception\InvalidArgumentException;
use ZendServer\Log\Log;
use Application\ConfigAwareInterface;
use Zend\View\Helper\HeadScript;

class HeadScriptWithVersion extends HeadScript implements ConfigAwareInterface {
	/**
	 * @var string
	 */
	private $version;
	
	public function __call($method, $args) {
		
		/// we need this regular expression here to avoid dropping into our code
		/// while we are executing in the constructor
		if (preg_match('/^(?P<action>set|(ap|pre)pend|offsetSet)(?P<mode>File|Script)$/', $method, $matches) && isset($args[0])) {
			try {
				$hrefUri = UriFactory::factory($args[0]);
				$query = $hrefUri->getQueryAsArray();
				$query['zsv'] = $this->getVersion();
				$hrefUri->setQuery($query);
				$args[0] = $hrefUri->toString();
			} catch (InvalidArgumentException $ex) {
				Log::debug($ex->getMessage());
			}
		}
		
		return parent::__call($method, $args);
	}
	
	/**
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * @param string $version
	 */
	public function setVersion($version) {
		$this->version = $version;
	}
	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::getAwareNamespace()
	 */
	public function getAwareNamespace() {
		return array('package');
	}

	/* (non-PHPdoc)
	 * @see \Application\ConfigAwareInterface::setConfig()
	 */
	public function setConfig($config) {
		$this->version = '';
		if (isset($config->version)) {
			$this->version = $config->version;
		}
		if (isset($config->build)) {
			$this->version .= $config->build;
		}
	}


}

