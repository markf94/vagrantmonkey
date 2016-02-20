<?php 
/**
 * Mapper agains plugins, that works directly with the file system. (without DB at all(!))
 */
 
namespace Plugins\Mapper;

use \ZendServer\Log\Log;
use \Plugins\PluginContainer;

class FileSystem {

	/**
	 * @var string
	 */
	protected $error = null;
	
	/**
	 * @param string $error
	 */
	public function setError($error) {
		if (!is_string($error)) {
			$error = null;
		}
		
		$this->error = $error;
	}
	
	/**
	 * @brief Clear the error
	 */
	protected function clearError() {
		$this->error = null;
	}
	
	/**
	 * @return string|null
	 */
	public function getError() {
		return $this->error;
	}
	
	/**
	 * @brief get list of installed plugins. The function reads "deployment.json" files from the folders
	 * @return array|false
	 */
	public function getPluginsList() {
		static $pluginId = 1;
		
		$this->clearError();
		
		$pluginsFolder = $this->getPluginsDir();
		
		if (!is_dir($pluginsFolder) || !is_readable($pluginsFolder)) {
			$this->setError('Plugins folder "'.$pluginsFolder.'" is not accessible');
			return false;
		}
		
		$pluginsList = array();
		
		foreach (scandir($pluginsFolder) as $file) {
			if ($file == '.' || $file == '..') continue;
			$pluginFolder = $pluginsFolder . DIRECTORY_SEPARATOR . $file;
			
			// readable?
			if (!is_readable($pluginFolder)) {
				Log::warn("Plugin folder '{$pluginFolder}' is not accessible, thus not returned in plugins list");
				continue;
			}
			
			// deployment.json exists?
			$packageJsonFilePath = $pluginFolder . DIRECTORY_SEPARATOR . 'deployment.json';
			if (!file_exists($packageJsonFilePath)) {
				Log::warn("Plugin folder '{$pluginFolder}' does not have 'deployment.json', thus not returned in plugins list");
				continue;
			}
			
			$jsonFileContents = file_get_contents($packageJsonFilePath);
			if ($jsonFileContents === false) {
				Log::warn("Cannot get contents of 'deployment.json' in folder '{$pluginFolder}', thus not returned in plugins list");
				continue;
			}
			
			// parse the JSON file
			$pluginDataArray = json_decode($jsonFileContents, $assoc = true);
			if ($pluginDataArray === false) {
				Log::warn("The file 'deployment.json' (in folder '{$pluginFolder}') contains not valid JSON format, thus not returned in plugins list");
				continue;
			}
			// prepare the array for the container
			$pluginMetaData = new \stdclass;
			$pluginMetaData->display_name = $pluginDataArray['display_name'];
			$pluginMetaData->type = $pluginDataArray['type'];
			
			$pluginData = array(
				'pluginId' => $pluginId++,
				'pluginName' => $pluginDataArray['name'],
				'unique_plugin_id' => rand(100000, 999999),
				'logo' => $pluginDataArray['logo'],
				
				'type_zray' => array_search('zray', $pluginDataArray['type']) !== false,
				'type_route' => array_search('route', $pluginDataArray['type']) !== false,
				'type_zs_ui' => array_search('ui', $pluginDataArray['type']) !== false,
				
				'status' => 'STAGED',
				'message' => '',
				'description' => '',
				
				// 'versions' => array('creation_time' => '...',),
				'version' => $pluginDataArray['version'],
				'install_path' => $pluginFolder,
				'prerequisitesIsValid' => true, // @TODO check
				
				'packageMetadata' => $pluginMetaData,
				'packageMetadataJson' => json_encode((array) $pluginMetaData),
			);
			
			// add the plugin to the list
			$pluginsList[] = new PluginContainer($pluginData);
		}
		
		return $pluginsList;
	}
	
	/**
	 * @brief get plugins folder path
	 * @return string
	 */
	protected function getPluginsDir() {
		return getCfgVar('zend.data_dir').DIRECTORY_SEPARATOR.'plugins';
	}
	
}