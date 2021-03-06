<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// Include the engine
$file = JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';

jimport('joomla.filesystem.file');

if (!JFile::exists($file)) {
    return;
}

// Include the engine file
require_once($file);

// Check if the foundry exists
if (!FD::exists()) {
    echo JText::_('COM_EASYSOCIAL_FOUNDRY_DEPENDENCY_MISSING');
    return;
}

FD::language()->loadSite();

// Load up the helper file 
require_once(dirname(__FILE__) . '/helper.php');

$my = ES::user();

require(JModuleHelper::getLayoutPath('mod_easysocial_calendar'));

// Load up the module engine
$modules = FD::modules( 'mod_easysocial_menu' );

// We need these packages
$modules->loadComponentScripts();
$modules->loadComponentStylesheets();
$modules->addDependency('css');
$modules->loadScript('script.js');