<?php
namespace Configuration\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Configuration\DirectiveContainer;

class DirectiveXml extends AbstractHelper {
	/**
	 * @param integer $severity
	 * @return string
	 */
	public function __invoke(DirectiveContainer $directive) {
		
		$units = ($directive->getUnits()) ? '<units><![CDATA[' .$directive->getUnits() . ']]></units>' : '';
		$listValues = '<listValues>';
		if ($directive->getlistValues()) {
			foreach ((array)$directive->getlistValues() as $key=>$value) {
				$listValues .= '<listValue><name><![CDATA[' .$key . ']]></name><value><![CDATA[' .$value . ']]></value></listValue>';
			}
		}
		$listValues .= '</listValues>';
		// output sanitation for ZSRV-7503
		$fileValue = preg_replace('/^"(.+)"$/', '$1', $directive->getFileValue());
		$defaultValue = preg_replace('/^"(.+)"$/', '$1', $directive->getDefaultValue());
		$previousValue = preg_replace('/^"(.+)"$/', '$1', $directive->getPreviousValue());
		return <<<XML
			
<directive>
		       <name>{$this->getView()->escapeHtml($directive->getName())}</name>
		       <context>{$directive->getContext()}</context>
		       <contextName>{$directive->getContextName()}</contextName>
		       <section>{$directive->getSection()}</section>
		       <fileValue><![CDATA[{$fileValue}]]></fileValue>
		       <defaultValue><![CDATA[{$defaultValue}]]></defaultValue>
		       <previousValue><![CDATA[{$previousValue}]]></previousValue>
		       <description><![CDATA[{$directive->getDescription()}]]></description>
		       <type>{$this->getView()->escapeHtml($directive->getType())}</type>
		       {$units}
		       {$listValues}
     </directive>
XML;
	}
	
}

