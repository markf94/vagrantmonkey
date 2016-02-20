<?php

namespace Plugins\Controller;

use Zend\Http\Header\SetCookie;
use Zend\Json\Json;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZendServer\Log\Log;
use ZendServer\Permissions\AclQuery;
use DeploymentLibrary\Mapper as librariesMapper;
use DeploymentLibrary\Db\Mapper as updatesMapper;

class SetUpdateCookie extends AbstractPlugin {
	
	/**
	 * @var AclQuery
	 */
	private $acl;
	
	/**
	 * @var pluginsModel
	 */
	private $pluginsModel;

    public function __invoke() {
        if ($this->getAcl()->isAllowedEdition('route:PluginsWebAPI', 'pluginDeploy') &&
                (! isset($this->getController()->getRequest()->getCookie()->ZSPLUGINS))) {

            $this->resetCookieContent($this->getPluginsModel());
			
        }
    }

    public function resetCookieContent($pluginsModel, $newPlugin=null) {
        $plugins = $pluginsModel->getMasterPluginsByIds(array(), 'ASC', 'id');
        $pluginsToSave = array();
        foreach ($plugins as $plugin) {
            $pluginsToSave[] = array ('name' => $plugin->getPluginName(), 'version' => $plugin->getPluginVersion());
        }
        
        if ($newPlugin) {
            $pluginsToSave[] = $newPlugin;
        }
        setcookie("ZSPLUGINS", Json::encode($pluginsToSave), time()+(24*3600), '/');
    }
    
    /**
     * @param \ZendServer\Permissions\AclQuery $acl
     */
    public function setAcl($acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return \ZendServer\Permissions\AclQuery
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param \Plugins\Model $pluginsMapper
     */
    public function setPluginsModel($pluginsModel)
    {
        $this->pluginsModel = $pluginsModel;
    }

    /**
     * @return \Plugins\Model
     */
    public function getPluginsModel()
    {
        return $this->pluginsModel;
    }

}