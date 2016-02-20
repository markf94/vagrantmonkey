<?php

namespace EventsGroup\View\Helper;

use ZendServer\PHPUnit\TestCase;

use EventsGroup\View\Helper\VariablesTree;

use Zend\View\HelperPluginManager;
use PHPUnit_Framework_TestCase, Zend;
use Zend\Log\Logger;
use ZendServer\Log\Log;
use Zend\Log\Writer\Mock;

require_once 'tests/bootstrap.php';

class VariablesTreeTest extends TestCase
{

	public function testSimpleString() {
		$helper = new VariablesTree();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black"></span><span style="color: green">'myString'</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper('myString'));
	}
	
	public function testInteger() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">1984</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper(1984));
	}
	
	public function testBoolean() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">true</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper(true));
	}
	
	public function testNull() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">NULL</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper(null));
	}
	
	public function testArray() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black"></span><span style="color: blue">array&nbsp;(<br />&nbsp;&nbsp;</span><span style="color: black">0&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">1</span><span style="color: blue">,<br />&nbsp;&nbsp;</span><span style="color: green">'x'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">2</span><span style="color: blue">,<br />&nbsp;&nbsp;</span><span style="color: black">1&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">false</span><span style="color: blue">,<br />)</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper(array(1,'x' => 2, false)));
	}
	
	public function testEmptyArray() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black"></span><span style="color: blue">array&nbsp;(<br />)</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper(array()));
	}
	
	public function testArrayWithObject() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black"></span><span style="color: blue">array&nbsp;(<br />&nbsp;&nbsp;</span><span style="color: black">0&nbsp;</span><span style="color: blue">=&gt;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;),<br />)</span>
</span>
</code>
STR;
		$this->assertEquals($expectedString, $helper(array(new \stdClass())));
	}
	
	public function testObject() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'x'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">111</span><span style="color: blue">,<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'y'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">false</span><span style="color: blue">,<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'z'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: green">'xyz'</span><span style="color: blue">,<br />)</span>
</span>
</code>
STR;
		
		$obj = new \stdClass();
		$obj->x = 111;
		$obj->y = false;
		$obj->z = 'xyz';
		
		$this->assertEquals($expectedString, $helper($obj));
	}
	
	public function testEmptyObject() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />)</span>
</span>
</code>
STR;
		
		$this->assertEquals($expectedString, $helper(new \stdClass()));
	}
	
	public function testComplexObject() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'a'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">1</span><span style="color: blue">,<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'anotherObj'&nbsp;</span><span style="color: blue">=&gt;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: green">'a'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">1</span><span style="color: blue">,<br />&nbsp;&nbsp;),<br />)</span>
</span>
</code>
STR;
		
		$obj = new \stdClass();
		$obj->a = 1;
		$obj->anotherObj = new \stdClass();
		$obj->anotherObj->a = 1;
		
		$this->assertEquals($expectedString, $helper($obj));
	}
	
	public function testComplexObject2() {
		$helper = new VariablesTree ();
		$expectedString = <<<STR
<code><span style="color: white">
<span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'a'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">1</span><span style="color: blue">,<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'anotherObj'&nbsp;</span><span style="color: blue">=&gt;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: green">'a'&nbsp;</span><span style="color: blue">=&gt;&nbsp;</span><span style="color: black">1</span><span style="color: blue">,<br />&nbsp;&nbsp;),<br />&nbsp;&nbsp;&nbsp;</span><span style="color: green">'anotherObj2'&nbsp;</span><span style="color: blue">=&gt;&nbsp;<br />&nbsp;&nbsp;</span><span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: green">'yetAnotherObj'&nbsp;</span><span style="color: blue">=&gt;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: black">stdClass&nbsp;Object&nbsp;</span><span style="color: blue">(<br />&nbsp;&nbsp;&nbsp;&nbsp;),<br />&nbsp;&nbsp;),<br />)</span>
</span>
</code>
STR;
		
		$obj = new \stdClass();
		$obj->a = 1;
		$obj->anotherObj = new \stdClass();
		$obj->anotherObj->a = 1;
		$obj->anotherObj2 = new \stdClass();
		$obj->anotherObj2->yetAnotherObj = new \stdClass();

		$this->assertEquals($expectedString, $helper($obj));
	}
	
	protected function setUp()
	{
			// Set the highlight colors
		ini_set ( 'highlight.string', 'green' );
		ini_set ( 'highlight.comment', 'gray' );
		ini_set ( 'highlight.keyword', 'blue' );
		ini_set ( 'highlight.bg', 'white' );
		ini_set ( 'highlight.default', 'black' );
		ini_set ( 'highlight.html', 'gold' );
		
		parent::setUp();
	}



	
}

