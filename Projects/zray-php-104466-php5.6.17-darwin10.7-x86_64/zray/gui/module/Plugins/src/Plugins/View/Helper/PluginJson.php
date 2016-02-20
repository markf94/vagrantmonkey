<?php
namespace Plugins\View\Helper;

use Zend\View\Helper\AbstractHelper,
    ZendServer\Log\Log,
	Plugins\PluginContainer;

class PluginJson extends AbstractHelper {

	public function __invoke(PluginContainer $plugin, $updates) {

	    $download_id = '';
	    $needs_update = -2;
	    $storePluginId = '-1';
	    if (isset($updates[$plugin->getPluginName()])) {
	       $extra = json_decode($updates[$plugin->getPluginName()]['EXTRA_DATA'], true);
	       $download_id = $extra['download_id'];
	       $needs_update = $extra['needs_update'];
	       $storePluginId = $extra['id'];
	    }
	       
		$pluginDataArray = array(
				'id'				    => $plugin->getPluginId(),
				'name'                  => $plugin->getPluginName(),
		        'displayName'           => $plugin->getPluginDisplayName(),
				'unique_id'	            => $plugin->getUniquePluginId(),
				'version'               => $plugin->getPluginVersion(),
				'type'                  => $plugin->getType(),
				'message'               => $plugin->getPluginMessage(),
				'status' 			    => $plugin->getMasterStatus(),				
				'creationTime'          => $this->getView()->webapidate($plugin->getPluginCreationTime()),
				'creationTimeTimestamp' => $plugin->getPluginCreationTime(),
				'logo'                  => $plugin->getPluginLogo(),
				'description' 			=> $plugin->getPluginDescription(),
				'prerequisitesIsValid' 	=> $plugin->getPrerequisitesIsValidFlag(),
		        'needUpdate'            => $needs_update,
		        'download_id'           => $download_id,
		        'storePluginId'         => $storePluginId,
		        'updateVersion'         => isset($updates[$plugin->getPluginName()]) ? $updates[$plugin->getPluginName()]['VERSION'] : '',
		        
		);
		
		return $this->getView()->json($pluginDataArray, array());
	}
}