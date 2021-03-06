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
<li data-badge-achievers-achiever class="mr-10">
	<a href="<?php echo $user->getPermalink();?>" class="es-avatar es-avatar-rounded pull-left"
		data-popbox="module://easysocial/profile/popbox"
		data-user-id="<?php echo $user->id;?>"
	>
		<img src="<?php echo $user->getAvatar( SOCIAL_AVATAR_MEDIUM );?>" alt="<?php echo $this->html( 'string.escape' , $user->getName() );?>" />
	</a>
</li>
