<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\SqlQueryContainer;

class SqlQueryFormat extends AbstractHelper {
	
	public function __invoke($query) {
		if (strpos($query, "<zend-too-large-value>") !== false) {
			return str_replace("<zend-too-large-value>", '...<Truncated large data>', $query);
		} elseif (strpos($query, "'<zend-resource-") !== false) {
			return preg_replace_callback("('\\<zend\\-resource\\-.+?\\>')", function($matches){
				return ucwords(str_replace(array('zend-', '-','\''), array('PHP ', ' ', ''), $matches[0]));
			}, $query);
		} else {
			return str_replace(array(
					"'<zend-null>'",
					"'<zend-resource>'",
					"'<zend-array>'",
					"'<zend-object>'",
					"'<zend-callable>'",
					"'<zend-constant>'",
					"'<zend-constant-array>'",
					"'<zend-binary-value>'",
			), array(
					'NULL',
					'<PHP Resource>',
					'<PHP Array>',
					'<PHP Object>',
					'<PHP Callable>',
					'<PHP Constant>',
					'<PHP Constant Array>',
					'<Binary Value>',
			), $query);
		}
		return $query;
	}
}