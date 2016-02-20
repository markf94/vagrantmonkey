<?php
namespace Audit\View\Helper;

use Zend\View\Helper\AbstractHelper,
Audit\Container;

class auditExtraData extends AbstractHelper {
    public function __invoke($extraData) {
        $messages = array();
        
        if (is_array($extraData) || $extraData instanceof \Traversable) {
	        foreach ($extraData as $idx => $paramater) {
	            if (is_array($paramater) || is_object($paramater)) {
	                foreach ($paramater as $key => $value) {
	                    
	                    if (is_array($value)) {
	                        foreach ($value as $paramName => $paramVal) {
	                            if ($paramName == 'value') {
	                                $messages[] = $paramVal;
	                            }
	                        }
	                        break;
	                    }
	                    
	                    $messages[] = $key . '=' . $value;
	                }
	            }
	        }
        }
        
        return implode(',', $messages);
    }
}