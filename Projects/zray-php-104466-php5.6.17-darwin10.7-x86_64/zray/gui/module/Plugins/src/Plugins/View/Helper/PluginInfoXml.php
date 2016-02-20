<?php
namespace Plugins\View\Helper;

use Zend\View\Helper\AbstractHelper;
use ZendServer\Log\Log;

class PluginInfoXml extends AbstractHelper {
	
	/**
	 * 
	 * @param \Plugins\PluginContainer $plugin
	 * @param array $servers
	 * @return string
	 */
	public function __invoke(\Plugins\PluginContainer $plugin, array $servers=array()) {            
	    
	    $serversXML = '';
	    if (empty($servers)) {
	    	$serversXML .= "<pluginServer>
                	    	<id>0</id>
                            <status>{$plugin->getMasterStatus()}</status>
                            </pluginServer>";
	    } else {
	    
    	    foreach ($servers as $server => $serverData) {
    	        $serversXML .= "<pluginServer>
    	        <id>$server</id>
    	        <deployedVersion>" . $serverData['version'] . "</deployedVersion>
    <status>" . $this->getView()->appStatus($serverData['status']) . '</status>
    </pluginServer>';
    	    }
	    }
	    
	    $type = implode(', ', $plugin->getType());
	    $description = '';
	    if(property_exists($plugin->getPackageMetadata(), 'description')) {
	       $description = $this->getView()->escapeHtml($plugin->getPackageMetadata()->description);
	    }
         
	    return <<<XML
		
<plugin>
		       <id>{$plugin->getPluginId()}</id>
		       <name><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginName())}]]></name>
		       <displayName><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginDisplayName())}]]></displayName>
		       <version><![CDATA[{$plugin->getPluginVersion()}]]></version>
		       <type><![CDATA[$type]]></type>
		       <status><![CDATA[{$plugin->getMasterStatus()}]]></status>
		       <message><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginMessage())}]]></message>
		       <description><![CDATA[{$description}]]></description>
		       <creationTime>{$this->getView()->webapidate($plugin->getPluginCreationTime())}</creationTime>
		       <creationTimeTimestamp>{$plugin->getPluginCreationTime()}</creationTimeTimestamp>
		       <servers>{$serversXML}</servers>
</plugin>
XML;
	}
	
}

