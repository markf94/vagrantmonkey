<?php
namespace StudioIntegration;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class ModelTest extends TestCase
{
	public function testDebuggerStartDebugModeEmpty() {
		$mapper = new Model();
		$wrapper = $this->getMock('StudioIntegration\Debugger\Wrapper');
		$wrapper->expects($this->once())->method('debugModeStart')
				->with(array(), array());
		$mapper->setDebuggerWrapper($wrapper);
		$config = array(
				'defaultPort' => '10081',
				'securedPort' => '10082',
				'enginePort' => '10083',
		);
		$mapper->setConfig($config);
		$mapper->debuggerStartDebugMode(array(), array());
	}
	
	public function testDebuggerStartDebugMode() {
		$mapper = new Model();
		$wrapper = $this->getMock('StudioIntegration\Debugger\Wrapper');
		$wrapper->expects($this->once())->method('debugModeStart')
				->with(array(), array('http://pattern/mypattern'));
		$mapper->setDebuggerWrapper($wrapper);
		$config = array(
				'defaultPort' => '10081',
				'securedPort' => '10082',
				'enginePort' => '10083',
		);
		$mapper->setConfig($config);
		$mapper->debuggerStartDebugMode(array(), array('http://pattern/mypattern'));
	}
	
	public function testDebuggerStartDebugModeWithPort() {
		$mapper = new Model();
		$wrapper = $this->getMock('StudioIntegration\Debugger\Wrapper');
		$wrapper->expects($this->once())->method('debugModeStart')
				->with(array(), array('http://pattern/mypattern:80'));
		$mapper->setDebuggerWrapper($wrapper);
		$config = array(
				'defaultPort' => '10081',
				'securedPort' => '10082',
				'enginePort' => '10083',
		);
		$mapper->setConfig($config);
		$mapper->debuggerStartDebugMode(array(), array('http://pattern/mypattern:80'));
	}
	
	/**
	 * @see https://il-jira.zend.net/browse/ZSRV-7856
	 */
	public function testDebuggerStartDebugModeZendServerPort() {
		$mapper = new Model();
		$wrapper = $this->getMock('StudioIntegration\Debugger\Wrapper');
		$wrapper->expects($this->never())->method('debugModeStart');
		$mapper->setDebuggerWrapper($wrapper);
		$config = array(
				'defaultPort' => '10084',
				'securedPort' => '10082',
				'enginePort' => '10083',
		);
		$mapper->setConfig($config);
		self::setExpectedException('ZendServer\Exception');
		$mapper->debuggerStartDebugMode(array(), array('localhost:10084'));
	}
}