<?php

namespace Application\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Messages\View\Helper\ServerErrorMessageXml;
use Messages\MessageContainer;
use Messages\Db\MessageMapper;
use Zend\Json\Json;
use Zend\View\Renderer\PhpRenderer;
use ZendServer\View\Helper\DaemonName;

require_once 'tests/bootstrap.php';

class ServerErrorMessageXmlTest extends TestCase
{
	/**
	 * @var ServerErrorMessageXml
	 */
	private $helper;
	
	public function testServerErrorMessageXmlExtensionSeverities() {
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertContains('<info><![CDATA[The extension \'extension\' will be enabled once restart is performed]]></info>', $result);

		// other severities have no meaning
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertContains('<error><![CDATA[Unknown Error]]></error>', $result);
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertContains('<error><![CDATA[Unknown Error]]></error>', $result);
	}
	
	public function testServerErrorMessageXmlExtension() {
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertContains('<info><![CDATA[The extension \'extension\' will be enabled once restart is performed]]></info>', $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_DISABLED))));
		self::assertContains('<info><![CDATA[The extension \'extension\' will be disabled once restart is performed]]></info>', $result);
	}

	public function testServerErrorMessageXmlDirectivesSeverities() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED, 'DETAILS' => Json::encode(array('directive', 'previous', 'current'))))));
		self::assertContains('<info><![CDATA[The directive \'directive\' value has been changed from \'previous\' to \'current\']]></info>', $result);

		// other severities have no meaning
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED, 'DETAILS' => Json::encode(array('directive', 'previous', 'current'))))));
		self::assertContains('<error><![CDATA[Unknown Error]]></error>', $result);
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED, 'DETAILS' => Json::encode(array('directive', 'previous', 'current'))))));
		self::assertContains('<error><![CDATA[Unknown Error]]></error>', $result);
	}

	public function testServerErrorMessageXmlDaemonsSeverities() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_DAEMON,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED))));
		self::assertContains('<info><![CDATA[Job Queue Daemon requires a restart]]></info>', $result);

		// other severities have no meaning
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::CONTEXT_DAEMON, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED))));
		self::assertContains('<error><![CDATA[Unknown Error]]></error>', $result);
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::CONTEXT_DAEMON, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED))));
		self::assertContains('<error><![CDATA[Unknown Error]]></error>', $result);
	}

	public function testServerErrorMessageXmlMismatch() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_MISSMATCH, 'DETAILS' => Json::encode(array('directive', 'blueprintValue', 'actualValue'))))));
		self::assertContains("<warning><![CDATA[The directive 'directive' is mismatched: expected 'blueprintValue', actual 'actualValue']]></warning>", $result);
	}

	public function testServerErrorMessageXmlNotLicensed() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,
						'TYPE' => MessageMapper::TYPE_NOT_LICENSED, 'DETAILS' => Json::encode(array('extension', 'blueprintValue', 'actualValue'))))));
		self::assertContains("<warning><![CDATA[The extension 'extension' is not licensed]]></warning>", $result);
	}

	public function testServerErrorMessageXmlNotLoaded() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,
						'TYPE' => MessageMapper::TYPE_NOT_LOADED, 'DETAILS' => Json::encode(array('extension', 'blueprintValue', 'actualValue'))))));
		self::assertContains("<warning><![CDATA[The extension 'extension' is not loaded]]></warning>", $result);
	}

	public function testServerErrorMessageXmlMissing() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_MISSING))));
		self::assertContains("<error><![CDATA[The daemon 'jqd' is missing]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,	'TYPE' => MessageMapper::TYPE_MISSING))));
		self::assertContains("<error><![CDATA[The extension 'jqd' is missing]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,	'TYPE' => MessageMapper::TYPE_MISSING))));
		self::assertContains("<error><![CDATA[The directive 'jqd' is missing]]></error>", $result);
	}

	public function testServerErrorMessageXmlOffline() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_OFFLINE))));
		self::assertContains("<error><![CDATA[The daemon 'jqd' is offline]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,	'TYPE' => MessageMapper::TYPE_OFFLINE))));
		self::assertContains("<error><![CDATA[The extension 'jqd' is offline]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,	'TYPE' => MessageMapper::TYPE_OFFLINE))));
		self::assertContains("<error><![CDATA[The directive 'jqd' is offline]]></error>", $result);
	}

	public function testServerErrorMessageXmlNotInstalled() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_NOT_INSTALLED))));
		self::assertContains("<error><![CDATA[The daemon 'jqd' seems to be missing]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,	'TYPE' => MessageMapper::TYPE_NOT_INSTALLED))));
		self::assertContains("<error><![CDATA[The extension 'jqd' seems to be missing]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,	'TYPE' => MessageMapper::TYPE_NOT_INSTALLED))));
		self::assertContains("<error><![CDATA[The directive 'jqd' seems to be missing]]></error>", $result);
	}

	public function testServerErrorMessageXmlWebServerNotResponding() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_WEBSERVER_NOT_RESPONDING))));
		self::assertContains("<error><![CDATA[Webserver is not responding]]></error>", $result);
	}

	public function testServerErrorMessageXmlScdStandby() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_SCD_STDBY_MODE))));
		self::assertContains("<error><![CDATA[Session Clustering daemon is inactive]]></error>", $result);
	}

	public function testServerErrorMessageVhostModified() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_MODIFIED))));
		self::assertContains("<error><![CDATA[Web server configuration files have been modified on this server]]></error>", $result);
	}

	public function testServerErrorMessageVhostAdded() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_ADDED))));
		self::assertContains("<error><![CDATA[Failed to create the virtual host 'jqd' on this server]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_ADDED,
						'DETAILS' => Json::encode(array('serverId'))))));
		self::assertContains("<error><![CDATA[Failed to create the virtual host 'jqd' on this server: serverId]]></error>", $result);
	}

	public function testServerErrorMessageVhostRedeployed() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_REDEPLOYED))));
		self::assertContains("<error><![CDATA[Failed to redeploy the virtual host 'jqd' on this server]]></error>", $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_REDEPLOYED,
						'DETAILS' => Json::encode(array('serverId'))))));
		self::assertContains("<error><![CDATA[Failed to redeploy the virtual host 'jqd' on this server: serverId]]></error>", $result);
	}
	
	protected function setUp() {
		parent::setUp();
		$this->helper = new ServerErrorMessageXml();
		$renderer = new PhpRenderer();
		$renderer->getHelperPluginManager()->setService('daemonName', new DaemonName());
		$this->helper->setView($renderer);
	}
}

