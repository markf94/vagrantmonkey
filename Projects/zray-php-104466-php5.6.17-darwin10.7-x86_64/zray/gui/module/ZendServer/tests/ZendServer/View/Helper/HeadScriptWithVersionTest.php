<?php
namespace ZendServer\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Zend\Config\Config;

require_once 'tests/bootstrap.php';

class HeadScriptWithVersionTest extends TestCase
{
	public function testCreateDataNoSrc() {
		$helper = new HeadScriptWithVersion();
		$result = $helper->appendFile('source','type')->toString();
		self::assertEquals('<script type="type" src="source"></script>', $result);
	}
	
	public function testCreateDataNoVersion() {
		$helper = new HeadScriptWithVersion();
		
		$result = $helper->appendFile('source','type')->toString();
		self::assertEquals('<script type="type" src="source"></script>', $result);
	}
	
	public function testCreateDataRelativeHref() {
		$helper = new HeadScriptWithVersion();
		$helper->setVersion('version');
		
		$result = $helper->appendFile('/source','type')->toString();
		self::assertEquals('<script type="type" src="/source?zsv=version"></script>', $result);
	}
	
	public function testCreateDataMultipleHref() {
		$helper = new HeadScriptWithVersion();
		$helper->setVersion('version');
		$helper->appendFile('/source','type');
		$helper->appendFile('/source2','type');
		$result = $helper->toString();
		$expected = '<script type="type" src="/source?zsv=version"></script>
				<script type="type" src="/source2?zsv=version"></script>';
		
		$result = trim(preg_replace('/\s+/', ' ', $result));
		$expected = trim(preg_replace('/\s+/', ' ', $expected));
		
		self::assertEquals($expected, $result);
	}
	
	public function testSetConfigNoValues() {
		$helper = new HeadScriptWithVersion();
		$helper->setVersion('version');
		
		$result = $helper->appendFile('http://boom/source','type')->toString();
		self::assertEquals('<script type="type" src="http://boom/source?zsv=version"></script>', $result);
	}
	
	public function testSetConfigVersionOnly() {
		$helper = new HeadScriptWithVersion();
		
		$config = new Config(array('version' => 'version'));
		$helper->setConfig($config);
		
		self::assertEquals('version', $helper->getVersion());
	}
	
	public function testSetConfigBuildOnly() {
		$helper = new HeadScriptWithVersion();
		
		$config = new Config(array('build' => 'build'));
		$helper->setConfig($config);
		
		self::assertEquals('build', $helper->getVersion());
	}
	
	public function testSetConfigFullData() {
		$helper = new HeadScriptWithVersion();
		
		$config = new Config(array('version' => 'version', 'build' => 'build'));
		$helper->setConfig($config);
		
		self::assertEquals('versionbuild', $helper->getVersion());
	}
}