<?php
namespace StudioIntegration;

use Zend\Config\Config;

use ZendServer\PHPUnit\TestCase;

require_once 'tests/bootstrap.php';

class MapperTest extends TestCase
{
	public function testGetConfiguration() {
		$mapper = new Mapper();
		$mapper->setModuleConfiguration(new Config(array(
					'studioHost' => 'studioHost',
					'studioPort' => '10137',
					'studioUseSsl' => '0',
					'studioAutoDetectionEnabled' => '1',
					'studioAutoDetection' => '0',
					'studioAutoDetectionPort' => '20080',
				)));
		$config = $mapper->getConfiguration();
		
		self::assertEquals('studioHost', $config->getHost());
		self::assertEquals('10137', $config->getPort());
		self::assertEquals(false, $config->getSsl());
		self::assertEquals(true, $config->getBrowserDetect());
		self::assertEquals(false, $config->getAutoDetect());
	}
}