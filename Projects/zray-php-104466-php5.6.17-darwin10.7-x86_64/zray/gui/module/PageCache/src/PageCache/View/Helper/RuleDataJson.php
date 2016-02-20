<?php
namespace PageCache\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class RuleDataJson extends AbstractHelper {
	
	/**
	 * @param \PageCache\Rule $rule
	 * @return string
	 */
	public function __invoke($rule) { 
		
		$conds = array();
		foreach ($rule->getConditions() as $cond) {
			$conds[] = $cond->toArray();
		}
		
		$split = array();
		foreach ($rule->getSplitBy() as $cond) {
			$split[] = $cond->toArray();
		}
			
		return $this->getView()->json(array(
	    		"ruleId" => (int) $rule->getId(),
				"conditionsType" => $rule->getConditionsType(),
				"conditions" => $conds,
				"splitBy" => $split,
				"lifetime" => (int) $rule->getLifetime(),
				"compress" => $rule->getCompress()	
	    ));
	}
}

