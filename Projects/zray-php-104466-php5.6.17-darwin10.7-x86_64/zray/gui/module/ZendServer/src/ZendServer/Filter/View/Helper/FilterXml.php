<?php
namespace ZendServer\Filter\View\Helper;

use ZendServer\Filter\Filter;
use Zend\View\Helper\AbstractHelper;

class FilterXml extends AbstractHelper {
    function __invoke(Filter $filter) {
        return <<<XML
			<filter>
				<id>{$filter->getId()}</id>
				<name><![CDATA[{$filter->getName()}]]></name>
				<type>{$filter->getType()}</type>
				<custom>{$filter->getCustom()}</custom>
				<data>{$this->getView()->json($filter->getData())}</data>
			</filter>
XML;
    }
}