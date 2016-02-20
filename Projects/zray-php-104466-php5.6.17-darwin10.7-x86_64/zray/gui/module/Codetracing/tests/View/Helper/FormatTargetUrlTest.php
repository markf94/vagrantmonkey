<?php
namespace Codetracing\View\Helper;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class FormatTargetUrlTest extends TestCase {
	
	public function test__invoke() {
		$helper = new FormatTargetUrl();
		$url = $helper('http://boom/index.php?dump_data=1');
		self::assertEquals('http://boom/index.php', $url);
		
		$url = $helper('http://boom/index.php?not_dump_data=1');
		self::assertEquals('http://boom/index.php?not_dump_data=1', $url);
		
		$url = $helper('http://boom/index.php?not_dump_data=1&dump_data=1');
		self::assertEquals('http://boom/index.php?not_dump_data=1', $url);

		$url = $helper('');
		self::assertEquals('', $url);
	}
}
