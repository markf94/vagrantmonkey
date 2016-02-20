<?php
namespace Plugins\Controller;

use ZendServer\Mvc\Controller\ActionController, ZendServer\Log\Log;
use Application\Module,
    Plugins\PluginContainer,
    ZendDeployment_PackageMetaData,
    ZendServer\Exception;

class IndexController extends ActionController
{

    public function indexAction()
    {
		// check preloaded plugins
		if (isZrayStandaloneEnv()) {
			// generally check if there are plugins in the plugins folder that aren't registered in the DB.
			// if there are some, manually add them to the DB
			$this->checkPreloadedPlugins();
		}
		
		/* @var \Plugins\Model */
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  
		/* @var \Plugins\Db\Mapper */
        $pluginsMapper = $this->getLocator()->get('Plugins\Db\Mapper');
        
        try {
            $plugins = $pluginsModel->getMasterPluginsByIds(array(), 'Asc', 'name');
        } catch (\Exception $e) {
            throw new \WebAPI\Exception($e->getMessage(),  \WebAPI\Exception::INVALID_SERVER_RESPONSE, $e);
        }
        
        $configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
        $plugins = $pluginsModel->updatePrerequisitesIsValidFlags($plugins, $configurationContainer);
        
        $mapper = $this->getLocator()->get('Plugins\Db\UpdatesMapper'); /* @var $mapper \Plugins\Db\UpdatesMapper */
        $updates = $mapper->getUpdates();
        $updates = $pluginsModel->removeBrokenUpdates($updates, $configurationContainer);
        
        $config = $this->getServiceLocator()->get('Configuration');
		$viewParams = array(
            'pageTitle' => 'Manage Plugins',
            'pageTitleDesc' => '',
           
            'plugins' => $plugins,
            'updates' => $updates,
            
            'storeUpdateApiUrl' => $config['plugins']['zend_gui']['storeApiUrl'] . 'update.php?hidden',
            'storeListApiUrl' => $config['plugins']['zend_gui']['storeApiUrl'] . 'list.php',
            'storeDownloadApiUrl' => $config['plugins']['zend_gui']['storeApiUrl'] . 'download.php',
            'serverInfo' => $this->ServerInfo()->get(),
        );
		
		return $viewParams;
    }
	
	/**
	 * @brief for (!)Z-Ray standalone(!), since we don't have an installation process, the preloaded plugins
	 * are located in their place, but not in the database.
	 * @return bool
	 */
	protected function checkPreloadedPlugins() {
		
		// get plugins in DB
		$pluginsInDb = $this->getPluginsNamesFromDb();
		
		// get plugins in the folder
		$pluginsInFS = $this->getPluginsFromPluginsFolder();
		if ($pluginsInFS === false) {
			return false;
		}
		$pluginsNamesInFS = array_keys($pluginsInFS);
		
		// check which plugins need to be installed
		$pluginsToInstall = array_diff($pluginsNamesInFS, $pluginsInDb);
		if (empty($pluginsToInstall)) {
			// no plugins to install. Everything is in sync.
			return true;
		}
		
		/* @var \Plugins\Model */
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  
		
		// install plugins
		foreach($pluginsToInstall as $pluginToInstall) {
			// install the plugin (manually insert rows to the DB)
			$deployResult = $pluginsModel->manuallyDeployPluginFolder(
				$pluginsInFS[$pluginToInstall]['deploymentObject'], 
				$pluginsInFS[$pluginToInstall]['path']
			);
			
			// remove "deployment.json" from the folder to skip checking this folder next time
			if ($deployResult !== false) {
				$deploymentJsonPath = $pluginsInFS[$pluginToInstall]['path'].DIRECTORY_SEPARATOR.'deployment.json';
				$unlinkResult = @unlink($deploymentJsonPath);
				if ($unlinkResult === false) {
					Log::warn('Plugins sync - cannot delete "'.$deploymentJsonPath.'" file');
				}
			}
		}
		
		return true;
	}
	
	/**
	 * @brief Read "deployment.json" file in every plugin folder on the FS, and return list of names
	 * @return array|false
	 */
	protected function getPluginsFromPluginsFolder() {
		
		// scan folder and get list of plugins in the folder (read deployment.json file in every plugin folder)
		$pluginsFolder = getCfgVar('zend.data_dir').DIRECTORY_SEPARATOR.'plugins';
		if (!is_dir($pluginsFolder) || !is_readable($pluginsFolder)) {
			Log::warn(_t('Plugins folder "%s" does not exist or does not have enough permissions)', array($pluginsFolder)));
			return false;
		}
		
		$pluginsInFS = array();
		foreach (scandir($pluginsFolder) as $pluginFolderName) {
			if ($pluginFolderName == '.' || $pluginFolderName == '..') {
				continue;
			}
			
			// check plugin folder
			$pluginPath = $pluginsFolder.DIRECTORY_SEPARATOR.$pluginFolderName;
			if (!is_readable($pluginPath)) {
				Log::warn(_t('Plugin folder "%s" is not accessible or does not have enough permissions', array($pluginPath)));
				continue;
			}
			
			// check deployment.json 
			$deploymentJsonPath = $pluginPath.DIRECTORY_SEPARATOR.'deployment.json';
			if (!file_exists($deploymentJsonPath)) {
				// if the file "deployment.json" does not exist, it means that the plugins was either already synchronized, 
				// or installed manually by the user from the GUI
				continue;
			}
			
			// check if the file is accessible
			if (!is_readable($deploymentJsonPath)) {
				Log::warn(_t('Plugin configuration file "%s" is not accessible or does not enough read permissions', array($deploymentJsonPath)));
				continue;
			}
			
			// read deployment.json
			$deploymentJsonContent = file_get_contents($deploymentJsonPath);
			if (!$deploymentJsonContent || empty($deploymentJsonContent)) {
				Log::warn(_t('Plugin configuration file "%s" is empty or could not be read', array($deploymentJsonPath)));
				continue;
			}
			
			// decode deployment.json
			$pluginDeploymentJson = json_decode($deploymentJsonContent, $__assoc = true);
			if (!$pluginDeploymentJson || !is_array($pluginDeploymentJson)) {
				Log::warn(_t('Plugin configuration file "%s" has bad JSON format', array($deploymentJsonPath)));
				continue;
			}
			
			// check "name" key
			if (!isset($pluginDeploymentJson['name']) || empty($pluginDeploymentJson['name'])) {
				Log::warn(_t('Plugin configuration file "%s" has no "name" parameter', array($deploymentJsonPath)));
				continue;
			}
			
			$pluginsInFS[$pluginDeploymentJson['name']] = array(
				'deploymentObject' => $pluginDeploymentJson,
				'path' => $pluginPath,
			);
		}
		
		return $pluginsInFS;
	}
	
	/**
	 * @brief get with names of deployed plugins
	 * @return array
	 */
	protected function getPluginsNamesFromDb() {
		// Z-Ray standalone has no cluster, thus servers list is one, hard coded, "0"
		$serversList = array(0);
		
		/* @var \Plugins\Model */
		$pluginsModel = $this->getLocator()->get('Plugins\Model');  
		
		// get the list from the DB
		$pluginsList = $pluginsModel->getPluginsList($serversList);
		$pluginsInDb = array_map(function($el) {
			return $el['name'];
		}, $pluginsList);
		
		return $pluginsInDb;
	}

	
	/**
	 * @brief same as indexAction but for Z-Ray standalone version
	 * @return  
	 */
	protected function zrayStandaloneIndexAction() {
		$config = $this->getServiceLocator()->get('Configuration');
		
		/* @var \Plugins\Mapper\FileSystem */
		$pluginsMapper = $this->getServiceLocator()->get('pluginsFileSystemMapper');
		
		// get list of plugins from the filesystem
		$plugins = $pluginsMapper->getPluginsList();
		if ($plugins === false) {
            throw new \WebAPI\Exception($pluginsMapper->getError(), \WebAPI\Exception::INVALID_SERVER_RESPONSE);
		}
		
		return array(
			'pageTitle' => 'Manage Plugins',
			'pageTitleDesc' => '',
			
			'plugins' => $pluginsMapper->getPluginsList(),
			'updates' => array(), // @TODO check for updates
			
			'storeUpdateApiUrl' => $config['plugins']['zend_gui']['storeApiUrl'] . 'update.php',
			'storeListApiUrl' => $config['plugins']['zend_gui']['storeApiUrl'] . 'list.php',
			'storeDownloadApiUrl' => $config['plugins']['zend_gui']['storeApiUrl'] . 'download.php',
			
			'serverInfo' => $this->ServerInfo()->get(),
		);
	}
    
    public function pluginIconAction() {
        $params = $this->getRequest()->getQuery(); /* @var $request \Zend\Http\PhpEnvironment\Request */
        $id = isset($params['id']) ? $params['id'] : '';
        if (empty($id)) {
            header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
            exit;
        }
    
        try {
            $existingPlugin = $this->getPluginsMapper()->getPluginById($params['id']);
            if (!($existingPlugin instanceof PluginContainer)) {
                throw new \WebAPI\Exception(_t('Plugin \'%s\' does not exist', array($params['plugin'])), \WebAPI\Exception::NO_SUCH_PLUGIN);
            }
            
            $image = $existingPlugin->getPluginLogo();
        } catch (Exception $e) {
            header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
            exit;
        }
    
        if (empty($image)) {
            header('Location: ' . Module::config()->baseUrl . '/images/deployment-default-logo.png');
            exit;
        }
        
        header('content-type: image/png');
        echo base64_decode($image);
        exit;
    }
    
    public function getPluginPrerequisitesAction() {
        $params = $this->getParameters(array('plugin' => array(), 'prerequisites' => ''));
        $this->validateMandatoryParameters($params, array('plugin'));
         
        $viewModel = new \Zend\View\Model\ViewModel ();
        $viewModel->setTerminal ( true );
    
        $metadataJson = null;
        $isValid = true;
        if (! empty($params['prerequisites'])) {
            $result = json_decode($params['prerequisites']);
            if (json_last_error() === 0) {
                $metadataJson = '{"dependencies": ' . $params['prerequisites'] . '}';
            }
        } else {
            $pluginId = $params['plugin'];
            $model = $this->getLocator()->get('Deployment\Model');
            $app = $model->getApplicationById($pluginId);
             
            $existingPlugin = $this->getPluginsMapper()->getPluginById($params['plugin']);
             
            if (!($existingPlugin instanceof PluginContainer)) {
                throw new Exception(_t('Plugin \'%s\' does not exist', array($params['plugin'])));
            }
            $metadataJson = $existingPlugin->getPackageMetadataJson();
        }
        
        $prerequisites = '';
        $message = false;
        $metadata =  new ZendDeployment_PackageMetaData();
        if (! is_null($metadataJson)) {
            $metadata->setPackageDescriptor($metadataJson);
             
            if ($metadata instanceof \ZendDeployment_PackageMetaData_Interface) {
				// get the XML file with prerequisites
    	        $prerequisites = $metadata->getPrerequisites();
				
    	        // remove <?xmlversion="1.0"? from the xml string if exists
    	        $prerequisites = substr($prerequisites, strpos($prerequisites, '?'.'>') + 2);
    	        $prerequisites = trim($prerequisites);
    	    }
                   
            $configuration = \Prerequisites\Validator\Generator::getConfiguration($prerequisites);
			
			if (!isZrayStandaloneEnv()) {
				$configurationContainer = $this->getLocator()->get('ZendServer\Configuration\Container');
				$configurationContainer->createConfigurationSnapshot(
					$configuration->getGenerator()->getDirectives(),
					$configuration->getGenerator()->getExtensions(),
					$configuration->getGenerator()->getLibraries(),
					$configuration->getGenerator()->needServerData());
				$isValid = false;
				if ($configuration->isValid($configurationContainer)) {
					$isValid = true;
				}
			} else {
				// @TODO implement prerequisites validation
				$isValid = true;
			}
            $message = $configuration->getMessages();
        }
        
        $viewModel->isValid = $isValid;
        $viewModel->messages =  $message;
         
        return $viewModel;
    }
}