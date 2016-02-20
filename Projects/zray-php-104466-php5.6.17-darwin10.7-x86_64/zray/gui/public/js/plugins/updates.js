var pluginsUpdates = new Class({
	
	storeListApiUrl: '',
	saveUpdatesUrl: '',
	serverInfo: {},
	
	initialize: function(saveUpdatesUrl, storeListApiUrl, serverInfo) {
		this.storeListApiUrl = storeListApiUrl;
		this.saveUpdatesUrl = saveUpdatesUrl;
		this.serverInfo = serverInfo;
	},

	runUpdate: function(installedPlugins, toCleanCookie) {
		// if emty installed list - don't send
		if (!installedPlugins) {
			return;
		}
		var params = new Object();
		params.plugins = installedPlugins;
		var jsonRequest = new Request.JSON({url: this.storeListApiUrl, onComplete: function(response){
			this.saveUpdates(response, toCleanCookie);
		}.bind(this)});
		
		delete jsonRequest.headers['Access-Token'];
		
		jsonRequest.post(Object.merge(params,this.serverInfo));
		
	},
	
	saveUpdates: function(response, toCleanCookie) {
		var request = new Request.WebAPI({
			url: this.saveUpdatesUrl,
			data: response,
			method: 'post',
			onComplete: function(response) {
				
				document.fireEvent('saveUpdatesComplete');
				
				if (toCleanCookie != 'undefined' && toCleanCookie) {
					Cookie.write('ZSPLUGINS', '', {duration: 1});
				}
			}
		}).send();
	}
});