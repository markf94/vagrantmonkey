<?php
namespace GuidePage\Controller;

use ZendServer\Mvc\Controller\ActionController,
	Application\Module,
	ZendServer\Edition,
	ZendServer\Configuration\Manager,
	Zend\View\Model\ViewModel,
	\Servers\Configuration\Mapper as ServersConfigurationMapper,
	ZendServer\FS\FS,
	ZendServer\Log\Log;
use Configuration\License\License;

class IndexController extends ActionController
{
	public function indexAction() {
	    $viewModel = new \Zend\View\Model\ViewModel ();
		
		$licenseInfo = $this->getZemUtilsWrapper()->getLicenseInfo(); /* @var $licenseInfo License */
		
		$edition = $licenseInfo->getEdition();
		$trial = $this->getLocator()->get('Configuration\License\ZemUtilsWrapper')->getLicenseEvaluation();
		if ($edition == \Configuration\License\License::EDITION_ENTERPRISE && $trial) {
			$edition = \Configuration\License\License::EDITION_ENTERPRISE_TRIAL;
		}
		
		$guidePage = Module::config('package', 'guidePage');
		
		$config = $this->getLocator()->get('Configuration');
		
		$azure = isAzureEnv();
		$zrayStandalone = isZrayStandaloneEnv();
		
		$deploymentSupported = false;
		if (!$azure && !$zrayStandalone) {
		    $deploymentModel = $this->getLocator()->get('Deployment\Model');
		    $deploymentSupported = $deploymentModel->isDeploySupportedByWebserver();
		}
		
		$serversMapper = new ServersConfigurationMapper();
		$authService = $this->getLocator('Zend\Authentication\AuthenticationService');
		$isDeveloper = ($authService->getIdentity()->getUsername() == 'developer');
		
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
		
		$uniqueId = Module::config('license', 'zend_gui', 'uniqueId');
		
		$viewModel->setVariables(array(
			'license' => $licenseInfo, 
			'edition' => $edition, 
			'zsVersion' => Module::config('package', 'version'),
			'profile' => @$config['package']['zend_gui']['serverProfile']?:'',
			'trial' => $trial, 
			'showGuide' => ($guidePage == 1),
			'deploymentSupported' => $deploymentSupported,
			'clusterSupported' => $serversMapper->isClusterSupport(),
			'showXmlToolkit' => FS::isAix(),
			'isDeveloper' => $isDeveloper,
		    
		    'uniqueId' => $uniqueId,
		    'zsVersion' => $zsVersion,
		    'phpVersion' => $phpVersion,
		    'osName' => $osName,
		    'arch' => $arch,
		));
		
		if ($azure) {
		    $viewModel->setTemplate('guide-page/index/azure');
		    
		    $profile = Module::config('package', 'zend_gui', 'serverProfile');
		    $welcomeContent = $this->getAzureWelcomePage($zsVersion, $phpVersion, $osName, $arch, $edition, $uniqueId, $profile);
		    
		    $builtInExtensions = array('apigility', 'composer', 'drupal', 'magento', 'samples', 'symfony', 'wordpress', 'xmltoolkit', 'zf1', 'zf2', 'laravel');
		    $viewModel->setVariable('builtInExtensions', $builtInExtensions);
		    
		    $azureLicense = getAzureLicense();
		    if (is_null($azureLicense)) {
		        $viewModel->setVariable('licenseType', 'basic');
		    } else {
		        $viewModel->setVariable('licenseType', $azureLicense);
		    }
		    
		    if (function_exists('zray_get_loaded_extensions')) {
		      $viewModel->setVariable('loadedExtensions', \zray_get_loaded_extensions());
		    }
		    $viewModel->setVariable('welcomeContent', $welcomeContent);
		}
		
		if ($zrayStandalone) {
		    $viewModel->setTemplate('guide-page/index/zray-standalone');
		    
		    $profile = Module::config('package', 'zend_gui', 'serverProfile');
			if (empty($profile)) {
				$profile = 'Development';
			}
			
			/* @var $directivesMapper \Configuration\MapperDirectives */
			$directivesMapper = $this->getLocator()->get('Configuration\MapperDirectives'); 
			$revision = $directivesMapper->getDirectiveValue('zend_gui.build');
			
		    $viewModel->setVariable('revision', $revision);
			
		    $welcomeContent = $this->getZrayStandaloneWelcomePage($revision, $phpVersion, $osName, $arch, $edition, $uniqueId, $profile);
			
		    
		    $builtInExtensions = array('apigility', 'composer', 'drupal', 'magento', 'samples', 'symfony', 'wordpress', 'xmltoolkit', 'zf1', 'zf2', 'laravel');
		    $viewModel->setVariable('builtInExtensions', $builtInExtensions);
		    
		    $viewModel->setVariable('licenseType', 'basic');
			
		    if (function_exists('zray_get_loaded_extensions')) {
				$viewModel->setVariable('loadedExtensions', \zray_get_loaded_extensions());
		    }
		    $viewModel->setVariable('welcomeContent', $welcomeContent);
		    $viewModel->setVariable('viewCalledFromZS', true);
		}
		
		return $viewModel;
	}
	
	public function showGuidePageAction() {
		$viewModel = new \Zend\View\Model\ViewModel ();
		$viewModel->setTerminal(true);
	
		if ($this->getRequest()->isPost()) {
			$params = $this->getRequest()->getPost ()->toArray();
			if ($params['show'] == 'TRUE') {
				$this->getGuiConfigurationMapper()->setGuiDirectives(array('guidePage' => 1));
			} else {
				$this->getGuiConfigurationMapper()->setGuiDirectives(array('guidePage' => 0));
			}
		}
		
		return $viewModel;
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
	
	/**
	 * @brief 
	 * @param string $url 
	 * @param int $timeout 
	 * @return  
	 */
	private function readUrlPage($url, $timeout = 15) {
		$ctx = stream_context_create(array(
			'http' => array(
				'timeout' => $timeout,
			),
		));
		$res = file_get_contents($url, false, $ctx);
		if ($res === false) {
			Log::notice(_t("Cannot read URL %s (timeout %d)", array($url, $timeout)));
		}
		
		return $res;
	}
	
	/**
	 * Retrieve the welcome page from the web for azure
	 * @param string $zsVersion
	 * @param string $phpVersion
	 * @param string $osName
	 * @param string $arch
	 * @param string $edition
	 * @param string $uniqueId
	 * @return string
	 */
	private function getAzureWelcomePage($zsVersion, $phpVersion, $osName, $arch, $edition, $uniqueId, $profile) {
	    try {
    	    $guiTemp = \ZendServer\FS\FS::getGuiTempDir();
    	    $welcomePath = $guiTemp . DIRECTORY_SEPARATOR . 'welcome.html';
    	    $welcomeTimestamp = 0;
    	    if (file_exists($welcomePath)) {
    	        $welcomeTimestamp = filemtime($welcomePath);
    	    }
    	    
    	    $welcomeContent = '';
    	    // check if timestamp is a day older - check once a day
    	    if ($welcomeTimestamp <= strtotime('-1 day')) {
    	        $updateUrl = "https://www.zend.com/azure/redirect/welcome?t={$welcomeTimestamp}&zs={$zsVersion}&php={$phpVersion}&os={$osName}&arch={$arch}&edition={$edition}&profile={$profile}&hash={$uniqueId}";
    	        $res = $this->readUrlPage($updateUrl);
    	        if ($res !== false) {
    	            if (trim($res) == 'OK') {
    	                $welcomeContent = file_get_contents($welcomePath);
    	            } else {
    	                file_put_contents($welcomePath, $res);
    	                $welcomeContent = $res;
    	            }
    	        } else {
    	             
    	        }
    	    } else {
    	        $welcomeContent = file_get_contents($welcomePath);
    	    }
	    } catch (\Exception $e) {
	        $welcomeContent = '';
	    }
	     
	    return $welcomeContent;
	}
	
	/**
	 * Retrieve the welcome page from the web for zray standalone
	 * @param string $zsVersion
	 * @param string $phpVersion
	 * @param string $osName
	 * @param string $arch
	 * @param string $edition
	 * @param string $uniqueId
	 * @return string
	 */
	private function getZrayStandaloneWelcomePage($revision, $phpVersion, $osName, $arch, $edition, $uniqueId, $profile) {
	    try {
    	    $guiTemp = \ZendServer\FS\FS::getGuiTempDir();
    	    $welcomePath = $guiTemp . DIRECTORY_SEPARATOR . 'welcome.html';
    	    $welcomeTimestamp = 0;
    	    if (file_exists($welcomePath)) {
    	        $welcomeTimestamp = filemtime($welcomePath);
    	    }
			
    	    $welcomeContent = '';
    	    // check if timestamp is a day older - check once a day
    	    if ($welcomeTimestamp <= strtotime('-1 day')) {
    	        $updateUrl = "https://www.zend.com/zray/redirect/welcome?t={$welcomeTimestamp}&revision={$revision}&php={$phpVersion}&os={$osName}&arch={$arch}&edition={$edition}&profile={$profile}&hash={$uniqueId}";
    	        $res = $this->readUrlPage($updateUrl);
				$resLength = $res ? strlen($res) : 0;
				Log::debug(_t("Homepage: Reading the guide page from %s. received %d bytes", array($updateUrl, $resLength)));
    	        if ($res !== false) {
    	            if (trim($res) == 'OK') {
						Log::debug(_t("Homepage: Received OK. Taking homepage content from the local file"));
						if (is_writable($welcomePath)) {
							Log::debug(_t("Homepage: Updated local guide page mtime"));
							touch($welcomePath);
						}
						
    	                $welcomeContent = file_get_contents($welcomePath);
    	            } else {
						Log::debug(_t("Homepage: Storing guide page to a local file %s", array($welcomePath)));
    	                file_put_contents($welcomePath, $res);
    	                $welcomeContent = $res;
    	            }
    	        } else {
					Log::debug(_t("Homepage: Failed to get the homepage from zend.com. Taking the contents from a local file"));
					// use the old content if zend.com is unreachable
					$welcomeContent = file_get_contents($welcomePath);
    	        }
    	    } else {
				Log::debug(_t("Homepage: Taking the contents of the homepage from a local file"));
    	        $welcomeContent = file_get_contents($welcomePath);
    	    }
	    } catch (\Exception $e) {
	        $welcomeContent = '';
	    }
	     
	    return $welcomeContent;
	}
}