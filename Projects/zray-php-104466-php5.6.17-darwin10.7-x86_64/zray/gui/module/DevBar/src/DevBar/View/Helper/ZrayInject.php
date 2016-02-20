<?php

namespace DevBar\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ZrayInject extends AbstractHelper {
	
	protected $pageId = '';
	protected $jsLoaded = false;
	protected $error = '';
	
	public function __invoke($pageId, $requestsSeparated = false) {
		$this->pageId = $pageId;
		
		if (($output = $this->getZrayContent($requestsSeparated)) !== false) {
			if (!$this->jsLoaded) {
				$output.= $this->getJavascript();
				$this->jsLoaded = true;
			}
			return $output;
		} else {
			return $this->error;
		}
	}
	
	/**
	 * Load devbar_footer.html file and replace the placeholders. add "ZrayAllRequests" parameter
	 * @return array
	 */
	protected function getZrayContent($requestsSeparated = false) {
		$this->error = '';
		$zrayContent = array();
		
		// locate footer script
		$footerScript = $this->getZendInstallDir() . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'devbar_footer.html';
		$footerScript = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $footerScript);
		if (!file_exists($footerScript)) {
			$this->error = 'Z-Ray script "'.$footerScript.'" was not found';
			return false;
		}
	
		// take footer script content
		$footerContent = file_get_contents($footerScript);
		if ($footerContent === false) {
			$this->error = 'Cannot read Z-Ray script "'.$footerScript.'"';
			return false;
		}
		
		// replace the placeholders within the footer scripts
		$footerContent = str_replace('$(ZENDSERVER_UI)', $this->getView()->serverUrl() . '/ZendServer', $footerContent);
		$footerContent = str_replace('$(DEVBAR_PAGE_ID)', ($this->pageId ? $this->pageId : ''), $footerContent);
		$footerContent = str_replace('$(DEVBAR_ACCESS_TOKEN)', '', $footerContent);
		$footerContent = str_replace('Z-Ray/iframe?', 'Z-Ray/iframe?embedded=1&', $footerContent);
		if (empty($this->pageId)) {
			$footerContent = str_replace('Z-Ray/iframe?', 'Z-Ray/iframe?ZRayAllRequests=1&', $footerContent);
		}
		if ($requestsSeparated) {
			$footerContent = str_replace('Z-Ray/iframe?', 'Z-Ray/iframe?requestsSeparated=1&', $footerContent);
		}
		
		return $footerContent;
	}
	
	/**
	 * Get ZS installation DIR
	 * @return string
	 */
	protected function getZendInstallDir() {
		return getCfgVar('zend.install_dir');
	}
	
	protected function getJavascript() {
		$js = "
		<script>
		(function() {
			if (!window.$) return;
				
			// put the iframe at the same level
			$('zend-dev-bar-iframe').setStyle('z-index', 0);
			
			// make margins to make it look part of the page
			$('zend-dev-bar-iframe').setStyle('width', 'calc(100% - 80px)');
			$('zend-dev-bar-iframe').setStyle('margin-right', '10px');
			$('zend-dev-bar-iframe').setStyle('margin-left', '10px');
			$('zend-dev-bar-iframe').setStyle('left', '210px');
		    
		    $(document.body).setStyle('padding-bottom', 0);
	
			// call callbackFn when iframe is injected and ready
			// (the function can be called several times, the callback is appended)
			var whenIframeLoads = (function() {
					
				var callbacks = [];
					
				var theFunction = function(callbackFn) {
					var iframe = $('zend-dev-bar-iframe').getElement('iframe');
					if (!iframe) {
						setTimeout(function() {
							theFunction(callbackFn);
						}, 20);
					} else {
						iframe.setStyle('width','calc(100% - 160px)');
						if (typeof(callbackFn) == 'function') {
							callbacks.push(callbackFn);
						}
					
						callbacks.forEach(function(_callbackFn) {
							_callbackFn(iframe);
						});
					}
				};
					
				return theFunction;
			})();
		
		";
		
		// add "loading" text, when no page id supplied
		if (empty($this->pageId)) {
		    $waitingText = _t('Waiting for incoming requests');
			$js.= "
			
			// add `recording` icon when iframe loads
			whenIframeLoads(function(iframe) {
					
				// create the icon
				var recIconWrapper = document.createElement('div');
				recIconWrapper.setAttribute('class', 'zdb-waiting-requests-wrapper');
				recIconWrapper.innerHTML = '<h2 title=\"$waitingText\">$waitingText</h2>';
			
				// add it to z-ray when it loads
				iframe.addEventListener('load', function() {
					var zendDevBarElem = iframe.contentWindow.document.getElementById('zend-dev-bar');
					zendDevBarElem.parentNode.insertBefore(recIconWrapper, zendDevBarElem);
				});
			});
			";
		}
		
		$js.= '})();</script>';
		return $js;
	}
	
}