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
<div data-oauth data-callback="<?php echo $callback; ?>">
	<div class="fd-small">
		<?php echo JText::_( 'COM_EASYSOCIAL_OAUTH_ALREADY_AUTHENTICATED_WITH_FACEBOOK' );?>
	</div>

	<div class="mt-15">
		<a href="javascript:void(0);" class="btn btn-es-danger btn-medium" data-oauth-facebook-revoke><?php echo JText::_( 'COM_EASYSOCIAL_OAUTH_REVOKE_ACCESS');?></a>
	</div>
</div>
