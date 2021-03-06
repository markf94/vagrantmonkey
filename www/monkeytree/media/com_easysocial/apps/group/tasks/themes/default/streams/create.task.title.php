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
<?php if( $total > 1 ){ ?>
	<?php echo JText::sprintf( 'APP_GROUP_TASKS_STREAM_TITLE_CREATE_MULTIPLE_TASKS' , $this->html( 'html.user' , $actor->id ) , $total , '<a href="' . $permalink . '">' . $milestone->get( 'title' ) . '</a>' ); ?>
<?php } else { ?>
	<?php echo JText::sprintf( 'APP_GROUP_TASKS_STREAM_TITLE_CREATE_SINGLE_TASK' , $this->html( 'html.user' , $actor->id ) , '<a href="' . $permalink . '">' . $milestone->get( 'title' ) . '</a>' ); ?>
<?php } ?>
