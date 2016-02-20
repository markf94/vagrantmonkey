<?php
namespace MonitorRules\View\Helper;

use Zend\View\Helper\AbstractHelper,
MonitorRules
;

class RuleJson extends AbstractHelper {
	/**
	 * @param \MonitorRules\Rule $rule
	 * @return string
	 */
	public function __invoke(\MonitorRules\Rule $rule) {
		
		$ruleArray = array(
					'id' => $rule->getId(), 
					'parentId' => $rule->getParentId(), 
					'appId' => $rule->getAppId(), 
					'name' => $rule->getName(), 
					'enabled' => $rule->getEnabled(), 
					'type' => $rule->getType(), 
					'url' => $rule->getUrl(),
					'creator' => $rule->getCreator(),
					'description' => $rule->getDescription(), 
					'conditions' => $this->getConditions($rule->getConditions()),
					'triggers' => $this->getTriggers($rule)
		);
		
		return $this->getView()->json($ruleArray, array());
	}
	
	protected function getTriggers(\MonitorRules\Rule $rule) {
		$triggers = $rule->getTriggers();
		$triggerArray = array();
		foreach ($triggers as $trigger) { /* @var $trigger \MonitorRules\Trigger */
			$triggerArray[] = array(
					'severity' => $trigger->getSeverity(),
					'conditions' => $this->getConditions($trigger->getConditions()),
					'actions' => $this->getActions($trigger),
			);
		}
		return $triggerArray;
	}
	
	protected function getConditions($conditions) {
		$conditionArray = array();
		foreach ($conditions as $condition) { /* @var $condition \MonitorRules\Condition */
			$conditionArray[] = array(
					'operation' => $condition->getOperation(),
					'attribute' => $condition->getAttribute(),
					'operand' => $condition->getOperand(),
			);
		}
		return $conditionArray;
	}
	
	protected function getActions(\MonitorRules\Trigger $trigger) {
		$actions = $trigger->getActions();
		$actionArray = array();
		foreach ($actions as $action) { /* @var $action \MonitorRules\Action */
			$actionArray[] = array(
				'type' => $action->getType(),
				'url' => $action->getUrl(),
				'sendToAddress' => $action->getSendToAddress(),
				'tracingDuration' => $action->getTracingDuration(),
			);
		}
		return $actionArray;
	}
}

