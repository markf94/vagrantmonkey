<?php
/**
 * Created by PhpStorm.
 * User: markfingerhuth
 * Date: 25.02.16
 * Time: 08:04   jimport('joomla.application.controller');
 */
defined('_JEXEC') or die("Access denied");
jimport('joomla.application.component.controller');
echo JText::_('COM_TESTCOMP_WELCOME_FRONTEND');

/**
 * Call the controller instance I've just created
 *
 */
$controller=JControllerLegacy::getInstance('TestComp');
/**
 * Execute the controller
 *
 */
$controller->execute(JRequest::getCmd('task'));
/**
 * Redirect
 *
 */
$controller->redirect();

