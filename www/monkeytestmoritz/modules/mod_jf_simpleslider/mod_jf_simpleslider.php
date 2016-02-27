<?php
/**
 * JF SimpleSlider
 * @author		JoomForest.com
 * @email		support@joomforest.com
 * @website		http://www.joomforest.com
 * @copyright	Copyright (C) 2011-2015 JoomForest. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// ini_set('display_errors', 'On');
// error_reporting(E_ALL | E_STRICT);
 
// no direct access
defined('_JEXEC') or die('Restricted access');

// Main Variables
$base = JURI::base();
$assets_path = $base.'modules/mod_jf_simpleslider/assets/';
$jf_doc = JFactory::getDocument();

require_once dirname(__FILE__).'/jf_ss_mob_det.php';
$jf_ss_mob_det = new JF_SS_Mobile_Detect;

/* START - FUNCTIONS ==================================================================================================== */

	// Slide #1 params
		$jf_ss_slide_1					= $params->get('jf_ss_slide_1');
		$jf_ss_slide_1_color			= $params->get('jf_ss_slide_1_color');
		$jf_ss_slide_1_html				= $params->get('jf_ss_slide_1_html');
	// Slide #2 params
		$jf_ss_slide_2					= $params->get('jf_ss_slide_2');
		$jf_ss_slide_2_color			= $params->get('jf_ss_slide_2_color');
		$jf_ss_slide_2_html				= $params->get('jf_ss_slide_2_html');
	// Slide #3 params
		$jf_ss_slide_3					= $params->get('jf_ss_slide_3');
		$jf_ss_slide_3_color			= $params->get('jf_ss_slide_3_color');
		$jf_ss_slide_3_html				= $params->get('jf_ss_slide_3_html');
	// Slide #4 params
		$jf_ss_slide_4					= $params->get('jf_ss_slide_4');
		$jf_ss_slide_4_color			= $params->get('jf_ss_slide_4_color');
		$jf_ss_slide_4_html				= $params->get('jf_ss_slide_4_html');
	// Slide #5 params
		$jf_ss_slide_5					= $params->get('jf_ss_slide_5');
		$jf_ss_slide_5_color			= $params->get('jf_ss_slide_5_color');
		$jf_ss_slide_5_html				= $params->get('jf_ss_slide_5_html');
	// Slide #6 params
		$jf_ss_slide_6					= $params->get('jf_ss_slide_6');
		$jf_ss_slide_6_color			= $params->get('jf_ss_slide_6_color');
		$jf_ss_slide_6_html				= $params->get('jf_ss_slide_6_html');
	// Slide #7 params
		$jf_ss_slide_7					= $params->get('jf_ss_slide_7');
		$jf_ss_slide_7_color			= $params->get('jf_ss_slide_7_color');
		$jf_ss_slide_7_html				= $params->get('jf_ss_slide_7_html');
	// Slide #8 params
		$jf_ss_slide_8					= $params->get('jf_ss_slide_8');
		$jf_ss_slide_8_color			= $params->get('jf_ss_slide_8_color');
		$jf_ss_slide_8_html				= $params->get('jf_ss_slide_8_html');
	// Slide #9 params
		$jf_ss_slide_9					= $params->get('jf_ss_slide_9');
		$jf_ss_slide_9_color			= $params->get('jf_ss_slide_9_color');
		$jf_ss_slide_9_html				= $params->get('jf_ss_slide_9_html');
	// Slide #10 params
		$jf_ss_slide_10					= $params->get('jf_ss_slide_10');
		$jf_ss_slide_10_color			= $params->get('jf_ss_slide_10_color');
		$jf_ss_slide_10_html			= $params->get('jf_ss_slide_10_html');
	// Params
		$jf_ss_ID								= $params->get('jf_ss_ID');
		$jf_ss_type								= $params->get('jf_ss_type');
		$jf_ss_Swipe							= $params->get('jf_ss_Swipe');
		$jf_ss_SwipeDevice						= $params->get('jf_ss_SwipeDevice');
		$jf_ss_Trans							= $params->get('jf_ss_Trans');
		$jf_ss_Auto								= $params->get('jf_ss_Auto');
		$jf_ss_Interval							= $params->get('jf_ss_Interval');
		$jf_ss_Duration							= $params->get('jf_ss_Duration');
		$jf_ss_Easing							= $params->get('jf_ss_Easing');
		$jf_ss_Pause							= $params->get('jf_ss_Pause');
		$jf_ss_Arrows							= $params->get('jf_ss_Arrows');
		$jf_ss_Nav								= $params->get('jf_ss_Nav');

		$jf_ss_CSS3								= $params->get('jf_ss_CSS3');
		$jf_ss_AnimateCSS						= $params->get('jf_ss_AnimateCSS');
		$jf_ss_AnimateFunc						= $params->get('jf_ss_AnimateFunc');

		$jf_ss_styles							= $params->get('jf_ss_styles');
	// Calling
		// CSS
			$jf_doc->addStyleSheet($assets_path.'jf_ss.min.css');
			$jf_doc->addStyleDeclaration(''.$jf_ss_styles.'');
		// JS
			$jf_doc->addScript($assets_path.'jquery.transit.min.js');

			if ($jf_ss_Swipe == 'true') {
				if($jf_ss_SwipeDevice == '1'){
					// Only Mobile
						if($jf_ss_mob_det->isMobile() && !$jf_ss_mob_det->isTablet()){
							// $jf_doc->addScriptDeclaration('alert("Only Mobile");');
								$jf_doc->addScript($assets_path.'jquery.touchSwipe.min.js');
						}
				} elseif ($jf_ss_SwipeDevice == '2'){
					// Mobile + Tablet
						if ($jf_ss_mob_det->isMobile()) {
							// $jf_doc->addScriptDeclaration('alert("Mobile + Tablet");');
								$jf_doc->addScript($assets_path.'jquery.touchSwipe.min.js');
						}
				} elseif ($jf_ss_SwipeDevice == '3'){
					// Only Tablet
						if($jf_ss_mob_det->isTablet()){
							// $jf_doc->addScriptDeclaration('alert("Only Tablet");');
								$jf_doc->addScript($assets_path.'jquery.touchSwipe.min.js');
						}
				} elseif ($jf_ss_SwipeDevice == '4'){
					// Tablets + Desktop
						if(!$jf_ss_mob_det->isMobile() && !$jf_ss_mob_det->isTablet()){
							// $jf_doc->addScriptDeclaration('alert("Tablets + Desktop");');
							$jf_doc->addScript($assets_path.'jquery.touchSwipe.min.js');
						}
				} elseif ($jf_ss_SwipeDevice == '5'){
					// Only Desktop
						if(!$jf_ss_mob_det->isMobile() && !$jf_ss_mob_det->isTablet()){
							// $jf_doc->addScriptDeclaration('alert("Only Desktop");');
							$jf_doc->addScript($assets_path.'jquery.touchSwipe.min.js');
						}
				} else {
					// ON ALL
					// $jf_doc->addScriptDeclaration('alert("ALL Devices");');
						$jf_doc->addScript($assets_path.'jquery.touchSwipe.min.js');
				}
			}
			
			$jf_doc->addScript($assets_path.'jquery.simpleslider.min.js');
			$jf_doc->addScript($assets_path.'jquery.backstretch.min.js');
			// $jf_doc->addScript($assets_path.'jf.min.js');

		// SCRIPT
			$jf_doc->addScriptDeclaration('
				jQuery(document).ready(function($){
					var '.$jf_ss_ID.'	= $("#'.$jf_ss_ID.'");
					'.$jf_ss_ID.'.simpleSlider({
						slides: ".slide", 						// The name of a slide in the slidesContainer
						swipe: '.$jf_ss_Swipe.',				// Add possibility to Swipe > note that you have to include touchSwipe for this
						transition: "'.$jf_ss_Trans.'",			// Accepts "slide" and "fade" for a slide or fade transition
						slideTracker: true,						// Add a UL with list items to track the current slide
						slideTrackerID: "slideposition_'.$jf_ss_ID.'",		// The name of the UL that tracks the slides
						slideOnInterval: '.$jf_ss_Auto.',		// Slide on interval
						interval: '.$jf_ss_Interval.',			// Interval to slide on if slideOnInterval is enabled
						animateDuration: '.$jf_ss_Duration.',	// Duration of an animation
						animationEasing: "'.$jf_ss_Easing.'",	// Accepts: linear ease in out in-out snap easeOutCubic easeInOutCubic easeInCirc easeOutCirc easeInOutCirc easeInExpo easeOutExpo easeInOutExpo easeInQuad easeOutQuad easeInOutQuad easeInQuart easeOutQuart easeInOutQuart easeInQuint easeOutQuint easeInOutQuint easeInSine easeOutSine easeInOutSine easeInBack easeOutBack easeInOutBack
						pauseOnHover: '.$jf_ss_Pause.'			// Pause when user hovers the slide container
					});
					'.$jf_ss_ID.'.find(".caption").first().show();

					'.$jf_ss_ID.'.on("beforeSliding", function(event){
						var prevSlide = event.prevSlide;
						var newSlide = event.newSlide;
						
						'.$jf_ss_ID.'.find(".slide[data-index=\""+prevSlide+"\"] [data-animation]").each(function(){
							$(this).css("-webkit-animation-delay","");
						});
						'.$jf_ss_ID.'.find(".slide[data-index=\""+prevSlide+"\"] .caption").fadeOut();
						'.$jf_ss_ID.'.find(".slide[data-index=\""+newSlide+"\"] .caption").fadeOut();
						// alert("beforeSliding");
					});

					'.$jf_ss_ID.'.on("afterSliding", function(event){
						var prevSlide = event.prevSlide;
						var newSlide = event.newSlide;
						
						'.$jf_ss_ID.'.find(".slide[data-index=\""+newSlide+"\"] .caption").fadeIn();
						// alert("afterSliding");
						'.$jf_ss_ID.'.find(".slide[data-index=\""+newSlide+"\"] .caption").find("[data-animation]").each(function() {
							var self_new = $(this);
							self_new.addClass("new");
							var animation_new = self_new.data("animation");
							var delay_new = (self_new.data("animation-delay") ? self_new.data("animation-delay") : 0);
							
							setTimeout(function(){
								self_new.css("animation-delay", delay_new + "s").css("-webkit-animation-delay", delay_new + "s");
								self_new.addClass(animation_new);
								self_new.addClass("animated").addClass("jf_anim_done");
								self_new.one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
									$(this).removeClass("animated").removeClass(animation_new);
								});
							}, delay_new);
						});
					});
					
					var slider_'.$jf_ss_ID.' = '.$jf_ss_ID.'.data("simpleslider");
					$(".jf_ss nav a.next").on("click", function() {
						slider_'.$jf_ss_ID.'.nextSlide(); // Go to the next slide
					});
					$(".jf_ss nav a.prev").on("click", function() {
						slider_'.$jf_ss_ID.'.prevSlide(); // Go to the next slide
					});
					
					'.(($jf_ss_slide_1)?''.$jf_ss_ID.'.find("#jf_ss_1").backstretch("'.$base.$jf_ss_slide_1.'");':"").'
					'.(($jf_ss_slide_2)?''.$jf_ss_ID.'.find("#jf_ss_2").backstretch("'.$base.$jf_ss_slide_2.'");':"").'
					'.(($jf_ss_slide_3)?''.$jf_ss_ID.'.find("#jf_ss_3").backstretch("'.$base.$jf_ss_slide_3.'");':"").'
					'.(($jf_ss_slide_4)?''.$jf_ss_ID.'.find("#jf_ss_4").backstretch("'.$base.$jf_ss_slide_4.'");':"").'
					'.(($jf_ss_slide_5)?''.$jf_ss_ID.'.find("#jf_ss_5").backstretch("'.$base.$jf_ss_slide_5.'");':"").'
					'.(($jf_ss_slide_6)?''.$jf_ss_ID.'.find("#jf_ss_6").backstretch("'.$base.$jf_ss_slide_6.'");':"").'
					'.(($jf_ss_slide_7)?''.$jf_ss_ID.'.find("#jf_ss_7").backstretch("'.$base.$jf_ss_slide_7.'");':"").'
					'.(($jf_ss_slide_8)?''.$jf_ss_ID.'.find("#jf_ss_8").backstretch("'.$base.$jf_ss_slide_8.'");':"").'
					'.(($jf_ss_slide_9)?''.$jf_ss_ID.'.find("#jf_ss_9").backstretch("'.$base.$jf_ss_slide_9.'");':"").'
					'.(($jf_ss_slide_10)?''.$jf_ss_ID.'.find("#jf_ss_10").backstretch("'.$base.$jf_ss_slide_10.'");':"").'

					'.$jf_ss_ID.'.find(".backstretch img").on("dragstart",function(event){event.preventDefault();});
					
					// Create class for Navigation
						$("#slideposition_'.$jf_ss_ID.'").addClass("jf_pagination");
				});
			');
			// NAVIGATION
			if (!$jf_ss_Nav) {$jf_doc->addStyleDeclaration('#slideposition_'.$jf_ss_ID.'{display:none!important}');}
			
		// ANIMATE CSS
			$jf_doc->addStyleDeclaration('.animated{-webkit-animation-duration:1s;animation-duration:1s;-webkit-animation-fill-mode:both;animation-fill-mode:both}.animated.infinite{-webkit-animation-iteration-count:infinite;animation-iteration-count:infinite}.animated.hinge{-webkit-animation-duration:2s;animation-duration:2s}[data-animation]{opacity:0;filter:alpha(opacity=0)}.jf_anim_done[data-animation]{opacity:1;filter:alpha(opacity=100)}');
			if ($jf_ss_CSS3 == '1') {
				$jf_doc->addStyleSheet(''.$jf_ss_AnimateCSS.'');
			} elseif ($jf_ss_CSS3 == '2') {
				$jf_doc->addStyleSheet($assets_path.'custom_css3.css');
			} else {
				// DISABLE
			}
			if ($jf_ss_AnimateFunc) {
				$jf_doc->addScriptDeclaration('
					jQuery(document).ready(function($){
						$(".jf_ss [data-animation]").each(function(){
							var self = $(this);
							var animation = self.data("animation");
							var delay = (self.data("animation-delay") ? self.data("animation-delay") : 0);
							setTimeout(function(){
								self.css("animation-delay", delay + "s").css("-webkit-animation-delay", delay + "s");
								self.addClass(animation);
								self.addClass("animated").addClass("jf_anim_done");
								self.one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend", function(){
									$(this).removeClass("animated").removeClass(animation);
								});
							}, delay);
						});
					});
				');
			}
/*   END - FUNCTIONS ==================================================================================================== */


$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_jf_simpleslider', $params->get('layout', 'default'));