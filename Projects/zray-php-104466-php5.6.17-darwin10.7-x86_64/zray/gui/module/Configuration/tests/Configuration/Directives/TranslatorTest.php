<?php
namespace Configuration\Directives;

use ZendServer\PHPUnit\TestCase;
use ZendServer\Configuration\Directives\Translator;
use Configuration\DirectiveContainer;
require_once 'tests/bootstrap.php';

class TranslatorTest extends TestCase {
	public function testGetIntegerValues() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => 0, 'type' => DirectiveContainer::TYPE_INT));
		self::assertEquals(0, Translator::getRealFileValue($directive));
		self::assertEquals('0', Translator::getStringFileValue($directive));
	}
	
	public function testGetStringValues() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => 'string', 'type' => DirectiveContainer::TYPE_STRING));
		self::assertEquals('string', Translator::getRealFileValue($directive));
		self::assertEquals('string', Translator::getStringFileValue($directive));
	}
	
	public function testGetNoType() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => 'string', 'type' => 0));
		self::assertEquals('string', Translator::getRealFileValue($directive));
		self::assertEquals('string', Translator::getStringFileValue($directive));
	}
	
	public function testGetBooleanValues() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => '0', 'type' => DirectiveContainer::TYPE_BOOLEAN));
		self::assertEquals(0, Translator::getRealFileValue($directive));
		self::assertEquals('0', Translator::getStringFileValue($directive));
		
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => '1', 'type' => DirectiveContainer::TYPE_BOOLEAN));
		self::assertEquals(1, Translator::getRealFileValue($directive));
		self::assertEquals('1', Translator::getStringFileValue($directive));
	}
	
	public function testGetSelectValues() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => 'option', 'type' => DirectiveContainer::TYPE_SELECT));
		self::assertEquals('option', Translator::getRealFileValue($directive));
		self::assertEquals('option', Translator::getStringFileValue($directive));
	}
	
	public function testGetShorthandValues() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => '1M', 'type' => DirectiveContainer::TYPE_SHORTHAND));
		self::assertEquals('1M', Translator::getRealFileValue($directive));
		self::assertEquals('1M', Translator::getStringFileValue($directive));
	}
	
	public function testGetIntBooleanValues() {
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => '0', 'type' => DirectiveContainer::TYPE_INT_BOOLEAN));
		self::assertEquals(0, Translator::getRealFileValue($directive));
		self::assertEquals('0', Translator::getStringFileValue($directive));
		
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => '1', 'type' => DirectiveContainer::TYPE_INT_BOOLEAN));
		self::assertEquals(1, Translator::getRealFileValue($directive));
		self::assertEquals('1', Translator::getStringFileValue($directive));
		
		self::markTestIncomplete('off,on and true/false should return true|false integer or boolean values');
		
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => 'off', 'type' => DirectiveContainer::TYPE_INT_BOOLEAN));
		self::assertEquals('off', Translator::getRealFileValue($directive));
		self::assertEquals('off', Translator::getStringFileValue($directive));
		
		$directive = new DirectiveContainer(array('NAME' => 'zend.some_directive', 'DISK_VALUE' => 'on', 'type' => DirectiveContainer::TYPE_INT_BOOLEAN));
		self::assertEquals('on', Translator::getRealFileValue($directive));
		self::assertEquals('on', Translator::getStringFileValue($directive));
	}
	
	public function testGetErrorReportingValues() {
		$directive = new DirectiveContainer(array('NAME' => 'error_reporting', 'DISK_VALUE' => 'E_ALL', 'type' => DirectiveContainer::TYPE_STRING));
		self::assertEquals(32767, Translator::getRealFileValue($directive));
		self::markTestIncomplete('string interpretation of error_reporting returns an empty value');
		self::assertEquals('E_ALL', Translator::getStringFileValue($directive));
	}
}
