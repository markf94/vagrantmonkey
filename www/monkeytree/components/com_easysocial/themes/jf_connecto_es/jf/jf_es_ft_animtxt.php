<?php
/**
* @version		JF_ES_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');JHtml::_('jquery.framework');$jf_doc=JFactory::getDocument();$jf_base=JURI::root();$jf_es_tmpl_defname='wireframe';$jf_es_tmpl_name='jf_connecto_es';$jf_assets_path=$jf_base.'components/com_easysocial/themes/'.$jf_es_tmpl_name.'/jf/assets/';$jf_es_Animtxt=$this->template->get('jf_es_Animtxt');$jf_es_Animtxt_effect=$this->template->get('jf_es_Animtxt_effect');$jf_es_Animtxt_delay=$this->template->get('jf_es_Animtxt_delay');$jf_es_Animtxt_1=$this->template->get('jf_es_Animtxt_1');$jf_es_Animtxt_2=$this->template->get('jf_es_Animtxt_2');$jf_es_Animtxt_3=$this->template->get('jf_es_Animtxt_3');$jf_es_Animtxt_4=$this->template->get('jf_es_Animtxt_4');$jf_es_Animtxt_5=$this->template->get('jf_es_Animtxt_5');$jf_es_Animtxt_6=$this->template->get('jf_es_Animtxt_6');$jf_es_Animtxt_7=$this->template->get('jf_es_Animtxt_7');$jf_es_Animtxt_8=$this->template->get('jf_es_Animtxt_8');$jf_es_Animtxt_9=$this->template->get('jf_es_Animtxt_9');$jf_es_Animtxt_10=$this->template->get('jf_es_Animtxt_10');$jf_doc->addStyleSheet($jf_assets_path.'features/animtext/animtxt.min.css');$jf_doc->addScript($jf_assets_path.'features/animtext/modernizr.js');$jf_doc->addScript($jf_assets_path.'features/animtext/main.min.js');$jf_doc->addScriptDeclaration('var jf_es_animtxt_global_delay='.$jf_es_Animtxt_delay.';var animationDelay=jf_es_animtxt_global_delay+500,barAnimationDelay=jf_es_animtxt_global_delay+800,barWaiting=barAnimationDelay-jf_es_animtxt_global_delay,lettersDelay=50,typeLettersDelay=150,selectionDuration=500,typeAnimationDelay=selectionDuration+800,revealDuration=600,revealAnimationDelay=jf_es_animtxt_global_delay;');
?>
<?php if ($this->my->id){ ?>
	<?php if ($jf_es_Animtxt) { ?>
		<div class="cd-headline <?php echo $jf_es_Animtxt_effect; ?>">
			<span></span> 
			<span class="cd-words-wrapper waiting">
				<?php if($jf_es_Animtxt_1 != '') { ?><b class="is-visible"><?php echo $jf_es_Animtxt_1; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_2 != '') { ?><b><?php echo $jf_es_Animtxt_2; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_3 != '') { ?><b><?php echo $jf_es_Animtxt_3; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_4 != '') { ?><b><?php echo $jf_es_Animtxt_4; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_5 != '') { ?><b><?php echo $jf_es_Animtxt_5; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_6 != '') { ?><b><?php echo $jf_es_Animtxt_6; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_7 != '') { ?><b><?php echo $jf_es_Animtxt_7; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_8 != '') { ?><b><?php echo $jf_es_Animtxt_8; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_9 != '') { ?><b><?php echo $jf_es_Animtxt_9; ?></b><?php } ?>
				<?php if($jf_es_Animtxt_10 != '') { ?><b><?php echo $jf_es_Animtxt_10; ?></b><?php } ?>
			</span>
		</div>
	<?php } ?>
<?php } ?>