<?php
namespace Configuration;

use ZendServer\PHPUnit\TestCase;
require_once 'tests/bootstrap.php';

/**
 * Test for cecking on the patterns we use for url rewrites in lighttpd in linux installations
 *
 */
class IntegrationTest extends TestCase {
	public function testRewriteRule() {
		$pattern = "#^[^\?]+\.(htm(l)?|php.*)$#";
		
		$urlIndex_php = '/ZendServer/index.php';
		$urlZendServer = '/ZendServer/';
		$urlExtensions = '/ZendServer/Extensions';
		$urlIssueDetails = '/ZendServer/Issue?issueId=66';
		$urlServersList = '/ZendServer/Api/serversList';
		$urlValidateDirectives = '/ZendServer/Api/configurationValidateDirectives?directives[zend_gui.asdf]=1';
		$urlValidateDirectivesAutoPrepend = '/ZendServer/Api/configurationValidateDirectives?directives[auto_prepend_file]=pre.php';
		
		self::assertEquals(1, preg_match($pattern, $urlIndex_php));
		self::assertEquals(0, preg_match($pattern, $urlZendServer));
		self::assertEquals(0, preg_match($pattern, $urlExtensions));
		self::assertEquals(0, preg_match($pattern, $urlIssueDetails));
		self::assertEquals(0, preg_match($pattern, $urlServersList));
		self::assertEquals(0, preg_match($pattern, $urlValidateDirectives), 'validate directives should not be caught by this expression');
		self::assertEquals(0, preg_match($pattern, $urlValidateDirectivesAutoPrepend), 'validate directives should not be caught by this expression');
		
	}
}
