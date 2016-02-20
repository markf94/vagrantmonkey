<?php
namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;

class SuperglobalStructureXml extends AbstractHelper {
	
	/**
	 * @param array $superglobals
	 * @return string
	 */
	public function __invoke($superglobals) {
		if (is_array($superglobals) || ($superglobals instanceof \Traversable && $superglobals)) {
			$entries = array();
			foreach ($superglobals as $superglobal) {
				$entries[] = "<parameters>" . PHP_EOL . $this->parameterMap($superglobal) . "</parameters>" . PHP_EOL;
			}
			return implode('', $entries);
		}
		return '';
	}
	
	/**
	 * 
	 * @param mixed $parameters
	 * @return array
	 */
	private function parameterMap($parameters) {
		$entries = array();
		if (is_array($parameters) || $parameters instanceof \Traversable) {
			foreach ($parameters as $key => $parameter) {
				$key = preg_replace('/\0.+\0/u', '', $key);
				if (is_object($parameter) || $parameter instanceof \__PHP_Incomplete_Class) {
					$properties = (array)$parameter;
					if ($parameter instanceof \__PHP_Incomplete_Class) {
						$class = $properties['__PHP_Incomplete_Class_Name'];
						unset($properties['__PHP_Incomplete_Class_Name']);
					} else {
						$class = get_class($parameter);
					}
					$entries[] = "<parameterMap>
					  <name><![CDATA[$key]]></name>
					  <type>{$class}</type>
					  <parameters>{$this->parameterMap($properties)}</parameters>
					</parameterMap>";
				} elseif (is_array($parameter)) {
					$entries[] = "<parameterMap>
					  <name><![CDATA[$key]]></name>
					  <type>array</type>
					  <parameters>{$this->parameterMap($parameter)}</parameters>
					</parameterMap>";
				} else {
				    // in zray stored data the object parameters are saved with "\0", so we normilize the values. bug #ZSRV-14122
					$entries[] = "<parameter><name><![CDATA[$key]]></name><value><![CDATA[" . str_replace("\0", '', $parameter) . "]]></value></parameter>" . PHP_EOL;
				}
			}
		}
		return implode('', $entries);
	}
}