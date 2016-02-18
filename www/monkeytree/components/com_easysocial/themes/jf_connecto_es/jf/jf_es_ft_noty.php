<?php
/**
* @version		JF_ES_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');JHtml::_('jquery.framework');$jf_doc=JFactory::getDocument();$jf_es_Noty=$this->template->get('jf_es_Noty');$jf_es_NotyText_p=$this->template->get('jf_es_NotyText_p');$jf_es_NotyText_h5=$this->template->get('jf_es_NotyText_h5');$jf_es_NotyText_h2=$this->template->get('jf_es_NotyText_h2');$jf_es_NotySocial=$this->template->get('jf_es_NotySocial');if($jf_es_Noty){$jf_doc->addScriptDeclaration('jQuery(document).ready(function(e){e("a.jf_es_noty_btn").click(function(){e(this).next().addClass("show");var s=e(this).next().find(".jf_es_custom_html h5"),_=e(this).next().find(".jf_es_custom_html p");e(this).next().find(".jf_es_social_btns").addClass("jf_es_bounceInDownBig jf_es_animated_1"),e(this).next().find(".jf_es_custom_html h2").addClass("jf_es_bounceInDownBig jf_es_animated_2"),setTimeout(function(){s.addClass("jf_es_bounceInDownBig jf_es_animated_2")},1e3),setTimeout(function(){_.addClass("jf_es_bounceInDownBig jf_es_animated_2")},2e3)}),e(".jf_es_noty_overlay").click(function(){e(this).parent().removeClass("show"),e(this).next().find(".jf_es_social_btns").removeClass("jf_es_bounceInDownBig jf_es_animated_1"),e(this).next().find(".jf_es_custom_html h2").removeClass("jf_es_bounceInDownBig jf_es_animated_2"),e(this).next().find(".jf_es_custom_html h5").removeClass("jf_es_bounceInDownBig jf_es_animated_2"),e(this).next().find(".jf_es_custom_html p").removeClass("jf_es_bounceInDownBig jf_es_animated_2")})});');if($jf_es_NotySocial){$jf_es_NotySocial_Google=$this->template->get('jf_es_NotySocial_Google');$jf_es_NotySocial_FB=$this->template->get('jf_es_NotySocial_FB');$jf_es_NotySocial_Tw=$this->template->get('jf_es_NotySocial_Tw');$jf_doc->addScriptDeclaration('(function($){$(window).load(function(){$("a.jf_es_noty_btn").click(function(){setTimeout(function(){if(jQuery(".twitter-follow-button").length){}else{simulateAjaxRequest()}},2000)})})})(jQuery);function loadSocial(){if(jQuery(".twitter-follow-button").length==0)return;if(typeof(twttr)!="undefined"){twttr.widgets.load()}else{jQuery.getScript("http://platform.twitter.com/widgets.js")}if(typeof(FB)!="undefined"){FB.init({status:true,cookie:true,xfbml:true})}else{jQuery.getScript("http://connect.facebook.net/en_US/all.js#xfbml=1",function(){FB.init({status:true,cookie:true,xfbml:true})})}if(typeof(gapi)!="undefined"){jQuery(".g-follow").each(function(){gapi.plusone.render(jQuery(this).get(0))})}else{jQuery.getScript("https://apis.google.com/js/platform.js")}}function simulateAjaxRequest(){var html="<div class=\"g-follow\" data-annotation=\"none\" data-height=\"20\" data-href=\"https://plus.google.com/'.$jf_es_NotySocial_Google.'\" data-rel=\"publisher\"></div>";html+="<div style=\"vertical-align:5px;margin:0 0 10px 5px\" class=\"fb-like\" data-href=\"https://www.facebook.com/'.$jf_es_NotySocial_FB.'\" data-layout=\"button\" data-action=\"like\" data-show-faces=\"true\" data-share=\"true\"></div><div id=\"fb-root\"></div>";html+="<a class=\"twitter-follow-button\" href=\"https://twitter.com/'.$jf_es_NotySocial_Tw.'\" data-show-count=\"false\" data-lang=\"en\">Follow</a>";jQuery(".jf_es_noty_content .jf_es_social_btns").html(html);loadSocial()}');}}
?>
<?php if ($this->my->id){ ?>
	<?php if ($jf_es_Noty) { ?>
		<a class="jf_es_noty_btn loader-2 pull-left" href="javascript:void(0);"></a>
		<div class="jf_es_noty_dialog">
			<div class="jf_es_noty_overlay"></div>
			<div class="jf_es_noty_content">
				<div class="wrap">
					<div class="jf_es_custom_html">
						<p><?php echo $jf_es_NotyText_p; ?></p>
						<h5><?php echo $jf_es_NotyText_h5; ?></h5>
						<h2><?php echo $jf_es_NotyText_h2; ?></h2>
					</div>
					<?php if ($jf_es_NotySocial) { ?><div class="jf_es_social_btns"><div class="jf_es_noty_btn loader-2 light"></div></div><?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } ?>