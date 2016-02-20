<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Navigation
 */

namespace ZendServer\Navigation\Service;

use ZendServer\Permissions\AclQuerierInterface;
use ZendServer\Permissions\AclQuery;
use ZendServer\Log\Log;
use Zend\Navigation\Service\AbstractNavigationFactory;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface as Router;

/**
 * Default navigation factory.
 *
 * @category  Zend
 * @package   Zend_Navigation
 */
class DefaultNavigationFactory extends AbstractNavigationFactory implements AclQuerierInterface
{
	/**
	 * @var AclQuery
	 */
	private $acl;
	
	/**
	 * @var array
	 */
	protected $filterPages = array();
    /**
     * @return string
     */
    protected function getName()
    {
        return 'default';
    }
	/* (non-PHPdoc)
	 * @see \ZendServer\Permissions\AclQuerierInterface::setAcl()
	 */
	public function setAcl(\ZendServer\Permissions\AclQuery $acl) {
		$this->acl = $acl;
		return $this;
	}
	
	/**
	 * @param array $filterPages
	 * @return \ZendServer\Navigation\Service\DefaultNavigationFactory
	 */
	public function setFilterPages(array $filterPages) {
		$this->filterPages = $filterPages;
		return $this;
	}

	/* (non-PHPdoc)
	 * @see \Zend\Navigation\Service\AbstractNavigationFactory::getPagesFromConfig()
	 */
	protected function injectComponents(array $pages, RouteMatch $routeMatch = null, Router $router = null, $request = null) {
		foreach ($pages as $key => &$page) {
			$hasMvc = isset($page['action']) || isset($page['controller']) || isset($page['route']);
			if ($hasMvc) {
				/**** ACL Check addition ***/
				$action = isset($page['action']) ? $page['action'] : null;
				if (isset($page['controller'])) {
					foreach ($this->filterPages as $filterPage) {
						if ($page['controller'] == $filterPage['controller']) {
							unset($pages[$key]);
							continue 2;
						}
					}
					$resource = "route:{$page['controller']}";
					
					if ($this->acl->hasResource($resource) && (! $this->acl->isAllowedIdentity($resource, $action))) {
						/// clean out completely from the navigation tree
						unset($pages[$key]);
						continue;
					}
				}
				/**** ACL Check addition ***/
				
				if (!isset($page['routeMatch']) && $routeMatch) {
					$page['routeMatch'] = $routeMatch;
				}
				if (!isset($page['router'])) {
					$page['router'] = $router;
				}
			}
		
			if (isset($page['pages'])) {
				$page['pages'] = $this->injectComponents($page['pages'], $routeMatch, $router);
			}
		}
		return $pages;
	}
}
