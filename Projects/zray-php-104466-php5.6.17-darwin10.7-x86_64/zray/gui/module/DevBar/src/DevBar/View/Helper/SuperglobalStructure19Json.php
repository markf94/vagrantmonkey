<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\LogEntryContainer;
use DevBar\functionStatsContainer;

class SuperglobalStructure19Json extends AbstractHelper {
	
	/**
	 * @param array $superglobals
	 * @return string
	 */
	public function __invoke($superglobals) {
		return $this->getView()->json($this->parameterMap($superglobals));
	}
	
	/**
	 * 
	 * @param mixed $parameters
	 * @return array
	 */
	private function parameterMap($parameters, $level = 0) {
	    if ($level >= 15) {
	        return array();
	    }
	    $level++;
		$entries = array();
		if (is_array($parameters) || $parameters instanceof \Traversable) {
			foreach ($parameters as $key => $parameter) {
				$key = preg_replace('/\0.+\0/u', '', $key);
				if (is_object($parameter) || $parameter instanceof \__PHP_Incomplete_Class) {
					if ($parameter instanceof \__PHP_Incomplete_Class) {
						$properties = (array)$parameter;
						$properties['__object_type'] = $properties['__PHP_Incomplete_Class_Name'];
						unset($properties['__PHP_Incomplete_Class_Name']);
						$entries[$key] =  array('key' => $key, 'value' => $this->parameterMap($properties, $level));
					} else {
						$properties = (array)$parameter;
						$properties['__object_type'] = get_class($parameter);
						$entries[$key] =  array('key' => $key, 'value' => $this->parameterMap($properties, $level));
					}
				} elseif (is_array($parameter)) {
					$entries[$key] = array('key' => $key, 'value' => $this->parameterMap($parameter, $level));
				} else {
					$entries[$key] = array('key' => $key, 'value' => base64_encode($parameter));
				}
			}
		}
		return $entries;
	}
}