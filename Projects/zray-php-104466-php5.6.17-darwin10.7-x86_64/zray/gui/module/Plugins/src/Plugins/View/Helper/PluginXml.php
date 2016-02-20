<?php
namespace Plugins\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Plugins\PluginContainer;

class PluginXml extends AbstractHelper {

	public function __invoke(PluginContainer $plugin) {
	    $type = implode(', ', $plugin->getType());
	    
		return <<<XML
			
<plugin>
		       <id>{$plugin->getPluginId()}</id>
		       <name><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginName())}]]></name>
		       <displayName><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginDisplayName())}]]></displayName>
		       <version><![CDATA[{$plugin->getPluginVersion()}]]></version>
 		       <unique_id><![CDATA[{$plugin->getUniquePluginId()}]]></unique_id>      		
		       <type><![CDATA[{$type}]]></type>		
		       <status><![CDATA[{$plugin->getMasterStatus()}]]></status>    		
		       <message><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginMessage())}]]></message>       		
		       <description><![CDATA[{$this->getView()->escapeHtml($plugin->getPluginDescription())}]]></description>       		
		       <creationTime>{$this->getView()->webapidate($plugin->getPluginCreationTime())}</creationTime>     		
		       <creationTimeTimestamp>{$plugin->getPluginCreationTime()}</creationTimeTimestamp>
</plugin>
XML;
		
	}
}

