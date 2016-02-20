<?php

namespace Application\View\Helper;

use Zend\ServiceManager\Config;

use Zend\View\HelperPluginManager;

use Zend\View\Renderer\PhpRenderer;

use Application\Module;


use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class EditionImageTest extends TestCase
{
	public function test__invoke() {
		
		$broker = new HelperPluginManager();
		
		$basePathHelper = $this->getMock('Zend\View\Helper\BasePath');
		$basePathHelper->expects($this->any())->method('__invoke')->with()->will($this->returnValue('/base/path'));
		$broker->setService('basepath', $basePathHelper);
		
		$renderer = new PhpRenderer();
		$renderer->setHelperPluginManager($broker);
		
		$helper = new EditionImage(EditionImage::SERVER_TYPE_SERVER);
		$helper->setView($renderer);
		
		
		$result = $helper('logoimage', 'serveredition');
		self::assertEquals('<a href="/base/path/" class="logo-placeholder" title="Zend Server edition"><img id="logoimage" class="logoimage" src="/base/path/images/single/logoimage"/><img id="edition" class="edition" src="/base/path/images/edition-basic.png"/></a>',
						$result);
		
		$result = $helper('logoimage', 'developer');
		self::assertEquals('<a href="/base/path/" class="logo-placeholder" title="Zend Server edition"><img id="logoimage" class="logoimage" src="/base/path/images/single/logoimage"/><img id="edition" class="edition" src="/base/path/images/edition-developer.png"/></a>',
						$result);
		
		$result = $helper('logoimage', 'developer_enterprise');
		self::assertEquals('<a href="/base/path/" class="logo-placeholder" title="Zend Server edition"><img id="logoimage" class="logoimage" src="/base/path/images/single/logoimage"/><img id="edition" class="edition" src="/base/path/images/edition-developer-enterprise.png"/></a>',
						$result);
		
		$helper = new EditionImage(EditionImage::SERVER_TYPE_CLUSTER_MEMBER);
		$helper->setView($renderer);
		
		$result = $helper('logoimage2', 'serveredition2');
		self::assertEquals('<a href="/base/path/" class="logo-placeholder" title="Zend Server edition"><img id="logoimage2" class="logoimage2" src="/base/path/images/single/logoimage2"/><img id="edition" class="edition" src="/base/path/images/edition-basic.png"/></a>',
						$result);

		/// 2nd parameter is a valid edition
		$result = $helper('logoimage2', 'free');
		self::assertEquals('<a href="/base/path/" class="logo-placeholder" title="Zend Server edition"><img id="logoimage2" class="logoimage2" src="/base/path/images/single/logoimage2"/><img id="edition" class="edition" src="/base/path/images/edition-free.png"/></a>',
						$result);
		
		/// 2nd parameter is empty
		$result = $helper('logoimage2');
		self::assertEquals('<a href="/base/path/" class="logo-placeholder" title="Zend Server edition"><img id="logoimage2" class="logoimage2" src="/base/path/images/single/logoimage2"/></a>',
						$result);
	}
	
}

