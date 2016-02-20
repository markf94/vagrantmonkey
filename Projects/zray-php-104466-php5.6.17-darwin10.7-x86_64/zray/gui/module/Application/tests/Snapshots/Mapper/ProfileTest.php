<?php
namespace Snapshots\Mapper;
use ZendServer\PHPUnit\TestCase;
use Zend\Session\SessionManager;
use Zend\Session\Storage\ArrayStorage;
use Zend\Config\Config;
require_once 'tests/bootstrap.php';

class ProfileTest extends TestCase {
	/**
	 * @var Config
	 */
	private $config;
	public function testActivateProfileClusterPHP53() {
		
		$profile = $this->getProfile();
		
		$directivesMapper = $this->getMock('Configuration\MapperDirectives');
		$directivesMapper->expects($this->never())->method('setDirectives');
		$profile->setDirectivesMapper($directivesMapper);
		
		$guiConfigurationMapper = $this->getMock('GuiConfiguration\Mapper\Configuration');
		$guiConfigurationMapper
			->expects($this->once())->method('setGuiDirectives')
			->with($this->config['profiles']['clusterDirectives']['GUI']->toArray());

		$profile->setGuiConfigurationMapper($guiConfigurationMapper);
		$profile->setPhpversion('5.3.0');
		
		$profile->activateProfile('clusterDirectives');
	}
	
	public function testActivateProfileDevelopmentPHP53() {
		
		$profile = $this->getProfile();
		
		$directivesMapper = $this->getMock('Configuration\MapperDirectives');
		$directivesMapper->expects($this->once())->method('setDirectives')
		->with(array_merge(
				$this->config['profiles']['developmentDirectives']['ZEND']->toArray(),
				$this->config['profiles']['developmentDirectives']['PHP_53']->toArray(),
				$this->config['profiles']['developmentDirectives']['PHP_ALL']->toArray()
		));
		$profile->setDirectivesMapper($directivesMapper);
		
		$guiConfigurationMapper = $this->getMock('GuiConfiguration\Mapper\Configuration');
		$guiConfigurationMapper
			->expects($this->once())->method('setGuiDirectives')
			->with($this->config['profiles']['developmentDirectives']['GUI']->toArray());

		$profile->setGuiConfigurationMapper($guiConfigurationMapper);
		$profile->setPhpversion('5.3.0');
		
		$profile->activateProfile('developmentDirectives');
	}
	
	public function testActivateProfileDevelopmentPHP54() {
		
		$profile = $this->getProfile();
		
		$directivesMapper = $this->getMock('Configuration\MapperDirectives');
		$directivesMapper->expects($this->once())->method('setDirectives')
		->with(array_merge(
				$this->config['profiles']['developmentDirectives']['ZEND']->toArray(),
				$this->config['profiles']['developmentDirectives']['PHP_54']->toArray(),
				$this->config['profiles']['developmentDirectives']['PHP_ALL']->toArray()
		));
		$profile->setDirectivesMapper($directivesMapper);
		
		$guiConfigurationMapper = $this->getMock('GuiConfiguration\Mapper\Configuration');
		$guiConfigurationMapper
			->expects($this->once())->method('setGuiDirectives')
			->with($this->config['profiles']['developmentDirectives']['GUI']->toArray());

		$profile->setGuiConfigurationMapper($guiConfigurationMapper);
		$profile->setPhpversion('5.4.0');
		
		$profile->activateProfile('developmentDirectives');
	}
	
	public function testActivateUnknownProfile() {
		
		$profile = $this->getProfile();
		
		self::setExpectedException('ZendServer\Exception');
		$profile->activateProfile('unknownProfile');
	}
	
	protected function getProfile() {
		$this->config = $config = new Config(include 'config/autoload/global.config.php');
		$profile = new Profile();
		$profile->setProfiles($config['profiles']);
		return $profile;
	}
	
}

