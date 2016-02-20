<?php
namespace DevBar\View\Helper;

use ZendServer\PHPUnit\TestCase;
use Zend\Json\Json;
use Zend\View\Renderer\PhpRenderer;

require_once 'tests/bootstrap.php';
require_once 'tests/devbar.test.superglobals.php';

class SuperglobalsStructureXmlTest extends TestCase
{
	public function test__invokeEmpty() {
		$helper = new SuperglobalStructureXml();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array());
		self::assertEquals('', $result);
		
		$result = $helper(0);
		self::assertEquals('', $result);
		
		$result = $helper(false);
		self::assertEquals('', $result);
		
		$result = $helper('');
		self::assertEquals('', $result);
		
		$result = $helper(null);
		self::assertEquals('', $result);
		
		
	}

	public function test__invokeTwoSessions() {
		$helper = new SuperglobalStructureXml();
		$helper->setView(new PhpRenderer());
	
		$result = $helper(array(array('var1' => 'val1'), array('var1' => 'val1')));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<testroot>
				<parameters>
						    <parameter>
						      <name><![CDATA[var1]]></name>
						      <value><![CDATA[val1]]></value>
						    </parameter>
			</parameters>
				<parameters>
						    <parameter>
						      <name><![CDATA[var1]]></name>
						      <value><![CDATA[val1]]></value>
						    </parameter>
				</parameters>
</testroot>
XMLRESULT
				, "<testroot>$result</testroot>");
	
		$result = $helper(array(array(), array('var1' => 'val1')));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<testroot>
					  <parameters></parameters>
					  <parameters>
						    <parameter>
						      <name><![CDATA[var1]]></name>
						      <value><![CDATA[val1]]></value>
						    </parameter>
			</parameters>
</testroot>
XMLRESULT
				, "<testroot>$result</testroot>");
	}
	
	public function test__invokeAssociativeArray() {
		$helper = new SuperglobalStructureXml();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => 'val1','var2' => 'val2','var3' => 'val3')));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<parameters>
						    <parameter>
						      <name>var1</name>
						      <value><![CDATA[val1]]></value>
						    </parameter>
						    <parameter>
						      <name>var2</name>
						      <value><![CDATA[val2]]></value>
						    </parameter>
						    <parameter>
						      <name>var3</name>
						      <value><![CDATA[val3]]></value>
						    </parameter>
				</parameters>
XMLRESULT
				, $result);
		
		$result = $helper(array(array('array1' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'), 'array2' => array('var1' => 'val1','var2' => 'val2','var3' => 'val3'))));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<parameters><parameterMap>
					  <name><![CDATA[array1]]></name>
					  <type>array</type>
					  <parameters><parameter><name><![CDATA[var1]]></name><value><![CDATA[val1]]></value></parameter><parameter><name><![CDATA[var2]]></name><value><![CDATA[val2]]></value></parameter><parameter><name><![CDATA[var3]]></name><value><![CDATA[val3]]></value></parameter></parameters>
					</parameterMap><parameterMap>
					  <name><![CDATA[array2]]></name>
					  <type>array</type>
					  <parameters><parameter><name><![CDATA[var1]]></name><value><![CDATA[val1]]></value></parameter><parameter><name><![CDATA[var2]]></name><value><![CDATA[val2]]></value></parameter><parameter><name><![CDATA[var3]]></name><value><![CDATA[val3]]></value></parameter></parameters>
					</parameterMap></parameters>
XMLRESULT
				, $result);
		
		$result = $helper(array(array('array1' => array('val1','val2','var3' => 'val3'))));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<parameters><parameterMap>
					  <name><![CDATA[array1]]></name>
					  <type>array</type>
					  <parameters><parameter><name><![CDATA[0]]></name><value><![CDATA[val1]]></value></parameter><parameter><name><![CDATA[1]]></name><value><![CDATA[val2]]></value></parameter><parameter><name><![CDATA[var3]]></name><value><![CDATA[val3]]></value></parameter></parameters>
					</parameterMap></parameters>
XMLRESULT
				, $result);
	}
	
	public function test__invokeKnownObject() {
		$helper = new SuperglobalStructureXml();
		$helper->setView(new PhpRenderer());
		
		$result = $helper(array(array('var1' => new superglobalsTest())));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<parameters>
				<parameterMap>
					  <name><![CDATA[var1]]></name>
					  <type>DevBar\View\Helper\superglobalsTest</type>
					  <parameters>
						    <parameter>
						      <name><![CDATA[private]]></name>
						      <value><![CDATA[private]]></value>
						    </parameter>
						    <parameter>
						      <name><![CDATA[protected]]></name>
						      <value><![CDATA[protected]]></value>
						    </parameter>
						    <parameter>
						      <name><![CDATA[public]]></name>
						      <value><![CDATA[public]]></value>
						    </parameter>
			</parameters>
		      </parameterMap>
				</parameters>
XMLRESULT
				, $result);
		
		$result = $helper(array(array('var1' => array(new superglobalsTest()))));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<parameters>
				<parameterMap>
					  <name><![CDATA[var1]]></name>
					<type>array</type>
				<parameters>
				<parameterMap>
					  <name><![CDATA[0]]></name>
					  <type>DevBar\View\Helper\superglobalsTest</type>
					  <parameters>
						    <parameter>
						      <name><![CDATA[private]]></name>
						      <value><![CDATA[private]]></value>
						    </parameter>
						    <parameter>
						      <name><![CDATA[protected]]></name>
						      <value><![CDATA[protected]]></value>
						    </parameter>
						    <parameter>
						      <name><![CDATA[public]]></name>
						      <value><![CDATA[public]]></value>
						    </parameter>
			</parameters>
		      </parameterMap>
				</parameters>
		      </parameterMap>
				</parameters>
XMLRESULT
				, $result);
	}
	
	public function test__invokeUnknownObject() {
		$helper = new SuperglobalStructureXml();
		$helper->setView(new PhpRenderer());
		
		$incomplete = new Test__PHP_Incomplete_Class();
		
		$result = $helper(array(array('var1' => $incomplete)));
		self::assertXmlStringEqualsXmlString(<<<XMLRESULT
<parameters>
				<parameterMap>
					  <name><![CDATA[var1]]></name>
					  <type>DevBar\View\Helper\superglobalsTest</type>
					  <parameters>
						    <parameter>
						      <name>private</name>
						      <value><![CDATA[private]]></value>
						    </parameter>
						    <parameter>
						      <name>protected</name>
						      <value><![CDATA[protected]]></value>
						    </parameter>
						    <parameter>
						      <name>public</name>
						      <value><![CDATA[public]]></value>
						    </parameter>
			</parameters>
		      </parameterMap>
				</parameters>
XMLRESULT
				, $result);
	}
}
