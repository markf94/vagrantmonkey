<?php
namespace DeploymentLibrary\View\Helper;
use Zend\View\Helper\AbstractHelper;
use \Application\Module;
use \ZendServer\FS\FS;

class LibraryUpdateCheck extends AbstractHelper {
	
	/**
	 * @param \DeploymentLibrary\Container $library
	 * @return string
	 */
	public function __invoke($stopAutoRun = false) {
		$basePath = Module::config()->baseUrl;
		$reportUpdateUrl = $basePath . '/DeploymentLibrary/NewUpdate';
		$reportNoUpdateUrl = $basePath . '/DeploymentLibrary/NoUpdate';
		$zsVersion = Module::config('package', 'version');
		$phpVersion = phpversion();
		$osName = FS::getOSAsString();
		$arch = php_uname('m');
		
		if (strtolower($osName) == 'linux') {
			$osName = $this->getLinuxDistro();
			if (empty($osName)) {
				$osName = 'Linux';
			}
		}
		
		$updateUrl = Module::config('deployment', 'updateUrl');
		$uniqueId = Module::config('license', 'zend_gui', 'uniqueId');
		
		$stopAutoRun = ($stopAutoRun) ? 'true' : 'false';
		
	    return "new deploymentLibrariesUpdates('{$updateUrl}', '{$reportUpdateUrl}', '{$reportNoUpdateUrl}', '{$zsVersion}', '{$phpVersion}', '{$osName}', '{$arch}', '{$uniqueId}', {$stopAutoRun});";
	}
	
	/**
	 * Return the linux distro name
	 * @return mixed
	 */
	private function getLinuxDistro() {
		exec('less /etc/issue', $output);
		if (count($output) > 0) {
		
			$distros = array(
				'Ubuntu' => 'Ubuntu',
				'Fedora' => 'Fedora',
				'OEL'	 => 'Oracle',
				'RHEL'	 => 'Red Hat',
				'OpenSUSE'	=> 'openSUSE',
				'SUSE'	 => 'SUSE',
				'Debian' => 'Debian',
				'CentOS' => 'CentOS',
			);
			
			foreach ($distros as $distro => $keyword) {
				foreach ($output as $outputRow) {
					if (strpos($outputRow, $keyword) !== false) {
						return $distro;
					}
				}
			}
		}
		
		return '';
	}
}