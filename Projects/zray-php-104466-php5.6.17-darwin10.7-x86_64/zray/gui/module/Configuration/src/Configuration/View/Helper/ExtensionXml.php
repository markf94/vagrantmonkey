<?php
namespace Configuration\View\Helper;

use Configuration\ExtensionContainer,
Configuration\View\Helper\DaemonXml;

class ExtensionXml extends DaemonXml {
	
	/**
	 * @param \Configuration\ExtensionContainer $extension
	 * @return string
	 */
	public function __invoke($extension) {
		$status = $extension->getStatus();
		
		if (! $this->getView()->isAllowed('data:components', $extension->getName())) {
			$status = 'Unsupported';
		}
		
		return <<<XML

<extension>
	<name>{$this->getView()->escapeHtml($extension->getName())}</name>
	<version>{$this->getView()->escapeHtml($extension->getVersion())}</version>
	<type>{$extension->getType()}</type>
	<status>{$status}</status>
	<loaded>{$this->getView()->escapeHtml($extension->getIsLoaded())}</loaded>
	<installed>{$this->getView()->escapeHtml($extension->getIsInstalled())}</installed>
	<builtIn>{$extension->getBuiltIn()}</builtIn>
	<dummy>{$extension->getDummy()}</dummy>
	<restartRequired>{$extension->getRestartRequired()}</restartRequired>
	<shortDescription><![CDATA[{$extension->getShortDescription()}]]></shortDescription>
	<longDescription><![CDATA[{$extension->getLongDescription()}]]></longDescription>
	<messageList>{$this->getMessageList($extension)}</messageList>
</extension>


XML;
	}	
}
