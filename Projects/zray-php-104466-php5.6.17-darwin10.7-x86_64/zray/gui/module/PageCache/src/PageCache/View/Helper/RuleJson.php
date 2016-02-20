<?php
namespace PageCache\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class RuleJson extends AbstractHelper {
	
	/**
	 * @param array $job
	 * @return string
	 */
	public function __invoke($rule) { /* @var $rule \PageCache\Rule */
		
		$path = $rule->getUrlPath();
		if (strstr($path, "/") === 0 && $rule->getMatchType() == \PageCache\Rule::MATCH_EXACT) {
			$path = substr($path, 1);
		}
		
		return $this->getView()->json(array(
	    	"id" => (int) $rule->getId(),
			"urlScheme" => $rule->getUrlScheme(),
			"urlHost" => htmlspecialchars($rule->getUrlHost()),
			"urlPath" => $path,
			"type" => $rule->getMatchType(),
			"application" => $rule->getAppName(),
			"applicationId" => (int) $rule->getAppId(),
			"name" => $rule->getName(),			
			"lifetime" => $rule->getLifetime(),				
	    ));
	}
}

