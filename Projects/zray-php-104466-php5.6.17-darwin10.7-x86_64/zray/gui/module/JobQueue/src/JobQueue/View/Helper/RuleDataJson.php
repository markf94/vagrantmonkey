<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class RuleDataJson extends AbstractHelper {
	
	/**
	 * @param array $rule
	 * @return string
	 */
	public function __invoke($rule) {
	
		$humanSchedule = $this->getView()->JobDetailsCronToHuman($rule['schedule']);
	    return $this->getView()->json(array(
	    		"ruleId" => $rule["id"],
	    		"schedule" => $humanSchedule,	    		
	    		"scheduleCron" => $rule['schedule'],
	    ));
	}
}

