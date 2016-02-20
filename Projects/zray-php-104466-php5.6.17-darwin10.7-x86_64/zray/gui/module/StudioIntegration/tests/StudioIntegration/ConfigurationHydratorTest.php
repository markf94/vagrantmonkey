<?php
namespace StudioIntegration;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class ConfigurationHydratorTest extends TestCase
{
	public function testExtractInvalidParam() {
		$hydrator = new ConfigurationHydrator();
		self::setExpectedException('InvalidArgumentException');
		$hydrator->extract(0);
	}
	
	public function testExtract() {
		$hydrator = new ConfigurationHydrator();
		$config = new Configuration();
		$result = $hydrator->extract($config);
		
		self::assertArrayHasKeys(array('studioAutoDetectionEnabled', 'studioHost', 'studioPort', 'studioUseSsl', 'studioAutoDetection'), $result);
	}
	
	public function testHydrate() {
		$hydrator = new ConfigurationHydrator();
		$config = new Configuration();
		$result = $hydrator->hydrate(array('studioAutoDetection' => 1, 'studioHost' => '127.0.0.1', 'studioPort' => '12345', 'studioUseSsl' => false), $config);
		
		self::assertInstanceOf('StudioIntegration\Configuration', $result);
	}
}