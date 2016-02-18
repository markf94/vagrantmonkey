<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="navbar es-toolbar wide" data-notifications>
	<div class="navbar-inner">
		<div class="es-toolbar-wrap">
			<ul class="fd-nav pull-right">
				
				<li class="divider-vertical"></li>
				<?php if( !$this->my->id && ( $login ) ){ ?>
				<li class="dropdown_">
					<?php echo $this->includeTemplate( 'site/toolbar/default.login' , array( 'facebook' => $facebook )); ?>
				</li>
				<?php } ?>

				<?php if (!$this->my->guest && $profile){ ?>
					<?php echo $this->includeTemplate('site/toolbar/default.profile'); ?>
				<?php } ?>

			</ul>
			<?php if (!$this->my->guest) { ?>
			<ul class="fd-nav pull-left">
				<?php if ($dashboard) { ?>
				<li class="toolbarItem toolbar-home" data-toolbar-item>
					<a data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_DASHBOARD' , true );?>"
						data-placement="left"
						data-es-provide="tooltip"
						href="<?php echo FRoute::dashboard();?>"
					>
						<i class="ies-home"></i>
						<span class="visible-phone"><?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_DASHBOARD' , true );?></span>
					</a>
				</li>
				<?php } ?>
				<li class="toolbarItem toolbar-menu" data-toolbar-menu
					data-popbox
					data-popbox-id="fd"
					data-popbox-component="es"
					data-popbox-type="toolbar"
					data-popbox-toggle="click"
					data-popbox-position="<?php echo JFactory::getDocument()->getDirection() == 'rtl' ? 'bottom-right' : 'bottom-left';?>"
					data-popbox-target=".toobar-menu-popbox"
				>
					<a href="javascript:void(0);" class="jf_es_menu2">
						<i class="ies-menu-2"></i>
						<span class="visible-phone"><?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_DASHBOARD' , true );?></span>
					</a>

					<div style="display:none;" class="toobar-menu-popbox" data-toolbar-menu-dropdown>
						<ul class="popbox-dropdown-menu dropdown-menu-user" style="display: block;">
							<?php if ($this->my->hasCommunityAccess()) { ?>
								<li>
									<a href="<?php echo FRoute::friends();?>">
										<i class="ies-user ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_FRIENDS');?>
									</a>
								</li>
								
								<?php if ($this->config->get('friends.invites.enabled')) { ?>
								<li>
									<a href="<?php echo FRoute::friends(array('layout' => 'invite'));?>">
										<i class="ies-user-add ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_INVITE_FRIENDS');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('followers.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::followers();?>">
										<i class="ies-tree-view ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_FOLLOWERS');?>
									</a>
								</li>
								<?php } ?>
								
								<?php if ($this->template->get('show_browse_users', true)) { ?>
								<li>
									<a href="<?php echo FRoute::users();?>">
										<i class="ies-users ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_BROWSE_USERS');?>
									</a>
								</li>
								<?php } ?>
								
								<?php if ($this->template->get('show_advanced_search', true)) { ?>
								<li>
									<a href="<?php echo FRoute::search(array('layout' => 'advanced'));?>">
										<i class="ies-search ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_ADVANCED_SEARCH');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('photos.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::albums(array('uid' => $this->my->getAlias() , 'type' => SOCIAL_TYPE_USER));?>">
										<i class="ies-picture ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_PHOTOS');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('groups.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::groups();?>">
										<i class="ies-users ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_GROUPS');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('events.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::events();?>">
										<i class="ies-calendar ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_EVENTS');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('badges.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::badges(array('layout' => 'achievements'));?>">
										<i class="ies-crown ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_ACHIEVEMENTS');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('points.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::points(array('layout' => 'history' , 'userid' => $this->my->getAlias()));?>">
										<i class="ies-health ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_POINTS_HISTORY');?>
									</a>
								</li>
								<?php } ?>

								<?php if ($this->config->get('conversations.enabled')){ ?>
								<li>
									<a href="<?php echo FRoute::conversations();?>">
										<i class="ies-comments-2 ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_PROFILE_CONVERSATIONS');?>
									</a>
								</li>
								<?php } ?>
								<li>
									<a href="<?php echo FRoute::apps();?>">
										<i class="ies-cube ies-small mr-5"></i> <?php echo JText::_('COM_EASYSOCIAL_TOOLBAR_APPS');?>
									</a>
								</li>
							<?php } ?>
						</ul>
					</div>
				</li>
			</ul>
			<ul class="fd-nav pull-right jf_es_toolbarNoties">
				<?php if ($friends) { ?>
					<?php echo $this->loadTemplate('site/toolbar/default.friends', array('requests' => $newRequests)); ?>
				<?php } ?>

				<?php if ($conversations) { ?>
					<?php echo $this->loadTemplate('site/toolbar/default.conversations', array('newConversations' => $newConversations)); ?>
				<?php } ?>

				<?php if ($notifications) { ?>
					<?php echo $this->loadTemplate('site/toolbar/default.notifications', array('newNotifications' => $newNotifications)); ?>
				<?php } ?>

			</ul>
			<?php } ?>

			<?php if ($search) { ?>
			<div class="fd-navbar-search pull-left" data-nav-search>
				<form action="<?php echo JRoute::_('index.php');?>" method="post">
					<i class="ies-search"></i>
					<input type="text" name="q" class="search-query" autocomplete="off" data-nav-search-input placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_TOOLBAR_FIND_FRIENDS' , true );?>" />

					<?php echo $this->html('form.itemid', FRoute::getItemId('search')); ?>
					<input type="hidden" name="view" value="search" />
					<input type="hidden" name="option" value="com_easysocial" />
				</form>
			</div>
			<?php } ?>
			
			<?php echo $this->includeTemplate( 'site/jf/jf_es_ft_noty' ); ?>
			<?php echo $this->includeTemplate( 'site/jf/jf_es_ft_animtxt' ); ?>
		</div>

	</div>
</div>