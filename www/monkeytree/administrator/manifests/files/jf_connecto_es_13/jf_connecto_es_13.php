<?php
/**
* @version		1.1
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class jf_connecto_es_13InstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent){}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent){}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent){}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent){
		// Prevent installation if Component Doesn't Exists
		$com_component 		= JPATH_ROOT.'/media/com_easysocial/';
		$component_txt		= 'EasySocial';
		$download_link		= 'http://www.stackideas.com/easysocial';
		$download_txt		= 'www.StackIdeas.com/EasySocial';
		if(!JFolder::exists($com_component)){
			ob_start();
			?>
				<style type="text/css">
					.jf_btn_alert {
						display: block;
						padding: 40px;
						font-size: 22px;
						color: #FFF;
						background: #D73000;
						text-align: center;
						line-height: 38px;
						position: relative;
					}
					.jf_btn_alert h1 {
						margin: 0 0 20px 0;
						border-bottom: 1px solid rgba(255, 255, 255, 0.19);
						padding: 0 0 20px 0;
					}
					.jf_btn_alert p {
						font-size: 14px;
						margin: 0;
						font-weight: 700;
					}
					.jf_btn_alert a {
						border-bottom: 1px dashed #FFF;
						color: #fff;
					}
					.jf_btn_alert a:hover {
						border-bottom: 1px solid #FFF;
						text-decoration: none;
					}
					.jf_close {
						position:absolute;
						bottom: 0;
						right: 0;
					}
					.jf_btn_alert .jf_close {
						bottom: 10px;
						right: 10px;
					}
				</style>
				<script>jQuery(document).ready(function($){$('#jf_modal').remove().prependTo('body').modal({backdrop:'static',keyboard:false})});</script>
				<div id="jf_modal" class="modal hide fade">
					<div class="modal-body">
						<div class="jf_btn_alert">
							<h1>Installation Failure</h1>
							You haven't Installed <u><?php echo $component_txt; ?></u> Component. For downloading please visit: <a href="<?php echo $download_link; ?>" target="_blank"><?php echo $download_txt; ?></a>
							<p>When you install it, please <u>try again</u> to install our <?php echo $component_txt; ?> Template</p>
							<button class="btn jf_close" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</div>
				</div>
			<?php
			$html = ob_get_contents();
			@ob_end_clean();
			JFactory::getApplication()->enqueueMessage($html,'error');
			
			return false;
		}
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		$jf_theme_name	= 'jf_connecto_es';
		$link = rtrim(JURI::root(), '/').'/administrator/index.php?option=com_easysocial&view=themes&layout=form&element='.$jf_theme_name;
		$linkText		= 'Go to EasySocial Template Manager';
		$component_txt	= 'EasySocial';
		$short_txt		= 'ES';
		ob_start();
			?>
			<style type="text/css">
				.jf_btn_alert {
					display: block;
					padding: 40px;
					font-size: 22px;
					color: #FFF;
					background: #D73000;
					text-align: center;
					line-height: 38px;
					position: relative;
				}
				.jf_btn_alert p {
					font-size: 14px;
					margin: 0;
					font-weight: 700;
				}
				.jf_btn_alert a {
					border-bottom: 1px dashed #FFF;
					color: #fff;
				}
				.jf_btn_alert a:hover {
					border-bottom: 1px solid #FFF;
					text-decoration: none;
				}
				.jf_success {
					text-align: center;
					padding: 10px;
					line-height: 22px;
					position: relative;
				}
				.jf_success h3 {
					font-size: 18px;
					line-height: 20px;
				}
				.jf_btn {
					margin: 10px 0px;
					padding: 10px 20px;
					font-size: 16px;
					color: #FFF;
					background: #009EB4;
					border: 0;
					cursor: pointer;
				}
				.jf_btn:hover {
					background: #008FA3;
				}
				.jf_btn:focus {
					background: #007C8D !important;
				}
				.jf_close {
					position:absolute;
					bottom: 0;
					right: 0;
				}
				.jf_btn_alert .jf_close {
					bottom: 10px;
					right: 10px;
				}
			</style>
			<script>jQuery(document).ready(function($){$('#jf_modal').remove().prependTo('body').modal({backdrop:'static',keyboard:false})});</script>
			<div id="jf_modal" class="modal hide fade">
				<div class="modal-body">
					<div class="jf_success">
						<h3>JoomForest <?php echo $component_txt; ?> Template installation was <u>Successful</u> !</h3>
						<p>Thank you very much for downloading our Template, please click below to check our <?php echo $short_txt; ?> Template features and options. <br> We hope you enjoy it <b>:)</b></p>
						<input type="button" class="jf_btn" onclick="window.location = '<?php echo $link; ?>'" value="<?php echo JText::_($linkText);?>"/>
						<button class="btn jf_close" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</div>
		<?php
		$html .= ob_get_contents();
		@ob_end_clean();
		echo $html;
	}
}