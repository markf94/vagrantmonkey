<?php

require_once 'bootstrap.php';

/**
 * Static test suite.
 */
class AllModuleSuites extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'All Module Suites' );
		$it = new RegexIterator ( new RecursiveIteratorIterator (
				new RecursiveDirectoryIterator ( dirname(__DIR__) . '/module' ) ), '/^[^\.].+TestSuite\.php$/' );
		foreach ( $it as $filePath ) { /* @var $filePath SplFileInfo */
			$this->addTestFile ( $filePath->getPathname() );
		}
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

