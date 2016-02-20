<?php
namespace MonitorRules\View\Helper;

use Zend\View\Helper\AbstractHelper,
MonitorRules;

class RuleXml extends AbstractHelper {
	/**
	 * @param \MonitorRules\Rule $rule
	 * @return string
	 */
	public function __invoke(\MonitorRules\Rule $rule) {
		return <<<XML
			<rule>
				<id>{$this->getView()->escapeHtml($rule->getId())}</id>
				<parentId>{$this->getView()->escapeHtml($rule->getParentId())}</parentId>
				<appId><![CDATA[{$rule->getAppId()}]]></appId>
				<name>{$this->getView()->escapeHtml($rule->getName())}</name>
				<enabled>{$this->getView()->escapeHtml($rule->getEnabled())}</enabled>
				<type>{$this->getView()->escapeHtml($rule->getType())}</type>
				<url><![CDATA[{$rule->getUrl()}]]></url>
				<creator>{$this->getView()->escapeHtml($rule->getCreator())}</creator>
				<description>{$this->getView()->escapeHtml($rule->getDescription())}</description>
				<conditions>{$this->getConditions($rule->getConditions())}
				</conditions>
				<triggers>{$this->getTriggers($rule)}
				</triggers>
			</rule>
				
XML;
	}
	
	protected function getTriggers(\MonitorRules\Rule $rule) {
	    $triggers = $rule->getTriggers();
	    $triggersXml = '';
	    foreach ($triggers as $trigger) { /* @var $trigger \MonitorRules\Trigger */
	        $triggersXml .= <<<XML
	        
				        <trigger>
			                    <severity>{$trigger->getSeverity()}</severity>
			                    <conditions>{$this->getConditions($trigger->getConditions())}
			                    </conditions>
			                    <actions>{$this->getActions($trigger)}</actions>
			            	</trigger>
XML;
	    }
	    return $triggersXml;
	}
	
	protected function getConditions($conditions) {		
		$conditionsXml = '';
		foreach ($conditions as $condition) { /* @var $condition \MonitorRules\Condition */
			$conditionsXml .= <<<XML
	    
					        <condition>
				                    <operation>{$condition->getOperation()}</operation>
				                    <attribute>{$condition->getAttribute()}</attribute>
				                    <operand>{$condition->getOperand()}</operand>
				            	</condition>
XML;
	    }
	    return $conditionsXml;
	}
	
	protected function getActions(\MonitorRules\Trigger $trigger) {
	    $actions = $trigger->getActions();
	    $actionXml = '';
	    foreach ($actions as $action) { /* @var $action \MonitorRules\Action */
	        $actionXml .= <<<XML
	        <action>
                    <type>{$action->getType()}</type>
                     <url>{$action->getUrl()}</url>                   		
                     <sendToAddress>{$action->getSendToAddress()}</sendToAddress>                   		
                    <tracingDuration>{$action->getTracingDuration()}</tracingDuration>		
	        </action>
XML;
	    }
	    return $actionXml;
	}
}

