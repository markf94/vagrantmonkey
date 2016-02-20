<?php
function _t($string, array $params = array()) {
	return vsprintf($string, $params);
}