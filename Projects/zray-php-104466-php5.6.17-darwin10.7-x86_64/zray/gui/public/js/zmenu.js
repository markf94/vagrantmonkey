var zMenu = new Class({
	Implements: [Events],
	keepMenuButton: false,
	currentId: null,
	timeout: null,
	zgridInstance: 'zgrid2',
	initialize: function() {
	},
	
	setZgrid: function(zgridInstance) {
		this.zgridInstance = zgridInstance;
	},
	
	showMenu: function(id) {
		if (! $("menuitem_" + id)) {
			return;
		}
		
		$("menuitem_" + id).removeClass("hidden");
	},
	
	hideMenu: function(id) {
		
		if (! $("menuitem_" + id)) {
			return;
		}
		
		if (! this.keepMenuButton) {
			$("menuitem_" + id).addClass("hidden");
			$$(".table-menu.active").removeClass("active");
		}
	},
	
	menuItemClicked: function(element) {
		this.keepMenuButton = true;
		element.addClass("active");
	},
	
	getMenuContent: function(id) {
		var zgrid = eval(this.zgridInstance);
		return '<ul> \
			<li id="menuitem_debug_' + id + '">' + _t('Debug in IDE') + '</li> \
			<li id="menuitem_profile_' + id + '">' + _t('Profile in IDE') + '</li> \
			<li id="menuitem_show_source_' + id + '">' + _t('Show in IDE') + '</li> \
			<li><a href="' + baseUrl() + '/IDEIntegration/exportIssueByEventsGroup?eventsGroupId=' + zgrid.data[id].whatHappenedDetails.eventsGroupId + '">' + _t('Export to IDE') + '</a></li> \
		</ul>';
	},
	
	createMenu: function(parent, id) {
		this.currentId = id;
		var tableColumn = new Element('div', {'title': 'IDE actions', 'class': "table-menu-wrapper", styles: {width: '3%'}});
		var menu = new Element('div', {'id': "menuitem_" + id , 'class': "table-menu hidden"});
		menu.inject(tableColumn);
		
		menu.addEvent('click', function() {
			this.menuItemClicked(menu);
		}.bind(this));
		
		tableColumn.inject(parent);
		
		var htmlContent = this.getMenuContent(id);
		
		var tip = new FloatingTips('.table-menu', {
    		// Content can also be a function of the target element!
    		content: function(e) { return htmlContent; },
    		html: true,
    		position: 'bottom', // Bottom positioned
    		center: true, // Place the tip aligned with target
    		arrowSize: 8, // A bigger arrow!
    		distance: -20,
    		showOn: 'click',
    		hideOn: 'null'
    	});
		
		tip.addEvent('show', function() {
    		$$('.floating-tip-wrapper').each(function(item) {
            	item.addEvent('mouseleave', function() {
            		tip._animate(item, 'out');
            		this.keepMenuButton = false;
            		this.hideMenu(this.currentId);
                }.bind(this));
        	}.bind(this));
    		
    		var zgrid = eval(this.zgridInstance);
    		var eventsGroupId = zgrid.data[id].whatHappenedDetails.eventsGroupId;
    		
    		$('menuitem_debug_' + id).addEvent('click', function() {
    			if ($('menuitem_debug_' + id).spinner == undefined) {
    				$('menuitem_debug_' + id).spinner = new Spinner($('menuitem_debug_' + id));
    			} 
    			$('menuitem_debug_' + id).spinner.show();
    			this.autoDetectStudioIntegrationAction('studioStartDebug', eventsGroupId, id);
    		}.bind(this));
    		
    		$('menuitem_profile_' + id).addEvent('click', function() {
    			if ($('menuitem_profile_' + id).spinner == undefined) {
    				$('menuitem_profile_' + id).spinner = new Spinner($('menuitem_profile_' + id));
    			} 
    			$('menuitem_profile_' + id).spinner.show();
    			this.autoDetectStudioIntegrationAction('studioStartProfile', eventsGroupId, id);
    		}.bind(this));
    		
    		$('menuitem_show_source_' + id).addEvent('click', function() {
    			if ($('menuitem_show_source_' + id).spinner == undefined) {
    				$('menuitem_show_source_' + id).spinner = new Spinner($('menuitem_show_source_' + id));
    			} 
    			$('menuitem_show_source_' + id).spinner.show();
    			this.autoDetectStudioIntegrationAction('studioShowSource', eventsGroupId, id);
    		}.bind(this));
    		
        }.bind(this));
	},
	
	runInStudio: function(action, eventGroupId, additionalParams) {
		// @todo: additionalParams are currently ignored
		var params = {
				eventsGroupId: eventGroupId
		};

	    if (additionalParams) {
	    	params = Object.merge(params, additionalParams);
	    }
	    
		var actionUrl = baseUrl() + '/Api/' + action;

		var request = new Request.WebAPI({url: actionUrl, data:params, method: 'post',
		onSuccess: function(response) {
			var toastType = response.responseData.debugRequest.success == '0' ? 'toastAlert' : 'toastNotification';
			document.fireEvent(toastType, {'message': response.responseData.debugRequest.content});
		},
		onFailure: function(response) {
			document.fireEvent('toastAlert', {'message': response.statusText});
		}});
		
		request.post();
	},
	
	autoDetectStudioIntegrationAction: function(action, id, spinnerId) {
		var callbackFn = function() {
        	if (action.test('debug', 'i') && $('menuitem_debug_' + spinnerId) && $('menuitem_debug_' + spinnerId).spinner) {					
				$('menuitem_debug_' + spinnerId).spinner.hide();
			}
			if (action.test('profile', 'i') && $('menuitem_profile_' + spinnerId) && $('menuitem_profile_' + spinnerId).spinner) {
				$('menuitem_profile_' + spinnerId).spinner.hide();
			}
			if (action.test('source', 'i') && $('menuitem_show_source_' + spinnerId) && $('menuitem_show_source_' + spinnerId).spinner) {
				$('menuitem_show_source_' + spinnerId).spinner.hide();
			}

			if (typeof zendStudioSettings != 'undefined') {
				this.runInStudio(action, id, zendStudioSettings);
			} else {
	            document.fireEvent('toastAlert', {'message': _t('IDE was not detected, action cannot be completed')});
			}
        }.bind(this);

        if (typeof(autoDetectIdeSettings) != 'undefined' && autoDetectIdeSettings === false) {
			// in this case, `zendStudioSettings` is already defined
			callbackFn();
		} else {
			var autoDetectPort = typeof(window.autoDetectIdePort) != 'undefined' ? window.autoDetectIdePort : 20080;
			var request = new Request.JSONP({url: 'http://localhost:'+autoDetectPort+'/?ZendServer=8.1',
				timeout:4000,
		        onTimeout: callbackFn
		    });
			request.send();
		}
	    			
	}
});
