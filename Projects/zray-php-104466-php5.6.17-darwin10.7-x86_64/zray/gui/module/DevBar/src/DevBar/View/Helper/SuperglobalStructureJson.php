<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper,
	DevBar\Controller\WebAPIController as DevBarWebAPIController,
	ZendServer\Log\Log,
	DevBar\LogEntryContainer;
use DevBar\functionStatsContainer;

class SuperglobalStructureJson extends AbstractHelper {
	
	private $maxDepth = 7;
	
	/**
	 * @param array $superglobals
	 * @return string
	 */
	public function __invoke($superglobals, $maxDepth = 15) {
		$this->maxDepth = $maxDepth;
		return $this->getView()->json($this->parameterMap($superglobals));
	}
	
	/**
	 * 
	 * @param mixed $parameters
	 * @return array
	 */
	private function parameterMap($parameters, $level = 0) {
	
		// it was changed to $level >= 15 once by Amit?, but there is unstopped "loading..." zray extension panel, so i back the max level 7 
		if ($level >= $this->maxDepth) {
			Log::notice('SuperglobalStructureJson::parameterMap: reached limit of ' . $level);
            return array('N/A: Reached depth tree limit' => 'Reached depth tree limit of ' . $level);
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
						$entries[$key] = $this->parameterMap($properties, $level);
					} else {
						$properties = (array)$parameter;
						$properties['__object_type'] = get_class($parameter);
						$entries[$key] = $this->parameterMap($properties, $level);
					}
				} elseif (is_array($parameter)) {
					$entries[$key] = $this->parameterMap($parameter, $level);
				} else {
					$entries[$key] = $parameter;
				}
			}
		}
		return $entries;
	}
}