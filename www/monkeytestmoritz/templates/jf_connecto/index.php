<?php
/**
* @version		JF_DTF_078
* @author		JoomForest http://www.joomforest.com
* @copyright	Copyright (C) 2011-2015 JoomForest.com
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

// load and inititialize gantry class
require_once(dirname(__FILE__) . '/lib/gantry/gantry.php');
$gantry->init();

// get the current preset
$gpreset = str_replace(' ','',strtolower($gantry->get('name')));

?>
<!doctype html>
<html xml:lang="<?php echo $gantry->language; ?>" lang="<?php echo $gantry->language;?>" >
<head>
	<?php if ($gantry->get('layout-mode') == '960fixed') : ?>
	<meta name="viewport" content="width=960px, initial-scale=1, minimum-scale=1, maximum-scale=1">
	<?php elseif ($gantry->get('layout-mode') == '1200fixed') : ?>
	<meta name="viewport" content="width=1200px, initial-scale=1, minimum-scale=1, maximum-scale=1">
	<?php else : ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php endif; ?>
<?php if ($gantry->browser->name == 'ie') : ?>
	<meta content="IE=edge" http-equiv="X-UA-Compatible" />
<?php endif; ?>	
    <?php
        $gantry->displayHead();
		$gantry->addStyle('grid-responsive.css', 5);
		// $gantry->addLess('bootstrap.less', 'bootstrap.css', 6);
        if ($gantry->browser->name == 'ie'){
        	if ($gantry->browser->shortversion == 9){
        		$gantry->addInlineScript("if (typeof RokMediaQueries !== 'undefined') window.addEvent('domready', function(){ RokMediaQueries._fireEvent(RokMediaQueries.getQuery()); });");
        	}
			if ($gantry->browser->shortversion == 8){
				$gantry->addScript('html5shim.js');
			}
		}
		if ($gantry->get('layout-mode', 'responsive') == 'responsive') $gantry->addScript('rokmediaqueries.js');
		if ($gantry->get('loadtransition')) {
		$gantry->addScript('load-transition.js');
		$hidden = ' class="rt-hidden"';}
		// include JF TOOLS
		require_once dirname(__FILE__).'/jf_tools.php';
    ?>
</head>
<body <?php echo $gantry->displayBodyTag(); ?>>
	<?php /** Begin JF PRELOADER **/ if ($gantry->countModules('jf-preloader')) : ?>
		<?php echo $gantry->displayModules('jf-preloader','basic','basic'); ?>
	<?php /** End JS PRELOADER **/ endif; ?>
	<?php /** Begin Top **/ if ($gantry->countModules('top')) : ?>
		<div id="rt-top" <?php echo $gantry->displayClassesByTag('rt-top'); ?>>
			<div class="rt-container">
				<?php echo $gantry->displayModules('top','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
	<?php /** End Top **/ endif; ?>

	<?php /** Begin Header **/ if ($gantry->countModules('header')) : ?>
	<header id="rt-top-surround">
		<div class="jf_head_set"></div>
		<div id="rt-header">
			<div class="rt-container">
				<?php echo $gantry->displayModules('header','standard','standard'); ?>
				<div id="jf_undermenu" class="rt-grid">
					<div class="rt-block">
						<?php /** Begin JF Login **/ if ($gantry->countModules('jf-login')) : ?>
							<?php echo $gantry->displayModules('jf-login','basic','basic'); ?>
						<?php /** End JF Login **/ endif; ?>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
			$menu 				= JFactory::getApplication()->getMenu();
			$active 			= $menu->getActive();
			if (is_object($active)) {
				$pageHeading 		= $active->params->get('page_heading');
				$show_pageHeading	= $active->params->get('show_page_heading');
				// CALL
				if($pageHeading != ''){ // or - if($pageHeading != '' && $show_pageHeading){
		?>
			<div id="jf_page_heading">
				<div class="rt-container">
					<div class="rt-block">
						<h1><?php echo $pageHeading; ?></h1>
						<?php echo $gantry->displayModules('jf-page-heading','basic','basic'); ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		<?php } } ?>
	</header>
	<?php /** End Top Surround **/ endif; ?>
	
	<?php /** Begin JF Slideshow **/ if ($gantry->countModules('jf-slideshow')) : ?>
	<section id="jf_slideshow">
		<?php echo $gantry->displayModules('jf-slideshow','basic','basic'); ?>
		<div class="clear"></div>
	</section>
	<?php /** End JF Slideshow **/ endif; ?>
	

	<?php /** Begin JF section **/ if ($gantry->countModules('jf-section')) : ?>
		<?php echo $gantry->displayModules('jf-section','basic','basic'); ?>
		<div class="clear"></div>
	<?php /** End JF section **/ endif; ?>
	
	<?php /** Begin Drawer **/ if ($gantry->countModules('drawer')) : ?>
		<div id="rt-drawer">
			<div class="rt-container">
				<?php echo $gantry->displayModules('drawer','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
	<?php /** End Drawer **/ endif; ?>

	<div id="rt-transition"<?php if ($gantry->get('loadtransition')) echo $hidden; ?>>
		<div id="rt-mainbody-surround">
	
			<?php /** Begin Showcase **/ if ($gantry->countModules('showcase')) : ?>
			<div id="rt-showcase">
				<div class="rt-showcase-pattern">
					<div class="rt-container">
						<?php echo $gantry->displayModules('showcase','standard','standard'); ?>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<?php /** End Showcase **/ endif; ?>
			
			<?php /** Begin Main Top **/ if ($gantry->countModules('maintop')) : ?>
			<div id="rt-maintop">
				<div class="rt-container">
					<?php echo $gantry->displayModules('maintop','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Main Top **/ endif; ?>

			<?php /** Begin Feature **/ if ($gantry->countModules('feature')) : ?>
			<div id="rt-feature">
				<div class="rt-container">
					<?php echo $gantry->displayModules('feature','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Feature **/ endif; ?>
			
			<?php /** Begin Utility **/ if ($gantry->countModules('utility')) : ?>
			<div id="rt-utility">
				<div class="rt-container">
					<?php echo $gantry->displayModules('utility','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Utility **/ endif; ?>

			<?php /** Begin Full Width**/ if ($gantry->countModules('fullwidth')) : ?>
				<div id="rt-fullwidth">
					<?php echo $gantry->displayModules('fullwidth','basic','basic'); ?>
					<div class="clear"></div>
				</div>
			<?php /** End Full Width **/ endif; ?>
			
			<?php /** Begin Main Body **/ ?>
			<div class="rt-container">
				<?php echo $gantry->displayMainbody('mainbody','sidebar','standard','standard','standard','standard','standard'); ?>
			</div>
			<?php /** End Main Body **/ ?>
			
			<?php /** Begin Main Bottom **/ if ($gantry->countModules('mainbottom')) : ?>
			<div id="rt-mainbottom">
				<div class="rt-container">
					<?php echo $gantry->displayModules('mainbottom','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Main Bottom **/ endif; ?>
			
			<?php /** Begin Extension **/ if ($gantry->countModules('extension')) : ?>
			<div id="rt-extension">
				<div class="rt-container">
					<?php echo $gantry->displayModules('extension','standard','standard'); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php /** End Extension **/ endif; ?>
			
			<?php /** Begin Breadcrumbs **/ if ($gantry->countModules('breadcrumb')) : ?>
			<div id="rt-breadcrumbs">
				<div class="rt-container">
					<div class="rt-grid">
						<div class="rt-block">
							<?php echo $gantry->displayModules('breadcrumb','basic','basic'); ?>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
			<?php /** End Breadcrumbs **/ endif; ?>
		</div>
	</div>
	
	<?php /** Begin Bottom **/ if ($gantry->countModules('bottom')) : ?>
	<div id="rt-bottom">
		<div class="rt-container">
			<?php echo $gantry->displayModules('bottom','standard','standard'); ?>
			<div class="clear"></div>
		</div>
	</div>
	<?php /** End Bottom **/ endif; ?>
	
	<?php if ($gantry->countModules('footer') or $gantry->countModules('copyright')) : ?>
	<footer id="rt-footer-surround">
		<?php /** Begin Footer **/ if ($gantry->countModules('footer')) : ?>
		<div id="rt-footer">
			<div class="rt-container">
				<?php echo $gantry->displayModules('footer','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php /** End Footer **/ endif; ?>
		
		<?php /** Begin Copyright **/ if ($gantry->countModules('copyright')) : ?>
		<div id="rt-copyright">
			<div class="rt-container">
				<?php echo $gantry->displayModules('copyright','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php /** End Copyright **/ endif; ?>
		
	</footer>
	<?php /** End Footer Surround **/ endif; ?>

	<?php /** Begin Debug **/ if ($gantry->countModules('debug')) : ?>
	<div id="rt-debug">
		<div class="rt-container">
			<?php echo $gantry->displayModules('debug','standard','standard'); ?>
			<div class="clear"></div>
		</div>
	</div>
	<?php /** End Debug **/ endif; ?>
	
	<?php /** Begin JF SCROLLTOP **/ if ($gantry->countModules('jf-scrolltop')) : ?>
		<?php echo $gantry->displayModules('jf-scrolltop','basic','basic'); ?>
	<?php /** End JF SCROLLTOP **/ endif; ?>
	
	<?php /** Begin Analytics **/ if ($gantry->countModules('analytics')) : ?>
		<?php echo $gantry->displayModules('analytics','basic','basic'); ?>
	<?php /** End Analytics **/ endif; ?>

	<?php /** Begin JF STYLESWITCHER **/ if ($gantry->countModules('jf-styleswitcher')) : ?>
		<?php echo $gantry->displayModules('jf-styleswitcher','basic','basic'); ?>
		<div class="clear"></div>
	<?php /** End JS STYLESWITCHER **/ endif; ?>
	
	<?php /** Begin JF MOBILEMENU **/ if ($gantry->countModules('jf-mobilemenu')) : ?>
		<?php echo $gantry->displayModules('jf-mobilemenu','basic','basic'); ?>
	<?php /** End JS MOBILEMENU **/ endif; ?>
</body>
</html>
<?php
$gantry->finalize();
?>
