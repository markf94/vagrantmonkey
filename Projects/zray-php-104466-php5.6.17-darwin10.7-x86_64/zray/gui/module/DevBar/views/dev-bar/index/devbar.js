(function ($) {
	baseUrl: ''
	pageId: 0
	lastRequestId: 0
	requestsCount: 0
	firstRequestTime: 0
	disableDevBar: false
	handlers: null
	requestListUpdaterHandler: null
	requestListResetHandler: null
	devBar: null
	requests: {}
	currentRequestIds: []
	requestsToParse: {}
	labelsToParse: {}
	aggregate: false
	popupTimer: null
	popupActiveElement: null
	popupInterval: 0
	useCredentials: true
	cookieParams: null
	previousState: false
	formatters: {}
	sorting: true
	stopUpdating: false
	jquery: null
	intervals: {}
	storage: {}
	socket: null
	inIframe: false
	host: ''
	loggedOut: false
	requestsLimit: 0
	showWarningPopup: false
	customData: null
	parametersMap: {}
	customDataConfig: {}
	customDataRequests: {}
	blockedExtensions: {}
	contactZend: ''
	customVisibleExtensions: []
	runActionsCallables: {}
	disableActions: true
	inDebugAll: false
	
	$.ZendDevBar = function (baseUrl, host, pageId, lastRequestId, firstRequestTime) {
		var that = this;
		this.jquery = $;
		this.baseUrl = baseUrl;
		this.host = host;
		this.pageId = pageId;
		this.lastRequestId = lastRequestId;
		this.requestsCount = 0;
		this.firstRequestTime = firstRequestTime;
		this.disableDevBar = false;
		this.handlers = {'set': {}, 'reset': {}, 'updateLabel': {}, 'close': {}, 'load': {}, 'collapse': {}, 'expand': {}, 'displayRequest': {}, 'customDataLoaded': {}};
		this.requestListUpdaterHandler = null;
		this.requestListResetHandler = null;
		this.devBar = null;
		this.popup = null;
		this.requests = {};
		this.requestsToParse = {};
		this.labelsToParse = {};
		this.aggregate = false;
		this.popupTimer = null;
		this.popupActiveElement = null;
		this.popupInterval = 200;
		this.cookieParams = {};
		this.previousState = false;
		this.sorting = true;
		this.stopUpdating = false;
		this.stopRequestsFlag = false;
		this.currentRequestIds = [];
		this.intervals = {};
		this.storage = {};
		this.inIframe = false;
		this.lastRequestTime = LOAD_MICROTIMESTAMP;
		this.requestsLimit = 0;
		this.showWarningPopup = false;
		this.parametersMap = {};
		this.customDataRequests = {};
		this.customDataConfig = {};
		this.blockedExtensions = {};
		this.contactZend = '';
		this.studioClientTimeout = 4000;
		this.MSG_ERROR = 'msg_error';
		this.displayAllRequests = (document.location.href.indexOf('ZRayAllRequests=1') >= 0); // show requests for all pages
		this.requestsSeparated = false;
		this.requestsSeparatedHeight = 300;
		this.maxElementsPerLevel = 180;	
		this.maxElementsInTree = 180;	
		this.embeddedMode = this.displayAllRequests || (document.location.href.indexOf('embedded=1') >= 0); // embedded mode is when Z-Ray is displayed on its own page (like in GUI requests info page)
		this.historyEmbeddedMode = (document.location.href.indexOf('historyEmbedded=1') >= 0); // embedded mode is when Z-Ray is displayed on its own page (like in GUI requests history page)
		this.customVisibleExtensions = [];
		this.runActionsCallables = {};
		this.disableActions = true;
		this.inDebugAll = false;
		this.socket = new zdbEasyXDM.Socket({
		    onReady: function() {
				// set height in embedded mode
				if (that.embeddedMode) {
					
					// display empty bar if no requests found
					if (that.getTotalRequests() == 0) {
						that.cleanRequests();
					}
					
					// set the height, so it'll look like part of the page
					that.setEmbeddedPopupHeight();
					
					// fix the height on resize
					$(window.parent).on('resize', function() {
						that.setEmbeddedPopupHeight();
					});
				}
		    	
		    	zendDevBar.postMessage('loaded', zendDevBar.getHeight());
		    	
		    	// if the requests panel is separated, lower the bar below the requests panel
		    	if (that.requestsSeparated) {
		    		document.getElementById('zend-dev-bar').style.top = that.requestsSeparatedHeight + 'px';
		    	}
		    },
		    onMessage: function(message, origin){
				var msg;
		    	try {
		    		msg = JSON.parse(message);
		    	} catch (e) {
		    		msg = {};
		    	}
		    	
		    	if (msg.action == 'updateRequestsList') {
		    		zendDevBar.updateRequestsList(msg.value);
		    	} else if (msg.action == 'loadRequestsList') {
		    		zendDevBar.loadRequestsList();
		    	} else if (msg.action == 'collapse') {
				    if (zendDevBar.devBar.hasClass('zdb-collapsed')) {
						zendDevBar.expandBar();
					} else {
						zendDevBar.collapseBar();
					}
		    	} else if (msg.action == 'runAction') {
					if (zendDevBar.runActionsCallables[msg.id].length > 0) {
		    			var userFunc = zendDevBar.runActionsCallables[msg.id][0];
		    			zendDevBar.runActionsCallables[msg.id].shift();
		    			userFunc(msg.value);
		    		}
		    	}
		    }
		});
				
		if (typeof $.cookie('ZSDEVBAR') == 'undefined') {
			$.cookie('ZSDEVBAR', JSON.stringify({}), {path: '/'});
		} else {
			var allCookieParams;
			try {
				allCookieParams = JSON.parse($.cookie('ZSDEVBAR'));
			} catch (e) {
				allCookieParams = {};				
			}
			
			if (typeof allCookieParams[this.host] != 'undefined') {
				this.cookieParams = allCookieParams[this.host];
			}
		}
		
		var that = this;
		$( window ).resize((function() {
			var resizeFn = function() {
				var $zdbPopup = that.devBar.find('#zdb-popup'); 
				$zdbPopup.css('top', '');
				
				var extPanels = (that.isExtensionsPanelsVisible()) ? 32 : 0;
				if (this.embeddedMode) {
					extPanels *= -1;
				}
				
				// if the requests panel is separated, calculate the height according to other panels (not requests)
				var tabHeight = that.requestsSeparated ? 
					parseInt(that.devBar.find('.zdb-toolbar-detail:not(.zsb-request-info-details-wrapper)').first().css('height')) :
					parseInt(that.devBar.find('.zdb-toolbar-detail').first().css('height'));
						
				// display the popup above the z-ray bar
				tabHeight -= 5;
				$zdbPopup.css('bottom', tabHeight + extPanels + parseInt(zendDevBar.devBar.css('height')));
				
				if (that.popup.hasClass('zdb-sticky')) {
					that.devBar.find('#zdb-popup-warning').css({'top': '', 'bottom': tabHeight + extPanels + parseInt(zendDevBar.devBar.css('height')) + 3});
				} else {
					that.devBar.find('#zdb-popup-warning').css({'top': '', 'bottom': parseInt(zendDevBar.devBar.css('height'))});
				}
				
				var $zdbDisabledPopup = that.devBar.find('#zdb-disabled-popup');
				$zdbDisabledPopup.css('height', tabHeight);
				$zdbDisabledPopup.css('width', '100%');
				$zdbDisabledPopup.css('background', 'rgba(255, 255, 255, 0)');
				$zdbDisabledPopup.css('line-height', tabHeight + 'px');
				
				that.devBar.find('.zdb-extension-blocker').css('height', tabHeight);
	
				// set spinner height
				var $zdbSpinner = that.devBar.find('.zdb-spinner'); 
				$zdbSpinner.css('height', tabHeight - 30);
				$zdbSpinner.css('line-height', (tabHeight - 30) + 'px');
				
				// calc the carousel width
				var outsideEntriesWidth = 0;
				$.each(that.devBar.find('.zdb-toolbar-entry'), function() {
					if (! $(this).parent('.zdb-modules').hasClass('zdb-modules')) {
						if(! $(this).closest('#custom-panels').length) {
							outsideEntriesWidth += $(this).outerWidth(true);
						}
					}
				});
				var modulesContainerWidth = that.devBar.width() - outsideEntriesWidth - 50;
				that.devBar.find('.zdb-modules-container').first().css('width', modulesContainerWidth);

				// handle main panels scroller
				var innerModulesWidth = 0;
				$.each(that.devBar.find('.zdb-modules-container .zdb-toolbar-entry'), function() {
					innerModulesWidth += $(this).outerWidth(true);
				});
				// add some spare width to avoid pixel bug in different browsers
				innerModulesWidth += 10;
				
				var modulesContainer = that.devBar.find('.zdb-modules');
				if (modulesContainerWidth < innerModulesWidth) {
					that.devBar.find('.zdb-modules-container').first().addClass('scrollActive');
				} else {
					that.devBar.find('.zdb-modules-container').first().removeClass('scrollActive');
				}
				
				that.devBar.find('.zdb-modules-container .zdb-modules').css('width', innerModulesWidth);
				that.devBar.find('.zdb-modules-container .zdb-modules-scroll').css('width', that.devBar.find('.zdb-modules-container').first().width());
				
				// handle case when scroller is part time hidden and the size of the window grow
				var lastModule = that.devBar.find('.zdb-modules-container .zdb-toolbar-entry:last');
				var spareWidth = modulesContainerWidth - parseInt(modulesContainer.css('margin-left')) - innerModulesWidth;
				if (spareWidth > 0) {
					modulesContainer.css('margin-left', Math.min(0, parseInt(modulesContainer.css('margin-left')) + spareWidth));
				}
				
				// reset arrows state 
				if (parseInt(modulesContainer.css('margin-left')) >= 0) {
					that.devBar.find('.zdb-modules-container .zdb-modules-arrow-right').removeClass('disabled');
					that.devBar.find('.zdb-modules-container .zdb-modules-arrow-left').addClass('disabled');
				}
				// end main panels scroller
				
				// handle extensions panels scroller
				if (that.isExtensionsPanelsVisible()) {
					that.devBar.find('#custom-panels .zdb-ext-modules').css('width', '100%');
					var innerExtModulesWidth = 0;
					$.each(that.devBar.find('#custom-panels .zdb-toolbar-entry').not('.hidden'), function() {
						innerExtModulesWidth += $(this).outerWidth(true);
					});
					// add some spare width to avoid pixel bug in different browsers
					innerExtModulesWidth += 26;
					
					var extModulesContainer = that.devBar.find('.zdb-ext-modules');
					var extModulesContainerWidth = that.devBar.width() - 50;
					if (extModulesContainerWidth < innerExtModulesWidth) {
						that.devBar.find('#custom-panels').first().addClass('scrollActive');
					} else {
						that.devBar.find('#custom-panels').first().removeClass('scrollActive');
					}
					
					that.devBar.find('#custom-panels .zdb-ext-modules').css('width', innerExtModulesWidth);
					that.devBar.find('#custom-panels .zdb-modules-scroll').css('width', that.devBar.find('#custom-panels').first().width() - 26);
					
					// handle case when scroller is part time hidden and the size of the window grow
					var lastModule = that.devBar.find('#custom-panels .zdb-toolbar-entry:last');
					var spareWidth = extModulesContainerWidth - parseInt(extModulesContainer.css('margin-left')) - innerExtModulesWidth;
					if (spareWidth > 0) {
						extModulesContainer.css('margin-left', Math.min(0, parseInt(extModulesContainer.css('margin-left')) + spareWidth));
					}
					
					// reset arrows state 
					if (parseInt(modulesContainer.css('margin-left')) >= 0) {
						that.devBar.find('#custom-panels .zdb-modules-arrow-right').removeClass('disabled');
						that.devBar.find('#custom-panels .zdb-modules-arrow-left').addClass('disabled');
					}
				}
				// end extensions panels scroller
				
				// change copy to clipboard file width
				$.each(that.devBar.find('.zdb-copy-clipboard'), function(key, value) {
					var elem = $(value); 
					var width = parseInt(elem.css('width')) - 50; // two icons - 25 each
					if (width > 0) {
						elem.find('.zdb-ellipsis').css('width',  width);
					}
				});
				
				if (that.requestsSeparated) {
					// when in a separated requests mode, hide the "requests" tab (the URL with the number-of-requests badge)
					// * the panel is (ofcourse) displayed.
					that.devBar.find('.zdb-request-details .zdb-toolbar-preview').css({
						width: '1px',
						height: '1px',
						margin: 0,
						padding: 0,
						overflow: 'hidden'
					});
					
					// hide the controls "pause" and "erase"
					that.devBar.find('.zdb-controls').hide();
					
					// fix the width of the requests panel. The summary table pane is hidden, so widen the main pane
					that.devBar.find('.zdb-request-details .zdb-entries-table-wrapper').css({
						width: 'initial'
					});
					
					// fix the "waiting for requests" title
					$zendDevBar('.zdb-waiting-requests-wrapper').css('top', Math.round((2 * that.requestsSeparatedHeight / 3)));
					
				}
				
				that.devBar.find('.zdb-toolbar-detail').css('width', that.devBar.width());
			};
			var timer = null;
			// first parameter evt comes from the 'resize' event of `window`
			return function(evt, execImmediate) {
				if (timer) clearTimeout(timer);
				if (execImmediate) {
					try {
						resizeFn();
					} catch(err) { 
							// do nothing 
					}
				} else {
					timer = setTimeout(resizeFn, 10);
				}
			}
		})());
		
		this.setFormaters();
    };
    
    $.ZendDevBar.prototype = {
    	init: function() {
			this.devBar = $('#zend-dev-bar');
			this.devBar.css('display', 'block');
			
			this.popup = $('<div id="zdb-popup" class="hidden"><div class="zdb-popup-wrapper"><div id="zdb-popup-header"><div class="zdb-popup-title"></div></div></div></div>');
			this.disabledPopup = $('<div id="zdb-disabled-popup" class="hidden">To fully enable Z-Ray, <a href="' + this.baseUrl + '" target="_blank">click here</a> to bootstrap Zend Server</div>');
			this.popup.css('bottom', parseInt($('#zend-dev-bar').css('height')) + 210);
			var that = this;
			
			this.devBar.append(this.popup);
			this.devBar.append(this.disabledPopup);
			this.devBar.append('<div id="custom-panels" class="hidden"><div class="zdb-modules-arrow-left disabled"></div><div class="zdb-modules-left"></div><div class="zdb-modules-scroll"><div class="zdb-ext-modules"></div></div><div class="zdb-modules-right"></div><div class="zdb-modules-arrow-right"></div></div>');
			
			var disablePopup = $('<div id="zdb-popup-warning" class="hidden"></div>')
			this.devBar.append(disablePopup);
			
			// append the bottom bar
			this.devBar.append($('<span class="zdb-modules-container"><div class="zdb-modules-arrow-left disabled"></div><div class="zdb-modules-left"></div><div class="zdb-modules-scroll"><div class="zdb-modules"></div></div><div class="zdb-modules-right"></div><div class="zdb-modules-arrow-right"></div></span>'));

			this.devBar.find('.zdf-toolbar-hide-button').remove();
			
			// move the bottom bar right and left
			var $zdbModulesArrowRight = this.devBar.find('.zdb-modules-container .zdb-modules-arrow-right'); 
			$zdbModulesArrowRight.mousedown(function() {			
				that.intervals['careoselNext'] = true;
				that.carouselNext('.zdb-modules-container', '.zdb-modules');
			});
			$zdbModulesArrowRight.mouseup(function() { delete that.intervals['careoselNext']; });
			$zdbModulesArrowRight.mouseleave(function() { delete that.intervals['careoselNext']; });
			$zdbModulesArrowRight.mousedown(function(e){ e.preventDefault(); })
			
			var $zdbModulesArrowLeft = this.devBar.find('.zdb-modules-container .zdb-modules-arrow-left'); 
			$zdbModulesArrowLeft.mousedown(function() {
				that.intervals['careoselPrev'] = true;
				that.carouselPrev('.zdb-modules-container', '.zdb-modules');
			});
			$zdbModulesArrowLeft.mouseup(function() { delete that.intervals['careoselPrev']; });
			$zdbModulesArrowLeft.mouseleave(function() { delete that.intervals['careoselPrev']; });
			$zdbModulesArrowLeft.mousedown(function(e){ e.preventDefault(); })
			
			// move the extensions bar right and left
			var $zdbModulesArrowRight = this.devBar.find('#custom-panels .zdb-modules-arrow-right'); 
			$zdbModulesArrowRight.mousedown(function() {			
				that.intervals['careoselNext'] = true;
				that.carouselNext('#custom-panels', '.zdb-ext-modules');
			});
			$zdbModulesArrowRight.mouseup(function() { delete that.intervals['careoselNext']; });
			$zdbModulesArrowRight.mouseleave(function() { delete that.intervals['careoselNext']; });
			$zdbModulesArrowRight.mousedown(function(e){ e.preventDefault(); })
			
			var $zdbModulesArrowLeft = this.devBar.find('#custom-panels .zdb-modules-arrow-left'); 
			$zdbModulesArrowLeft.mousedown(function() {
				that.intervals['careoselPrev'] = true;
				that.carouselPrev('#custom-panels', '.zdb-ext-modules');
			});
			$zdbModulesArrowLeft.mouseup(function() { delete that.intervals['careoselPrev']; });
			$zdbModulesArrowLeft.mouseleave(function() { delete that.intervals['careoselPrev']; });
			$zdbModulesArrowLeft.mousedown(function(e){ e.preventDefault(); })
			
			if (this.embeddedMode) {
				// remove the draggable top bar (with the request URL)
				$('#zdb-popup').remove();
				$('.zdb-toolbar-pin').remove();
				
				// add new styles
				var newCssLink = document.createElement('link');
				newCssLink.setAttribute('rel', 'stylesheet');
				newCssLink.setAttribute('href', this.baseUrl + '/css/devbar-embedded.css?'+Math.random());
				document.body.appendChild(newCssLink);
			} else {
				
				// drag the bar
				var tempLoc = 0;
				var initSize = 0;
				var isDragged = false;
				
				// bind the resize action to parent
				$("#zend-dev-bar #zdb-popup").hover(
				  function() {
					  if (! isDragged) {
						  that.postMessage('resizeStart', zendDevBar.getHeight());
					  }
				  }, function() {
					  if (! isDragged) {
						  that.postMessage('resizeEnd', zendDevBar.getHeight());
					  }
				  }
				);
				
				$zendDevBar('#zdb-popup').draggable({
					axis: "y",
					containment: "document",
					iframeFix: true,
					cancel: ".zdb-popup-title",
					start: function() {		
						isDragged = true;
						tempLoc = that.devBar.find('#zdb-popup').position().top;
						initSize = parseInt(that.devBar.find('.zdb-toolbar-detail').first().css('height'));
				      },
				      drag: function() {
				    	  var newHeight = initSize + tempLoc - that.devBar.find('#zdb-popup').position().top + 1;
				    	  
				    	  that.devBar.find('.zdb-toolbar-detail').css('height', newHeight);
				    	  
				    	  that.devBar.find('.zdb-adaptive-height').each(function(elem) {
								var e = $zendDevBar(this);
					    		var tabs = e.closest('.zdb-toolbar-detail').find('ul.tabs label');
					    		if (tabs.length > 0) {
					    			e.css('height', newHeight - 50 - tabs.outerHeight() - 10);
					    		} else {
					    			e.css('height', newHeight - 50);
					    		}
				    	  });				    	  
				    	  
				    	  if (that.isElementDisabled()) {
				    		  that.disabledPopup.css('height', newHeight);
				    	  }
				    	  var $zdbSpinner = zendDevBar.devBar.find('.zdb-spinner'); 
				    	  if ($zdbSpinner.length > 0) {
				    		  $zdbSpinner.css('height', newHeight - 30);
				    		  $zdbSpinner.css('line-height', (newHeight - 30) + 'px');
				    	  }
				      },
				      stop: function() {
				    	  var newHeight = initSize + tempLoc - that.devBar.find('#zdb-popup').position().top;
				    	  if (newHeight >= 40) {
				    		  that.devBar.find('.zdb-toolbar-detail').css('height', newHeight);
				    	  } else {
				    		  newHeight = 40;
				    		  that.devBar.find('.zdb-toolbar-detail').css('height', 40);
				    		  var $zdbPopup = that.devBar.find('#zdb-popup');
				    		  $zdbPopup.css('top', '');
				    		  $zdbPopup.css('bottom', 65);
				    	  }
				    	  that.cookieParams.height = newHeight;
				    	  that.updateCookieParams();
				    	  
				    	  $(window).trigger('resize');
				    	  
				    	  isDragged = false;
				      }
				});
			}
			
			if(window['ref']){
				//add referer
				this.referer = ref;
			}
			
			$('#zend-dev-bar').attr('actions-enabled', this.actionsEnabled());
			
			// initialize the panels
			this.loadRequestInfo([]);
    	},
		getReferer: function() {
			return this.referer;
		},
		getTotalRequests: function() {
			var total = 0;
			$.each(this.requests, function(i, reqObj) {
				total ++;
			});
			
			return total;
		},
    	setShortcuts: function(shortcuts) {
    		var that = this;
    		
    		// attach collapse and expand
    		if (shortcuts.collapse.trim() != '') {
				this.postMessage('shortcuts', shortcuts.collapse);
				zdbShortcut.add(shortcuts.collapse, function () {
					if ($('#zend-dev-bar').hasClass('zdb-collapsed')) {
						that.expandBar();
					} else {
						that.collapseBar();
					}
				});
    		}
    	},
    	setInDebugAll: function(status) {
    		this.inDebugAll = status;
    	},
    	setRequestsSeparated: function(newBoolVal) {
    		this.requestsSeparated = !!newBoolVal;
    	},
    	setCustomDataConfig: function(customDataConfig) {
    		this.customDataConfig = customDataConfig;
    	},
    	postMessage: function(type, message) {
    		this.socket.postMessage(JSON.stringify({action: type, value: message}));
    	},
    	getHeight: function() {
    		if (this.requestsSeparated) {
    			return this.getEmbeddedPopupHeight();
    		}
    		
    		var height = this.getMinHeight() + 1;
    		
    		if (this.isExtensionsPanelsVisible() && ! this.embeddedMode || (this.requestsSeparated && this.embeddedMode)) {
    			height += 32;
    		}
    		
    		if (this.popupActiveElement != null) {
    			height += parseInt(this.devBar.find('.zdb-toolbar-detail').first().css('height'));
    			
    			// add 20 for the url label
    			if (!this.embeddedMode) {
    				height += 20;
    			}
    		}
    		
    		if (this.showWarningPopup) {
    			height += 20;
    		}
    		
    		if ($zendDevBar('.zendDevBarQtip:visible').length > 0) {
    			height = Math.max(height, parseInt($zendDevBar('.zendDevBarQtip:visible').css('height')) + 45);
    		}

    		return height;
    	},
    	showWarning: function(message) {
    		this.showWarningPopup = true;
    		this.devBar.find('#zdb-popup-warning').html(message).removeClass('hidden');
    	},
    	hideWarning: function() {
    		this.showWarningPopup = false;
    		this.devBar.find('#zdb-popup-warning').addClass('hidden');
    	},
    	getMinHeight: function() {
    		return parseInt(this.devBar.css('height'));
    	},
    	
    	// set the height of the panels
		setPopupHeight: function(newHeight) {
			var that = this;
			
			// display requests panel if in embedded mode and no requests found
			if (this.embeddedMode && this.devBar.find('.zdb-toolbar-detail:visible').length == 0) {
				// set requests tab as the active tab
				this.popupActiveElement = this.devBar.find('.zdb-request-details .zdb-toolbar-detail').parent();
				this.popupActiveElement.addClass('active');
				this.popup.removeClass('hidden');
				this.disabledPopup.removeClass('hidden');
			}
			
			// set the panel heights
			if (this.requestsSeparated) {
				this.devBar.find('.zdb-toolbar-detail:not(.zsb-request-info-details-wrapper)').css('height', newHeight - this.requestsSeparatedHeight - this.devBar.outerHeight());
				this.devBar.find('.zdb-toolbar-detail.zsb-request-info-details-wrapper').css('height', this.requestsSeparatedHeight);
				this.devBar.find('.zdb-toolbar-detail.zsb-request-info-details-wrapper').css('top', 0);
				
				// fix the bar position (not bar refaeli but z-ray bar)
				this.devBar.css('top', this.requestsSeparatedHeight);
				
			} else {
				this.devBar.find('.zdb-toolbar-detail').css('height', newHeight);
			}
			
			this.devBar.find('#zdb-popup').css({'top': '', 'bottom': newHeight + parseInt(this.devBar.css('height')) + 1});
			
			if (this.popup.hasClass('zdb-sticky')) {
				this.devBar.find('#zdb-popup-warning').css({'top': '', 'bottom': newHeight + parseInt(this.devBar.css('height')) + 3});
			} else {
				this.devBar.find('#zdb-popup-warning').css({'top': '', 'bottom': parseInt(this.devBar.css('height')) + 1});
			}
			
			this.devBar.find('.zdb-adaptive-height').each(function(elem) {
				var newHeightForAdaptivePanels = newHeight;
				var e = $zendDevBar(this);
	    		var tabs = e.closest('.zdb-toolbar-detail').find('ul.tabs label');
	    		
	    		// when in requests separated mode, take different height for adaptive panels in requests panel
	    		// and different height for the others
	    		if (that.embeddedMode) {
	    			if (that.requestsSeparated) {
			    		if (e.parents('.zsb-requests-panel').length) {
			    			newHeightForAdaptivePanels = that.requestsSeparatedHeight;
			    		} else {
			    			newHeightForAdaptivePanels = newHeight - that.requestsSeparatedHeight - (parseInt(that.devBar.css('height')) + 1);
			    			if (that.isExtensionsPanelsVisible()) {
			    				newHeightForAdaptivePanels -= 32;
			    			}
			    		}
	    			}
	    		}
	    		
	    		if (tabs.length > 0) {
	    			e.css('height', newHeightForAdaptivePanels - 50 - tabs.outerHeight() - 10 - 5);
	    		} else {
	    			e.css('height', newHeightForAdaptivePanels - 50 -5);
	    		}
	    	});
			
			if (this.requestsSeparated) {
				zendDevBar.postMessage('resize', newHeight);
			} else {
				zendDevBar.postMessage('resize', zendDevBar.getHeight());
			}
		},
    	setPopupTitle: function(title) {
    		this.popup.find('.zdb-popup-title').text(title);
    	},
    	getEmbeddedPopupHeight: function() {
    		var $mainWindow = $(window.parent.document);
			var newZRayHeight = $mainWindow.height() - $mainWindow.find('#topbar').height() - $mainWindow.find('#main-container').height() - 40;
			if (this.requestsSeparated) {
				newZRayHeight += 40;
			}
			return newZRayHeight;
    	},
    	setEmbeddedPopupHeight: function() {
    		var newZRayHeight = this.getEmbeddedPopupHeight();
    		
    		this.requestsSeparatedHeight = Math.floor(newZRayHeight * 0.33);
			
			this.cookieParams.height = newZRayHeight;
			this.updateCookieParams();
			
			this.setPopupHeight(newZRayHeight);
    	},
    	setRequestsLimit: function(limit) {
    		this.requestsLimit = limit;	
    	},
    	setTreeLimits: function(maxElementsPerLevel, maxElementsInTree) {
    		this.maxElementsPerLevel = maxElementsPerLevel;	
    		this.maxElementsInTree = maxElementsInTree;	
    	},
    	carouselNext: function(container, modulesContainer) {
    		var elem = this.devBar.find(container + '.scrollActive ' + modulesContainer);
			var moduleWidth = this.devBar.find(container + ' .zdb-toolbar-entry').not('.hidden').find('.zdb-toolbar-preview').first().outerWidth(true);
			
			var modulesContainerWidth = parseInt(this.devBar.find(container).first().width());
			if ( Math.abs(parseInt(elem.css('margin-left'))) + modulesContainerWidth  < parseInt(elem.width())) {
				var maxMargin = -(parseInt(elem.width()) - modulesContainerWidth);
				var marginLeft = Math.max(parseInt(elem.css('margin-left')) - moduleWidth, maxMargin);
				elem.css('margin-left', marginLeft);
				
				if (marginLeft == maxMargin) {
					this.devBar.find(container + ' .zdb-modules-arrow-right').addClass('disabled');
				}
				
				this.devBar.find(container + ' .zdb-modules-arrow-left').removeClass('disabled');
			}
			
			var that = this; 
			setTimeout(function() { if (typeof that.intervals['careoselNext'] != 'undefined') { that.carouselNext(container, modulesContainer) } }, 350);
    	},
    	carouselPrev: function(container, modulesContainer) {
    		var elem = this.devBar.find(container + '.scrollActive ' + modulesContainer);
			var moduleWidth = this.devBar.find(container + ' .zdb-toolbar-entry').not('.hidden').find('.zdb-toolbar-preview').first().outerWidth(true);
			var marginLeft = Math.min(0, parseInt(elem.css('margin-left')) + moduleWidth);
			elem.css('margin-left', marginLeft);
			
			if (marginLeft == 0) {
				this.devBar.find(container + ' .zdb-modules-arrow-left').addClass('disabled');
			}
			
			this.devBar.find(container + ' .zdb-modules-arrow-right').removeClass('disabled');
			
			var that = this; 
			setTimeout(function() { if (typeof that.intervals['careoselPrev'] != 'undefined') { that.carouselPrev(container, modulesContainer) } }, 350);
    	},
    	unpin: function() {
			delete this.cookieParams.pin;
    		this.updateCookieParams();
    		this.devBar.find('.zdb-toolbar-entry').removeClass('active');
    		this.popup.removeClass('zdb-sticky');
    		this.disabledPopup.removeClass('zdb-sticky');
    		this.deactivatePopup();
    		
    		zendDevBar.postMessage('resize', zendDevBar.getHeight());
    	},
    	getJquery: function() {
    		return this.jquery;
    	},
    	closeDevBar: function() {
    		this.stopRequests();
    		this.devBar.css('display', 'none');
    		this.postMessage('closeZRay');
    	},
    	closeDevBarSession: function() {
    		this.closeDevBar();
    		this.postMessage('closeZRay');
    	},
    	stopRequests: function() {
    		this.stopRequestsFlag = true;
    	},
    	pauseRequests: function() {
    		if (this.licenseExpired()) {
    			alert('Zend Server license has expired, launch Zend Server to extend your license');
    			return;
    		} else if (this.notBootstrap()) {
    			alert('To fully enable Z-Ray, launch Zend Server');
    			return;
    		}
    		
    		this.stopUpdating = ! this.stopUpdating;
    		var $zdbControlsPause = this.devBar.find('.zdb-controls-pause'); 
    		$zdbControlsPause.toggleClass('zdb-controls-play');
    		if (this.stopUpdating) {
    			$zdbControlsPause.attr('title', 'Resume tracking');
    		} else {
    			$zdbControlsPause.attr('title', 'Pause tracking');
    		}
    	},
    	cleanRequests: function() {
    		if (this.licenseExpired()) {
    			alert('Zend Server license has expired, launch Zend Server to extend your license');
    			return;
    		} else if (this.notBootstrap()) {
    			alert('To fully enable Z-Ray, launch Zend Server');
    			return;
    		}
    		
    		this.requests = {};
    		this.requestsCount = 0;
    		this.aggregate = false;
    		
    		this.resetData();
    		
    		if (typeof(this.requestListResetHandler) == 'function') {
    			this.requestListResetHandler($);
    		}
    		
    		this.devBar.find('.zdb-toolbar-entry .zdb-toolbar-detail .zdb-spinner').remove();
    		this.hideWarning();
    		
    		if ($('.zdb-waiting-requests-wrapper').length > 0) {
				$('.zdb-waiting-requests-wrapper').removeClass('hidden');
			}
    	},
    	setInIframe: function(inIframe) {
    		this.inIframe = inIframe;
    	},
    	selectModule: function(module) {
    		// remove previous "active" status
    		// if requests separated mode, don't hide requests panel
    		if (!(this.requestsSeparated && $zendDevBar(".zdb-toolbar-entry.active").data('name') == 'requests')) {
    			$zendDevBar(".zdb-toolbar-entry.active").removeClass('active');
    		} 
    		
    		module.addClass('zdb-clickable');
    		module.addClass('active');
    		this.popupActiveElement = module;
    	},
    	updateCookieParams: function() {
    		var allCookieParams = {};
    		if (typeof $.cookie('ZSDEVBAR') != 'undefined') {
    			try {
    				allCookieParams = JSON.parse($.cookie('ZSDEVBAR'));
    			} catch (e) {
    				allCookieParams = {};
    			}
    		}
    		allCookieParams[this.host] = this.cookieParams;
    		
    		$.cookie('ZSDEVBAR', JSON.stringify(allCookieParams));
    	},
    	insertProducer: function(htmlCode) {
    		var temp = $(htmlCode);
    		if (this.embeddedMode || this.historyEmbeddedMode) {
    			if (temp.hasClass('zdb-toolbar-group-serverinfo') || temp.hasClass('zdb-studio-integration') || temp.hasClass('zdb-notifications')) {
    				return;
    			}
    		}
    		
    		if (this.inDebugAll) {
    			if (temp.hasClass('zdb-secure') || temp.hasClass('zdb-message-panel') || temp.hasClass('zdb-studio-integration') || temp.hasClass('zdb-toolbar-group-serverinfo')) {
	    			temp.insertBefore(this.devBar.find('.zdb-modules-container').first());
	    		}
    		} else {
	    		if (temp.attr('data-extension')) {
	    			var modulesContainer = $($.find('.zdb-modules'));
	    			var extName = temp.attr('data-extension');
	    			
	    			var panelName = temp.attr('data-name');
	    			if (panelName != 'undefined') {
	    				panelName = panelName.replace('zrayExtension:' + extName + '/', '');
	    				
	    				if (typeof zendDevBar.customDataConfig[extName][panelName] != 'undefined') {
	    					if (typeof zendDevBar.customDataConfig[extName][panelName]['params']['alwaysShow'] != 'undefined' && zendDevBar.customDataConfig[extName][panelName]['params']['alwaysShow']) {
	    						this.customVisibleExtensions.push(extName);
	    					}
	    				}
	    			}
	    			
	    			var wrapper = document.createElement("div");
	    			wrapper.appendChild(temp.find('.zdb-toolbar-icon')[0].cloneNode(true));
	    			var logo = wrapper.innerHTML;
	    			
	    			if (modulesContainer.find('.zdb-extension-panel[data-extension="' + extName + '"]').length == 0) {
						var mainExtHtml = $('<div class="zdb-toolbar-entry zdb-extension-panel hidden" data-extension="' + extName + '"></div>');
						mainExtHtml.append('<div class="zdb-toolbar-preview" title="' + extName + '">' + logo + '<span class="zdb-toolbar-info">' + extName + '</span></div>');
						modulesContainer.append(mainExtHtml);
					}
	    			
	    			this.devBar.find('#custom-panels .zdb-ext-modules').append(temp);
	    		} else {
		    		if (temp.hasClass('zdb-request-details') || temp.hasClass('zdb-secure') || temp.hasClass('zdb-controls') || temp.hasClass('zdb-studio-integration') || temp.hasClass('zdb-notifications') || temp.hasClass('zdb-toolbar-group-serverinfo')) {
		    			temp.insertBefore(this.devBar.find('.zdb-modules-container').first());
		    		} else {
		    			this.devBar.find('.zdb-modules').first().append(temp);
		    		}
	    		}
    		}
    	},
	    loadRequestInfo: function(id) {
			$('.zdb-modules .zdb-toolbar-entry.zdb-extension-panel.active').removeClass('active');
			$('.zdb-ext-modules .zdb-toolbar-entry.active').removeClass('active')
			$('#custom-panels').addClass('hidden');
	    	this.bindClick();
	    	this.popupActiveElement = null;
			$('.zdb-modules .zdb-toolbar-entry.active').removeClass('active');
			
	    	if (typeof id == 'object') {
	    		if (id.length == 0) {
	    			this.resetData();
	    		} else {
		    		var currentIds = this.currentRequestIds;
		    		// reset data in case any id was removed from the list 
		    		var removedIds = currentIds.filter(function(n) {
		    		    return id.indexOf(n) == -1
		    		});
		    		if (removedIds.length > 0 || ! this.aggregate) {
		    			// if switch from single to aggregated and its completely different
		    			if (! this.aggregate && id.indexOf(currentIds[0]) < 0) {
		    				this.resetData();
		    			}
		    		}
		    		
		    		this.aggregate = true;
		    		
		    		var addedIds = id.filter(function(n) {
		    		    return currentIds.indexOf(n) == -1;
		    		});
		    		
		    		this.loadAggregatedData(addedIds);
		    		this.currentRequestIds = id;
	    		}
	    	} else if (typeof this.requests[id] != 'undefined') {
	    		if (this.currentRequestIds.length != 1 || (this.currentRequestIds.length == 1 && this.currentRequestIds[0] != id)) {
	    			this.aggregate = false;
	    		
	    			this.resetData();
	    			this.setData(this.requests[id]);
	    			this.currentRequestIds = [id];
	    			this.loadCustomData(this.requests[id]);
	    		}
	    	} else if (id == -1) { // aggregated data
	    		this.aggregate = true;
	    		this.resetData();
	    		this.loadAggregatedData(-1);
	    	}
	    	
	    	if (typeof this.requests[id] != 'undefined') {
	    		zendDevBar.callRequestInfoLoad(this.requests[id]);
	    	}

			if (this.lastActivePanel) {
				if($('.zdb-toolbar-entry[data-extension="'+this.lastActivePanel.attr('data-extension')+'"]:not(.hidden)').length > 0 ) {
					this.popupActiveElement = this.lastActivePanel;
				} else if(!this.lastActivePanel.attr('data-extension')) {
					this.lastActivePanel.children().first().click();
				}
			}
	    	if (this.popupActiveElement != null && this.popupActiveElement.attr('data-extension')) { // sometimes the attribute doens't exist ???
	    		this.showExtensionPanels(this.popupActiveElement.attr('data-extension'));
	    	}
			if(!$('.zdb-loading-custom:not(.zdb-error-message-wrapper)').hasClass('hidden')){
				$('#custom-panels .zdb-toolbar-entry').addClass('hidden');
			}
		},
		loadCustomData: function(data) {
			if (data.hasCustomData) {
				this.devBar.find('.zdb-loading-custom').removeClass('hidden');
				this.devBar.find('#custom-panels .zdb-toolbar-entry, .zdb-extension-panel').addClass('hidden');
			}
			if (typeof this.customDataRequests[data.RequestInfo.id] != 'undefined') {
				this.mapCustomDataResponse(this.customDataRequests[data.RequestInfo.id]);
				
				$.each(this.customDataRequests[data.RequestInfo.id].zrayExtensions, function(key, extension) {
					zendDevBar.callCustomDataLoaded(extension.extensionName, data);
				});
				zendDevBar.callCustomDataLoaded('default', data);

				this.hideCustomExtensions(this.customDataRequests[data.RequestInfo.id].zrayExtensions);
				this.hideExtensionsByEdition();
				return;
			}
			
			var url = zendDevBar.baseUrl + '/Api/zrayGetCustomData?requestId=' + data.RequestInfo.id;
			
			var that = this;
			that.devBar.find('.zdb-loading-custom.zdb-error-message-wrapper').addClass('hidden');
			zendDevBar.loadJSON(url, function (response){
				var extensionsData = response.responseData.zrayExtensionsData;
				that.customDataRequests[data.RequestInfo.id] = response.responseData; 
				
				that.mapCustomDataResponse(response.responseData)
				$.each(response.responseData.zrayExtensions, function(key, extension) {
					zendDevBar.callCustomDataLoaded(extension.extensionName, data);
				});
				zendDevBar.callCustomDataLoaded('default', data);				
				
				that.hideCustomExtensions(response.responseData.zrayExtensions);
				that.hideExtensionsByEdition();
				if($('.zdb-ext-modules>.zdb-toolbar-entry:not(.hidden)').length == 0){
					$('#custom-panels').addClass('hidden');
				}
				
				if (that.lastActivePanel) {
					if($('.zdb-toolbar-entry[data-extension="'+that.lastActivePanel.attr('data-extension')+'"]:not(.hidden)').length > 0 ) {
						that.popupActiveElement = that.lastActivePanel;
						setTimeout(function(){
							$('.zdb-toolbar-entry.zdb-extension-panel[data-extension="'+that.lastActivePanel.attr('data-extension')+'"]').children().first().click();
						}.bind(that),1);
					} else if(!that.lastActivePanel.attr('data-extension')) {
						that.lastActivePanel.children().first().click();
					}
				}
				
			}, function(res) {
				// hide the spinner wrapper
				that.devBar.find('.zdb-loading-custom, #custom-panels .zdb-toolbar-entry, .zdb-extension-panel').addClass('hidden');
				
				// create the error message wrapper
				var $errorMessageEntry = that.devBar.find('.zdb-loading-custom.zdb-error-message-wrapper');
				if ($errorMessageEntry.length == 0) {
					$errorMessageEntry = that.devBar.find('.zdb-loading-custom').clone();
					$errorMessageEntry.insertAfter(that.devBar.find('.zdb-loading-custom'));
					$errorMessageEntry.addClass('zdb-error-message-wrapper');
				}
				$errorMessageEntry.removeClass('hidden');
				
				// prepare the error message from the failure response
				var errorMessage = res && res.errorData && res.errorData.errorMessage ? res.errorData.errorMessage : 'error loading data';
				var warningImageHTML = '<img style="vertical-align:middle;margin:0 5px 3px 0;" width="16" height="14" src="data:image/png;base64,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"/>';
				$errorMessageEntry.html(warningImageHTML + errorMessage);
			});
		},
		hideCustomExtensions: function(zrayExtensions) {
			var that = this;
			zendDevBar.devBar.find('.zdb-toolbar-entry[data-extension]').addClass('hidden');
			$.each(zrayExtensions, function(key, extension) {
				if(extension.datatypes.length == 0 ) { return; }
				that.devBar.find('.zdb-toolbar-entry[data-extension="' + extension.extensionName + '"]').removeClass('hidden');
			});
			
			$.each(that.customVisibleExtensions, function(key, extension) {
				that.devBar.find('.zdb-toolbar-entry[data-extension="' + extension + '"]').removeClass('hidden');
			});
			
			// show all panels with alwaysShow directive
			$.each(this.customDataConfig, function(extName, extension) {
				$.each(extension, function(panelName, panel) {
					if (typeof panel.params['alwaysShow'] != 'undefined' && panel.params['alwaysShow'] === true) {
						that.devBar.find('.zdb-toolbar-entry[data-name="zrayExtension:' + extName + '/' + panelName + '"]').removeClass('hidden');
					}
				});
			});
			$(window).trigger('resize');
		},
		mapCustomDataResponse: function(data) {
			var parametersMap = {};
			
			var extensionsData = data.zrayExtensionsData;
			$.each(extensionsData, function(key, extension) {
				/// map parameters to extension/datatype for later easy access
				if (! parametersMap[extension.extensionName]) {
					parametersMap[extension.extensionName] = {};
				}
				
				parametersMap[extension.extensionName][extension.datatype] = extension.parameters;
			});
			
			zendDevBar.parametersMap = parametersMap;
			zendDevBar.customData = data;
		},
		loadAggregatedData: function(id) {
    		var allRequests = this.requests;
    		var requests = {};
    		if (typeof id == 'object') {
    			$.each(id, function(key, value) {
    				if (typeof allRequests[value] != 'undefined') {
    					requests[value] = allRequests[value];
    				}
    			});
    		} else {
    			requests = allRequests;
    		}
    		
    		var requestsArray = [];
    		$.each(requests, function(i, obj) {
    			requestsArray.push(obj);
    		})
    		
    		this.setData(requestsArray);
		},
		reachedRequestsLimit: function() {
			if (this.requestsLimit <= 0) {
				return false;
			}
			
			return (Object.keys(this.requests).length >= this.requestsLimit); 
		},
		loadRequestsList: function(repeatFlag) {
			if (this.stopRequestsFlag) {
				return ;
			}
			
			var that = this;
			
			// ZRayAllRequests
			
			var successCallback = function(response) {
				if (response.responseData) {
					var requestsInfo = response.responseData.RequestsInfo;
					if (requestsInfo.length > 0) {
						
						that.lastRequestId = parseInt(requestsInfo[requestsInfo.length - 1].RequestInfo.id);
						
						if (that.disableDevBar) {
							if (that.inIframe) {
								zendDevBar.postMessage('updateRequestsList', requestsInfo);
							}
						} else {
							that.updateRequestsList(requestsInfo);
						}
					}
				}
				
				if (repeatFlag) {
					setTimeout(function() {
						that.loadRequestsList(repeatFlag);
					}, 5000);
				}
			};
			
			var failureCallback = function(failedResponse) {
				if (failedResponse.errorData.errorCode == "malformedRequest") {
					/// try to reestablish the session
					that.callDevbar(function() {
						that.loadRequestsList(true);
					});
				} else {
					console.error('Z-Ray lost communications with Zend Server and cannot reestablish the link. Try to refresh the page to reconnect');
					if (repeatFlag) {
						setTimeout(function() {
							that.loadRequestsList(repeatFlag);
						}, 5000);
					}
				}
			};
			
			if (this.displayAllRequests) {
				// load all requests starting from `this.lastRequestTime`
				$.each(this.requests, function(idx, reqData) {
					var reqTime = reqData.RequestInfo.startTimeTimestamp;
					if (reqTime > that.lastRequestTime) {
						that.lastRequestTime = reqTime;
					}
				});
				
				this.loadJSON(this.baseUrl + '/Api/zrayGetAllRequestsInfo?from_timestamp=' + this.lastRequestTime, successCallback, failureCallback);				
			} else if (this.pageId != '') { // run the zrayGetRequestsInfo only if it's not empty pageId
				this.loadJSON(this.baseUrl + '/Api/zrayGetRequestsInfo?pageId=' + this.pageId + '&lastId=' + this.lastRequestId, successCallback, failureCallback);
			}
		},
		disable: function() {
			this.disableDevBar = true;
			$('#zend-dev-bar').remove();
		},
		isDisabled: function() {
			return this.disableDevBar;
		},
		updateRequestsList: function(requests) {
			if (this.stopUpdating) {
				
				// update last requests time, for Z-Ray live
				if (this.displayAllRequests && requests && requests.length) {
					this.lastRequestTime = requests[requests.length - 1].RequestInfo.startTimeTimestamp;
				}
				
				return;
			}

			if (this.disableDevBar) {
				if (this.inIframe && !this.reachedRequestsLimit()) {
					zendDevBar.postMessage('updateRequestsList', requests);
				}
			} else {
				var that = this;
				$.each(requests, function() {
					if (typeof that.requests[this.RequestInfo.id] == 'undefined' && !that.reachedRequestsLimit()) {
						that.requests[this.RequestInfo.id] = this;
						that.requestListUpdaterHandler($, [this]);
						that.callRequestLoad(this);
					}
				});
			}
			
			if (! this.showWarningPopup && this.reachedRequestsLimit()) {
				var limitationLink = this.baseUrl + '/Z-Ray/Settings/#panel=devbar-settings';
				this.showWarning('Number of requests has exceeded the <a href="' + limitationLink + '" target="_blank">defined limitation</a>. To continue tracking, <a href="javascript:void(0);" onclick="zendDevBar.cleanRequests()">clean request information</a>.');
			}
			
			if ($('.zdb-waiting-requests-wrapper').length > 0 && Object.keys(this.requests).length > 0) {
				$('.zdb-waiting-requests-wrapper').addClass('hidden');
			}
		},
		/// attempt to reestablish a connection with devbar to elevate the session
		/// this is not a WebAPI action, so no webapi accept header
		callDevbar: function(callback) {
			var data_file = this.baseUrl + '/Z-Ray?pageId=' + this.pageId + '&ZRayDisable=1&disable_debug=1';
		   var http_request = new XMLHttpRequest();
		   try {
		      // Opera 8.0+, Firefox, Chrome, Safari
		      http_request = new XMLHttpRequest();
		   } catch (e) {
		      // Internet Explorer Browsers
		      try {
		         http_request = new ActiveXObject("Msxml2.XMLHTTP");
		      } catch (e) {
		         try {
		            http_request = new ActiveXObject("Microsoft.XMLHTTP");
		         } catch (e){
		            // Something went wrong
		            return false;
		         }
		      }
		   }
		   
		   http_request.onreadystatechange  = function(){
		      if (http_request.readyState == 4 ) {
		    	  if (http_request.status == 200) {
		    		  callback();
		    	  }
		      }
		   };
		   http_request.open("GET", data_file, true);
		   if (this.useCredentials) {
			   http_request.withCredentials = true;
		   }
		   http_request.send();
		},
		loadJSON: function(url, callback, failCallback, method, paramsString) {
			method = method || "GET";
			if (!/(GET|POST)/.test(method)) {
				failCallback({errorData: {errorMessage: 'bad parameter supplied to load json method'}});
				return false;
			}
			
		   var data_file = url;
		   if (data_file.indexOf('?') > -1) { // no params
			   data_file += '&ZRayDisable=1&disable_debug=1';
		   } else {
			   data_file += '?ZRayDisable=1&disable_debug=1';   
		   }
		   var http_request;
		   try {
		      // Opera 8.0+, Firefox, Chrome, Safari
		      http_request = new XMLHttpRequest();
		   } catch (e) {
		      // Internet Explorer Browsers
		      try {
		         http_request = new ActiveXObject("Msxml2.XMLHTTP");
		      } catch (e) {
		         try {
		            http_request = new ActiveXObject("Microsoft.XMLHTTP");
		         } catch (e){
		            // Something went wrong
		            return false;
		         }
		      }
		   }
		   
		   http_request.onreadystatechange  = function(){
		      if (http_request.readyState == 4 ) {
				  var respJsonObj, successulParse = true;
				  try {
					  respJsonObj = $.parseJSON(http_request.responseText);
				  } catch (e) {
					  successulParse = false;
					  
					  // simulate error message
					  respJsonObj = {
						"zendServerAPIResponse": "http://www.zend.com/server/api/1.9",
						"requestData":{
							"apiKeyName":"Unknown", 
							"method":"Unknown"
						},
						"errorData": {
							"errorCode": "badResponseFormat",
							"errorMessage":"bad response format"
						}
					  };
				  }
		    	  if (successulParse && http_request.status == 200 && respJsonObj !== false) {
		    		  this.loggedOut = false;
		    		  callback(respJsonObj);
		    	  } else if (failCallback) {
		    		  failCallback(respJsonObj);
		    	  }

		      }
		   };
		   http_request.open(method, data_file, true);
		   http_request.setRequestHeader("Accept", "application/vnd.zend.serverapi+json;version=1.9");
		   if (method == 'POST' && paramsString) {
			   http_request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		   }
		   if (this.useCredentials) {
			   http_request.withCredentials = true;
		   }
		   http_request.send(paramsString);
		},
		getModuleOverlay: function(namespace) {
			var module = this.devBar.find('.zdb-toolbar-entry[data-name="' + namespace + '"] .zdb-toolbar-detail');
			if (module.find('.zdb-module-overlay').length > 0) {
				return module.find('.zdb-module-overlay').first();
			}
			var overlay = $('<div class="zdb-module-overlay"></div>');
			module.append(overlay);
			
			return overlay;
		},
		removeModuleOverlay: function(namespace) {
			this.devBar.find('.zdb-toolbar-entry[data-name="' + namespace + '"] .zdb-toolbar-detail .zdb-module-overlay').remove();
		},
		startLoading: function(namespace) {
			var module = this.devBar.find('.zdb-toolbar-entry[data-name="' + namespace + '"] .zdb-toolbar-detail');
			var spinner = $('<div class="zdb-spinner" style="height: ' + (module.outerHeight() - 30) + 'px; line-height: ' + (module.outerHeight() - 30) + 'px"><div><div class="zdb-spinner-img"></div>Loading...</div></div>');
			module.append(spinner);
		},
		finishLoading: function(namespace) {
			this.devBar.find('.zdb-toolbar-entry[data-name="' + namespace + '"] .zdb-toolbar-detail .zdb-spinner').remove();
		},
		registerHandler: function(namespace, userFunc) {
			this.handlers.set[namespace] = userFunc;
		},
		registerCollapseHandler: function(namespace, userFunc) {
			this.handlers.collapse[namespace] = userFunc;
		},
		registerExpandHandler: function(namespace, userFunc) {
			this.handlers.expand[namespace] = userFunc;
		},
		registerResetHandler: function(namespace, userFunc) {
			this.handlers.reset[namespace] = userFunc;
		},
		registerRequestDisplay: function(namespace, userFunc) {
			this.handlers.displayRequest[namespace] = userFunc;
		},
		registerCustomDataLoaded: function(extension, namespace, userFunc) {
			if (typeof this.handlers.customDataLoaded[extension] == 'undefined') {
				this.handlers.customDataLoaded[extension] = {};
			}
			if (typeof this.handlers.customDataLoaded[extension][namespace] == 'undefined') {
				this.handlers.customDataLoaded[extension][namespace] = [];
			}
			this.handlers.customDataLoaded[extension][namespace].push(userFunc);
		},
		registerRequestsListUpdater: function(userFunc) {
			this.requestListUpdaterHandler = userFunc;
		},
		registerRequestsListReset: function(userFunc) {
			this.requestListResetHandler = userFunc;
		},
		registerOnUpdateLabel: function(namespace, userFunc) {
			this.handlers.updateLabel[namespace] = userFunc;
		},
		registerOnClose: function(namespace, userFunc) {
			this.handlers.close[namespace] = userFunc;
		},
		registerOnRequestLoad: function(namespace, userFunc) {
			this.handlers.load[namespace] = userFunc;
		},
		updateLabel: function(namespace, label) {
			var elem = this.devBar.find('.zdb-toolbar-entry[data-name="' + namespace + '"] .zdb-toolbar-preview span.zdb-toolbar-info');
			
			var icon = null;
			if (elem.find('.zdb-toolbar-icon').length > 0) {
				icon = elem.find('.zdb-toolbar-icon');
			}
			
			elem.html('').append(label);
			
			if (icon != null) {
				elem.prepend(icon);
			}
		},
		getLabel: function(namespace, htmlFlag) {
			var elem = this.devBar.find('.zdb-toolbar-entry[data-name="' + namespace + '"] .zdb-toolbar-preview span.zdb-toolbar-info');
			if (typeof htmlFlag != 'undefined' && htmlFlag) {
				if (elem.find('.zdb-toolbar-icon').length > 0) {
					var temp = $('<div></div>');
					temp.append(elem.html());
					temp.find('.zdb-toolbar-icon').remove();
					return temp.html();
				} else {
					return elem.html();
				}
			} else {
				return elem.text();
			}
		},
		isAggregated: function() {
			return this.aggregate;
		},
		applyCookieParams: function() {
			if (typeof this.cookieParams.height != 'undefined') {
				if (this.requestsSeparated) {
					this.setPopupHeight(this.getHeight());
				} else {
					this.setPopupHeight(this.cookieParams.height);
				}
			}		
		},
		resetCookieParams: function() {
			this.cookieParams = {};
			this.updateCookieParams();
			this.postMessage('reload');
		},
		setData: function(data) {
			var that = this;
			if (zendDevBar.disableDevBar) {
				if (this.inIframe) {
					zendDevBar.postMessage('updateRequestsList', data);
				}
			} else {
				if (!(data instanceof Array)) data = [data];
				var requestsForUpdatingLabels = [];
				$.each(data, function(n, reqObject) {
					
					// cache the requests
					$.each(that.handlers.set, function(key, handler) {
						if (typeof that.requestsToParse[key] == 'undefined') {
							that.requestsToParse[key] = {};
						}
						if (typeof reqObject.RequestInfo != 'undefined' && typeof that.requestsToParse[key][reqObject.RequestInfo.id] == 'undefined') {
							that.requestsToParse[key][reqObject.RequestInfo.id] = reqObject;
						}
					});
					
					// prepare requests for update panels' labels
					var requestIds = [];
					if (typeof reqObject.RequestInfo != 'undefined' && that.currentRequestIds.indexOf(reqObject.RequestInfo.id) == -1) {
						$.each(that.handlers.updateLabel, function(key, handler) {
							if (requestIds.indexOf(reqObject.RequestInfo.id) < 0) {
								requestIds.push(reqObject.RequestInfo.id);
								requestsForUpdatingLabels.push(reqObject);
							}
						});
					}
					
				});
				
				// update panels' labels
				$.each(that.handlers.updateLabel, function(key, handler) {
					handler($, requestsForUpdatingLabels);
				});
				
				if (!this.previousState) {
					this.previousState = true;
					this.loadModuleFromCookie(2);
				}

				// set panel data
				if (this.popupActiveElement != null) {
					this.showData(this.popupActiveElement.data('name'));
				}
			}
			
			$(window).trigger('resize');
		},
		showExtensionPanels: function(extensionName) {
			$('#custom-panels .zdb-toolbar-entry').addClass('hidden');
			$('#custom-panels .zdb-toolbar-entry[data-extension="' + extensionName + '"]').removeClass('hidden');
			$('#custom-panels').removeClass('hidden');
			
			var panel = null;
			var panels = $('#custom-panels .zdb-toolbar-preview');
			if (typeof this.cookieParams.extensionPin != 'undefined' && typeof panels[this.cookieParams.extensionPin] != 'undefined' &&
				$(panels[this.cookieParams.extensionPin]).parent().attr('data-extension') == extensionName) {
				panel = panels[this.cookieParams.extensionPin];
			} else {
				panel = $('#custom-panels .zdb-toolbar-entry[data-extension="' + extensionName + '"] .zdb-toolbar-preview')[0];
			}
			
			if (! $(panel).parent().hasClass('active')) {
				panel.click();
			}
			
			zendDevBar.postMessage('resize', 64);
			
			this.applyCookieParams();
		},
		hideExtensionPanels: function() {
			$('#custom-panels').addClass('hidden');
			zendDevBar.postMessage('resize', 31);
		},
    	isExtensionsPanelsVisible: function() {
    		return ! ($('#custom-panels').hasClass('hidden'));
    	},
		bindClick: function() {
			var that = this;
			$.each($('.zdb-toolbar-preview'), function() {
				if (typeof $(this).data('hover') == 'undefined') {
					$(this).data('hover', 'true');
					
					// fix width of the main panel
					$(this).next('.zdb-toolbar-detail').css('width', that.devBar.width());
					
					$(this).off('click').on('click', function() {
						// if in requests separated mode, avoid clicking the requests tab
						var $elem = $(this);
						
						// check if clicked on main extensions panel
						if ($elem.parent().hasClass('zdb-extension-panel')) {
							var popupActiveElement = $elem.parent();
							if (! $elem.parent().hasClass('active')) {
								$('.zdb-modules .zdb-toolbar-entry').removeClass('active');
								if (that.embeddedMode) {
									$('.zdb-modules .zdb-toolbar-entry.zdb-request-details').addClass('active');
								}
							
								that.popupActiveElement = $(this).parent();
								$.each(that.devBar.find('.zdb-toolbar-entry'), function(key, value) {
									if ($(this).is(that.popupActiveElement)) {
										that.cookieParams.pin = key;	
										return false;
									}
								});
								
								that.popupActiveElement.addClass('active');
								that.popup.removeClass('hidden');
								that.disabledPopup.removeClass('hidden');
								
								that.showExtensionPanels(popupActiveElement.attr('data-extension'));
							} else {
								if (!that.embeddedMode) {
									that.hideExtensionPanels(popupActiveElement.attr('data-extension'));
									that.deactivatePopup();
									that.unpin();
								}
							}
							$(window).trigger('resize');
							zendDevBar.postMessage('resize', zendDevBar.getHeight());
							return;
						} else if ($elem.closest('#custom-panels').length == 0){
							if ($elem.siblings('.zdb-toolbar-detail-redundant').length == 0) {
								that.hideExtensionPanels($elem.parent().attr('data-extension'));
							}
						}
						
						while ($elem && $elem.length && !$elem.hasClass('zdb-request-details')) $elem = $elem.parent();
						var isRequestsTab = ($elem && $elem.hasClass('zdb-request-details'));
						if (isRequestsTab && that.requestsSeparated) {
							return false;
						}
						
						// continue if the clicked panel is closed
						if (! $(this).next('.zdb-toolbar-detail').hasClass('zdb-toolbar-detail-redundant')) {
							
							// popup is sticky
							if (that.popup.hasClass('zdb-sticky')) {
								// same selected module was clicked again - remove sticky 
								if ($(this).parent().is(that.popupActiveElement)) {
									// prevent closing the panel when in embedded mode and prevent unactive plugins main panel
									if (!that.embeddedMode) {
										if ($(this).closest('#custom-panels').length > 0) {
											delete that.cookieParams.extensionPin;
										} else {
											delete that.cookieParams.pin;
										}
									}
								} else { // different module was clicked - set this one as sticky
									var clickedParent = $(this).parent();
									$.each(that.devBar.find('.zdb-toolbar-entry'), function(key, value) {
										if ($(this).is(clickedParent)) {
											
											// if in "requests separated" mode, don't hide the `requests` tab
											if (that.popupActiveElement && !(that.requestsSeparated && that.popupActiveElement.data('name') == 'requests')) {
												that.hideData(that.popupActiveElement.data('name'));
											}
											
											if (that.isExtensionsPanelsVisible()) {
												that.cookieParams.extensionPin = key;
											} else {
												that.cookieParams.pin = key;
											}
											return false;
										}
									});
								}
							} else {
								that.popupActiveElement = $(this).parent();
								that.popupActiveElement.addClass('active');
								that.popup.removeClass('hidden');
								that.disabledPopup.removeClass('hidden');
								
								$.each(that.devBar.find('.zdb-toolbar-entry'), function(key, value) {
									if ($(this).is(that.popupActiveElement)) {
										if (that.isExtensionsPanelsVisible()) {
											that.cookieParams.extensionPin = key;
										} else {
											that.cookieParams.pin = key;	
										}
										return false;
									}
								});
							}
							
							var extensionPanel = ($(this).closest('#custom-panels').length > 0);
							
							if (typeof that.cookieParams.pin == 'undefined' || (extensionPanel && typeof that.cookieParams.extensionPin == 'undefined')) {
								that.devBar.find('#zdb-popup').removeClass('zdb-sticky');
								that.devBar.find('#zdb-disabled-popup').removeClass('zdb-sticky');
								that.hideData($(this).parent().data('name'));
							} else {
								that.devBar.find('#zdb-popup').addClass('zdb-sticky');
								that.devBar.find('#zdb-disabled-popup').addClass('zdb-sticky');
								that.showData($(this).parent().data('name'));
							}
							
							that.updateCookieParams();
							
							// don't hide `requests` tab when in `requests separated` mode
							$('.zdb-toolbar-entry').removeClass('active');
							if (that.requestsSeparated) {
								$('.zdb-toolbar-entry.zdb-request-details').addClass('active');
							}
							
							that.lastActivePanel = $(this).parent();	
							that.popupActiveElement = $(this).parent();
							that.popupActiveElement.addClass('active');
							that.popup.removeClass('hidden');
							that.disabledPopup.removeClass('hidden');

							var activePanel = that.popupActiveElement;
							var activePin = that.cookieParams.pin;
							
							if (typeof that.cookieParams.pin == 'undefined' || (extensionPanel && typeof that.cookieParams.extensionPin == 'undefined')) {
								that.deactivatePopup();
								that.unpin();
							}
							
							if (activePanel.closest('.zdb-toolbar-entry').attr('data-extension') != 'undefined') {
								$('.zdb-extension-panel[data-extension="' + activePanel.closest('.zdb-toolbar-entry').attr('data-extension') + '"]').addClass('active');
								that.cookieParams.pin = activePin;
							}
							
				    		$(window).trigger('resize');
						}
						
						zendDevBar.postMessage('resize', zendDevBar.getHeight());
					});
				}
			});
		},
		loadModuleFromCookie: function(counter) {
			if (counter <= 0) {
				delete this.cookieParams.pin;
				this.updateCookieParams();
				return;
			}
			
			// embedded mode: open requests tab if no tab was open previously 
			if (this.embeddedMode && !this.cookieParams.pin) {
				// find requests panel index
				var requestsPanelIndex = 0;
				$('.zdb-toolbar-entry').each(function(i, entry) {
					if ($(entry).hasClass('zdb-request-details')) {
						return requestsPanelIndex = i, false;
					}
				});
				this.cookieParams.pin = requestsPanelIndex;
				this.updateCookieParams();
			}
			
			if (typeof this.cookieParams.pin != 'undefined') {
				var popupCandidate = $('.zdb-toolbar-entry').eq(this.cookieParams.pin);
				
				// if module have a data
				if (typeof popupCandidate != 'undefined' && typeof popupCandidate.html() != 'undefined' && popupCandidate.find('.zdb-toolbar-detail.zdb-toolbar-detail-redundant').length == 0 && ! popupCandidate.hasClass('hidden')) {
					this.applyCookieParams();
					
					if (popupCandidate.hasClass('zdb-extension-panel')) {
						if (! popupCandidate.hasClass('active')) {
							popupCandidate.find('.zdb-toolbar-preview').click();
						}
					} else {
						this.selectModule(popupCandidate);
						this.popup.addClass('zdb-sticky');
						this.popup.removeClass('hidden');
						this.disabledPopup.addClass('zdb-sticky');
						this.disabledPopup.removeClass('hidden');
						this.showData(popupCandidate.data('name'));
		    		}
					$(window).trigger('resize');
				} else {
					var that = this;
					setTimeout(function() {
						that.loadModuleFromCookie(counter - 1);
					}, 1000);
				}
			}
		},
		deactivatePopup: function() {
			if (this.popupActiveElement != null && ! this.popup.hasClass('zdb-sticky')) {
				this.popupActiveElement.removeClass('active');
				this.popupActiveElement = null;
				this.popup.addClass('hidden');
				this.disabledPopup.addClass('hidden');
			}
		},
		callRequestLoad: function(data) {
			$.each(this.handlers.load, function(key, handler) {
				handler($, data);
			});
		},
		callCustomDataLoaded: function(extension, data) {
			if (typeof this.handlers.customDataLoaded[extension] != 'undefined') {
				$.each(this.handlers.customDataLoaded[extension], function(key, handlers) {
					$.each(handlers, function(handlerKey, handler) {
						handler($, data);
					});
				});
			}
			this.devBar.find('.zdb-loading-custom').addClass('hidden');
		},
		callRequestInfoLoad: function(data) {
			$.each(this.handlers.displayRequest, function(key, handler) {
				handler($, data);
			});
		},
		resetData: function() {
			var that = this;
			$.each(this.handlers.reset, function(key, handler) {
				handler($);
				if (!that.popupActiveElement || key != that.popupActiveElement.data('name')) {
					that.startLoading(key);
				}
			});
			this.requestsToParse = {};
			this.currentRequestIds = [];
		},
		collapseBar: function(){
			$('#zend-dev-bar').addClass('zdb-collapsed');
			this.cookieParams.collapsed = true;
			this.updateCookieParams();
			$.each(this.handlers.collapse, function(key, handler) {
				handler($);
			});
			this.unpin();
			$(window).trigger('resize');
			zendDevBar.postMessage('collapse');
			zendDevBar.postMessage('resize', zendDevBar.getMinHeight());
		},
		expandBar: function(){
			$('#zend-dev-bar').removeClass('zdb-collapsed');
			delete this.cookieParams.collapsed;
			this.updateCookieParams();
			$.each(this.handlers.expand, function(key, handler) {
				handler($);
			});
			$(window).trigger('resize');
			zendDevBar.postMessage('expand');
			zendDevBar.postMessage('resize', zendDevBar.getHeight());
		},
		showData: function(name) {
			if (typeof name != 'undefined' && typeof this.handlers.set[name] != 'undefined') {
				if (this.requestsToParse[name] && Object.keys(this.requestsToParse[name]).length > 0) {
					this.handlers.set[name]($, this.requestsToParse[name]);
					this.requestsToParse[name] = {};
				} else {
					this.handlers.set[name]($, null);
				}
			}
		},
		hideData: function(name) {
			if (typeof name != 'undefined' && typeof this.handlers.close[name] != 'undefined') {
				this.handlers.close[name]($);
			}
		},
		setNotBootstrap: function() {
			this.devBar.addClass('disabled');
			this.devBar.addClass('notbootstrapped');
			$(window).trigger('resize');
		},
		setLicenseExpired: function() {
			this.devBar.addClass('disabled');
			this.devBar.addClass('expired');
			this.disabledPopup.html('Zend Server license has expired, <a href="' + this.baseUrl + '" target="_blank">click here</a> to extend your license');
			$(window).trigger('resize');
		},
		setAzureNotLicensed: function() {
			this.devBar.addClass('disabled');
			this.devBar.addClass('expired');
			this.disabledPopup.html('Oops. The Z-Ray license you are using is invalid. <a href="#">Click here</a> to find out why');
			$(window).trigger('resize');
		},
		setContactZend: function(contactUrl) {
			this.contactZend = contactUrl;
		},
		setBlockedExtensions: function(blockedExtensions) {
			this.blockedExtensions = blockedExtensions;
		},
		hideExtensionsByEdition: function() {
			var that = this;
			$.each(this.blockedExtensions, function(extensionName, availableEdition) {
				//var extension = 'samples';
				$.each(that.devBar.find('div[data-extension="' + extensionName + '"]'), function() {
					var elem = $(this);
					if (! elem.hasClass('disabled')) {
						elem.addClass('disabled');
						var blocker = $('<div class="zdb-extension-blocker">This extension is available in the Zend Server ' + availableEdition + ' edition, <a href="javascript:void(0)" onclick="window.open(\'' + that.contactZend + '\', \'_blank\');" target="_blank">click here</a><a></a> to upgrade your license</div>')
						elem.find('.zdb-toolbar-detail').append(blocker);
					}
				});	
			});
			
			//<div id="zdb-disabled-popup" class="zdb-sticky" style="height: 360px; width: 100%; line-height: 360px; background: rgba(255, 255, 255, 0);">To fully enable Z-Ray, <a href="http://localhost:10081/ZendServer" target="_blank">click here</a><a></a> to bootstrap Zend Server</div>
		},
		isElementDisabled: function() {
			return this.devBar.hasClass('disabled');
		},
		notBootstrap: function() {
			return this.devBar.hasClass('notbootstrapped');
		},
		licenseExpired: function() {
			return this.devBar.hasClass('expired');
		},
		sortByKeys: function(obj) {
			var t = {};
			Object.keys(obj).sort().forEach(function(k){
				t[k] = obj[k];
			});
			
			return t;
		},
		shorten: function(str, maxLength) {
			if (str.length > maxLength) {
				var cutSize = (maxLength / 2);
				return str.substr(0, cutSize) + '...' + str.substr(str.length - cutSize);
			}
			return str;
		},
		// filterFn - filter callback before collapsing the text
		expendedText: function(str, maxLength, filterFn) {
			maxLength = maxLength || 170;
			
			// define empty filter, to avoid many "if"s, but just call the filter everywhere needed.
			filterFn = (typeof(filterFn) == 'function') ? filterFn : function(p){return p;};
			
			var cleanedText = $('<div />').text(str).text();
			
			if (cleanedText.length <= maxLength) {
				return filterFn(cleanedText);
			}
			
			var expandWrapper = $('<span>').addClass("zdb-expand-wrp").attr("title", cleanedText).append(
					$('<span>').addClass("zdb-expand-inside").html(filterFn(cleanedText.substr(0, maxLength)))
				);
			
			var expandLink = $('<div>').addClass('zdb-expand-button zdb-text-collapsed').text('Show more');
			expandLink.click(function() {
				var btn = $(this);
				if (btn.text() == 'Show more') {
					btn.text('Show less');
					btn.parent().find('span').html(filterFn(cleanedText));
				} else {
					btn.text('Show more');
					btn.parent().find('span').html(filterFn(cleanedText.substr(0, maxLength)));
				}
				expandWrapper.trigger('expandingTextChanged', {'target': btn.parent().find('span')});
				
				btn.toggleClass('zdb-text-collapsed');
				btn.toggleClass('zdb-text-expended');
			});
			expandWrapper.append(expandLink);
			
			return expandWrapper;
		},
		expandTableRows: function(btn, selector, forceCollapse) {
			forceCollapse = forceCollapse == null ? false : forceCollapse;
			
			if ( ! forceCollapse && $(btn).text() == 'Expand all') {
				$(btn).text('Collapse all');
				$(selector).find('.zdb-expand-button.zdb-text-collapsed').click();
			} else if ($(btn).text() == 'Collapse all' || forceCollapse) {
				$(btn).text('Expand all');
				$(selector).find('.zdb-expand-button.zdb-text-expended').click();
			}
		},
		// btn - the DOM element of the button/link, selector - jq selector for the relevant panel
		// the tree object should be as a DOM objects item with key `devbarTableInstance` 
		// (e.g. document.getElementById('#some-id').devbarTableInstance = new $.devbarTreeTable(...); )
		// (The class $.devbarTreeTable already does that)
		expandTreeTableRows: function(btn, selector, forceCollapse) {
			if (!selector) {
				selector = $(btn).parents('.zdb-toolbar-detail');
			} else {
				selector = $(selector);
			}
			
			forceCollapse = forceCollapse == null ? false : forceCollapse;
			var treeObj = selector.find('.zdb-entries-table-wrapper').get(0) ? 
					selector.find('.zdb-entries-table-wrapper').get(0).devbarTableInstance : null;

			if ( ! forceCollapse && $(btn).text() == 'Expand all') {
				if (treeObj instanceof $.zrayTreeTable && treeObj.expandAll) {
					treeObj.expandAll();
				}
				
				$(btn).text('Collapse all');
				selector.find('.zdb-tree-table-leaf').css('display', 'table-row');
				selector.find('.zdb-tree-table-node').addClass('zdb-tree-table-node-expanded').css('display', 'table-row');
				
			} else if ($(btn).text() == 'Collapse all' || forceCollapse) {
				if (treeObj instanceof $.zrayTreeTable && treeObj.collapseAll) {
					treeObj.collapseAll();
				}
				
				$(btn).text('Expand all');
				selector.find('.zdb-tree-table-node[zdb-tree-parent!=""]').removeClass('zdb-tree-table-node-expanded').css('display', 'none');
				selector.find('[zdb-tree-parent!=""].zdb-tree-table-leaf').css('display', 'none');
				selector.find('[zdb-tree-parent=""].zdb-tree-table-node').removeClass('zdb-tree-table-node-expanded')
			}
			
			// set zebra stripes
			var visibleRows = selector.find('tr');
			var counter = 0;
			for (var i = 0; i < visibleRows.length; i ++) {
				if ($(visibleRows[i]).css('display') == 'none') {
					continue;
				}
				
				if (counter % 2 != 0) {
					$(visibleRows[i]).addClass('zdb-even-row');
				} else {
					$(visibleRows[i]).removeClass('zdb-even-row');
				}
				
				counter++;
			}
		},
		actionsEnabled: function() {
			//this.embeddedMode -> if Z-Ray running from ZS UI, actions is not allowed.
			return ! this.disableActions && ! this.embeddedMode;
		},
		setActionsStatus: function(status) {
			this.disableActions = status;
			$('#zend-dev-bar').attr('actions-enabled', this.actionsEnabled());
		},
		getLocation: function(href) {
		    var match = href.match(/^(https?\:)\/\/(([^:\/?#]*)(?:\:([0-9]+))?)(\/[^?#]*)(\?[^#]*|)(#.*|)$/);
		    return match && {
		        protocol: match[1],
		        host: match[2],
		        hostname: match[3],
		        port: match[4],
		        pathname: match[5],
		        search: match[6],
		        hash: match[7]
		    }
		},
		setFormaters: function() {
			this.formatters = {
				'formatMemory': function (bytes, precision, minDenom) {
					/// minDenom normalizes values to a specific minimum denomination (i.e all values should be displayed atleast in KB)
					minDenom = minDenom == null ? 0 : minDenom;
					var origBytes = parseInt(bytes);
					var radix = ['B', 'KB', 'MB', 'GB'];
					var i = 0;
					
					while(bytes > 1024 || i < minDenom) {
						bytes = parseInt(bytes) / 1024;
						i++;
					}

					bytes = Math.round(bytes);
					
					if (precision > 0) {
						var precise = (parseInt(origBytes) / Math.pow(1024, i)).toFixed(precision);
						return precise + ' ' + radix[i];
					}
					
					return bytes + ' ' + radix[i];
				},
				'phpFunctionParameters': function(arguments){
					var dictionary = {
	        			"<zend-binary-value>": '<Binary>',
	        			"<zend-null>": '<NULL>',
	        			"<zend-resource>": '<PHP Resource>',
	        			"<zend-array>": 'array()',
	        			"<zend-object>": '<PHP Object>',
	        			"<zend-callable>": '<PHP Callable>',
	        			"<zend-constant>": '<PHP Constant>',
	        			"<zend-constant-array>": '<PHP Constant Array>',
	        		}
	        		
	        		var dictionaryKeys = Object.keys(dictionary);
		        	var objectExpression = /\<zend\-object name="(.+)"\>/;
		        	
	        		var args = arguments.map(function(element) {
	        			if ($zendDevBar.inArray(element, dictionaryKeys) !== -1) {
		        			return zendDevBar.formatters.htmlEntities(dictionary[element]);
	        			} else if (objectExpression.test(element)) { /// special handling for <zend-object name="class"> element
	        				var objectName = objectExpression.exec(element);
	        				return objectName[1];
	        			}
	        			
			        	return '"' + zendDevBar.formatters.htmlEntities(element) + '"';
		        	});
	        		
	        		return args.join(', ');
				},
				'formatMicroseconds': function(timestamp, percision, showFormat) {
					if (percision == null) {
						percision = 2;
					}
					timestamp = parseInt(timestamp, 10);
					timestamp /= 1000;
					
					var res = timestamp.toFixed(percision);
					res = res.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					if (typeof showFormat == 'undefined' || showFormat) {
						return res + ' ms'; 
					}
					
					return res;
				},
				'formatTime': function(microseconds, percision) {
					if (percision == null) {
						percision = 3;
					}
					
					var microseconds = parseInt(microseconds, 10);
					var ms = microseconds / 1000;
					var secs = ms / 1000;
					var mins = secs / 60;
					
					if (mins > 1) {
						return mins.toFixed(percision) + ' m';
					} else if (secs > 1) {
						return secs.toFixed(percision) + ' s';
					} else {
						return ms.toFixed(percision) + ' ms';
					}
				},
				'htmlEntities': function(str) {
				    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				}
			};
		},

		// spinner 16x16 which appears on top of the given `elem` (width and height are defined according to elem, but the icon is 16x16)
		spinner: (function() {
			var currentElem = null;
			return {
				show: function(elem) {
					currentElem = elem;
				
					if (elem.getElementsByClassName('zdb-small-spinner') && elem.getElementsByClassName('zdb-small-spinner').length) {
						return;
					}
					
					var img = document.createElement('img');
					img.setAttribute('src', '/ZendServer/images/preload-16.gif');
					img.setAttribute('class', 'zdb-small-spinner');
					img.setAttribute('width', elem.clientWidth);
					img.setAttribute('height', elem.clientHeight);
					
					if (elem.firstChild) {
						elem.insertBefore(img, elem.firstChild);
					} else {
						elem.appendChild(img);
					}
				},
				
				hide: function() {
					if (currentElem) {
						var el = currentElem.getElementsByClassName('zdb-small-spinner')[0];
						if (el && el.parentNode) { 
							el.parentNode.removeChild(el);
						}
					}
					
					currentElem = null;
				}
			};
		})(),
		
		message: (function() {
			
			var t = null, el = null, elWrapper = null, elCurtain = null;
			var createMsgElements = function() {
				if (!elWrapper || !el || !elCurtain) {
					elWrapper = document.createElement('div');
					elWrapper.classList.add('zdb-message-wrapper');
					elWrapper.classList.add('hidden');
					
					elCurtain = document.createElement('div');
					elCurtain.classList.add('zdb-message-curtain');
					elCurtain.classList.add('hidden');
					
					el = document.createElement('div');
					el.classList.add('zdb-message');
					elWrapper.appendChild(el);
					
					document.getElementById('zend-dev-bar').appendChild(elWrapper);
					document.getElementById('zend-dev-bar').appendChild(elCurtain);
				}
			}

			var self = {
				show: function(msgHtml, msgType) {
					if (!el) createMsgElements();
					
					// set the message
					el.innerHTML = msgHtml;
					
					// remove all the `type` classes
					if (el.classList.contains('zdb-message-type-error')) el.classList.remove('zdb-message-type-error');
					
					// add type
					if (msgType == zendDevBar.MSG_ERROR) {
						el.classList.add('zdb-message-type-error');
					}
					
					if (elWrapper.classList.contains('hidden')) {
						elWrapper.classList.remove('hidden');
					}
					if (elCurtain.classList.contains('hidden')) {
						elCurtain.classList.remove('hidden');
					}
					
					if (t) clearTimeout(t);
					t = setTimeout(function() {
						zendDevBar.message.hide();
						t = null;
					}, 3000);
					
					// add dot every second to improve UX
					/*
					setTimeout(function addDot() {
						el.innerHTML+= '.';
						if (t) setTimeout(addDot, 1000);
					}, 500);
					*/
				},
				
				hide: function() {
					if (!elWrapper.classList.contains('hidden')) {
						elWrapper.classList.add('hidden');
					}
					if (!elCurtain.classList.contains('hidden')) {
						elCurtain.classList.add('hidden');
					}
				}
			};
			
			return self;
		})(),
		
		setStudioClientTimeout: function(studioClientTimeout) {
			this.studioClientTimeout = studioClientTimeout;
		},
		
		loadIdeSettings: function(callbackFn, failureCallbackFn) {
			if (zendDevBar.notBootstrap()) {
				alert('To fully enable Z-Ray, launch Zend Server');
				return;
			}

			if (!this.ideSettings || parseInt(this.ideSettings.studioAutoDetection)) {			
				// add jsonp with 'zendStudioSettings' variable
				var autoDetectPort = this.ideSettings.studioAutoDetectionPort;
				var script = document.createElement("script");
				script.setAttribute("src", 'http://localhost:' + autoDetectPort + '/?ZendServer=8.0');
				document.head.appendChild(script);
			} else if (this.ideSettings) {
				zendStudioSettings = {
					debug_port: parseInt(this.ideSettings.studioPort),
					use_ssl: parseInt(this.ideSettings.studioUseSsl),
					debug_host: this.ideSettings.studioHost,
				};
			} else {
				return;
			}

			var zendStudioSettingsPollStart = new Date().getTime();
			var zendStudioSettingsPollTimer = setInterval(function() {
				if (typeof zendStudioSettings != 'undefined') {
					// success
					clearInterval(zendStudioSettingsPollTimer);
					
					// callback
					if (typeof(callbackFn) == 'function') {
						callbackFn(zendStudioSettings);
					}
				} else if (((new Date()).getTime() - zendStudioSettingsPollStart) > zendDevBar.studioClientTimeout) {
					// timeout
					clearInterval(zendStudioSettingsPollTimer);
					
					// failure callback
					if (typeof(failureCallbackFn) == 'function') {
						failureCallbackFn();
					} else {
					    alert('Cannot detect running IDE on localhost.');
					}
				}
			}, 500);
		},
		
		// (optional) fullUrl - for the debugger
		// (optional) eventsGroupId - show source for specific event. (In this case the first 3 parameters can be empty)
		showInIde: function(event, filePath, line, fullUrl, eventsGroupId) {
			zendDevBar.loadIdeSettings(function(ideSettings) {
				var params, actionUrl = zendDevBar.baseUrl + '/Api/studioShowSource';
				fullUrl = fullUrl || document.location.href;
				
				if (eventsGroupId) {
					params = ['eventsGroupId=' + eventsGroupId];
				} else {
					params = [
						'filePath='+encodeURIComponent(filePath),
						'line='+line,
						'fullUrl='+fullUrl
					];
				}
				
				// add all the loaded IDE settings
				for (var key in ideSettings) {
					params.push(key + '=' + encodeURIComponent(ideSettings[key]));
				}
				
				zendDevBar.loadJSON(actionUrl, function(res) {
					zendDevBar.spinner.hide();
					
					if (res && res.responseData && res.responseData.debugRequest && res.responseData.debugRequest.success == '0') {
						// get the message from the server (the short version)
						var theMsg = res.responseData.debugRequest.content ?
								res.responseData.debugRequest.content.replace('Show source session failed: ', '').split('.')[0] :
								'Failed to communicate with the IDE';
							
						zendDevBar.message.show(theMsg, zendDevBar.MSG_ERROR);
						console.error(res.responseData.debugRequest.content);
					}
				}, function(res) {
					zendDevBar.spinner.hide();
				
					var errMessage = res && res.errorData && res.errorData.errorMessage ? 
						res.errorData.errorMessage : 'Cannot detect running IDE settings';
						
					zendDevBar.message.show(errMessage, zendDevBar.MSG_ERROR);
				}, "POST", params.join('&'));
			}, function() {
				zendDevBar.spinner.hide();
				
				// Display error message
				zendDevBar.message.show('Cannot detect running IDE settings', zendDevBar.MSG_ERROR);
			});
		},
		
		addSlashes: function(str) {
			return str.replace(/\\/g, '\\\\');
		},
		
		// showInIdeParams - {file, line, fullUrl}
		getFileActions: function(shortText, longText, titleText, showInIdeParams) {
			if (shortText == '' || longText == '' || shortText == null) {
				return '';
			}
			
			var showInIDEButton = '';
			if (showInIdeParams && showInIdeParams.filePath && showInIdeParams.line && showInIdeParams.fullUrl && zendDevBar.zendDebuggerEnabled) {
				showInIDEButton = '<div class="zdb-show-in-ide" title="Show in IDE" onclick="zendDevBar.spinner.show(this); zendDevBar.showInIde(this, \''+zendDevBar.addSlashes(showInIdeParams.filePath)+'\', \''+showInIdeParams.line+'\', \''+zendDevBar.addSlashes(showInIdeParams.fullUrl)+'\')"></div>'
			}
			
			return '<div class="zdb-copy-clipboard" title="' + titleText + '">' +
				'<div class="zdb-ellipsis">' + shortText + '</div>' + 
				showInIDEButton + 
				'<div class="zdb-copy-img" title="See full path" onclick="window.prompt(\'Press CTRL+C, then ENTER\', this.parentNode.title);"></div>' +
			'</div>'; 
		},
		getStorage: function(namespace) {
			if (typeof this.storage[namespace] == 'undefined') {
				return this.createStorage(namespace);
			}
			
			return this.storage[namespace];
		},
		createStorage: function(namespace) {
			if (typeof this.storage[namespace] != 'undefined') {
				delete this.storage[namespace];
			}
			this.storage[namespace] = new $.devbarStorage();
			return this.storage[namespace];
		},
		getStorageName: function(storageObject) {
			for (var storageName in this.storage) {
				if (this.storage[storageName] == storageObject) {
					return storageName;
				}
			}
			
			return false;
		},
		createTable: function(dataStorage, $container) {
			return new $.devbarTable($container.get(0), dataStorage);
		},
		createSummaryTable: function(dataStorage, $container) {
			return new $.devbarSummaryTable($container.get(0), dataStorage);
		},
		createTreeTable: function(dataStorage, $container) {
			return new $.devbarTreeTable($container.get(0), dataStorage);
		},
		setGlobalCss: function(table, rule) {
			if (this.devBar.find('#zdb-styles-wrapper').length == 0) {
				this.devBar.append($('<div id="zdb-styles-wrapper"></div>'));
			}
			$('style#' + table + '-style').remove();
			$('<style type="text/css" id="' + table + '-style">').html(rule).appendTo(this.devBar.find('#zdb-styles-wrapper'));
		},
		getPagerSize: function() {
			if (typeof this.cookieParams.pager == 'undefined') {
				return 0;
			}
			
			return this.cookieParams.pager;
		},
		setPagerSize: function(size) {
    		zendDevBar.cookieParams.pager = size;
    		zendDevBar.updateCookieParams();
		}
    };
    
}($zendDevBar));

(function ($) {
	handlers: null
	formatters: {}
	
	$.Zray = function () {
		this.handlers = {'data': {}};
		this.formatters = zendDevBar.formatters;
	}
	
	$.Zray.prototype = {
		registerDataHandler: function(extension, namespace, userFunc) {
			zendDevBar.registerCustomDataLoaded(extension, namespace, function($, data) { 
				if (typeof zendDevBar.parametersMap[extension] != 'undefined' && typeof zendDevBar.parametersMap[extension][namespace] != 'undefined') {
					userFunc(zendDevBar.parametersMap[extension][namespace], data); 
				}
			});
		},
		registerExtensionLoaded: function(extension, userFunc) {
			zendDevBar.registerCustomDataLoaded(extension, 'default', function($, data) { userFunc($, data); });
		},
		getExtensionMetadata: function(extension) {
			var metadata = {};
			$.each(zendDevBar.customData.zrayExtensions, function(key, ext) {
				if (ext.extensionName == extension) {
					metadata = ext.metadata; 
					return true;
				}
			});
			
			return {'extensionParams': zendDevBar.customDataConfig[extension], 'metadata': metadata};
		},
		updateMenuTitle: function(extension, namespace, label) {
			var dataName = 'zrayExtension:' + extension + '/' + namespace;
			zendDevBar.updateLabel(dataName, label);
		},
		getMenuTitle: function(extension, namespace, htmlFlag) {
			var dataName = 'zrayExtension:' + extension + '/' + namespace;
			return zendDevBar.getLabel(dataName, htmlFlag);
		},
		getStorage: function(namespace) {
			return zendDevBar.getStorage(namespace);
		},
		createTable: function(dataStorage, $container) {
			return zendDevBar.createTable(dataStorage, $container);
		},
		createSummaryTable: function(dataStorage, $container) {
			return zendDevBar.createSummaryTable(dataStorage, $container);
		},
		createPager: function(storage, $container) {
			return new $.zdbPager(storage, $container.find('.zdb-pager'));
		},
		createSearch: function(storage, $container, maintable) {
			return new $.zdbSearch(storage, $container.find('.zdb-toolbar-input-search'), maintable);
		},
		createTreeTable: function(dataStorage, $container) {
			return new $.devbarKeyValueTreeTable($container.get(0), dataStorage);
		},
		createGeneralTreeTable: function(dataStorage, $container) {
			return new $.zrayTreeTable($container.get(0), dataStorage);
		},
		showInIde: function(filePath, line, fullUrl, eventsGroupId){
			return zendDevBar.showInIde(filePath, line, fullUrl, eventsGroupId);
		},
		getFileActions: function(shortText, longText, titleText, showInIdeParams) {
			return zendDevBar.getFileActions(shortText, longText, titleText, showInIdeParams);
		},
		call: function(actionName, params) {
			return zendDevBar.postMessage('userAction', {'action': actionName, 'params': params});
		},
		runAction: function(extension, action, params, userFunc) {
			if (! zendDevBar.actionsEnabled()) {
				alert('Run actions is disabled'); // TRANSLATE
				return;
			}
			if (typeof zray.getExtensionMetadata(extension).metadata.actionsBaseUrl == 'undefined') {
				
				zray.getExtensionMetadata(extension).metadata.actionsBaseUrl = zendDevBar.getReferer();
			}
			if (typeof zendDevBar.runActionsCallables[extension + ':' + action] == 'undefined') {
				zendDevBar.runActionsCallables[extension + ':' + action] = [];
			}
			zendDevBar.runActionsCallables[extension + ':' + action].push(userFunc);
			var url = zray.getExtensionMetadata(extension).metadata.actionsBaseUrl;
			zendDevBar.postMessage('runAction', {'url': url, 'extension': extension, 'action': action, 'params': params});
		},
		reloadPage: function() {
			zendDevBar.postMessage('reload');
		},
		actionsEnabled: function() {
			return zendDevBar.actionsEnabled();
		}
	}
}($zendDevBar));