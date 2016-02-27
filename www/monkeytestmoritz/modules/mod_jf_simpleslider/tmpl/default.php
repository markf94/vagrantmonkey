<?php
/**
 * JF SimpleSlider
 * @author		JoomForest.com
 * @email		support@joomforest.com
 * @website		http://www.joomforest.com
 * @copyright	Copyright (C) 2011-2015 JoomForest. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php if($jf_ss_type == '2') { ?><section class="jf_ss_testimonials"><?php } ?>
<div id="<?php echo $jf_ss_ID; ?>" class="jf_ss">
	<?php if($jf_ss_slide_1 != '' || $jf_ss_slide_1_color != '' || $jf_ss_slide_1_html != '') { ?><div class="slide" id="jf_ss_1" style="background-color:<?php echo $jf_ss_slide_1_color; ?>"><?php if($jf_ss_slide_1_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_1_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_2 != '' || $jf_ss_slide_2_color != '' || $jf_ss_slide_2_html != '') { ?><div class="slide" id="jf_ss_2" style="background-color:<?php echo $jf_ss_slide_2_color; ?>"><?php if($jf_ss_slide_2_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_2_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_3 != '' || $jf_ss_slide_3_color != '' || $jf_ss_slide_3_html != '') { ?><div class="slide" id="jf_ss_3" style="background-color:<?php echo $jf_ss_slide_3_color; ?>"><?php if($jf_ss_slide_3_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_3_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_4 != '' || $jf_ss_slide_4_color != '' || $jf_ss_slide_4_html != '') { ?><div class="slide" id="jf_ss_4" style="background-color:<?php echo $jf_ss_slide_4_color; ?>"><?php if($jf_ss_slide_4_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_4_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_5 != '' || $jf_ss_slide_5_color != '' || $jf_ss_slide_5_html != '') { ?><div class="slide" id="jf_ss_5" style="background-color:<?php echo $jf_ss_slide_5_color; ?>"><?php if($jf_ss_slide_5_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_5_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_6 != '' || $jf_ss_slide_6_color != '' || $jf_ss_slide_6_html != '') { ?><div class="slide" id="jf_ss_6" style="background-color:<?php echo $jf_ss_slide_6_color; ?>"><?php if($jf_ss_slide_6_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_6_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_7 != '' || $jf_ss_slide_7_color != '' || $jf_ss_slide_7_html != '') { ?><div class="slide" id="jf_ss_7" style="background-color:<?php echo $jf_ss_slide_7_color; ?>"><?php if($jf_ss_slide_7_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_7_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_8 != '' || $jf_ss_slide_8_color != '' || $jf_ss_slide_8_html != '') { ?><div class="slide" id="jf_ss_8" style="background-color:<?php echo $jf_ss_slide_8_color; ?>"><?php if($jf_ss_slide_8_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_8_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_9 != '' || $jf_ss_slide_9_color != '' || $jf_ss_slide_9_html != '') { ?><div class="slide" id="jf_ss_9" style="background-color:<?php echo $jf_ss_slide_9_color; ?>"><?php if($jf_ss_slide_9_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_9_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_slide_10 != '' || $jf_ss_slide_10_color != '' || $jf_ss_slide_10_html != '') { ?><div class="slide" id="jf_ss_10" style="background-color:<?php echo $jf_ss_slide_10_color; ?>"><?php if($jf_ss_slide_10_html != '') { ?><div class="caption"><?php echo $jf_ss_slide_10_html; ?></div><?php } ?></div><?php } ?>
	<?php if($jf_ss_Arrows){ ?>
		<nav class="nav-circlepop">
			<a class="prev" href="javascript:void(0);"><span class="icon-wrap"></span></a>
			<a class="next" href="javascript:void(0);"><span class="icon-wrap"></span></a>
		</nav>
	<?php } ?>
</div>
<?php if($jf_ss_type == '2') { ?></section><?php } ?>