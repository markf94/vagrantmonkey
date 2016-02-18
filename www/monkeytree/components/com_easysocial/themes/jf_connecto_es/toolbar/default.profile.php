<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2015 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<li class="toolbarItem toolbar-profile" data-toolbar-profile
    data-popbox
    data-popbox-id="fd"
    data-popbox-component="es"
    data-popbox-type="toolbar"
    data-popbox-toggle="click"
    data-popbox-position="<?php echo JFactory::getDocument()->getDirection() == 'rtl' ? 'bottom-left' : 'bottom-right';?>"
    data-popbox-target=".toobar-profile-popbox"
>
	<a href="javascript:void(0);" class="dropdown-toggle_ login-link loginLink">
		<span class="es-avatar">
			<img src="<?php echo $this->my->getAvatar();?>" alt="<?php echo $this->html('string.escape' , $this->my->getName());?>" />
		</span>
		<span class="toolbar-user-name"><?php echo $this->my->getName();?></span>
		<b class="caret"></b>
	</a>

	<div style="display:none;" class="toobar-profile-popbox" data-toolbar-profile-dropdown>
		<ul class="popbox-dropdown-menu dropdown-menu-user" style="display: block;">
			<li>
				<a href="<?php echo FRoute::profile();?>">
					<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_VIEW_YOUR_PROFILE');?>
				</a>
			</li>
			
			<li>
				<a href="<?php echo FRoute::profile(array('layout' => 'edit'));?>">
					<?php echo JText::_('COM_EASYSOCIAL_HEADING_PROFILE_ACCOUNT_SETTINGS');?>
				</a>
			</li>
			<?php if ($this->my->hasCommunityAccess()) { ?>
				<li>
					<a href="<?php echo FRoute::profile(array('layout' => 'editPrivacy'));?>">
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PRIVACY_SETTINGS');?>
					</a>
				</li>
				<li>
					<a href="<?php echo FRoute::profile(array('layout' => 'editNotifications'));?>">
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_NOTIFICATION_SETTINGS');?>
					</a>
				</li>
				<li>
					<a href="<?php echo FRoute::activities();?>">
						<?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACTIVITIES');?>
					</a>
				</li>
				<li class="divider"></li>
			<?php } ?>
			<li>
				<a href="javascript:void(0);" class="logout-link" data-toolbar-logout-button>
					<i class="ies-switch ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_SIGN_OUT');?>
				</a>
				<form class="logout-form" action="<?php echo JRoute::_('index.php');?>" data-toolbar-logout-form method="post">
					<input type="hidden" name="return" value="<?php echo $logoutReturn;?>" />
					<input type="hidden" name="option" value="com_easysocial" />
					<input type="hidden" name="controller" value="account" />
					<input type="hidden" name="task" value="logout" />
					<input type="hidden" name="view" value="" />
					<?php echo $this->html('form.token'); ?>
				</form>
			</li>
		</ul>
	</div>
</li>
