<?php
namespace Plugins;

use ZendServer\Log\Log;

class PluginContainer {

	/**
	 * @var array
	 */
	protected $pluginData;
	
	public function __construct(array $pluginData) {
		$this->pluginData = $pluginData;
	}
	
	public function toArray() {
		return $this->pluginData;
	}
	
	/**
	 * @return integer
	 */
	public function getPluginId() {
		return (integer) $this->pluginData['pluginId'];
	}

	/**
	 * @return string
	 */
	public function getPluginName() {
	    return $this->pluginData['pluginName'];
	}
	
	/**
	 * @return string
	 */
	public function getPluginDisplayName() {
	    $metaData = $this->getPackageMetadata();
	    return (isset($metaData->display_name) && ! empty($metaData->display_name)) ? $metaData->display_name : $this->getPluginName();
	}
	
	public function getUniquePluginId() {
	    return $this->pluginData['unique_plugin_id'];
	}

	
	public function getPluginLogo() {
	     if (isset($this->pluginData['logo'])) {
	        return $this->pluginData['logo'];
	    }
	    
	    return "";
	}
	
    public function isZrayType() {
	    if (isset($this->pluginData['type_zray'])) {
	        return $this->pluginData['type_zray'];
	    }
	    
	    return 0;
	}
	
	
	public function isRouteType() {
	    if (isset($this->pluginData['type_route'])) {
	        return $this->pluginData['type_route'];
	    }
	    
	    return 0;
	}
	
    public function isUiType() {
	    if (isset($this->pluginData['type_zs_ui'])) {
	        return $this->pluginData['type_zs_ui'];
	    }
	    
	    return 0;
	}
	
	public function getType() {
	    $typeArray = array();
	    
	    if (isset($this->pluginData['packageMetadata'])) {
	        foreach ($this->pluginData['packageMetadata']->type as $type) {
	            $typeArray[] = 'type_' . $type;
	        }
	    }
	    
	    return $typeArray;
	}
	
	public function getPluginStatus() {
	    if (isset($this->pluginData['status'])) {
	        return $this->pluginData['status'];
	    }
	    
	    return "";
	}

	public function getPluginMessage() {
	    if (isset($this->pluginData['message'])) {
	        return $this->pluginData['message'];
	    }
	     
	    return "";
	}

	public function setPluginMessage($message) {
	    $this->pluginData['message'] = $message;
	}
	
	public function getPluginDescription() {
	    if (isset($this->pluginData['description'])) {
	        return $this->pluginData['description'];
	    }
	
	    return "";
	}
	
	public function getPluginCreationTime() {
	    if (isset($this->pluginData['versions'])) {
	        foreach ($this->pluginData['versions'] as $version) {
	            return $version['creationTime'];
	        }
	    }
	
	    return "";
	}
	
	public function getVersions() {
	    return $this->pluginData['versions'];
	}
	
	public function setVersion($version) {
	    $this->pluginData['version'] = $version;
	}
	
	public function getPluginVersion() {
	    return isset($this->pluginData['version']) ? $this->pluginData['version'] : (isset($this->pluginData['packageMetadata']) ? $this->pluginData['packageMetadata']->version : null);
	}
	
	public function getPackageMetadata() {
	    return isset($this->pluginData['packageMetadata']) ? $this->pluginData['packageMetadata'] : null;
	}
	
	public function getPackageMetadataJson() {
	    return isset($this->pluginData['packageMetadataJson']) ? $this->pluginData['packageMetadataJson'] : null;
	}
	
	public function setMasterStatus($status) {
	    $this->pluginData['masterStatus'] = $status;
	}
	
	public function getMasterStatus() {
	    return isset($this->pluginData['masterStatus']) ? $this->pluginData['masterStatus'] : $this->getPluginStatus();
	}
	
	public function setVersions($versions) {
	    $this->pluginData['versions'] = $versions;
	}
	
	public function getErrors() {
	    return isset($this->pluginData['errors']) ? $this->pluginData['errors'] : array();
	}
	
	public function setErrors($errors) {
	    $this->pluginData['errors'] = $errors;
	}
	
	/**
	 * @return string
	 */
	public function getInstallPath() {
	    return isset($this->pluginData['install_path']) ? $this->pluginData['install_path'] : null;
	}
	
	public function setPrerequisitesIsValidFlag($flag) {
	    $this->pluginData['prerequisitesIsValid'] = $flag;
	}
	
	public function getPrerequisitesIsValidFlag() {
	     return isset($this->pluginData['prerequisitesIsValid']) ? $this->pluginData['prerequisitesIsValid'] : null; 
	}
	
}