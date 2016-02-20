<?php

namespace StudioIntegration\Controller;

use ZendServer\Mvc\Controller\ActionController;
use Zend\Stdlib\Parameters;
use Application\Module;

class IndexController extends ActionController
{
	public function oldIndexAction() {
		$mapper = $this->getLocator()->get('StudioIntegration\Mapper');
		$config = $mapper->getConfiguration();
		
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives');
		
		$studioConfigForm = $this->getLocator()->get('StudioIntegration\Form\Configuration');
		$studioConfigForm->setData($studioConfigForm->getHydrator()->extract($config));
		$studioConfigForm->setObject($config);

		$extensionsMapper = $this->getLocator()->get('Configuration\MapperExtensions');
		$zendDebuggerLoaded = $extensionsMapper->isExtensionLoaded('Zend Debugger');
		$xdebugLoaded = $extensionsMapper->isExtensionLoaded('xdebug');
		
		// Choose Debugger Form
		$chooseDebuggerForm = $this->getLocator()->get('StudioIntegration\Form\ChooseDebugger');
		$chooseDebuggerForm->setData(array(
			'Debugger' => $zendDebuggerLoaded ? 'Zend Debugger' : ($xdebugLoaded ? 'xdebug' : 'none')
		));
		
		// Xdebug settings form
		$xdebugForm = $this->getLocator()->get('StudioIntegration\Form\Xdebug');
		$xdebugSettings = $directivesMapper->getDirectivesValues(array(
			'xdebug.remote_enable', 'xdebug.remote_host', 'xdebug.remote_port', 'xdebug.remote_handler', 'xdebug.ide_key'
		));
		$xdebugSettingsFixed = array();
		array_walk($xdebugSettings, function($elem, $key) use (&$xdebugSettingsFixed) {
			$xdebugSettingsFixed[str_replace('xdebug.', '', $key)] = $elem;
		});
		$xdebugSettings = $xdebugSettingsFixed;
		$xdebugForm->setData($xdebugSettings);
		
		// IDEIntegration form
		$ideIntegrationForm = $this->getLocator()->get('StudioIntegration\Form\IdeIntegration');
		$ideIntegrationForm->setData($studioConfigForm->getHydrator()->extract($config));
		
		
		// Zend Debugger Hosts List
		$studioHostsLists = $this->getLocator()->get('StudioIntegration\Form\HostsList');
		$debuggerAllowed = $directivesMapper->getDirectiveValue('zend_debugger.allow_hosts', true);
		$debuggerDenied = $directivesMapper->getDirectiveValue('zend_debugger.deny_hosts', true);
		$studioHostsLists->setData(array(
			'studioAllowedHostsList' => $debuggerAllowed,
			'studioDeniedHostsList' => $debuggerDenied
		));
		
		// set the browser ip as default studio host and also propose to add it to allowed host
		$browserHost = $this->getBrowserRemoterAddress();
		if ($browserHost) {
			$studioConfigForm->get('studioHost')->setOptions(array('default' => $browserHost));
			$studioHostsLists->get('studioAllowedHostsList')->setOptions(array('default' => $browserHost));
		}
		
		return array(
			'pageTitle' => 'IDE Intergration',
			'pageTitleDesc' => '',  /* Daniel */
			'studioConfig' => $config,
			'studioHostsLists' => $studioHostsLists,
			'zendDebuggerLoaded' => $zendDebuggerLoaded,
			'xdebugLoaded' => $xdebugLoaded,
			'studioConfigForm' => $studioConfigForm,
			'chooseDebuggerForm' => $chooseDebuggerForm,
			'xdebugForm' => $xdebugForm,
			'ideIntegrationForm' => $ideIntegrationForm,
		);
	}
	
	public function indexAction() {
		
		/* @var $directivesMapper \Configuration\MapperDirectives */
		$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); 
		
		/* @var $directivesMapper \Configuration\MapperExtensions */
		$extensionsMapper = $this->getLocator()->get('Configuration\MapperExtensions');
		
		// STEPS //
		
		// (1) get current active debugger
		$zendDebuggerLoaded = $extensionsMapper->isExtensionLoaded('Zend Debugger');
		$xdebugLoaded = $extensionsMapper->isExtensionLoaded('xdebug');		
		$activeDebugger = $zendDebuggerLoaded ? 'Zend Debugger' : ($xdebugLoaded ? 'xdebug' : 'none');
		
		// (2) get allowed/denied hosts
		$allowedHosts = $directivesMapper->getDirectiveValue('zend_debugger.allow_hosts', true);
		$deniedHosts = $directivesMapper->getDirectiveValue('zend_debugger.deny_hosts', true);
		
		// (3) get IDE client settings (autodetect IDE, ide host, port, ssl)
		// (4) get IDE integration settings (break on first line, use remote)
		/* @var $directivesMapper \StudioIntegration\Configuration */
		$ideSettings = $this->getLocator()->get('StudioIntegration\Mapper')->getConfiguration();
		
		// (5) get Xdebug settings
		$xdebugSettings = $directivesMapper->getDirectivesValues(array(
			'xdebug.remote_enable', 
			'xdebug.remote_host', 
			'xdebug.remote_port', 
			'xdebug.remote_handler', 
			'xdebug.idekey'
		));
		
		return array(
			'pageTitle' => 'Debugger',
			'debuggerSettings' => $this->getServiceLocator()->get('StudioIntegration\Form\DebuggerSettings'),
			'formData' => array(
				'ActiveDebugger' 			=> $activeDebugger,
				
				// security
				'studioAllowedHostsList' 	=> $allowedHosts,
				'studioDeniedHostsList' 	=> $deniedHosts,
				
				// ide settings
				'studioAutoDetection' 		=> $ideSettings->getAutoDetect(),
				'studioHost' 				=> empty(trim($ideSettings->getHost())) ? $this->getBrowserRemoterAddress() : $ideSettings->getHost(),
				'studioAutoDetectionEnabled'=> $ideSettings->getBrowserDetect(),
				'studioPort'				=> $ideSettings->getPort(),
				'studioUseSsl' 				=> $ideSettings->getSsl(),
				
				// integration
				'studioBreakOnFirstLine' 	=> $ideSettings->getBrakeOnFirstLine(), // $integrationSettings['zend_gui.studioBreakOnFirstLine'],
				'studioUseRemote' 			=> $ideSettings->getUseRemote(), // $integrationSettings['zend_gui.studioUseRemote'],
				
				// xdebug
				'remote_enable' 			=> !isset($xdebugSettings['xdebug.remote_enable']) || trim($xdebugSettings['xdebug.remote_enable']) == '' ? '1' : 
												(isset($xdebugSettings['xdebug.remote_enable']) ? (boolean)$xdebugSettings['xdebug.remote_enable'] : '1'),
				'remote_handler' 			=> isset($xdebugSettings['xdebug.remote_handler']) && !empty($xdebugSettings['xdebug.remote_handler']) ? $xdebugSettings['xdebug.remote_handler'] : 'dbgp',
				'remote_host' 				=> isset($xdebugSettings['xdebug.remote_host']) && !empty($xdebugSettings['xdebug.remote_host']) ? $xdebugSettings['xdebug.remote_host'] : '127.0.0.1',
				'remote_port' 				=> isset($xdebugSettings['xdebug.remote_port']) && !empty($xdebugSettings['xdebug.remote_port']) ? $xdebugSettings['xdebug.remote_port'] : 9000,
				'idekey' 					=> isset($xdebugSettings['xdebug.idekey']) && !empty($xdebugSettings['xdebug.idekey']) ? $xdebugSettings['xdebug.idekey'] : '',
			),
		);
	}
	
	public function exportIssueByEventsGroupAction() {
		$request = $this->getRequest(); /* @var $request \Zend\Http\PhpEnvironment\Request */
		$request->setQuery(new Parameters($request->getQuery()->toArray()));
		$this->forward()->dispatch('StudioWebApi-1_3', array('action' => 'monitorExportIssueByEventsGroup'));
		$this->getEvent()->setParam('do-not-compress', true);
		return $this->getResponse();
	}
	
	/**
	 * @return string
	 */
	private function getBrowserRemoterAddress() {
		$browserRemoteAddress = $_SERVER['REMOTE_ADDR'];
		// Special case of IPv6 local loopback
		if ('::1' == $browserRemoteAddress) {
			$browserRemoteAddress = '127.0.0.1';
		}
		// Special case of IPv6 which holds an IPv4 address (e.g. ::ffff:10.1.1.1) bug #30319
		if (0 === strpos($browserRemoteAddress, '::ffff:')) {
			$browserRemoteAddress = substr($browserRemoteAddress, 7);
		}
		return $browserRemoteAddress;
	}
}
