<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class DiscussionsViewCanvas extends SocialAppsView
{
    /**
     * Displays the application output in the canvas.
     *
     * @since    1.0
     * @access    public
     * @param    int        The user id that is currently being viewed.
     */
    public function display($uid = null, $docType = null)
    {
        FD::requireLogin();

        $event = FD::event($uid);
        $editor = JFactory::getEditor();

        $this->set('editor', $editor);
        $this->set('event', $event);

        echo parent::display('canvas/form');
    }
}
