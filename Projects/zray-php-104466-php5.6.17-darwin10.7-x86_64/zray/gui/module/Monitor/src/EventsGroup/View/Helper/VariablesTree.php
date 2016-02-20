<?php
namespace EventsGroup\View\Helper;

use Zend\View\Helper\AbstractHelper;

class VariablesTree extends AbstractHelper {
	
	/**
	 * Taking a variable in PHP and convert it to a highlighted readable html string
	 *
	 * @param mixed $var
	 * @return string
	 */
	public function __invoke($var) {
		// Convert the mixed variable to a readable string
		$code = var_export($var, true);
	
		// If the variable contained object, var_export will add __set_state callbacks that need to be removed
		$code = str_replace('::__set_state(array(', ' Object (', $code);
		$code = str_replace('))', ')', $code);
	
		// set color scheme
		ini_set ( 'highlight.string', 'green' );
		ini_set ( 'highlight.comment', 'gray' );
		ini_set ( 'highlight.keyword', 'blue' );
		ini_set ( 'highlight.bg', 'white' );
		ini_set ( 'highlight.default', 'black' );
		ini_set ( 'highlight.html', 'white' );
		
		// "Paint" the code in syntax highlighting colors
		//	Using highlight_string requires the php tag in the code itsef, the php tag removed after the highlighting
		$code = highlight_string('<?php ' . $code, true);
		$code = str_replace('&lt;?php&nbsp;', '', $code);
	
		return $code;
	}
}

