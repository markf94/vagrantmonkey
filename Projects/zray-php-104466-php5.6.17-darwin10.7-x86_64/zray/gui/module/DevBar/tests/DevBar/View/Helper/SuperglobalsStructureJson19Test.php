<?php
namespace DevBar\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Zend\Json\Json;
use Zend\View\Renderer\PhpRenderer;

require_once 'tests/bootstrap.php';
require_once 'tests/devbar.test.superglobals.php';

class SuperglobalsStructure19JsonTest extends TestCase
{
	public function test__invokeEmpty() {
		$helper = new SuperglobalStructure19Json();
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
		$helper = new SuperglobalStructure19Json();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => 'val1'), array('var1' => 'val1')));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(
				0 => array('key' => 0, 'value' => array('var1' => array('key' => 'var1', 'value' => 'val1'))),
				1 => array('key' => 1, 'value' => array('var1' => array('key' => 'var1', 'value' => 'val1'))),
				), $output);
		
		$result = $helper(array(array(), array('var1' => 'val1')));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(
				0 => array('key' => 0, 'value' => array()),
				1 => array('key' => 1, 'value' => array('var1' => array('key' => 'var1', 'value' => 'val1'))),
				), $output);
	}
	
	public function test__invokeAssociativeArray() {
		$helper = new SuperglobalStructure19Json();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => 'val1','var2' => 'val2','var3' => 'val3')));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('key' => 0, 'value' => 
				array('var1' => array('key' => 'var1', 'value' => 'val1'),
						'var2' => array('key' => 'var2', 'value' => 'val2'),
						'var3' => array('key' => 'var3', 'value' => 'val3')
				))), $output);
		
		$result = $helper(array(array('array1' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'), 'array2' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'))));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('key' => '0', 'value' => array(
				'array1' => array('key' => 'array1', 'value' => array(
						'var1' => array('key' => 'var1', 'value' => 'val1'),
						'var2' => array('key' => 'var2', 'value' => 'val2'),
						'var3' => array('key' => 'var3', 'value' => 'val3')
					)),
				'array2' => array('key' => 'array2', 'value' => array(
						'var1' => array('key' => 'var1', 'value' => 'val1'),
						'var2' => array('key' => 'var2', 'value' => 'val2'),
						'var3' => array('key' => 'var3', 'value' => 'val3')
					)),
		))), $output);
		
		$result = $helper(array(array('array1' => array('val1','val2','var3' => 'val3'))));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('key' => '0', 'value' => array(
				'array1' => array('key' => 'array1', 'value' => array(
						0 => array('key' => 0, 'value' => 'val1'),
						1 => array('key' => 1, 'value' => 'val2'),
						'var3' => array('key' => 'var3', 'value' => 'val3')
					))))), $output);
	}
	
	public function test__invokeKnownObject() {
		$helper = new SuperglobalStructure19Json();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => new superglobalsTest())));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('key' => '0', 'value' => array(
				'var1' => array('key' => 'var1', 'value' => array(
					'private' => array('key' => 'private', 'value' => 'private'),
					'protected' => array('key' => 'protected', 'value' => 'protected'),
					'public' => array('key' => 'public', 'value' => 'public'),
					'__object_type' => array('key' => '__object_type', 'value' => 'DevBar\View\Helper\superglobalsTest'),
				
		))))), $output);
		
		$result = $helper(array(array('var1' => array(new superglobalsTest()))));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		self::assertEquals(array(array('key' => '0', 'value' => array(
				'var1' => array('key' => 'var1', 'value' => array(
					array('key' => '0', 'value' => array(
						'private' => array('key' => 'private', 'value' => 'private'),
						'protected' => array('key' => 'protected', 'value' => 'protected'),
						'public' => array('key' => 'public', 'value' => 'public'),
						'__object_type' => array('key' => '__object_type', 'value' => 'DevBar\View\Helper\superglobalsTest'),
		
		))))))), $output);
	}
	
	public function test__invokeUnknownObject() {
		$helper = new SuperglobalStructure19Json();
		$helper->setView(new PhpRenderer());
		
		$incomplete = new Test__PHP_Incomplete_Class();
		
		$result = $helper(array(array('var1' => $incomplete)));
		$output = Json::decode($result, Json::TYPE_ARRAY);
		
		self::assertEquals(array(array('key' => '0', 'value' => array(
				'var1' => array('key' => 'var1', 'value' => array(
					'private' => array('key' => 'private', 'value' => 'private'),
					'protected' => array('key' => 'protected', 'value' => 'protected'),
					'public' => array('key' => 'public', 'value' => 'public'),
					'__object_type' => array('key' => '__object_type', 'value' => 'DevBar\View\Helper\superglobalsTest'),
		
		))))), $output);
	}
}
