<?php
namespace ZendServer\View\Helper;


use ZendServer\PHPUnit\TestCase;
use Zend\Config\Config;

require_once 'tests/bootstrap.php';

class ParseMarkdownTest extends TestCase
{
	/**
	 * @var ParseMarkdown
	 */
	private $helper;
	public function testParseMarkdown() {
		$helper = $this->helper;
		self::assertContains('<p>text</p>', $helper('text'));
		self::assertContains('<p><code>text</code></p>', $helper('```text```'));
	}
	
	protected function setUp() {
		$this->helper = new ParseMarkdown();
	}
}