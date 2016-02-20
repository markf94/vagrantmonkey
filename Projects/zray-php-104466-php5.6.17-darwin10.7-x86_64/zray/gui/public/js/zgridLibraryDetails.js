var zgridLibraryDetails = new Class({
	Implements: [Events,Options],
	
	prefix: '',
	
	getOptions: function(){
		return {
		};
	},
	
	initialize: function(options){
		this.setOptions(this.getOptions(), options);
	},
	
	loadData: function(data)
	{
		if (data) {
			var rowContent = this.getRow(data);
		
			$('tableDescContent_' + data.libraryId).set('html', rowContent);
			createRuleHelpTooltip();   
			
			var tabPane = new TabPane('tableDescContent_' + data.libraryId, {}, function() {
				return 0;
			});
			
			var myVerticalSlide = new Fx.Slide('tableDescContent_' + data.libraryId, {resetHeight: true});
			myVerticalSlide.hide();
			myVerticalSlide.slideIn();
		}
	},
	
	getRow: function(data) {
		var serversLi = '';
		var errorTabContent = '';
		var errorTabTitle = '';
        var errorTabContent = '';
		
        var prerequisitesExists = false;
        
        //if (applicationPackage.prerequisites != '' && applicationPackage.prerequisites != '<dependencies></dependencies>') {
        	prerequisitesExists = true;
        //}
        
        prerequisitesExists = true;
        var serversHTML = '';
        var serversLi = '';
        if (Object.keys(data.servers).length > 0) {
        	serversLi = '<li class="tab">Active Servers</li> \ ';
        	serversHTML = this._prepareServers(data.originalServers, data.originalId);
        }

		var html =	'<ul class="tabs"> \
                            '+ errorTabTitle +' \
                    <li class="tab first">Details</li>';
	   
	    if (prerequisitesExists) {
	    	html += '<li class="tab" onclick="getLibraryPrerequisites(\'' + data.originalId + '\');">Prerequisites</li>';
	    }
		
	    var defaultLibPath = '';
	    if (data.default != undefined && data.default) {
	    	defaultLibPath = '<tr> \
	                                <td> \
	                                	Default Path \
	                                	<div class="rule-help" rel="Insert this API call in your code to use the default version of this library"></div> \
	                                	</td> \
	                                <td> \
	                                	 <code>zend_deployment_library_path(\'' + data.originalName + '\');</code> \
	                                </td> \
	                        </tr>';
	    }
	    
	    var releaseDate = '';
	    if (data.releaseDateTimestamp != '') {
	    	releaseDate  = '<tr> \
	    		<td>Release Date</td> \
	    		<td>' + formatDate(data.releaseDateTimestamp) + '</td> \
            </tr>';
	    }
	    
		html += serversLi + ' \
		        </ul> \
		        ' + errorTabContent +' \
		        <div class="content"> \
		        			<img src="/ZendServer/DeploymentLibrary/Library-Icon?id=' + data.originalId + '" class="app-details-logo"> \
		                    <table class="tableWithDesc"> \
		                            <tr> \
		                                    <td>Library</td> \
		                                    <td>' + data.originalName + '</td> \
		                            </tr> \
		                            <tr> \
		                                    <td>Version</td> \
		                                    <td>' + data.version + '</td> \
		                            </tr> \
		                            ' + releaseDate + ' \
		                            <tr> \
		                                    <td>Deployed On</td> \
		                                    <td>' + formatDate(data.creationTimeTimestamp) + '</td> \
		                            </tr> \
		                            <tr> \
		                                    <td> \
		                                    	Library Path \
		                                    	<div class="rule-help" rel="Insert this API call in your code to use this specific library version"></div> \
		                                    	</td> \
		                                    <td> \
		                                    	 <code>zend_deployment_library_path(\'' + data.originalName + '\', \'' + data.version + '\');</code> \
		                                    </td> \
	                                </tr> \
		                            ' + defaultLibPath + ' \
		                    </table> \
		        </div>';
		
		if (prerequisitesExists) {
			html += '<div class="content prerequisites_container" id="prerequisites_' + data.originalId + '"></div>';
		}
         
         html += '<div>' + serversHTML + ' \
         </div>';
         
		return html;
	},
	
	_prepareServers: function (servers, libraryId) {
    	
    	serversHTML = '<div id="libraryServersList_'+libraryId+'" class="content libraryServersList"><table class="tableWithDescServers">';
    	serversHTML +=  '<tr>';
    	
    	if (servers.length != 1) {        		
    		serversHTML += '<th>Shown: ' + servers.length + '</th>';
    	}
    	
    	serversHTML +=  '<th></th> \
            <th>Name</th> \
    		<th>Messages</th> \
    </tr> \ ';
    	
    	Object.each(servers, function(serverData, id){
    		
    		serversHTML +=  '<tr id="serverRow_'+ 'libraryId_' + id +'" class="serverRow">';
    		
    		var message = serverData.lastMessage;
    		if (message == null) {
    			message = "";
    		}
    		serversHTML += '<td class="statusImage">' + this.setServerIcon(serverData) + '</td> \
				            <td style="width: 200px">' + serverData.serverName + '</td> \
				       		<td style="white-space: normal;">' + message + '</td> \
			            </tr>';
    	}.bind(this));
    	
    	serversHTML += '</table>';
    	serversHTML += '</div>';
    	return serversHTML;
    },
    
    setServerIcon: function(serverData) {
    	var statusImage = '<img src="' + baseUrl() + '/images/apps-status-ok.png"  title="' + _t('Ok') + '" />';
    	// error/Error not unknown
		if (	serverData.status.indexOf("Error") != -1 || 
				serverData.status.indexOf("error") != -1 || 
				serverData.status.indexOf("not") != -1 || 
				serverData.status.indexOf("unknown") != -1 ) {
			statusImage = '<img src="' + baseUrl() + '/images/apps-status-warning.png"  title="' + _t('Warning') + '" />';
		}
		return statusImage;
    }

});