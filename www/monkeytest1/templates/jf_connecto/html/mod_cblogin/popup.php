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
			<h3><?php echo htmlspecialchars(CBTxt::T('Account')); ?></h3>
			<div>
			<!-- START - CB WRAPPER -->
			<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeForm' ); ?>
				<form action="<?php echo $_CB_framework->viewUrl( 'login', true, null, 'html', $secureForm ); ?>" method="post" class="cbLoginForm">
					<input type="hidden" name="option" value="com_comprofiler" />
					<input type="hidden" name="view" value="login" />
					<input type="hidden" name="op2" value="login" />
					<input type="hidden" name="return" value="B:<?php echo $loginReturnUrl; ?>" />
					<input type="hidden" name="message" value="<?php echo (int) $params->get( 'login_message', 0 ); ?>" />
					<input type="hidden" name="loginfrom" value="<?php echo htmlspecialchars( ( defined( '_UE_LOGIN_FROM' ) ? _UE_LOGIN_FROM : 'loginmodule' ) ); ?>" />
					<?php echo cbGetSpoofInputTag( 'login' ); ?>
					<?php echo modCBLoginHelper::getPlugins( $params, $type, 'start' ); ?>
					<?php if ( $preLogintText ) { ?>
						<div class="pretext">
							<p><?php echo $preLogintText; ?></p>
						</div>
					<?php } ?>
					<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostStart' ); ?>
					
					
					<?php if ( $loginMethod != 4 ) { ?>
					<fieldset class="userdata">
						<div class="jf_authorizing_wrap">
							<div class="jf_authorize">
							
								<p id="form-login-username">
									<?php if ( $showForgotLogin ) { ?>
									<a class="jf_forgot" href="<?php echo $_CB_framework->viewUrl( 'lostpassword', true, null, 'html', $secureForm ); ?>">
										<i class="fa fa-question"></i>
									</a>
									<?php } ?>
									<input id="modlgn-username" type="text" name="username" class="inputbox"  size="<?php echo $usernameInputLength; ?>"<?php echo ( in_array( $showUsernameLabel, array( 4, 5 ) ) ? ' placeholder="' . htmlspecialchars( $userNameText ) . '"' : null ); ?> />
									<label class="jf_input_icon" for="modlgn-username"><i class="fa fa-user"></i></label>
								</p>
								
								<p id="form-login-password">
									<input id="modlgn-passwd" type="password" name="passwd" class="inputbox" size="<?php echo $passwordInputLength; ?>"<?php echo ( in_array( $showPasswordLabel, array( 4, 5 ) ) ? ' placeholder="' . htmlspecialchars( CBTxt::T( 'Password' ) ) . '"' : null ); ?>  />
									<label class="jf_input_icon" for="modlgn-passwd"><i class="fa fa-key"></i></label>
									<?php if ( $showForgotLogin ) { ?>
									<a class="jf_forgot" href="<?php echo $_CB_framework->viewUrl( 'lostpassword', true, null, 'html', $secureForm ); ?>">
										<i class="fa fa-question"></i>
									</a>
									<?php } ?>
								</p>
								
								<?php if ( count( $twoFactorMethods ) > 1 ) { ?>
									<p id="form-login-secretkey">
										<?php if ( in_array( $showSecretKeyLabel, array( 1, 2, 3, 5 ) ) ) { ?>
											<?php if ( in_array( $showSecretKeyLabel, array( 2, 5 ) ) ) { ?>
												<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
													<span class="cbModuleSecretKeyIcon fa fa-star" title="<?php echo htmlspecialchars( CBTxt::T( 'Secret Key' ) ); ?>"></span>
												</span>
											<?php } else { ?>
												<label for="modlgn-secretkey">
													<?php if ( $showSecretKeyLabel == 3 ) { ?>
														<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
															<span class="cbModuleSecretKeyIcon fa fa-star" title="<?php echo htmlspecialchars( CBTxt::T( 'Secret Key' ) ); ?>"></span>
														</span>
													<?php } ?>
													<?php if ( in_array( $showSecretKeyLabel, array( 1, 3 ) ) ) { ?>
														<?php echo htmlspecialchars( CBTxt::T( 'Secret Key' ) ); ?>
													<?php } ?>
												</label>
											<?php } ?>
										<?php } ?>
										<input id="modlgn-secretkey" type="text" name="secretkey" class="inputbox" size="<?php echo $secretKeyInputLength; ?>"<?php echo ( in_array( $showSecretKeyLabel, array( 4, 5 ) ) ? ' placeholder="' . htmlspecialchars( CBTxt::T( 'Secret Key' ) ) . '"' : null ); ?>  />
									</p>
								<?php } ?>
								
								<?php if ( in_array( $showRememberMe, array( 1, 3 ) ) ) { ?>
									<p id="form-login-remember">
										<label for="modlgn-remember"><?php echo htmlspecialchars(CBTxt::T('Remember Me')); ?></label>
										<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"<?php echo ( $showRememberMe == 3 ? ' checked="checked"' : null ); ?> />
									</p>
								<?php } elseif ( $showRememberMe == 2 ) { ?>
									<input id="modlgn-remember" type="hidden" name="remember" class="inputbox" value="yes" />
								<?php } ?>
								
								<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeButton', 'p' ); ?>
								<button type="submit" name="Submit" class="button jf_authoriz_btn"<?php echo $buttonStyle; ?>>
									<?php echo htmlspecialchars(CBTxt::T('Sign in')); ?>
								</button>
								<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterButton', 'p' ); ?>
								
		
							</div>
							<span class="jf_authorizing_text"><?php echo htmlspecialchars(CBTxt::T('Please wait, authorizing ...')); ?></span>
						</div>
					</fieldset>
					<?php } else { ?>
						<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeButton', 'p' ); ?>
						<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterButton', 'p' ); ?>
					<?php } ?>
					
					<div class="clear"></div>
					
					<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostEnd' ); ?>
					<?php if ( $postLoginText ) { ?>
						<div class="posttext">
							<p><?php echo $postLoginText; ?></p>
						</div>
					<?php } ?>
					<?php echo modCBLoginHelper::getPlugins( $params, $type, 'end' ); ?>
				</form>
			<!-- END - CB WRAPPER -->
			<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterForm' ); ?>
				<div class="jf_modal_close">×</div>
			</div>
		</div>
	</div>
	<div class="jf_modal_overlay"></div>
	<button class="jf_modal_trigger"><?php echo JText::_('JF_LOGIN_NEW_SIGN_IN'); ?></button>
			
	<?php 
		$usersConfig = JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration'))
	: ?>
		<a class="jf_register_btn_new" href="<?php echo $_CB_framework->viewUrl( 'registers', true, null, 'html', $secureForm ); ?>">
			<?php echo htmlspecialchars(CBTxt::T('Sign Up')); ?>
		</a>
	<?php endif; ?>
	
</div>