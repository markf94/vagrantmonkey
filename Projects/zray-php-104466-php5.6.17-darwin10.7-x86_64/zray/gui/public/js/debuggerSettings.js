(function() {

	var formFields = [
		'activeDebugger',
		'studioAllowedHostsList',
		'studioDeniedHostsList',
		'studioAutoDetection',
		'studioHost',
		'studioAutoDetectionEnabled',
		'studioPort',
		'studioUseSsl',
		'studioBreakOnFirstLine',
		'studioUseRemote',
		'remote_enable',
		'remote_handler',
		'remote_host',
		'remote_port'
	];
	
	// gather all form fields values into one object.
	// prepare for sending to the server
	var getFormValues = function() {
		var data = {};
		var form = document.getElementById('debugger-settings');
		
		// debugger
		data['activeDebugger'] = document.querySelector('input[name="ActiveDebugger"]:checked').value;
		
		// allowed/denied hosts for debugger
		data['studioAllowedHostsList'] = form['studioAllowedHostsList'].value;
		data['studioDeniedHostsList'] = form['studioDeniedHostsList'].value;
		
		// studio config
		data['studioAutoDetection'] = form['studioAutoDetection'][1].checked ? 1 : 0;
		data['studioHost'] = form['studioHost'].value;
		data['studioAutoDetectionEnabled'] = form['studioAutoDetectionEnabled'][1].checked ? 1 : 0;
		data['studioPort'] = form['studioPort'].value;
		data['studioUseSsl'] = form['studioUseSsl'][1].checked ? 1 : 0;
		
		data['studioBreakOnFirstLine'] = form['studioBreakOnFirstLine'][1].checked ? 1 : 0;
		data['studioUseRemote'] = form['studioUseRemote'][1].checked ? 1 : 0;
		
		// xdebug configuration
		data['remote_enable'] = form['remote_enable'][1].checked ? 1 : 0;
		data['remote_handler'] = form['remote_handler'].value;
		data['remote_host'] = form['remote_host'].value;
		data['remote_port'] = form['remote_port'].value;
		data['idekey'] = form['idekey'].value;
		
		return data;
	}
	
	// put the radio buttons under the label only for "choose debugger" section
	var putDebuggerSectionUnderItsLabel = function() {
		
		// get the header
		var debuggerHeader = document.getElementById('section_debugger');
		
		// find the relevant form row
		var formRow = debuggerHeader;
		while (formRow && !formRow.classList.contains('form-row')) {
			formRow = formRow.nextSibling;
		}
		if (!formRow) return;
		
		var elementLabel = formRow.getElementsByClassName('zform-label')[0];
		if (elementLabel && !elementLabel.classList.contains('zform-wide')) {
			elementLabel.classList.add('zform-wide');
		}
		var elementContent = formRow.getElementsByClassName('zform-element')[0];
		if (elementContent && !elementContent.classList.contains('zform-wide')) {
			elementContent.classList.add('zform-wide');
		}
	};
	
	window.addEvent('load', function() {
		
		var form = document.getElementById('debugger-settings');
		
		
		// prevent from exiting the page by mistake
		form.addEventListener('change', function() {
			document.body.onbeforeunload = function() {
				return 'Changed were made';
			}
		});
		
		/**
		 * Display or hide settings section
		 * @param string section 
		 * @param bool showState
		 */
		var showSection = function(section, showState) {
			section = 'section_wrapper_' + section.replace(/\s/g, '_').toLowerCase();
			var elem = document.getElementById(section);
			if (!elem) return;
			
			if (showState) {
				if (elem.classList.contains('hidden')) {
					elem.classList.remove('hidden');
				}
			} else {
				if (!elem.classList.contains('hidden')) {
					elem.classList.add('hidden');
				}
			}
		}
		
		/** 
		 * initialize debugger toggle
		 */
		var toggleDebuggerSettings = function() {
			if (document.getElementById('choose-debugger-zend').checked) {
				// show Zend Debugger settings
				showSection('Security', true);
				showSection('IDE Client Settings', true);
				showSection('IDE Integration Settings', true);
				showSection('Xdebug Settings', false);
			} else if (document.getElementById('choose-debugger-xdebug').checked) {
				// show Xdebug settings
				showSection('Security', false);
				showSection('IDE Client Settings', false);
				showSection('IDE Integration Settings', false);
				showSection('Xdebug Settings', true);
			} else {
				// hide both
				showSection('Security', false);
				showSection('IDE Client Settings', false);
				showSection('IDE Integration Settings', false);
				showSection('Xdebug Settings', false);
			}
		};
		[].forEach.call(document.querySelectorAll('[name="ActiveDebugger"]'), function(el) {
			el.addEventListener('change', toggleDebuggerSettings);
		});
		toggleDebuggerSettings();
		
		// toggle autodetect IP
		var toggleAutodetectIp = function() {
			var studioHostIpWidgetElems = document.getElementById('studio-host').parentNode.querySelectorAll('.ipwidget_field');
			
			// if no elements for studio host, wait for them to load
			if (!studioHostIpWidgetElems.length) {
				setTimeout(toggleAutodetectIp, 50);
				return;
			}
			
			// loop the elements and mark them as `disabled` or not
			var autodetectChecked = document.getElementById('studio-autoDetectBrowser').checked;
			[].forEach.call(studioHostIpWidgetElems, function(el) {
				if (autodetectChecked) {
					el.setAttribute('disabled', 'disabled');
				} else {
					el.removeAttribute('disabled');
				}
			});
		};
		document.getElementById('studio-autoDetectBrowser').addEventListener('change', toggleAutodetectIp);
		toggleAutodetectIp();
		
		// assign submit button
		if (allowToEdit) {
			$('settings_submit').addEvent('click', function() {
				if (!confirm('The server is going to be restarted. Sure?')) return;
				document.body.onbeforeunload = null;
				$('settings_submit').spin();
				
				// manually trigger restart spinner
				notificationCenter.addMessage({type:22});
				// change the notification center hash to discover that the restart finished
				notificationCenter.hash = 5;
				
				new Request.WebAPI({
					method: 'post',
					url: DEBUGGER_SETTINGS_URL, // defined in the view
					onSuccess: function(response) {
						if (response && response.responseData && response.responseData.success == '1') {
							document.fireEvent('toastNotification', {'message': 'Settings updated successfully'});
						} else if (response && response.errorData && response.errorData.errorMessage) {
							document.fireEvent('toastAlert', {'message': response.errorData.errorMessage});
						} else {
							document.fireEvent('toastAlert', {'message': 'Error updating settings'});
						}
					},
					onFailure: function(response) {
						var response = JSON.decode(response.responseText);
						if (response && response.errorData && response.errorData.errorMessage) {
							document.fireEvent('toastAlert', {'message': response.errorData.errorMessage});
						} else {
							document.fireEvent('toastAlert', {'message': 'Request failed'});
						}
					}
				}).post(getFormValues());
				
				notificationCenter.addEvent('restartComplete', function(){
					$('settings_submit').unspin();
				});
			});
		} else {
			$('settings_submit').set('disabled', 'disabled');
		}		
		
		// put the debugger choice under its label
		putDebuggerSectionUnderItsLabel();
	});

})();