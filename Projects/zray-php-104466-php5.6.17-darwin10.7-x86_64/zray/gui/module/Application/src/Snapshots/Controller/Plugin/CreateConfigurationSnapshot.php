<?php

namespace Snapshots\Controller\Plugin;

use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

use ZendServer\Log\Log;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class CreateConfigurationSnapshot extends AbstractPlugin
{
	/**
	 * @var \Snapshots\Db\Mapper
	 */
	private $snapshotsMapper;
	/**
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	private $serviceLocator;
	
	/**
	 * 
	 * @param string $name
	 * @param string $exportedConfig
	 */
	public function __invoke($name=\Snapshots\Db\Mapper::SNAPSHOT_SYSTEM_BOOT) {
		$request = $this->getController()->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$request->setMethod(\Zend\Http\PhpEnvironment\Request::METHOD_GET);

		$headers = clone $this->getController()->getResponse()->getHeaders();// backup headers for overriding later
		$exportConfigurations = $this->getExportConfigurationsString($name);
		$exportConfigurations->getHeaders()->clearHeaders();
		$exportConfigurations->setHeaders($headers);		
		$exportConfigurations->setContent('');// clear response content, it has the export's result
	
		$request->setMethod(\Zend\Http\PhpEnvironment\Request::METHOD_POST);
	}

	/**
	 * @return \Snapshots\Db\Mapper $snapshotsMapper
	 */
	public function getSnapshotsMapper() {
		return $this->snapshotsMapper;
	}

	/**
	 * @param \Snapshots\Db\Mapper $snapshotsMapper
	 * @return CreateConfigurationSnapshot
	 */
	public function setSnapshotsMapper($snapshotsMapper) {
		$this->snapshotsMapper = $snapshotsMapper;
		return $this;
	}

	/**
	 * @return \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return CreateConfigurationSnapshot
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

	/**
	 * @return string
	 */
	private function getExportConfigurationsString($name) {
		$resolver = $this->getServiceLocator()->get('\Zend\View\Resolver\TemplatePathStack'); /* @var $resolver \Zend\View\Resolver\TemplatePathStack */
		$defaultSuffix = $resolver->getDefaultSuffix();
			
		$resolver->setDefaultSuffix('pxml.phtml');
		$paths = $resolver->getPaths();
			
		$renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer'); /* @var $renderer \Zend\View\Renderer\PhpRenderer */
		$renderer->setResolver($resolver);

		$this->getController()->getRequest()->setQuery(new \Zend\Stdlib\Parameters(array('snapshotName'=>$name) + $this->getController()->getRequest()->getQuery()->toArray()));
		$exportView = $this->getController()->forward()->dispatch('ConfigurationWebApi-1_3', array('action' => 'configurationExport')); /* @var $exportView \Zend\Http\PhpEnvironment\Response */
	
		// Get back the default suffix and paths to render WebAPI json/xml template
		$resolver->setDefaultSuffix($defaultSuffix);
		$resolver->setPaths($paths);
		$renderer->setResolver($resolver);
	
		return $exportView;
	}
}