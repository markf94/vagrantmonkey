<?php
/**
 * JF Login
 * @author		JoomForest.com
 * @email		support@joomforest.com
 * @website		http://www.joomforest.com
 * @copyright	Copyright (C) 2011-2015 JoomForest. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
 
// ini_set('display_errors', 'On');
// error_reporting(E_ALL | E_STRICT);

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

// Main Variables
$base = JURI::base();
$assets_path = $base.'modules/mod_jf_login/assets/';
$jf_doc = JFactory::getDocument();

/* START - FUNCTIONS ==================================================================================================== */
	// Calling
		// CSS
			$jf_doc->addStyleSheet($assets_path.'mod_jf_login.min.css');
			// $jf_doc->addStyleDeclaration(''.$jf_login_styles.'');
		// JS
			$jf_doc->addScript($assets_path.'mod_jf_login.min.js');
			$jf_doc->addScriptDeclaration('(function(a){a(window).load(function(){a("#jf_login").jf_login_modal()})})(jQuery);');
/*   END - FUNCTIONS ==================================================================================================== */


/* START - SECONDRY FUNCTIONS ==================================================================================================== */
	if ($params->get('jf_bs_tooltips','')) {
		$jf_doc->addStyleSheet($assets_path.'jf_bs_tooltips_31.min.css');
		$jf_doc->addScript($assets_path.'jf_bs_tooltips_31.min.js');
		$jf_doc->addScriptDeclaration('jQuery(document).ready(function($){$(function(){$("[data-toggle=\'tooltip\']").tooltip({container:"body"})})});');
		// $jf_doc->addScriptDeclaration('alert("enabled tooltips");');
	}
	if ($params->get('jf_fa','')) {
		$jf_doc->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css');
		// $jf_doc->addScriptDeclaration('alert("enabled fontawesome");');
	}
/*   END - SECONDRY FUNCTIONS ==================================================================================================== */


$jf_login_register							= $params->get('jf_login_register','');
$jf_login_register_link_url					= $params->get('jf_login_register_link_url','');
$jf_login_register_link_menu				= $params->get('jf_login_register_link_menu','');

$jf_login_custom_link_1						= $params->get('jf_login_custom_link_1','');
$jf_login_custom_link_1_url					= $params->get('jf_login_custom_link_1_url','');
$jf_login_custom_link_1_menu				= $params->get('jf_login_custom_link_1_menu','');
$jf_login_custom_link_1_text				= $params->get('jf_login_custom_link_1_text','');

$jf_login_custom_link_2						= $params->get('jf_login_custom_link_2','');
$jf_login_custom_link_2_url					= $params->get('jf_login_custom_link_2_url','');
$jf_login_custom_link_2_menu				= $params->get('jf_login_custom_link_2_menu','');
$jf_login_custom_link_2_text				= $params->get('jf_login_custom_link_2_text','');

$jf_login_custom_link_3						= $params->get('jf_login_custom_link_3','');
$jf_login_custom_link_3_url					= $params->get('jf_login_custom_link_3_url','');
$jf_login_custom_link_3_menu				= $params->get('jf_login_custom_link_3_menu','');
$jf_login_custom_link_3_text				= $params->get('jf_login_custom_link_3_text','');

$jf_login_custom_link_4						= $params->get('jf_login_custom_link_4','');
$jf_login_custom_link_4_url					= $params->get('jf_login_custom_link_4_url','');
$jf_login_custom_link_4_menu				= $params->get('jf_login_custom_link_4_menu','');
$jf_login_custom_link_4_text				= $params->get('jf_login_custom_link_4_text','');

$jf_login_custom_link_5						= $params->get('jf_login_custom_link_5','');
$jf_login_custom_link_5_url					= $params->get('jf_login_custom_link_5_url','');
$jf_login_custom_link_5_menu				= $params->get('jf_login_custom_link_5_menu','');
$jf_login_custom_link_5_text				= $params->get('jf_login_custom_link_5_text','');

$jf_login_custom_link_6						= $params->get('jf_login_custom_link_6','');
$jf_login_custom_link_6_url					= $params->get('jf_login_custom_link_6_url','');
$jf_login_custom_link_6_menu				= $params->get('jf_login_custom_link_6_menu','');
$jf_login_custom_link_6_text				= $params->get('jf_login_custom_link_6_text','');

$jf_login_custom_link_7						= $params->get('jf_login_custom_link_7','');
$jf_login_custom_link_7_url					= $params->get('jf_login_custom_link_7_url','');
$jf_login_custom_link_7_menu				= $params->get('jf_login_custom_link_7_menu','');
$jf_login_custom_link_7_text				= $params->get('jf_login_custom_link_7_text','');

$jf_login_custom_link_8						= $params->get('jf_login_custom_link_8','');
$jf_login_custom_link_8_url					= $params->get('jf_login_custom_link_8_url','');
$jf_login_custom_link_8_menu				= $params->get('jf_login_custom_link_8_menu','');
$jf_login_custom_link_8_text				= $params->get('jf_login_custom_link_8_text','');

$jf_login_custom_link_9						= $params->get('jf_login_custom_link_9','');
$jf_login_custom_link_9_url					= $params->get('jf_login_custom_link_9_url','');
$jf_login_custom_link_9_menu				= $params->get('jf_login_custom_link_9_menu','');
$jf_login_custom_link_9_text				= $params->get('jf_login_custom_link_9_text','');

$jf_login_custom_link_10					= $params->get('jf_login_custom_link_10','');
$jf_login_custom_link_10_url				= $params->get('jf_login_custom_link_10_url','');
$jf_login_custom_link_10_menu				= $params->get('jf_login_custom_link_10_menu','');
$jf_login_custom_link_10_text				= $params->get('jf_login_custom_link_10_text','');

$params->def('greeting', 1);

$type	= ModJFLoginHelper::getType();
$return	= ModJFLoginHelper::getReturnURL($params, $type);
$user	= JFactory::getUser();

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_jf_login', $params->get('layout', 'default'));

/* 
<?php
	$doc = JFactory::getDocument();
	$renderer   = $doc->loadRenderer('modules');
	$position   = 'custompositionname';
	$options   = array('style' => 'raw');
	echo $renderer->render($position, $options, null); 
?>
*/