var zgridPluginInfo = new Class(
{
	Implements : [ Events, Options ],

	data : {},
	responseData : {},
	reload : false,

	getOptions : function() {
		return {
			url : null
		};
	},

	initialize : function(options) {
		this.setOptions(this.getOptions(), options);
	},

	onLoadData : function(data, response) {
		// to save data of the plugin row
		this.data = data;

		if (response) {
			this.responseData = response.responseData;
			var rowContent = this.getRow(response.responseData);
			$(data.rowId).set('html', rowContent);

			var tabPane = new TabPane('tableDescContent_' + data.id,
					{}, function() {
						return 0;
					});

			var myVerticalSlide = new Fx.Slide('tableDescContent_'
					+ data.id, {
				resetHeight : true
			});
			myVerticalSlide.hide();
			myVerticalSlide.slideIn();
		}
	},

	reloadData : function(data) {
		this.reload = true;
		this.loadData(data);
	},

	loadData : function(data) {
		// if data already loaded - just show them
		if (!this.reload
				&& $('tableDescContent_' + data.id).get('html') != '') {
			var myVerticalSlide = new Fx.Slide('tableDescContent_'
					+ data.id, {
				resetHeight : true
			});
			myVerticalSlide.hide();
			myVerticalSlide.slideIn();
			return;
		}

		if (this.reload) {
			this.reload = false;
		}

		if (!this.options.url)
			return;

		var params = {
			plugin : data.id
		};

		var request = new Request.WebAPI({
			url : this.options.url,
			data : params
		});

		request.addEvent("complete", this.onLoadData.bind(this, data));

		request.get();
	},

	getRow : function(data) {
		var pluginInfo = data.pluginDetails.pluginInfo;
		var pluginPackage = data.pluginDetails.pluginPackage;
		var servers = data.pluginDetails.servers;

		var errorTabTitle = '';
		var errorTabContent = '';

		if (pluginInfo.status.test('error', [ 'i' ])
				&& (0 < pluginInfo.messageList.length)) {
			var errorTabTitle = '<li class="tab">' + _t('Error')
					+ '</li>';
			var result = '<h4>'
					+ pluginInfo.messageList[0].type
					+ ':</h4>&nbsp;<span>'
					+ this
							.messageFormat(pluginInfo.messageList[0].message)
					+ '</span>';
			var errorTabContent = '<div class="content">' + result
					+ '</div>';
		}



		var prerequisites = '<div class="content prerequisites_container" id="prerequisites_'
				+ pluginInfo.id + '"></table></div>';

		var serversHTML = '';
		var serversLi = '';
		if (Object.keys(data.pluginDetails.servers).length > 0) {
			serversLi = '<li class="tab">Active Servers</li> \ ';
			serversHTML = this._prepareServers(
					data.pluginDetails.servers, pluginInfo.id);
		}

		var html = '<div id="tableDescContent_'
				+ pluginInfo.id
				+ '" class="appDetails">\
                <ul class="tabs"> \
                            '
				+ errorTabTitle
				+ ' \
                    <li class="tab first">Details</li>';

			html += '<li class="tab" onclick="getPluginPrerequisites(\''
					+ pluginInfo.id + '\');">Prerequisites</li>';

		html += serversLi
				+ ' \
                </ul> \
                '
				+ errorTabContent
				+ ' \
                <div class="content"> \
                            <table class="tableWithDesc"> \
                                    <tr> \
                                            <td>Plugin Name</td> \
                                            <td>'
				+ pluginInfo.displayName
				+ '</td> \
                                    </tr> \
                                    <tr> \
                                            <td>Version</td> \
                                            <td>'
				+ pluginInfo.version
				+ '</td> \
                                    </tr> \
                                    <tr> \
                                            <td>Deployed On</td> \
                                            <td>'
				+ formatDate(pluginInfo.creationTimeTimestamp)
				+ '</td> \
                                    </tr> \
									<tr> \
					                <td>Deployed In</td> \
					                <td>'
				+ pluginInfo.installPath
				+ '</td> \
					        </tr> \
                            </table> \
                    <img src="/ZendServer/Plugins/Plugin-Icon?id='
				+ pluginInfo.id
				+ '" class="app-details-logo"> \
                </div>';

			html += prerequisites;

		html += serversHTML + ' \
            </div>';
		return html;
	},

	setServerIcon : function(serverData) {
		
		var statusImage = '<img src="' + baseUrl() + '/images/apps-status-ok.png"  title="' + _t('OK') + '" />';
		if (serverData.status != 'STAGED' && serverData.status != 'ENABLED') {
			statusImage = '<img src="' + baseUrl() + '/images/apps-status-warning.png"  title="' + _t('Warning') + '" />';
		}
		return statusImage;
	},
	
	_prepareServers : function(data, pluginInfoId) {

		serversHTML = '<div id="appServersList_'
				+ pluginInfoId
				+ '" class="content appServersList"><table class="tableWithDescServers">';
		serversHTML += '<tr>';

		// Only if the list more than 1 server show the redeploy option
		if (Object.keys(this.responseData.pluginDetails.servers).length != 1) {
			serversHTML += '<th>Shown: '
					+ Object
							.keys(this.responseData.pluginDetails.servers).length
					+ '</th>';
		}

		serversHTML += '<th>!</th> \
        <th>Name</th> \
		<th>Messages</th> \
</tr> \ ';

		Object
				.each(
						data,
						function(serverData, id) {

							serversHTML += '<tr id="serverRow_'
									+ pluginInfoId + '_' + id
									+ '" class="serverRow">';
							serversHTML += '<td class="statusImage">'
									+ this.setServerIcon(serverData)
									+ '</td> \
			            <td style="width: 200px">'
									+ serverData.serverName
									+ '</td> \
			       		<td style="white-space: normal;">'
									+ serverData.messages
									+ '</td> \
		            </tr>';
						}.bind(this));

		serversHTML += '</table>';
		serversHTML += '</div>';
		return serversHTML;
	},

	_makeList : function(data) {

		var result = '<ul class="prerequisites-list">';

		Object
				.each(
						data,
						function(message, element) {
							result += '<li class="'
									+ (message.isValid != 'true' ? 'prerequisite-item-error'
											: 'prerequisite-item-valid')
									+ '">';
							result += '<span>' + message.message
									+ '</span>';
							result += '</li>';
						})

		return result;
	},

	clean : function(value) {
		var cleaner = new Element('div');
		cleaner.set('text', value);

		return cleaner.get('html');
	},

	getCount : function(value) {
		if (value > 1000) {
			value = Math.floor(value / 1000) + "k";
		}
		return '<div class="issues-count-wrapper"><div class="issues-count">'
				+ value + '</div></div>';
	},

	messageFormat : function(string) {
		return (string + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
				'$1' + '<br>' + '$2');
	}
});