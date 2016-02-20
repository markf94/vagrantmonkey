<?php
namespace DevBar\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Zend\Json\Json;
use Zend\View\Renderer\PhpRenderer;

require_once 'tests/bootstrap.php';
require_once 'tests/devbar.test.superglobals.php';

class SqlQueryFormatTest extends TestCase
{
	public function test__invokeEmpty() {
		$helper = new SqlQueryFormat();
		self::assertEquals('', $helper->__invoke(''));
	}
	
	public function test__invokeNull() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE ISNULL(column) and null_column IS NULL', $helper->__invoke('SELECT * FROM table WHERE ISNULL(column) and null_column IS \'<zend-null>\''));
	}
	
	public function test__invokeResource() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Resource>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-resource>\''));
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Resource File Descriptor>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-resource-file-descriptor>\''));
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Resource Db Connection>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-resource-db-connection>\''));
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Resource Curl>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-resource-curl>\''));
		
		self::assertEquals('DECLARE
TYPE curtype IS REF CURSOR;
cursor_var curtype;
BEGIN
    OPEN cursor_var FOR SELECT id, value FROM cursor_bind_tab;
    <PHP Resource Oci8 Statement> := cursor_var;
END;',
				$helper->__invoke('DECLARE
TYPE curtype IS REF CURSOR;
cursor_var curtype;
BEGIN
    OPEN cursor_var FOR SELECT id, value FROM cursor_bind_tab;
    \'<zend-resource-oci8 statement>\' := cursor_var;
END;'));
	}
	
	public function test__invokeArray() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Array>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-array>\''));
	}
	
	public function test__invokeObject() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Object>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-object>\''));
	}
	
	public function test__invokeCallable() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Callable>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-callable>\''));
	}
	
	public function test__invokeConstant() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Constant>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-constant>\''));
	}
	
	public function test__invokeConstantArray() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <PHP Constant Array>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-constant-array>\''));
	}
	
	public function test__invokeBinary() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = <Binary Value>', $helper->__invoke('SELECT * FROM table WHERE id = \'<zend-binary-value>\''));
	}
	
	public function test__invokeLOB() {
		$helper = new SqlQueryFormat();
		self::assertEquals('SELECT * FROM table WHERE id = \'very large suffix...<Truncated large data>\'', $helper->__invoke('SELECT * FROM table WHERE id = \'very large suffix<zend-too-large-value>\''));
	}
}
