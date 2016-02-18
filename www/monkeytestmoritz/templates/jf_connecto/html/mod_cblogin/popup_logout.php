<?php
/**
 * JF Login
 * @author		JoomForest.com
 * @email		support@joomforest.com
 * @website		http://www.joomforest.com
 * @copyright	Copyright (C) 2011-2015 JoomForest. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }
JHtml::_('behavior.keepalive');
// ini_set('display_errors', 'On');
// error_reporting(E_ALL | E_STRICT);
$base = JURI::base();
$assets_path = $base.'templates/jf_connecto/html/mod_cblogin/assets/';
$jf_doc = JFactory::getDocument();
// CSS
	$jf_doc->addStyleSheet($assets_path.'mod_jf_login.min.css');
// JS
	$jf_doc->addScript($assets_path.'mod_jf_login.min.js');
	$jf_doc->addScriptDeclaration('(function(a){a(window).load(function(){a("#jf_login").jf_login_modal()})})(jQuery);');
?>
<div id="jf_login">
	<div class="jf_modal jf_modal_effect_1">
		<div class="jf_modal_content">
			<h3><?php echo htmlspecialchars(CBTxt::T('My Account')); ?></h3>
			<div>
			<!-- START - CB WRAPPER -->
			<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeForm' ); ?>
			
				<?php echo modCBLoginHelper::getPlugins( $params, $type, 'start' ); ?>
				<?php if ( $preLogoutText ) { ?>
					<div class="pretext">
						<p><?php echo $preLogoutText; ?></p>
					</div>
				<?php } ?>
				<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostStart' ); ?>
				
				<div class="jf_greeting">
						<?php if ( (int) $params->get( 'greeting', 1 ) ) { ?>
							
								<?php echo $greetingText; ?>
							
						<?php } ?>
				</div>
				<?php if ( (int) $params->get( 'show_avatar', 1 ) ) { ?>
					<div class="login-avatar">
						<p><?php echo $cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true ); ?></p>
					</div>
				<?php } ?>
				
				
				<?php if ( $profileViewText || $profileEditText || $showPrivateMessages || $showConnectionRequests ) { ?>
					<p>
						<ul class="logout-links jf_profile_links">
							<?php if ( $showPrivateMessages ) { ?>
								<li class="logout-private-messages">
									<a href="<?php echo $privateMessageURL; ?>">
										<?php if ( $params->get( 'show_pms_icon', 0 ) ) { ?>
											<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
												<span class="cbModulePMIcon fa fa-envelope" title="<?php echo htmlspecialchars( CBTxt::T( 'Private Messages' ) ); ?>"></span>
											</span>
										<?php } ?>
										<?php if ( $newMessageCount ) { ?>
											<?php echo ( $newMessageCount == 1 ? CBTxt::T( 'YOU_HAVE_COUNT_NEW_PRIVATE_MESSAGE', 'You have [count] new private message.', array( '[count]' => $newMessageCount ) ) : CBTxt::T( 'YOU_HAVE_COUNT_NEW_PRIVATE_MESSAGES', 'You have [count] new private messages.', array( '[count]' => $newMessageCount ) ) ); ?>
										<?php } else { ?>
											<?php echo CBTxt::T( 'You have no new private messages.' ); ?>
										<?php } ?>
									</a>
								</li>
							<?php } ?>
							<?php if ( $showConnectionRequests ) { ?>
								<li class="logout-connection-requests">
									<a href="<?php echo $_CB_framework->viewUrl( 'manageconnections' ); ?>">
										<?php if ( $params->get( 'show_connection_notifications_icon', 0 ) ) { ?>
											<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
												<span class="cbModuleConnectionsIcon fa fa-users" title="<?php echo htmlspecialchars( CBTxt::T( 'Connections' ) ); ?>"></span>
											</span>
										<?php } ?>
										<?php if ( $newConnectionRequests ) { ?>
											<?php echo ( $newConnectionRequests == 1 ? CBTxt::T( 'YOU_HAVE_COUNT_NEW_CONNECTION_REQUEST', 'You have [count] new connection request.', array( '[count]' => $newConnectionRequests ) ) : CBTxt::T( 'YOU_HAVE_COUNT_NEW_CONNECTION_REQUESTS', 'You have [count] new connection requests.', array( '[count]' => $newConnectionRequests ) ) ); ?>
										<?php } else { ?>
											<?php echo CBTxt::T( 'You have no new connection requests.' ); ?>
										<?php } ?>
									</a>
								</li>
							<?php } ?>
							<?php if ( $profileViewText ) { ?>
								<li class="logout-profile">
									<a href="<?php echo $_CB_framework->userProfileUrl(); ?>">
										<?php if ( $params->get( 'icon_show_profile', 0 ) ) { ?>
											<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
												<span class="cbModuleProfileViewIcon fa fa-user" title="<?php echo htmlspecialchars( $profileViewText ); ?>"></span>
											</span>
										<?php } ?>
										<?php echo $profileViewText; ?>
									</a>
								</li>
							<?php } ?>
							<?php if ( $profileEditText ) { ?>
								<li class="logout-profile-edit">
									<a href="<?php echo $_CB_framework->userProfileEditUrl(); ?>">
										<?php if ( $params->get( 'icon_edit_profile', 0 ) ) { ?>
											<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
												<span class="cbModuleProfileEditIcon fa fa-pencil" title="<?php echo htmlspecialchars( $profileEditText ); ?>"></span>
											</span>
										<?php } ?>
										<?php echo $profileEditText; ?>
									</a>
								</li>
							<?php } ?>
						</ul>
					</p>
				<?php } ?>
				
				
				<form action="<?php echo $_CB_framework->viewUrl( 'logout', true, null, 'html', $secureForm ); ?>" method="post" class="cbLogoutForm jf_logged">
					<input type="hidden" name="option" value="com_comprofiler" />
					<input type="hidden" name="view" value="logout" />
					<input type="hidden" name="op2" value="logout" />
					<input type="hidden" name="return" value="B:<?php echo $logoutReturnUrl; ?>" />
					<input type="hidden" name="message" value="<?php echo (int) $params->get( 'logout_message', 0 ); ?>" />
					<?php echo cbGetSpoofInputTag( 'logout' ); ?>
					<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeButton', 'p' ); ?>
					<div class="logout-button">
						<button type="submit" name="Submit" class="jf_authoriz_btn ">
							<?php if ( in_array( $showButton, array( 1, 2, 3 ) ) ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModuleLogoutIcon fa fa-sign-out" title="<?php echo htmlspecialchars( CBTxt::T( 'Log out' ) ); ?>"></span>
								</span>
							<?php } ?>
							<?php if ( in_array( $showButton, array( 0, 1, 4 ) ) ) { ?>
								<?php echo htmlspecialchars( CBTxt::T( 'Log out' ) ); ?>
							<?php } ?>
						</button>
					</div>
					<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterButton', 'p' ); ?>
				</form>
				
				<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostEnd' ); ?>
				<?php if ( $postLogoutText ) { ?>
					<div class="posttext">
						<p><?php echo $postLogoutText; ?></p>
					</div>
				<?php } ?>
				<?php echo modCBLoginHelper::getPlugins( $params, $type, 'end' ); ?>
				
			<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterForm' ); ?>
			<!-- END - CB WRAPPER -->
				<div class="jf_modal_close">×</div>
			</div>
		</div>
	</div>
	<div class="jf_modal_overlay"></div>
	<button class="jf_modal_trigger new"><?php echo htmlspecialchars(CBTxt::T('My Account')); ?></button>
</div>