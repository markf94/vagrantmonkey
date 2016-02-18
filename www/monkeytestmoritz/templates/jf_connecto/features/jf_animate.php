<?php
/**
* @version		JF_DTP_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('JPATH_BASE') or die();
gantry_import('core.gantryfeature');

class GantryFeatureJf_Animate extends GantryFeature {
    var $_feature_name = 'jf_animate';

	function init() {
		global $gantry, $option;

		if ($this->get('enabled')) {
		
			JHtml::_('jquery.framework');
			
			$jf_doc = JFactory::getDocument();
			$getapp = JFactory::getApplication();
			$template = $getapp->getTemplate();
			$jf_template_dir = JURI::base().'templates/'.$template;
			
			// Variables ------------------------------------------------------
				// CSS3 STYLES
				$jf_animate_all			= $this->get('jf_animate_all');
				$jf_animate_all_sheet	= $this->get('jf_animate_all_sheet');
				$jf_animate_custom		= $this->get('jf_animate_custom');
				// 1
				$jf_animate_1			= $this->get('jf_animate_1');
				$jf_animate_1_type		= $this->get('jf_animate_1_type');
				$jf_animate_1_delay 	= $this->get('jf_animate_1_delay');
				$jf_animate_1_tags		= $this->get('jf_animate_1_tags');
				// 2
				$jf_animate_2			= $this->get('jf_animate_2');
				$jf_animate_2_type		= $this->get('jf_animate_2_type');
				$jf_animate_2_delay 	= $this->get('jf_animate_2_delay');
				$jf_animate_2_tags		= $this->get('jf_animate_2_tags');
				// 3
				$jf_animate_3			= $this->get('jf_animate_3');
				$jf_animate_3_type		= $this->get('jf_animate_3_type');
				$jf_animate_3_delay 	= $this->get('jf_animate_3_delay');
				$jf_animate_3_tags		= $this->get('jf_animate_3_tags');
				// 4
				$jf_animate_4			= $this->get('jf_animate_4');
				$jf_animate_4_type		= $this->get('jf_animate_4_type');
				$jf_animate_4_delay 	= $this->get('jf_animate_4_delay');
				$jf_animate_4_tags		= $this->get('jf_animate_4_tags');
				// 5
				$jf_animate_5			= $this->get('jf_animate_5');
				$jf_animate_5_type		= $this->get('jf_animate_5_type');
				$jf_animate_5_delay 	= $this->get('jf_animate_5_delay');
				$jf_animate_5_tags		= $this->get('jf_animate_5_tags');
				// 6
				$jf_animate_6			= $this->get('jf_animate_6');
				$jf_animate_6_type		= $this->get('jf_animate_6_type');
				$jf_animate_6_delay 	= $this->get('jf_animate_6_delay');
				$jf_animate_6_tags		= $this->get('jf_animate_6_tags');
				// 7
				$jf_animate_7			= $this->get('jf_animate_7');
				$jf_animate_7_type		= $this->get('jf_animate_7_type');
				$jf_animate_7_delay 	= $this->get('jf_animate_7_delay');
				$jf_animate_7_tags		= $this->get('jf_animate_7_tags');

			// Calling -----------------------------------------------------------
				$jf_doc->addStyleDeclaration('.animated{-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both}.animated.infinite{-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite}.animated.hinge{-webkit-animation-duration:2s;animation-duration:2s}[data-animation]{opacity:0;filter:alpha(opacity=0)}.jf_anim_done[data-animation]{opacity:1;filter:alpha(opacity=100)}');
				// CSS3 STYLES
				if ($jf_animate_all) {
					$jf_doc->addStyleSheet($jf_animate_all_sheet);
				}
				if ($jf_animate_custom) {
					$jf_doc->addStyleSheet($jf_template_dir.'/features/jf_animate/custom_animate.min.css');
				}
				// 1
				if ($jf_animate_1) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_1_tags.'").attr("data-animation","'.$jf_animate_1_type.'").attr("data-animation-delay","'.$jf_animate_1_delay.'");
						});
					');
				}
				// 2
				if ($jf_animate_2) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_2_tags.'").attr("data-animation","'.$jf_animate_2_type.'").attr("data-animation-delay","'.$jf_animate_2_delay.'");
						});
					');
				}
				// 3
				if ($jf_animate_3) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_3_tags.'").attr("data-animation","'.$jf_animate_3_type.'").attr("data-animation-delay","'.$jf_animate_3_delay.'");
						});
					');
				}
				// 4
				if ($jf_animate_4) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_4_tags.'").attr("data-animation","'.$jf_animate_4_type.'").attr("data-animation-delay","'.$jf_animate_4_delay.'");
						});
					');
				}
				// 5
				if ($jf_animate_5) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_5_tags.'").attr("data-animation","'.$jf_animate_5_type.'").attr("data-animation-delay","'.$jf_animate_5_delay.'");
						});
					');
				}
				// 6
				if ($jf_animate_6) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_6_tags.'").attr("data-animation","'.$jf_animate_6_type.'").attr("data-animation-delay","'.$jf_animate_6_delay.'");
						});
					');
				}
				// 7
				if ($jf_animate_7) {
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("'.$jf_animate_7_tags.'").attr("data-animation","'.$jf_animate_7_type.'").attr("data-animation-delay","'.$jf_animate_7_delay.'");
						});
					');
				}
			
			// CORE FUNCS ------------------------------------------------------
				$jf_doc->addScript($jf_template_dir.'/features/jf_animate/jquery.appear.min.js');
				$gantry->addInlineScript('
					jQuery(document).ready(function($){
						$("[data-animation]").each(function() {
							var self = $(this);
							var animation = self.data("animation");
							var delay = (self.data("animation-delay") ? self.data("animation-delay") : 0);
							self.appear(function(){
								setTimeout(function(){
									self.css("animation-delay", delay + "s").css("-webkit-animation-delay", delay + "s");
									self.addClass(animation);
									self.addClass("animated").addClass("jf_anim_done");
									self.one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
										$(this).removeClass("animated").removeClass(animation);
									});
								}, delay);
							}, {accX: 0, accY: -50});
						});
					});
				');
		}
		
    }

	function isOrderable() {
		return false;
	}

}