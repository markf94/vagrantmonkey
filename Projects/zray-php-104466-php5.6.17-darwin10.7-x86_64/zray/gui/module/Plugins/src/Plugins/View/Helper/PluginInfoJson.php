<?php
namespace Plugins\View\Helper;

use Zend\View\Helper\AbstractHelper,
    ZendServer\Log\Log,
	Plugins\PluginContainer;

class PluginInfoJson extends AbstractHelper {

	public function __invoke(PluginContainer $plugin) {

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
				'installPath' 			=> $plugin->getInstallPath(),
		);
		
		return $this->getView()->json($pluginDataArray, array());
	}
}