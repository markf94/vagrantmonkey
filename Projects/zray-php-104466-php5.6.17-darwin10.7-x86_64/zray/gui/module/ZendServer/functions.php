<?php

/**
 * Convenience function for calling the translation helper in a global way
 * @param type $string
 * @param array $params
 * @return string 
 */
function _t($string, array $params = array()) {
	/// translate the template only
	$string = \Application\Module::serviceManager()->get('translator')->translate($string);
	return vsprintf($string, $params);
}