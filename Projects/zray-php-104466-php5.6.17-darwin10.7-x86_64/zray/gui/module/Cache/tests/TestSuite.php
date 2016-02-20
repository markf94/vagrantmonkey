<?php
namespace Cache;
use \PHPUnit_Framework_TestSuite, \RegexIterator, \RecursiveIteratorIterator, \RecursiveDirectoryIterator;



/**
 * Static test suite.
 */
class TestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ('Cache Module Tests');
		$it = new RegexIterator (new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( __DIR__ ) ), '/^[^\.].+Test\.php$/' );
		foreach ($it as $filePath) { /* @var $filePath SplFileInfo */
			$this->addTestFile($filePath->getPathname());
		}
	}
	
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

