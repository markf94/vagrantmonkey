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
<div class="es-widget es-widget-borderless">
	<div class="es-widget-head">
        <div class="widget-title pull-left">
            <?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_SIDEBAR_FILTER');?>
        </div>
	</div>

	<div class="es-widget-body">
		<ul class="fd-nav fd-nav-stacked activity-items" data-activity-log>

			<li class="<?php echo $active == 'all' ? ' active' : '';?>"
				data-sidebar-menu
				data-sidebar-item
				data-type="all"
				data-url="<?php echo FRoute::activities();?>"
				data-title="<?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_YOUR_LOGS' , true ); ?>"
				data-description=""
			>
				<a href="javascript:void(0);">
					<i class="fa fa-rss-square mr-5"></i> <?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_ALL_ACTIVITIES' );?>
					<div class="label label-notification pull-right mr-20"></div>
				</a>
			</li>
			<li class="<?php echo $active == 'hidden' ? ' active' : '';?>"
				data-sidebar-menu
				data-sidebar-item
				data-type="hidden"
				data-url="<?php echo FRoute::activities( array( 'type' => 'hidden' ) );?>"
				data-title="<?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_YOUR_HIDDEN_ACTIVITIES' , true ); ?>"
				data-description=""
			>
				<a href="javascript:void(0);">
					<i class="fa fa-eye mr-5"></i> <?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTIVITIES' );?>
				</a>
			</li>

			<li class="<?php echo $active == 'hiddenapp' ? ' active' : '';?>"
				data-sidebar-menu
				data-sidebar-item
				data-type="hiddenapp"
				data-url="<?php echo FRoute::activities( array( 'type' => 'hiddenapp' ) );?>"
				data-title="<?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_YOUR_HIDDEN_APPS' , true ); ?>"
				data-description=""
			>
				<a href="javascript:void(0);">
					<i class="fa fa-eye mr-5"></i> <?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_APPS' );?>
				</a>
			</li>

			<li class="<?php echo $active == 'hiddenactor' ? ' active' : '';?>"
				data-sidebar-menu
				data-sidebar-item
				data-type="hiddenactor"
				data-url="<?php echo FRoute::activities( array( 'type' => 'hiddenactor' ) );?>"
				data-title="<?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_YOUR_HIDDEN_ACTORS' , true ); ?>"
				data-description=""
			>
				<a href="javascript:void(0);">
					<i class="fa fa-eye mr-5"></i> <?php echo JText::_( 'COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS' );?>
				</a>
			</li>

		</ul>
	</div>

</div>
