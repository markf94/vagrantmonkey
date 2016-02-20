<?php

namespace Application\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Messages\MessageContainer;
use Messages\Db\MessageMapper;
use Zend\Json\Json;
use Messages\View\Helper\ServerErrorMessageJson;
use Zend\View\Renderer\PhpRenderer;
use ZendServer\View\Helper\DaemonName;

require_once 'tests/bootstrap.php';

class ServerErrorMessageJsonTest extends TestCase
{
	/**
	 * @var ServerErrorMessageJson
	 */
	private $helper;
	
	public function testServerErrorMessageXmlExtensionSeverities() {
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertEquals(array(array('Info' => htmlspecialchars("The extension 'extension' will be enabled once restart is performed", ENT_QUOTES))), $result);

		// other severities have no meaning
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertEquals(array(array('Error' => 'Unknown Error')), $result);
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertEquals(array(array('Error' => 'Unknown Error')), $result);
	}
	
	public function testServerErrorMessageXmlExtension() {
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_ENABLED))));
		self::assertEquals(array(array('Info' => htmlspecialchars("The extension 'extension' will be enabled once restart is performed", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION, 'TYPE' => MessageMapper::TYPE_EXTENSION_DISABLED))));
		self::assertEquals(array(array('Info' => htmlspecialchars("The extension 'extension' will be disabled once restart is performed", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlDirectivesSeverities() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED, 'DETAILS' => Json::encode(array('directive', 'previous', 'current'))))));
		self::assertEquals(array(array('Info' => htmlspecialchars("The directive 'directive' value has been changed from 'previous' to 'current'", ENT_QUOTES))), $result);

		// other severities have no meaning
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED, 'DETAILS' => Json::encode(array('directive', 'previous', 'current'))))));
		self::assertEquals(array(array('Error' => 'Unknown Error')), $result);
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED, 'DETAILS' => Json::encode(array('directive', 'previous', 'current'))))));
		self::assertEquals(array(array('Error' => 'Unknown Error')), $result);
	}

	public function testServerErrorMessageXmlDaemonsSeverities() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_INFO, 'CONTEXT' => MessageMapper::CONTEXT_DAEMON,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED))));
		self::assertEquals(array(array('Info' => htmlspecialchars("Job Queue Daemon requires a restart", ENT_QUOTES))), $result);

		// other severities have no meaning
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::CONTEXT_DAEMON, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED))));
		self::assertEquals(array(array('Error' => 'Unknown Error')), $result);
		$result = $this->helper->__invoke(array(new MessageContainer(array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::CONTEXT_DAEMON, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_DIRECTIVE_MODIFIED))));
		self::assertEquals(array(array('Error' => 'Unknown Error')), $result);
	}

	public function testServerErrorMessageXmlMismatch() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'directive', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,
						'TYPE' => MessageMapper::TYPE_MISSMATCH, 'DETAILS' => Json::encode(array('directive', 'blueprintValue', 'actualValue'))))));
		self::assertEquals(array(array('Warning' => htmlspecialchars("The directive 'directive' is mismatched: expected 'blueprintValue', actual 'actualValue'", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlNotLicensed() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,
						'TYPE' => MessageMapper::TYPE_NOT_LICENSED, 'DETAILS' => Json::encode(array('extension', 'blueprintValue', 'actualValue'))))));
		self::assertEquals(array(array('Warning' => htmlspecialchars("The extension 'extension' is not licensed", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlNotLoaded() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'extension', 'MSG_SEVERITY' => MessageMapper::SEVERITY_WARNING, 'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,
						'TYPE' => MessageMapper::TYPE_NOT_LOADED, 'DETAILS' => Json::encode(array('extension', 'blueprintValue', 'actualValue'))))));
		self::assertEquals(array(array('Warning' => htmlspecialchars("The extension 'extension' is not loaded", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlMissing() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_MISSING))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The daemon 'jqd' is missing", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,	'TYPE' => MessageMapper::TYPE_MISSING))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The extension 'jqd' is missing", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,	'TYPE' => MessageMapper::TYPE_MISSING))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The directive 'jqd' is missing", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlOffline() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_OFFLINE))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The daemon 'jqd' is offline", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,	'TYPE' => MessageMapper::TYPE_OFFLINE))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The extension 'jqd' is offline", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,	'TYPE' => MessageMapper::TYPE_OFFLINE))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The directive 'jqd' is offline", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlNotInstalled() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_NOT_INSTALLED))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The daemon 'jqd' seems to be missing", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_EXTENSION,	'TYPE' => MessageMapper::TYPE_NOT_INSTALLED))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The extension 'jqd' seems to be missing", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DIRECTIVE,	'TYPE' => MessageMapper::TYPE_NOT_INSTALLED))));
		self::assertEquals(array(array('Error' => htmlspecialchars("The directive 'jqd' seems to be missing", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlWebServerNotResponding() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_WEBSERVER_NOT_RESPONDING))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Webserver is not responding", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageXmlScdStandby() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_SCD_STDBY_MODE))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Session Clustering daemon is inactive", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageVhostModified() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_MODIFIED))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Web server configuration files have been modified on this server", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageVhostAdded() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_ADDED))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Failed to create the virtual host 'jqd' on this server", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_ADDED,
						'DETAILS' => Json::encode(array('serverId'))))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Failed to create the virtual host 'jqd' on this server: serverId", ENT_QUOTES))), $result);
	}

	public function testServerErrorMessageVhostRedeployed() {
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_REDEPLOYED))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Failed to redeploy the virtual host 'jqd' on this server", ENT_QUOTES))), $result);
		
		$result = $this->helper->__invoke(array(new MessageContainer(
				array('MSG_KEY' => 'jqd', 'MSG_SEVERITY' => MessageMapper::SEVERITY_ERROR,
						'CONTEXT' => MessageMapper::CONTEXT_DAEMON,	'TYPE' => MessageMapper::TYPE_VHOST_REDEPLOYED,
						'DETAILS' => Json::encode(array('serverId'))))));
		self::assertEquals(array(array('Error' => htmlspecialchars("Failed to redeploy the virtual host 'jqd' on this server: serverId", ENT_QUOTES))), $result);
	}
	
	protected function setUp() {
		parent::setUp();
		$this->helper = new ServerErrorMessageJson();
		$renderer = new PhpRenderer();
		$renderer->getHelperPluginManager()->setService('daemonName', new DaemonName());
		$this->helper->setView($renderer);
	}
}

