<?php
namespace DevBar\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Zend\Json\Json;
use Zend\View\Renderer\PhpRenderer;

require_once 'tests/bootstrap.php';
require_once 'tests/devbar.test.superglobals.php';

class SuperglobalsStructureJsonTest extends TestCase
{
	public function test__invokeEmpty() {
		$helper = new SuperglobalStructureJson();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array());
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(), $output);
		
		$result = $helper(0);
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(), $output);
		
		$result = $helper(false);
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(), $output);
		
		$result = $helper('');
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(), $output);
		
		$result = $helper(null);
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(), $output);
		
		
	}
	
	public function test__invokeTwoSessions() {
		$helper = new SuperglobalStructureJson();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => 'val1'), array('var1' => 'val1')));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('var1' => 'val1'), array('var1' => 'val1')), $output);
		
		$result = $helper(array(array(), array('var1' => 'val1')));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array(), array('var1' => 'val1')), $output);
	}
	
	public function test__invokeAssociativeArray() {
		$helper = new SuperglobalStructureJson();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => 'val1','var2' => 'val2','var3' => 'val3')));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('var1' => 'val1','var2' => 'val2','var3' => 'val3')), $output);
		
		$result = $helper(array(array('array1' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'), 'array2' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'))));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('array1' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'), 'array2' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'))), $output);
		
		$result = $helper(array(array('array1' => array('val1','val2','var3' => 'val3'))));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('array1' => array('val1','val2','var3' => 'val3'))), $output);
	}
	
	public function test__invokeKnownObject() {
		$helper = new SuperglobalStructureJson();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => new superglobalsTest())));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('var1' => array(
				'private' => 'private',
				'protected' => 'protected',
				'public' => 'public',
				'__object_type' => 'DevBar\View\Helper\superglobalsTest'
				
		))), $output);
		
		$result = $helper(array(array('var1' => array(new superglobalsTest()))));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('var1' => array(array(
				'private' => 'private',
				'protected' => 'protected',
				'public' => 'public',
				'__object_type' => 'DevBar\View\Helper\superglobalsTest'
				
		)))), $output);
	}
	
	public function test__invokeUnknownObject() {
		$helper = new SuperglobalStructureJson();
		$helper->setView(new PhpRenderer());
		
		$incomplete = new Test__PHP_Incomplete_Class();
		
		$result = $helper(array(array('var1' => $incomplete)));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('var1' => array(
				'private' => 'private',
				'protected' => 'protected',
				'public' => 'public',
				'__object_type' => 'DevBar\View\Helper\superglobalsTest'
				
		))), $output);
	}
}
