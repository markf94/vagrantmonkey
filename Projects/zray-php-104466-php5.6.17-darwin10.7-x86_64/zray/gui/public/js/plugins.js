Request.StoreAPI = new Class({
	Extends: Request.JSON,
	initialize: function(options) {
		options.noCache = true;
		
		this.parent(options);
        var accept = 'application/vnd.zend.serverapi+json';
        if (options.version != undefined) {
            accept += (';version=' + options.version);
        }

		Object.append(this.headers, {
            'X-Accept': accept
        });
		
        delete this.headers['Access-Token'];
	}
});

var pluginsResponse;

function getPlugin(id, plugins_url){
	
	var params = {
			plugin_id: id
    };
	
	var installedPluginsRequest = new Request.StoreAPI({
		data: 		params,
		method: 	'post',
		url:		plugins_url,
		version:	'1.0.2'
	});

	installedPluginsRequest.addEvent('success', function(response){
		pluginsResponse = response.plugins;
		moreDetails(id,response.plugins);
	});
	installedPluginsRequest.post();
}

function moreDetails(id, plugins){
	
	if (!plugins) {		
		var plugin = pluginsResponse[id];
	} else {
		var plugin = plugins[id];		
	}
	
	var html = $$('[template="modal-plugin"]')[0].get('html');
	
	var match = false;
	while(match = html.match(/\{\{([a-zA-Z0-9_/-]+)\}\}/)){
		html = html.replace(match[0],plugin[match[1]] || '');
	}
	if(plugin.tab_hexcolor){
		var rgb = false;
		try{
			rgb = hexToRgb('#'+plugin.tab_hexcolor);
		}catch(e){}
		if(rgb) { 
			html += '<style>\
			.plugin-modal .tabs a[selected]:before { border-bottom: 2px solid rgb('+rgb.r+', '+rgb.g+', '+rgb.b+'); } \
			.plugin-modal .tabs a:not([selected]):hover:before { border-bottom: 2px solid rgba('+rgb.r+', '+rgb.g+', '+rgb.b+',0.3); } \
			</style>';
		}
	}
	simpleModel = new SimpleModal({
		closeButton: true,
		hideHeader: true,
		hideFooter: true,
		draggable: false,
		draggableContainer: 'wizard-title',
		overlayClick: false,
		template: "<div class=\"contents\">{_CONTENTS_}</div>"
	});
	simpleModel.show({
		"model":	"modal",
		"contents":	html,
		"onRequestComplete": function() { 			
		}
	});
	
	var package_details_attrs = {
		"name":"Name",
		"version":"Version",
		"author_name":"Author Name",
		"author_email":"Author Email",
		"website":"Website URL",
		"company":"Company",
		"downloads":"Downloads",
		"route":"Route Support",
		"zray":"Z-Ray Support"
	};
	
	Object.each(package_details_attrs,function(k,v){
		if(!plugin[v]||plugin[v]=='') { return; }
		switch(v){
			case 'website':
				var text = '<a href="'+plugin[v]+'" target="_blank">'+plugin[v]+'</a>';
				break;
			case 'author_email':
				if(plugin[v]!=''){
					var text = '<a href="mailto:'+plugin[v]+'" target="_blank">'+plugin[v]+'</a>';
				}else{
					return;
				}
				break;
			case 'zray':
			case 'route':
				var text = plugin[v]==1 ? "Yes" : "No" ;
				break;
			default:
				var text = plugin[v];
				break;
		}
		$$('.simple-modal .content-tabs [tab="package-details"] table tbody')[0].innerHTML+='<tr>\
				<td>'+k+':</td>\
				<td>'+text+'</td>\
			</tr>';	
	});
	
	
	html = $$('.simple-modal .plugin-gallery-items')[0].get('html');
	if(plugin.youtube_id){
		html += '<iframe width="355" height="200" src="https://www.youtube.com/embed/'+plugin.youtube_id+'" frameborder="0" allowfullscreen></iframe>';
	}
	for(var i=0;i<plugin.gallery.length;i++){
		html += '<img style="  background-image: url('+plugin.gallery[i]+');" ph-src="'+plugin.gallery[i]+'" onclick="showFullImage(\''+plugin.gallery[i]+'\','+plugin.id+')" />';
	}
	if(!plugin.youtube_id && plugin.gallery.length==0){
		$$('.simple-modal .plugin-modal .plugin-gallery')[0].destroy();
	}else{
		$$('.simple-modal .plugin-gallery-items')[0].set('html',html);
		/* Slideshow */
		var slide_width = $$('.simple-modal .plugin-gallery-items>*').pick().getWidth()+5;
		var slides = $$('.simple-modal .plugin-gallery-items>*').length;
		var slideshow_width = slides*slide_width;
		$$('.simple-modal .plugin-gallery').set('current-slide',0);
		
		$$('.simple-modal .plugin-gallery-items').pick().setStyle('width',slideshow_width);
		$$('.simple-modal .plugin-gallery .plugin-gallery-next').pick().addEvent('click',function(e){
			if($$('.simple-modal .plugin-gallery').get('current-slide')>=slides-2) { return; }
			$$('.simple-modal .plugin-gallery-items').pick().setStyle('margin-left',$$('.simple-modal .plugin-gallery-items').pick().getStyle('margin-left').toInt()-slide_width);
			$$('.simple-modal .plugin-gallery').set('current-slide',$$('.simple-modal .plugin-gallery').get('current-slide')*1+1);
		});
		$$('.simple-modal .plugin-gallery .plugin-gallery-prev').pick().addEvent('click',function(e){
			if($$('.simple-modal .plugin-gallery').get('current-slide')<=0) { return; }
			$$('.simple-modal .plugin-gallery-items').pick().setStyle('margin-left',$$('.simple-modal .plugin-gallery-items').pick().getStyle('margin-left').toInt()+slide_width);
			$$('.simple-modal .plugin-gallery').set('current-slide',$$('.simple-modal .plugin-gallery').get('current-slide')*1-1);
		});
		/* EOF Slideshow */
	}
	if ($$('.plugin[plugin-id="'+plugin.id+'"] .install-button')['0']) {
		$$('.simple-modal footer div.btn-group')[0].innerHTML += $$('.plugin[plugin-id="'+plugin.id+'"] .install-button')[0].outerHTML;
		$$('.simple-modal footer div.btn-group')[0].innerHTML += $$('.plugin[plugin-id="'+plugin.id+'"] .download-button')[0].outerHTML;
	}
}

function hexToRgb(hex) {
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : false;
}

function versionCompare(v1, v2, options) {
    var lexicographical = options && options.lexicographical,
        zeroExtend = options && options.zeroExtend,
        v1parts = v1.split('.'),
        v2parts = v2.split('.');

    function isValidPart(x) {
        return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
    }

    if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
        return NaN;
    }

    if (zeroExtend) {
        while (v1parts.length < v2parts.length) v1parts.push("0");
        while (v2parts.length < v1parts.length) v2parts.push("0");
    }

    if (!lexicographical) {
        v1parts = v1parts.map(Number);
        v2parts = v2parts.map(Number);
    }

    for (var i = 0; i < v1parts.length; ++i) {
        if (v2parts.length == i) {
            return 1;
        }

        if (v1parts[i] == v2parts[i]) {
            continue;
        }
        else if (v1parts[i] > v2parts[i]) {
            return 1;
        }
        else {
            return -1;
        }
    }

    if (v1parts.length != v2parts.length) {
        return -1;
    }

    return 0;
}


function switchTab(e){
	$$('.simple-modal .plugin-modal [tab][selected]').each(function(item) { item.removeAttribute('selected') });
	$$('.simple-modal .plugin-modal [tab='+e.get('tab')+']').each(function(item) { item.setAttribute('selected',true) });
}

function tryParseJSON(jsonString) {
    try {
        var o = JSON.parse(jsonString);

        // Handle non-exception-throwing cases:
        // Neither JSON.parse(false) or JSON.parse(1234) throw errors, hence the type-checking,
        // but... JSON.parse(null) returns 'null', and typeof null === "object", 
        // so we must check for that, too.
        if (o && typeof o === "object" && o !== null) {
            return o;
        }
    }
    catch (e) { }

    return false;
};

function loadPrerequisites(e, id, prerequisitesUrl) {
	if ($$('.simple-modal .plugin-modal .content-tabs [tab="prerequisites"]').get('html')[0].trim() != '') {
		return;
	}
	
	$$('.simple-modal .plugin-modal .content-tabs [tab="prerequisites"]').set('html', 'Loading...');
	
	if (typeof pluginsResponse[id]['prerequisites'] != 'undefined') {
		if (pluginsResponse[id]['prerequisites'] == null || pluginsResponse[id]['prerequisites'].trim() == '') {
			$$('.simple-modal .plugin-modal .content-tabs [tab="prerequisites"]').set('html', _t('This plugin has no required prerequisites'));
		} else if (tryParseJSON(pluginsResponse[id]['prerequisites']) === false) {
			$$('.simple-modal .plugin-modal .content-tabs [tab="prerequisites"]').set('html', _t('Zend Server failed to validate the required prerequisites. As a result, the plugin will not be installed correctly.'));
		} else {
			//make the request
			var request = new Request.HTML({
				method: 'post',
				url: prerequisitesUrl,
				data: {'prerequisites' : pluginsResponse[id]['prerequisites']}, 
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
					$$('.simple-modal .plugin-modal .content-tabs [tab="prerequisites"]').set('html', responseHTML);
				},
				onFailure: function(response) {
					var decoded = this.decodeResponse(response);
					document.fireEvent('toastAlert', {'message': decoded.errorData.errorMessage});
				}
			}).send();
		}
		
	}
}

function showFullImage(img,plugin_id){
	var SM = new SimpleModal({
		closeButton: true,
		hideHeader: true,
		hideFooter: true,
		draggable: false,
		overlayClick: false,
		template: "<div class=\"contents\"><img class=\"plugin-modal-zoom-image\" src=\""+img+"\"></div>"
	});
	SM.show({
		"model":"modal"
	});
	/* Hooking close func of SimpleModel */
	SM._oldOverlay = SM._overlay
	SM._overlay = function(status){
		this._oldOverlay(status);
		//On Close
		moreDetails(plugin_id);
	}
}