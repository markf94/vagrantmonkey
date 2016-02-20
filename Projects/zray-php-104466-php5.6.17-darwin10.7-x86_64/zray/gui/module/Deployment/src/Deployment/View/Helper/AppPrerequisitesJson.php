<?php
namespace Deployment\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module,
	ZendServer\Exception;
use Prerequisites\Validator\Generator;

class AppPrerequisitesJson extends AbstractHelper {
	
	/**
	 * @param array $prerequisites
	 * @return string
	 */
	public function __invoke($prerequisites) {
	    $jsonArray = array();
	    foreach ($prerequisites as $element => $messages) {
	        foreach ($messages as $code => $message) {
	        	$keys = array_keys($message);
	        	
	            $jsonArray[$element][] = array(
	                'type' => $element,
	                'name' => $code,
	                'isValid' => false === strpos($keys[0], 'valid') ? 'false' : 'true',
	                'message' => implode(',', array_values($message)),
	            );
	            
	        }
	    }
	    return $this->view->json($jsonArray);
	}	    
}