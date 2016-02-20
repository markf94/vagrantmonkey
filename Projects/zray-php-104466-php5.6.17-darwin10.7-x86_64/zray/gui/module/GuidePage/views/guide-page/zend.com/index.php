<?php
/**
 * This file is located on Zend.com site.
 * It's under SVN only for versioning
 */
 
// for dev. env., Updates class isn't accessible, skip it
$updatesClassPath = './classes/Updates.php';
$updatesClassAccessible = file_exists($updatesClassPath) && is_readable($updatesClassPath);
if ($updatesClassAccessible) {
	include ($updatesClassPath);
}

if (!isset($reqData)) {
	$reqData = array();
}
$reqData['ip'] = 			isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$reqData['php'] = 			isset($_GET['php']) ? $_GET['php'] : '';
$reqData['zs'] = 			isset($_GET['revision']) ? $_GET['revision'] : ''; // Workaround - using revision instead of zs
$reqData['edition'] = 		isset($_GET['edition']) ? $_GET['edition'] : '';
$reqData['profile'] = 		isset($_GET['profile']) ? $_GET['profile'] : '';
$reqData['os'] = 			isset($_GET['os']) ? $_GET['os'] : '';
$reqData['arch'] = 			isset($_GET['arch']) ? $_GET['arch'] : '';
$reqData['hash'] = 			isset($_GET['hash']) ? $_GET['hash'] : '';

// If os == OS400 don't write the "arch" parameter to analytics - approved by product manager (Guy Harpaz, 7.1.2014)
if (isset($reqData['os']) &&  $reqData['os'] == 'OS400') {
    unset($reqData['os']);
}
$ip = $reqData['ip'];
unset($reqData['ip']);

$label = implode('/', $reqData);
$reqData['ip'] = $ip;

if ($updatesClassAccessible) {
	$updates = new Updates($reqData, $label);
	$updates->fireHit();
	$updates->fireEvents('GuidePage', 'Zray', $label);
}
 
if (isset($_GET['t']) && (filemtime(__FILE__) - $_GET['t'] < -5)) {
    echo 'OK';
    exit;
}
?>
<?php 
/**
 * This file is also located under the URL "https://www.zend.com/zray/redirect/welcome"
 * For every change, please keep in mind that the file can be accessed directly (without ZF2 controller (GUI))
 */
 
// if coming from within zend server controller, display the content
// (the content was taken from `zend.com`)
if (isset($welcomeContent) && !empty($welcomeContent)) { 
	echo str_replace('%BASE_PATH%', $this->basePath(), $welcomeContent);
	return;
}

// if reached here, than this view is accessed from within our GUI
if (!isset($viewCalledFromZS)) {
	$viewCalledFromZS = false;
}

// define revisions of releases
$techPreview2Revision = 101668;

// get the revision number
$revisionNumber = 0;
if (isset($revision)) {
	$revisionNumber = $revision;
} elseif (isset($_GET['revision']) && !empty($_GET['revision']) && is_numeric($_GET['revision']) && $_GET['revision'] > 0) {
	$revisionNumber = $_GET['revision'];
}

// check if there's a new version
$thereIsNewVersion = (empty($revisionNumber) || $revisionNumber < $techPreview2Revision);

// check if the current version is prior to "technical preview 2" version
$priorToTechPreview2 = (empty($revisionNumber) || $revisionNumber < $techPreview2Revision);
?>
<style>
#main-container{
	padding:0;
}
#bread-wrp {
	margin: -10px 0px 20px -10px;
}
#welcome-page {
	margin-left: -10px;
}
#welcome-page .font-md{
	font-size:18px;
}
#welcome-page .btn{
	display:inline-block;
	padding: 5px 30px 5px 10px;
	position:relative;
}
#welcome-page .black-btn{
	background: #f39c2b;
	color:#fff;
}

#welcome-devbar img.devbar-preview {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    margin-bottom: -59px;
    width: 100%;
}

/*	FOOTER */
#welcome-page footer{
	background:#3b3b3b;
	padding: 15px 0;
}
#welcome-page footer h2{
	color: #9B9B9B;
	font-size: 16px;
	font-weight:normal;
	margin-right: 30px;
}
#welcome-page footer .container>* {
	display: inline-block;
	vertical-align:middle;
}
#welcome-page footer nav a {
	background: #585858;
  	padding: 3px 21px 3px 38px;
  	color: #48A9C5;
  	margin-right: 15px;
	font-size: 14px;
	
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	-khtml-border-radius: 4px;
	border-radius: 4px;
}
#welcome-page footer nav a i::before{
	top: 5px;
	font-size: 15px;
  	left: 11px;
  	color: #48A9C5;
}
#welcome-page footer nav a:hover {
	background: #48A9C5;
	color: #fff;
	text-decoration: none;
}
#welcome-page footer nav a:hover i::before{
	color: #fff;
}
/*	EOF FOOTER */



#welcome-page .container{
	width:1150px;
	margin:0 auto;
	padding: 0 15px;
}

#welcome-page .video-box{
	position:relative;
	display:inline-block;
	text-align:center;
	cursor:pointer;
}

#welcome-page .video-box:hover::before{
	opacity:1;
}

#welcome-page .video-box::before{
	-webkit-transition:all 0.5s;
	opacity:0.5;
	content:" ";
	background:url(/ZendServer/images/welcome/play-btn.png) no-repeat center;
	position:absolute;
	top:50%;
	left:50%;
	margin-left:-29px;
	margin-top:-29px;
	width:58px;
	height:58px;
}

.video-box.video-box-tiny {
	width: 89px;
	height: 57px;
	margin: 0;
}
.video-box.video-box-tiny img {
	
}
#welcome-page .video-box.video-box-tiny::before{
	background-size:34px;	
}
.video-box.video-box-small {
	width: 146px;
	height: 93px;
	margin: 0;
}
.video-box.video-box-small img {
	width: 145px;
}
#welcome-page .video-box.video-box-small::before{
	background-size:44px;	
}
.welcome-content-screen.welcome-content-screen-tiny {
    font-size: 10px;
}
#supportSearchWrapper select{
	border: 0;
	padding: 0;
}
#supportSearchWrapper .searchBox{
	margin:0;
}
.text-wrapper {
    height: 200px;
}
#apps-logos .logos a {
    margin-right: 10px;
}
#apps-logos .logos a img{
    height: 65px;
}
.logosImageDisabled {
	opacity: 0.3;
}
#apps-logos .logos {
    margin-top: 10px;
}

a.disabled{
	cursor:default;
}

#welcome-page section {
    padding-left: 20px;
    padding-right: 20px;
}

@media (max-width: 1400px) {
  .container {
  		min-width: 1000px;
  }
  #welcome-page .container {
  	width: 1000px;
  }
  #main-container.super-container {
    min-width: 1000px;
  	
  }
  #apps-logos .logos a img{
    height: 55px;
  	
  }
  #welcome-page section#welcome_blue .cd{
    width: 400px;
  	
  }
  #welcome-page .video-box{
	margin-top: 28px;
  }
  #welcome-page footer h2{
  	padding-bottom:20px;
  }
}


/* * * * ZRAY STANDALONE * * * */

#welcome-page .container {
	width: auto;
	padding: 10px 0;
	margin: 0;
}

#welcome-page .centralized {
	width: 1270px;
	margin: 0 auto;
	padding: 0 20px;
}

#welcome-page h1 {
	font-size: 40px;
	line-height: 40px;
	padding: 10px 0;
}
#welcome-page h2 {
	font-size: 30px;
	line-height: 40px;
	padding: 10px 0;
	margin: 0;
	xborder-bottom: 1px solid #CCC;
}
#welcome-page h3 {
	font-size: 20px;
	line-height: 26px;
}
ul.top-links {
	margin: 0;
	padding: 0;
}
ul.top-links li {
	display: inline-block;
	margin-right: 10px;
}
#welcome-page #disclaimer {
	font-size: 14px;
	line-height: 18px;
	color: #C11;
}

#welcome-page section:not(#whats-new) {
	padding: 0;
}

#welcome-page section {
	margin-top: 50px;
}

.screenshot-item {
	overflow: hidden;
	margin: 0 0 20px 0;
	padding: 20px 0;
	background: #EEE;
}
.screenshot-item:first-child {
	margin-top: 0;
	padding-top: 0;
}
.screenshot-item p {
	font-size: 14px;
	line-height: 18px;
}
.coming-soon-section {
	width: 50%;
	float: left;
	padding: 0 20px;
	box-sizing: border-box;
}
.wide-section {
	width: 75%;
	float: left;
	padding: 0 50px;
	box-sizing: border-box;
}
.wide-section img {
	width: 100%;
}
ul.section-list {
	font-size: 14px;
	padding-left: 0px;
	list-style: none;
}
.narrow-section {
	width: 25%;
	float: left;
	padding: 10px;
	box-sizing: border-box;
}
ul.changelog li {
	list-style: initial;
}
#revision {
	float: right;
	padding: 0 10px;
	font-size: 12px;
	color: #AAA;
}

#feedback-link {
	margin: 0;
	padding: 0;
	font-size: 14px;
}

h2 small {
	font-size: 14px;
	font-weight: normal;
}

.new-version-notification {
	border: 1px solid #57A4D0;
	font-size: 16px;
	line-height: 20px;
	padding: 15px;
	background: #78D9F5;
	text-align: center;
	margin-bottom: 50px;
}

</style>

<?php if ($revisionNumber) { ?>
<div id="revision">Revision <?php echo $revisionNumber; ?></div>
<?php } ?>
<div id="welcome-page">
	<div class="container">
		<?php if ($thereIsNewVersion) { ?>
		<div class="centralized">
			<div class="new-version-notification">
				<strong>This version of Z-Ray has expired.</strong> See the <a href="http://www.zend.com/products/z-ray/z-ray-preview" target="_blank">Z-Ray product page</a> for information on updates.
			</div>
		</div>
		<?php } ?>
		<!-- TITLE -->
		<div class="centralized">
			<h1>Welcome to Z-Ray!</h1>
			<ul class="top-links">
				<li><a href="#what_is_zray">[What is Z-Ray]</a></li>
				<li><a href="#features">[Features]</li>
				<li><a href="#whats_new">[<?php 
				if ($priorToTechPreview2) { 
					echo "Coming soon";
				} else {
					echo "What's New";
				} ?>]</a></li>
			</ul>
			<p id="disclaimer">This is a technology preview of Z-Ray. We accept no responsibility for any errors resulting from usage.</p>
			<!-- GETTING STARTED -->
			<p class="font-md getting-started-summary">
			   Z-Ray is an advanced debugging and productivity solution which provides insight into PHP 
			   apps by displaying all the under-the-hood details of a page request, across all the PHP 
			   scripts involved in building the page, in your browser.
			</p>
			<div id="feedback-link">
				Got an idea for improving Z-Ray? Did we miss something out? <a href="http://www.zend.com/redirect/z-ray-preview/feedback" target="_blank">Let us know!</a>
			</div>
		</div>
		<a name="what_is_zray"></a>
		<section>
			<h2 class="centralized">
				What is Z-Ray?
			</h2>
			<div class="screenshot-item">
				<div class="centralized">
					<div class="narrow-section">
						<h3>In-browser insight</h3>
						<p>
							See detailed and deep insight on all the PHP elements constructing your page right in front of 
							you in your browser, including information on the request, functions, database queries, errors 
							and warnings, exceptions, and more. <a href="http://files.zend.com/help/Z-Ray/content/what_is_z-ray.htm" target="_blank">More info</a> and 
							<a href="http://www.zend.com/z-ray/redirect/z-ray-overview" target="_blank">video</a>.
						</p>
					</div>
					<div class="wide-section">
						<img src="<?php echo $viewCalledFromZS ? $this->basePath() : '%BASE_PATH%'; ?>/images/welcome/plugins/builtin/page-requests.png">
					</div>
				</div>
			</div>
			<div class="screenshot-item">
				<div class="centralized">
					<div class="wide-section">
						<img src="<?php echo $viewCalledFromZS ? $this->basePath() : '%BASE_PATH%'; ?>/images/welcome/zray-live-screenshot.png">
					</div>
					<div class="narrow-section">
						<h3>Live request insight</h3>
						<p>
							View real-time info on all the requests being made to the web server, including non-browser based 
							requests, such as APIs, Web services, and mobile requests. All the information recorded by Z-Ray, 
							such as request execution time, events, exceptions, errors, database queries, functions, request 
							headers, and more, is displayed in one central location. <a href="http://files.zend.com/help/Z-Ray/content/z-ray-live.htm" target="_blank">More info</a> and 
							<a href="http://www.zend.com/z-ray/redirect/z-ray-live" target="_blank">video</a>.
						</p>
					</div>
				</div>
			</div>
		</section>
		
		<a name="features"></a>
		<section>
			<div  class="centralized">
			<h2>Features</h2>
			</div>
			<div class="screenshot-item">
				<div class="centralized">
				<div class="narrow-section">
					<h3>Extending Z-Ray</h3>
					<p>
						Z-Ray comes bundled with support for some of the most popular PHP apps and frameworks, such as WordPress, Drupal, 
						Magento, Joomla, Zend Framework, Laravel, Symfony, and more, and you can easily extend Z-Ray to show any info you 
						like on the application/framework/platform you are working on. 
					</p>
				</div>
				<div class="wide-section">
					<img src="<?php echo $viewCalledFromZS ? $this->basePath() : '%BASE_PATH%'; ?>/images/welcome/plugins/wordpress/wp-plugins.png">
				</div>
				</div>
			</div>
			<div class="screenshot-item">
				<div class="centralized">
				<div class="wide-section">
<!-- php example code -->
<code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php
<br></span><span style="color: #FF8000">//&nbsp;create&nbsp;the&nbsp;extension&nbsp;object.&nbsp;(By&nbsp;default&nbsp;the&nbsp;extension&nbsp;is&nbsp;disabled)
<br></span><span style="color: #0000BB">$zre&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;\</span><span style="color: #0000BB">ZRayExtension</span><span style="color: #007700">(</span><span style="color: #DD0000">'MyExtension'</span><span style="color: #007700">);
<br>
<br></span><span style="color: #FF8000">//&nbsp;define&nbsp;a&nbsp;logo&nbsp;that&nbsp;will&nbsp;appear&nbsp;on&nbsp;my&nbsp;Z-Ray&nbsp;panels
<br></span><span style="color: #0000BB">$zre</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">setMetadata</span><span style="color: #007700">(array(
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'logo'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">__DIR__&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">DIRECTORY_SEPARATOR&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #DD0000">'logo.png'</span><span style="color: #007700">,
<br>));
<br>
<br></span><span style="color: #FF8000">//&nbsp;enable&nbsp;the&nbsp;plugin&nbsp;once&nbsp;the&nbsp;function&nbsp;"myBootstrapFuntion"&nbsp;is&nbsp;triggered
<br></span><span style="color: #0000BB">$zre</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">setEnabledAfter</span><span style="color: #007700">(</span><span style="color: #DD0000">'myBootstrapFuntion'</span><span style="color: #007700">);
<br>
<br></span><span style="color: #FF8000">//&nbsp;track&nbsp;`myFunction`&nbsp;function.&nbsp;
<br></span><span style="color: #0000BB">$zre</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">traceFunction</span><span style="color: #007700">(</span><span style="color: #DD0000">'myFunction'</span><span style="color: #007700">,function(){},&nbsp;function(</span><span style="color: #0000BB">$context</span><span style="color: #007700">,&nbsp;&amp;</span><span style="color: #0000BB">$storage</span><span style="color: #007700">)&nbsp;{
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;add&nbsp;some&nbsp;data&nbsp;to&nbsp;the&nbsp;storage,&nbsp;to&nbsp;display&nbsp;it&nbsp;later&nbsp;on&nbsp;the&nbsp;extension&nbsp;panel
<br>&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$storage</span><span style="color: #007700">[</span><span style="color: #DD0000">'my_panel'</span><span style="color: #007700">]&nbsp;=&nbsp;array(
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'foo'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'bar'</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #DD0000">'bat'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #DD0000">'baz'</span><span style="color: #007700">,
<br>&nbsp;&nbsp;&nbsp;&nbsp;);
<br>});
<br></span>
</span>
</code>
<!-- / php example code -->
				</div>
				<div class="narrow-section">
					<h3>Extension API</h3>
					<p>
						Using Z-Ray’s extension API, you can plug into Z-Ray’s tracking mechanism and create a plugin of 
						your own that adds panels to Z-Ray designed the way you like and displaying the info you want it 
						to show. You can find the 
						<a href="https://github.com/zend-server-plugins/Documentation/blob/master/ZRayApi.md" target="_blank">full API documentation on GitHub</a>.
					</p>
				</div>
				</div>
			</div>
		</section>
		<a name="whats_new"></a>
		<section>
			<div  class="centralized">
			<?php if ($priorToTechPreview2) { ?>
			<h2>Coming soon</h2>
			</div>
			<div class="screenshot-item">
				<div  class="centralized">
				<div class="coming-soon-section">
					<h3>Zend Gallery</h3>
					<p>
						A new online marketplace containing Z-Ray plugins developed by the community and by Zend for popular PHP frameworks and applications. 
					</p>
				</div>
				<div class="coming-soon-section">
					<h3>Platform Support</h3>
					<p>
						In addition to Linux, Z-Ray will soon be available for Windows and Mac users.
					</p>
				</div>
				</div>
			</div>
			<?php } else { ?>
			<h2>What's New</h2>
			</div>
			<div class="screenshot-item">
				<div  class="centralized">
					<div class="narrow-section">
						<h3>Plugins Gallery</h3>
						<p>
							Z-Ray is fully integrated with the new <a href="/ZendServer/Plugins/Gallery" title="Visit plugins gallery">Plugins Gallery</a> - a marketplace for plugins that extend Z-Ray’s functionality and that have been developed by and for other Z-Ray users for all to use.
						</p>
					</div>
					<div class="narrow-section">
						<h3>Platform support</h3>
						<p>
							In addition to Debian and Ubuntu, Z-Ray is now also supported on Mac and CentOS/RHEL. For more info on the supported systems and environments, check out the <a href="http://www.zend.com/redirect/z-ray/installation" target="_blank">System Requirements</a>.
						</p>
					</div>
					<div class="narrow-section">
						<h3>Installer</h3>
						<p>
							Z-Ray can now be automatically installed using a new installer script. Please check the <a href="http://www.zend.com/redirect/z-ray/installation" target="_blank">installation instructions</a> for more details.
						</p>
					</div>
					<div class="narrow-section">
						<h3>Bug Fixes</h3>
						<p>
							<ul class="section-list">
								<li>- Database cleanup</li>
								<li>- A few minor bugs</li>
							</ul>
						</p>
					</div>
				</div>
			</div>
			<?php } ?>
		</section>
		
	</div>
</div>