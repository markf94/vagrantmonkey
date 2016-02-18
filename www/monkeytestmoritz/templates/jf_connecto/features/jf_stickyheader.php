<?php
/**
* @version		JF_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('JPATH_BASE') or die();
gantry_import('core.gantryfeature');

class GantryFeatureJf_StickyHeader extends GantryFeature {
    var $_feature_name = 'jf_stickyheader';

	function init() {
		global $gantry, $option;

		if ($this->get('enabled')) {
		
			JHtml::_('jquery.framework');
			
			// CALL FEATURE VARIABLES
				$jf_doc = JFactory::getDocument();
				$getapp = JFactory::getApplication();
				$template = $getapp->getTemplate();
				$jf_template_dir = JURI::base().'templates/'.$template;
				
				$jf_stickyheader_Style 			= $this->get('jf_stickyheader_Style');
				$jf_stickyheader_Color			= $this->get('jf_stickyheader_Color');
				
			// CALL FEATURE
				// CALL STYLE
					$gantry->addInlineStyle('
						#rt-header {
							position: fixed;
							right: 0;
							left: 0;
							top: 0;
							-webkit-transition: 	all 0.3s ease;
							-moz-transition: 		all 0.3s ease;
							-o-transition: 			all 0.3s ease;
							transition: 			all 0.3s ease;
							background: transparent;
							z-index: 999;
						}
						#rt-header.headroom--not-top {
							background: '.$jf_stickyheader_Color.';
						}
						#rt-header.headroom--not-top.slideUp {
							top: -100%;
							webkit-transition: 0;
							-moz-transition: 0;
							-o-transition: 0;
							transition: 0;
						}
						
						.jf_stickyHeader_light #rt-header.headroom--not-top .gf-menu .item {
							color: #555;
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top {
							border-bottom: 1px solid rgba(0, 0, 0, 0.1);
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top #jf_undermenu .rt-block #jf_login .jf_modal_trigger:hover,
						.jf_stickyHeader_light #rt-header.headroom--not-top #jf_undermenu .rt-block #jf_login .jf_register_btn_new:hover {
							background-color: rgba(0, 0, 0, 0.05);
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top .gf-menu li:hover > .item,
						.jf_stickyHeader_light #rt-header.headroom--not-top .gf-menu.l1 > li.active > .item {
							background-color: rgba(0, 0, 0, 0.05);
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top #jf_undermenu .rt-block #jf_login .jf_modal_trigger,
						.jf_stickyHeader_light #rt-header.headroom--not-top #jf_undermenu .rt-block #jf_login .jf_register_btn_new {
							color: #444;
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top #jf_undermenu .rt-block #jf_login .jf_register_btn_new,
						.jf_stickyHeader_light #rt-header.headroom--not-top #jf_undermenu .rt-block #jf_login .jf_modal_trigger.new {
							border: 2px solid rgba(0, 0, 0, 0.5);
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top .gf-menu li.parent > .item:after {
							border-top-color: rgba(0, 0, 0, 0.5);
						}
						.jf_stickyHeader_light #rt-header.headroom--not-top .gf-menu .dropdown ul li.parent > .item:after {
							border-top-color: rgba(255, 255, 255, 0);
							border-left-color: rgba(0, 0, 0, 0.5);
						}
						
						.logo-type-gantry .jf_stickyHeader_light #rt-header.headroom--not-top #rt-logo {
							background-position: left bottom;
						}
					');
					if ($jf_stickyheader_Style == 'light') {
						$gantry->addInlineScript('jQuery(document).ready(function($){$("header").addClass("jf_stickyHeader_light");});');
					}
				// CALL FUNCTION
					$jf_doc->addScript($jf_template_dir.'/features/jf_stickyheader/headroom.min.js');
					$jf_doc->addScript($jf_template_dir.'/features/jf_stickyheader/jQuery.headroom.min.js');
					$gantry->addInlineScript('
						jQuery(document).ready(function($){
							$("#rt-header").headroom({
								"offset": 0,
								"tolerance": 0,
								"classes": {
									"initial": "headroom",
									"pinned": "slideDown",
									"unpinned": "slideUp"
								},
								"onPin": 	function() {
									// console.log("Scroll up");
								},
								"onUnpin": 	function() {
									// console.log("Scroll down");
									$("body").removeClass("jf_sticky_body_pos_top");
								},
								"onTop": 	function() {
									// console.log("top");
									$("body").addClass("jf_sticky_body_pos_top");
								}
							});
							// SET HEIGHT
								if($("#jf_slideshow").length){
									$("body").addClass("headroom_hasSlideshow");
									var jf_header_H		= 0;
								} else {
									var jf_rt_header	= $("#rt-header").height();
									var jf_header_H		= jf_rt_header;
								}
								$(".jf_head_set").css({height:jf_header_H});
							// ON WINDOW RESIZE
								$(window).resize(function(){
									if($("#jf_slideshow").length){
										var jf_header_H		= 0;
									} else {
										var jf_rt_header	= $("#rt-header").height();
										var jf_header_H		= jf_rt_header;
									}
									$(".jf_head_set").css({height:jf_header_H});
								});
						});
					');

		}
		
    }

	function isOrderable() {
		return false;
	}

}