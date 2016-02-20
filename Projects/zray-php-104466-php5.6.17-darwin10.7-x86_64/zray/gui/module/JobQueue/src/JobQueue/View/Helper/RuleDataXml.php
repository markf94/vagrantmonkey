<?php
namespace JobQueue\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class RuleDataXml extends AbstractHelper {
	
	/**
	 * @param array $rule
	 * @return string
	 */
	public function __invoke($rule) {
		
		$humanSchedule = $this->getView()->JobDetailsCronToHuman($rule['schedule']);
		
	    return <<<RULEXML
	    <ruleDetails>
			<ruleId>{$rule['id']}</ruleId>
			<schedule>$humanSchedule</schedule>
			<scheduleCron>{$rule['schedule']}</scheduleCron>
		</ruleDetails>
RULEXML;
	}
}

