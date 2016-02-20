<?php
namespace PageCache\View\Helper;

use Zend\View\Helper\AbstractHelper,
JobQueue\Model\RecurringJob;

class RuleXml extends AbstractHelper {
	
	/**
	 * @param \PageCache\Model\Rule $rule
	 * @return string
	 */
	public function __invoke($rule) { //@todo - to be completed
		
		$path = $rule->getUrlPath();
		if (strstr($path, "/") === 0 && $rule->getMatchType() == \PageCache\Rule::MATCH_EXACT) {
			$path = substr($path, 1);
		}
		
		return <<<RULEXML
	    <rule>
			<id>{$rule->getId()}</id>
			<urlScheme>{$rule->getUrlScheme()}</urlScheme>
			<urlHost><![CDATA[{$rule->getUrlHost()}]]></urlHost>
			<urlPath><![CDATA[$path]]></urlPath>
			<type>{$rule->getMatchType()}</type>
			<application><![CDATA[{$this->getView()->escapeHtml($rule->getAppName())}]]></application>
			<applicationId>{$rule->getAppId()}</applicationId>
			<name><![CDATA[{$this->getView()->escapeHtml($rule->getName())}]]></name>
			<lifetime>{$rule->getLifetime()}</lifetime>        		
		</rule>
		
RULEXML;

	}
}

