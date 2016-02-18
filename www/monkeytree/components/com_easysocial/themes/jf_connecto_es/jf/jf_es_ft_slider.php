<?php
/**
* @version		JF_ES_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');JHtml::_('jquery.framework');$jf_doc=JFactory::getDocument();$jf_base=JURI::root();$jf_es_tmpl_defname='wireframe';$jf_es_tmpl_name='jf_connecto_es';$jf_assets_path=$jf_base.'components/com_easysocial/themes/'.$jf_es_tmpl_name.'/jf/assets/';$jf_es_Slider=$this->template->get('jf_es_Slider');$jf_es_Slider_prepend=$this->template->get('jf_es_Slider_prepend');$jf_es_Slider_h=$this->template->get('jf_es_Slider_h');$jf_es_Slider_speed=$this->template->get('jf_es_Slider_speed');$jf_es_Slider_delay=$this->template->get('jf_es_Slider_delay');$jf_es_Slider_html_h1=$this->template->get('jf_es_Slider_html_h1');$jf_es_Slider_html_h3=$this->template->get('jf_es_Slider_html_h3');$jf_es_Slider_img_1=$this->template->get('jf_es_Slider_img_1');$jf_es_Slider_img_2=$this->template->get('jf_es_Slider_img_2');$jf_es_Slider_img_3=$this->template->get('jf_es_Slider_img_3');$jf_es_Slider_img_4=$this->template->get('jf_es_Slider_img_4');$jf_es_Slider_img_5=$this->template->get('jf_es_Slider_img_5');$jf_es_Slider_img_6=$this->template->get('jf_es_Slider_img_6');$jf_es_Slider_img_7=$this->template->get('jf_es_Slider_img_7');$jf_es_Slider_img_8=$this->template->get('jf_es_Slider_img_8');$jf_es_Slider_maskColor=$this->template->get('jf_es_Slider_maskColor');$jf_es_Slider_maskOpacy=$this->template->get('jf_es_Slider_maskOpacy');if($jf_es_Slider){$jf_doc->addScript($jf_assets_path.'features/rslides/responsiveslides.min.js');$jf_doc->addStyleSheet($jf_assets_path.'features/rslides/jf_rslides.min.css');$jf_doc->addStyleDeclaration('body #jf_es_bgslideshow:after{background:'.$jf_es_Slider_maskColor.';opacity:0.'.$jf_es_Slider_maskOpacy.';filter:alpha(opacity='.$jf_es_Slider_maskOpacy.'0)}');$jf_doc->addScriptDeclaration('jQuery(document).ready(function($){$("'.$jf_es_Slider_prepend.'").addClass("jf_es_frontslider");$("#jf_es_bgslideshow").prependTo(".jf_es_frontslider");$("#jf_es_bgslideshow").responsiveSlides({speed:'.$jf_es_Slider_speed.',timeout:'.$jf_es_Slider_delay.'})});');}
?>
<?php if ($jf_es_Slider) { ?>
	<ul id="jf_es_bgslideshow" class="rslides" style="height:<?php echo $jf_es_Slider_h; ?>">
		<?php if($jf_es_Slider_img_1 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_1; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_2 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_2; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_3 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_3; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_4 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_4; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_5 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_5; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_6 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_6; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_7 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_7; ?>)"></li><?php } ?>
		<?php if($jf_es_Slider_img_8 != '') { ?><li style="background-image:url(<?php echo $jf_base; ?><?php echo $jf_es_Slider_img_8; ?>)"></li><?php } ?>
	</ul>
	<div class="jf_es_sl_content">
		<h1><?php echo $jf_es_Slider_html_h1; ?></h1>
		<h3><?php echo $jf_es_Slider_html_h3; ?></h3>
	</div>
<?php } ?>