<?php

namespace ZendServer\View\Renderer;


use ZendServer\Permissions\AclQuerierInterface;

use ZendServer\Permissions\AclQuery;

use Zend\View\Renderer\PhpRenderer as BasePhpRenderer;

class PhpRenderer extends BasePhpRenderer implements AclQuerierInterface {
    /**
     * @var AclQuery
     */
    protected $acl;
    
    /**
     *
     * @var string
     */
    protected $role;
    
    public static function fromRenderer(BasePhpRenderer $oldRenderer) {
    	$renderer = new self();
    	$renderer->setHelperPluginManager($oldRenderer->getHelperPluginManager());
    	$renderer->setResolver($oldRenderer->resolver());
    	return $renderer;
    }
    
    /**
	 * 
	 * @param AclQuery $acl
	 */
	public function setAcl(AclQuery $acl) {
	    $this->acl = $acl;
	}
	
	/**
	 * 
	 * @return AclQuery
	 */
	public function getAcl() {
	    return $this->acl;
	}
	
	/**
	 * 
	 * @param string $controller
	 * @param string $action
	 * @return boolean
	 */
	public function isAllowed($controller, $action='') {
		try {
	    	return $this->getAcl()->isAllowed($controller, $action);
		} catch (\Exception $e) {
			return false;
		}
	}
	
	public function isAllowedIdentity($resource = null, $privilege = null) {
		try {
			return $this->getAcl()->isAllowedIdentity($resource, $privilege);
		} catch (\Exception $e) {
			return false;
		}
	}
	
	public function isAllowedEdition($resource = null, $privilege = null) {
		try {
			return $this->getAcl()->isAllowedEdition($resource, $privilege);
		} catch (\Exception $e) {
			return false;
		}
	}
	
}