<?php
namespace PageCache\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Module,
Deployment\Model;

class RuleDataXml extends AbstractHelper {
	
	/**
	 * @param \PageCache\Rule $rule
	 * @return string
	 */
	public function __invoke($rule) { 
		
		$conditions = $rule->getConditions();
		
		$xml = "<conditionsType>" . $rule->getConditionsType() . "</conditionsType>" . PHP_EOL;
		$xml .= "<conditions>";
		foreach ($conditions as $cond) { /** @var $cond \PageCache\Model\RuleCondition */
		
			$xml .= "
			<condition>
				<global>{$cond->getSuperGlobal()}</global>
				<element>{$cond->getElement()}</element>
				<type>{$cond->getMatchType()}</type>
				<value>{$cond->getValue()}</value>
			</condition>" . PHP_EOL;
			
		}
		$xml .= "</conditions>";

		$splitConditions = $rule->getSplitBy();
		$splitXml = "<splitBy>";
		foreach ($splitConditions as $cond) { /** @var $cond \PageCache\Model\SplitByCondition */
		
			$splitXml .= "
			<splitCondition>
			<global>{$cond->getSuperGlobal()}</global>
			<element>{$cond->getElement()}</element>
			</splitCondition>" . PHP_EOL;
				
		}
		$splitXml .= "</splitBy>";
		
		
		$compress = $rule->getCompress()?"yes":"no";
		return <<<RULEXML
	    <ruleData>
	    		<ruleId>{$rule->getId()}</ruleId>
	    		$xml
				$splitXml
				<lifetime>{$rule->getLifetime()}</lifetime>
				<compress>{$compress}</compress>	
	  	</ruleData>
		
RULEXML;
	}
}

