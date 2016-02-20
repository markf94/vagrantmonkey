<?php
/**
* @version		JF_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('JPATH_BASE') or die();
gantry_import('core.gantryfeature');

class GantryFeatureJf_Preloader extends GantryFeature {
	var $_feature_name = 'jf_preloader';

	function init() {
		/** @var $gantry Gantry */
		global $gantry;

		if ($this->get('enabled')) {
		
			JHtml::_('jquery.framework');
			
			// GLOBAL
				$jf_doc = JFactory::getDocument();
			// MAIN VARS
				$jf_preloader_type			= $this->get('jf_preloader_type');
				$jf_preloader_bg			= $this->get('jf_preloader_bg');
				// Custom IMage
					$jf_preloader_image_decode 	= json_decode($this->get('image'));
					if(!$jf_preloader_image_decode == '') {
						$jf_preloader_image_ul		= $jf_preloader_image_decode->path;
					} else {
						$jf_preloader_image_ul		= '';
					}
				// CSS3 Animation
					$jf_preloader_color			= $this->get('jf_preloader_color');
			
			// CALL FUNCTION
				// MAIN GLOBAL
					$gantry->addLess('jf_features_preloader.less');
					$jf_doc->addStyleDeclaration('#jf_preloader{background-color:'.$jf_preloader_bg.'}');
					$gantry->addInlineScript('!function(e){e(window).load(function(){e("#jf_preloader,#jf_preloader_css3").fadeOut()})}(jQuery);');
				// TYPES
				if($jf_preloader_type == 'custom') {
					if($jf_preloader_image_ul != '') {
						$jf_doc->addStyleDeclaration('#jf_preloader{background-image:url('.JURI::base().$jf_preloader_image_ul.')}');
					}
				}  elseif($jf_preloader_type == 'css3') {
					$jf_doc->addStyleDeclaration('
						#jf_preloader_css3,#jf_preloader_css3 .container .animated-preloader:after{background-color:'.$jf_preloader_bg.'}
						#jf_preloader_css3 .container .animated-preloader,#jf_preloader_css3 .container .animated-preloader:before{background:'.$jf_preloader_color.'}
					');
				} else {
					// $jf_doc->addStyleDeclaration('');
				}
		}
	}

	function render($position) {
		// MAIN VARS
			$jf_preloader_type			= $this->get('jf_preloader_type');
		ob_start();
	?>
	
		<?php if($jf_preloader_type == 'custom') { ?>
			<div id="jf_preloader"></div>
		<?php } elseif($jf_preloader_type == 'css3') { ?>
			<div id="jf_preloader_css3"><div class="container"><span class="animated-preloader"></span></div></div>
		<?php } else { ?>
			<div id="jf_preloader"><div class="spinner">
  <div class="bounce1"></div>
  <div class="bounce2"></div>
  <div class="bounce3"></div>
</div></div>
		<?php } ?>
	<?php
		return ob_get_clean();
	}
}