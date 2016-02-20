<?php
namespace ZendServer\Model;

use ZendServer\Set;

use PHPUnit_Framework_TestCase,
	ZendServer\Exception;

require_once 'tests/bootstrap.php';

class SetTest extends PHPUnit_Framework_TestCase
{
	public function test__construct() {
		$set = new Set(array(array('item' => 'item')));
		$result = $set[0];
		self::assertTrue($result instanceof \ArrayObject);
	}
	
	public function test__constructHydrateOtherClass() {
		$set = new Set(array(array('item' => 'item')), 'stdClass');
		$result = $set[0];
		self::assertTrue($result instanceof \stdClass);
	}

	public function testEmptyArray() { // ZSRV-5526
		$array = array(array(), array('item' => 'item'));
		$set = new Set($array);
		$i=0;
		foreach ($set as $member) {		
			self::assertTrue($member == new \ArrayObject($array[$i]), "[$i] position");
			$i++;
		}

		self::assertEquals(sizeof($array), $i, "size check");
	}	
	
}