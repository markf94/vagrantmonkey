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
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.keepalive');
?>
<div id="jf_login" class="<?php echo $moduleclass_sfx; ?>">
	<?php if ($type == 'logout') : ?>
		<div class="jf_modal jf_modal_effect_1">
			<div class="jf_modal_content">
				<h3><?php echo JText::_('MOD_JF_LOGIN_MY_ACCOUNT'); ?></h3>
				<div>
					<div class="jf_greeting">
						<?php if ($params->get('greeting')) : ?>
							<?php if($params->get('name') == 0) : {
								echo JText::sprintf('MOD_JF_LOGIN_HINAME', htmlspecialchars($user->get('name')));
							} else : {
								echo JText::sprintf('MOD_JF_LOGIN_HINAME', htmlspecialchars($user->get('username')));
							} endif; ?>
						<?php endif; ?>
					</div>
					<?php if ($jf_login_custom_link_1) { ?>
						<ul class="jf_profile_links">
							<?php if ($jf_login_custom_link_1) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_1_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_1_menu); ?>">
											<?php echo $jf_login_custom_link_1_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_1_url; ?>">
											<?php echo $jf_login_custom_link_1_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_2) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_2_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_2_menu); ?>">
											<?php echo $jf_login_custom_link_2_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_2_url; ?>">
											<?php echo $jf_login_custom_link_2_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_3) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_3_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_3_menu); ?>">
											<?php echo $jf_login_custom_link_3_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_3_url; ?>">
											<?php echo $jf_login_custom_link_3_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_4) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_4_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_4_menu); ?>">
											<?php echo $jf_login_custom_link_4_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_4_url; ?>">
											<?php echo $jf_login_custom_link_4_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_5) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_5_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_5_menu); ?>">
											<?php echo $jf_login_custom_link_5_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_5_url; ?>">
											<?php echo $jf_login_custom_link_5_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_6) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_6_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_6_menu); ?>">
											<?php echo $jf_login_custom_link_6_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_6_url; ?>">
											<?php echo $jf_login_custom_link_6_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_7) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_7_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_7_menu); ?>">
											<?php echo $jf_login_custom_link_7_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_7_url; ?>">
											<?php echo $jf_login_custom_link_7_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_8) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_8_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_8_menu); ?>">
											<?php echo $jf_login_custom_link_8_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_8_url; ?>">
											<?php echo $jf_login_custom_link_8_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_9) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_9_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_9_menu); ?>">
											<?php echo $jf_login_custom_link_9_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_9_url; ?>">
											<?php echo $jf_login_custom_link_9_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
							<?php if ($jf_login_custom_link_10) { ?>
								<li>
									<i class="fa fa-arrow-right"></i>
									<?php if ($jf_login_custom_link_10_url == '') { ?>
										<a href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_custom_link_10_menu); ?>">
											<?php echo $jf_login_custom_link_10_text; ?>
										</a>
									<?php } else { ?>
										<a href="<?php echo $jf_login_custom_link_10_url; ?>">
											<?php echo $jf_login_custom_link_10_text; ?>
										</a>
									<?php } ?>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
					<form action="<?php echo JRoute::_(JUri::getInstance()->toString(), true, $params->get('usesecure')); ?>" method="post" class="jf_logged">
						<input type="submit" name="Submit" class="button jf_authoriz_btn" value="<?php echo JText::_('MOD_JF_LOGIN_SIGNOUT'); ?>" />
						<input type="hidden" name="option" value="com_users" />
						<input type="hidden" name="task" value="user.logout" />
						<input type="hidden" name="return" value="<?php echo $return; ?>" />
						<?php echo JHtml::_('form.token'); ?>
					</form>
					<div class="jf_modal_close">×</div>
				</div>
			</div>
		</div>
		<div class="jf_modal_overlay"></div>
		<button class="jf_modal_trigger new"><?php echo JText::_('MOD_JF_LOGIN_MY_ACCOUNT'); ?></button>
	<?php else : ?>
		<div class="jf_modal jf_modal_effect_1">
			<div class="jf_modal_content">
				<h3><?php echo JText::_('MOD_JF_LOGIN_ACCOUNT'); ?></h3>
				<div>
					<form action="<?php echo JRoute::_(JUri::getInstance()->toString(), true, $params->get('usesecure')); ?>" method="post" >
						<?php if ($params->get('pretext')): ?>
							<div class="pretext">
							<p><?php echo $params->get('pretext'); ?></p>
							</div>
						<?php endif; ?>
						<fieldset class="userdata">
							<div class="jf_authorizing_wrap">
								<div class="jf_authorize">
									<p id="form-login-username">
										<a class="jf_forgot" href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" data-toggle="tooltip" data-placement="left" title="<?php echo JText::_('MOD_JF_LOGIN_FORGOT_YOUR_USERNAME'); ?>">
											<i class="fa fa-question"></i>
										</a>
										<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" placeholder="<?php echo JText::_('MOD_JF_LOGIN_USERNAME') ?>" />
										<label class="jf_input_icon" for="modlgn-username"><i class="fa fa-user"></i></label>
									</p>
									<p id="form-login-password">
										<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18" placeholder="<?php echo JText::_('MOD_JF_LOGIN_PASSWORD') ?>" />
										<label class="jf_input_icon" for="modlgn-passwd"><i class="fa fa-key"></i></label>
										<a class="jf_forgot" href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" data-toggle="tooltip" data-placement="left" title="<?php echo JText::_('MOD_JF_LOGIN_FORGOT_YOUR_PASSWORD'); ?>">
											<i class="fa fa-question"></i>
										</a>
									</p>
									<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
									<p id="form-login-remember">
										<label for="modlgn-remember"><?php echo JText::_('MOD_JF_LOGIN_REMEMBER_ME') ?></label>
										<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
									</p>
									<?php endif; ?>
									<input type="submit" name="Submit" class="jf_authoriz_btn" value="<?php echo JText::_('MOD_JF_LOGIN_SIGN_IN') ?>" />
									<input type="hidden" name="option" value="com_users" />
									<input type="hidden" name="task" value="user.login" />
									<input type="hidden" name="return" value="<?php echo $return; ?>" />
									<?php echo JHtml::_('form.token'); ?>
								</div>
								<span class="jf_authorizing_text"><?php echo JText::_('MOD_JF_LOGIN_AUTHORIZATING_TEXT'); ?></span>
							</div>
						</fieldset>
						
						<div class="clear"></div>
						
						<?php if ($params->get('posttext')): ?>
							<div class="posttext">
							<p><?php echo $params->get('posttext'); ?></p>
							</div>
						<?php endif; ?>
					</form>
					<div class="jf_modal_close">×</div>
				</div>
			</div>
		</div>
		<div class="jf_modal_overlay"></div>
		<button class="jf_modal_trigger"><?php echo JText::_('JF_LOGIN_NEW_SIGN_IN'); ?></button>
			
		<?php if ($jf_login_register) { ?>
			<?php 
				$usersConfig = JComponentHelper::getParams('com_users');
				if ($usersConfig->get('allowUserRegistration'))
			: ?>
				<?php if ($jf_login_register_link_url == '') { ?>
					<a class="jf_register_btn_new" href="<?php echo JRoute::_('index.php?Itemid='.$jf_login_register_link_menu); ?>">
						<?php echo JText::_('JF_LOGIN_NEW_SIGN_UP'); ?>
					</a>
				<?php } else { ?>
					<a class="jf_register_btn_new" href="<?php echo $jf_login_register_link_url; ?>">
						<?php echo JText::_('JF_LOGIN_NEW_SIGN_UP'); ?>
					</a>
				<?php } ?>
			<?php endif; ?>
		<?php } ?>
	<?php endif; ?>
</div>