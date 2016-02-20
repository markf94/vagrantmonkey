<?php

namespace ZendServer\View\Helper;

use Zend\View\Helper\HeadLink;
use Zend\Uri\UriFactory;
use Zend\Uri\Exception\InvalidArgumentException;
use ZendServer\Log\Log;
use Application\ConfigAwareInterface;

class HeadLinkWithVersion extends HeadLink implements ConfigAwareInterface {
	/**
	 * @var string
	 */
	private $version;
	
	/* (non-PHPdoc)
	 * @see \Zend\View\Helper\HeadLink::createDataStylesheet()
	 */
	public function createDataStylesheet(array $args)
	{
		if (isset($args[0])) {
			try {
				$hrefUri = UriFactory::factory($args[0]);
				$query = $hrefUri->getQueryAsArray();
				$query['v'] = $this->getVersion();
				$hrefUri->setQuery($query);
				$args[0] = $hrefUri->toString();
			} catch (InvalidArgumentException $ex) {
				Log::debug($ex->getMessage());
			}
		}
		return parent::createDataStylesheet($args);
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

