<?php
namespace ZendServer\View\Helper;


use ZendServer\PHPUnit\TestCase;
use Zend\Config\Config;

require_once 'tests/bootstrap.php';

class HeadLinkWithVersionTest extends TestCase
{
	public function testCreateDataStylesheetNotUri() {
		$helper = new HeadLinkWithVersion();
		$result = $helper->createDataStylesheet(array('value'));
		self::assertEquals('value', $result->href);
	}
	
	public function testCreateDataRelativeUri() {
		$helper = new HeadLinkWithVersion();
		$helper->setVersion('version');
		$result = $helper->createDataStylesheet(array('/boom'));
		self::assertEquals('/boom?v=version', $result->href);
	}
	
	public function testCreateDataAbsoluteUri() {
		$helper = new HeadLinkWithVersion();
		$helper->setVersion('version');
		$result = $helper->createDataStylesheet(array('http://boom/boom'));
		self::assertEquals('http://boom/boom?v=version', $result->href);
	}
	
	public function testSetConfigNoValues() {
		$helper = new HeadLinkWithVersion();
		
		$config = new Config(array());
		$helper->setConfig($config);
		
		self::assertEquals('', $helper->getVersion());
	}
	
	public function testSetConfigVersionOnly() {
		$helper = new HeadLinkWithVersion();
		
		$config = new Config(array('version' => 'version'));
		$helper->setConfig($config);
		
		self::assertEquals('version', $helper->getVersion());
	}
	
	public function testSetConfigBuildOnly() {
		$helper = new HeadLinkWithVersion();
		
		$config = new Config(array('build' => 'build'));
		$helper->setConfig($config);
		
		self::assertEquals('build', $helper->getVersion());
	}
	
	public function testSetConfigFullData() {
		$helper = new HeadLinkWithVersion();
		
		$config = new Config(array('version' => 'version', 'build' => 'build'));
		$helper->setConfig($config);
		
		self::assertEquals('versionbuild', $helper->getVersion());
	}
}