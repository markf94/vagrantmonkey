var persistantHeaders = new Class({
	prev: 0,
	next: 0,
	headers: new Array(),
	positions: new Array(),
	inPersist: new Array(),
	wrapper: null,
	elementWrapper: '_pers_wrp',
	containerPadding: 0,
	
	initialize: function() {
		var wrapper = new Element('div', {id: 'persistant-wrapper'});
		wrapper.inject($(document.body), 'top');
		
		this.wrapper = wrapper;
	},
	
	/**
	 * Header id is the id of the element (unique in the dom)
	 * @param headerId
	 */
	addHeader: function(headerId) {
		if(headerId=='topbar') { return; }
		this.headers.push(headerId);
	},
	
	resize: function() {
		if($$('body').get('layout')=='login' || !$('side-bar')){ return; }
		
		var sidebar_width = $('side-bar').getWidth();
		var containerWidth = $('main-container').getComputedSize({'styles': ['border']}).totalWidth - 20;
		this.containerPadding = $('main-container').getStyle('padding-left').toInt() + sidebar_width + 10;
		
		//$('persistant-wrapper').setStyle('width', containerWidth - 20);
		
		// this is special check for ie since the computedSize is calculated wrong so we check it 
		// getSize width is bigger and if so - use it
		if ($('main-container').getSize().x > containerWidth) {
			containerWidth = $('main-container').getSize().x; 
		}
		
		this.headers.each(function(item) {
			var innerContainerWidth = containerWidth;
			if (item != 'topbar') {
				innerContainerWidth -= (this.containerPadding - sidebar_width) * 2;
			}
			
			if ($$(".{className}".substitute({'className': item}))) {
				$$(".{className}".substitute({'className': item})).each(function(item) {
					var itemSize = item.getComputedSize();
					var itemGetSize = item.getSize();
					// this is special check for ie since the computedSize is calculated wrong so we check it 
					// getSize width is bigger and if so - use it
					if (itemGetSize.x > itemSize.totalWidth) {
						item.setStyle('width', innerContainerWidth - itemGetSize.x);
					} else {
						item.setStyle('width', innerContainerWidth - (itemSize.totalWidth - itemSize.width));
					}
				});
			} else if ($(item)) {
				var itemSize = $(item).getComputedSize();
				var itemGetSize = $(item).getSize();
				// this is special check for ie since the computedSize is calculated wrong so we check it 
				// getSize width is bigger and if so - use it
				if (itemGetSize.x > itemSize.totalWidth) {
					$(item).setStyle('width', innerContainerWidth - itemGetSize.x);
				} else {
					$(item).setStyle('width', innerContainerWidth - (itemSize.totalWidth - itemSize.width));
				}
			}
		}.bind(this));
	},
	
	swapChildren: function(elem1, elem2) {
		tempElement = new Element('div');
		
		elem1.getChildren().each(function(item) {
			tempElement.grab(item);
		});
		
		elem2.getChildren().each(function(item) {
			elem1.grab(item);
		});
		
		tempElement.getChildren().each(function(item) {
			elem2.grab(item);
		});
	},
	
	scroll: function() {
		if(!$('side-bar')){ return; }
		var position = $(document.body).getScroll();
		var sidebar_width = $('side-bar').getWidth();
		var topbar_height = $('topbar').getHeight();
		
		// make header move according to x scroller
		$$('.persClone').setStyle('left', -position.x + this.containerPadding);
		// add container padding to every element except the topbar
		$$('.persClone#topbar').setStyle('left', -position.x);
		
		var checkPos = position.y + $('persistant-wrapper').getSize().y + topbar_height;
		if (position.y <= this.prev || (this.next != null && checkPos > this.next)) {
			this.headers.each(function(header) {
				var theHeader = $$('#' + header)[0];
				if (! theHeader) {
					return;
				}
				var headerY = theHeader.getPosition().y;
				var containerMargin = $('persistant-wrapper').getSize().y + topbar_height; //$('main-container').getStyle('margin-top').toInt();
				
				if (position.y + containerMargin > headerY && theHeader.getStyle('position') != 'fixed') {
					var cloneHeader = theHeader.clone(true, true);
					
					this.wrapper.setStyle('display', 'block');
					this.prev = theHeader.getPosition().y - $('main-container').getStyle('margin-top').toInt();
					
					parentWidth = $('main-container').getSize().x - 40;
					
					cloneHeader.setStyle('width', parentWidth);
					
					this.positions[header] = headerY; 
					this.inPersist.push(header);
					
					var parentClasses = theHeader.getParent().get('class');
					
					this.wrapper.adopt(cloneHeader);
					var cloneWrapperElement = new Element('div', {'class': parentClasses}).wraps(cloneHeader);
					
					cloneHeader.setStyle('position', 'fixed');
					if(cloneHeader.tagName.toUpperCase()=='THEAD'){
						containerMargin-=topbar_height;
					}
					cloneHeader.setStyle('top', containerMargin);
					cloneHeader.setStyle('left', 0);
					cloneHeader.setStyle('right', 0);
					
					// add container padding to every element except the topbar
					if (theHeader.get('class') == 'topbar') {
						cloneHeader.setStyle('left', 0);
					} else {
						cloneHeader.setStyle('left', this.containerPadding);
					}
					
					this.wrapper.setStyle('height', containerMargin + theHeader.getSize().y + "px");
					
					cloneHeader.addClass('persClone');
					theHeader.addClass('persOrig');
					this.swapChildren(cloneHeader, theHeader);
					
					document.fireEvent('persistHeaderOn', {'element': theHeader});
				}
			}.bind(this));
			
			var reversed = Array.clone(this.headers);
			reversed = reversed.reverse();
			reversed.each(function(header) {
				var theHeader = $$('#' + header)[0];
				if (! theHeader) {
					//return;
				}
				var headerY = theHeader.getPosition().y;
	
				var containerMargin = $('persistant-wrapper').getSize().y; //$('main-container').getStyle('margin-top').toInt();
				if (position.y + containerMargin > headerY && theHeader.getStyle('position') != 'fixed') {
				} else if (position.y + containerMargin <= this.positions[header] + theHeader.getSize().y && theHeader.getStyle('position') == 'fixed') {
					var containerMargin = $('main-container').getStyle('margin-top').toInt();
					this.wrapper.setStyle('height', this.wrapper.getSize().y - theHeader.getSize().y + "px");

					var cloneHeader = theHeader;
					var origHeader = $$('.persOrig#' + theHeader.get('id'))[0];
					this.swapChildren(cloneHeader, origHeader);
					cloneHeader.removeClass('persClone');
					origHeader.removeClass('persOrig');
					
					var persWrapper = theHeader.getParent();
					
					persWrapper.dispose();
					
					delete this.positions[header];
					this.inPersist.pop();
					
					if (Object.keys(this.positions).length == 0) {
						this.prev = 0;
						this.wrapper.setStyle('display', 'none');
					} else {
						var prev = this.inPersist.pop();
						this.prev = this.positions[prev];
						this.inPersist.push(prev);
					}
					document.fireEvent('persistHeaderOff', {'element': theHeader});
				}
			}.bind(this));
			
			this.next = null;
			this.headers.some(function(header) {
				if (this.positions[header] === undefined) {
					this.next = $(header).getPosition().y - $('main-container').getStyle('margin-top').toInt();
					return true;
				}
			}.bind(this));
		}
	}
});

window.addEvent("domready", function() {
	persistantHeaders = new persistantHeaders();
	window.onscroll = function (e) {
		persistantHeaders.scroll();
	}
	
	window.addEvent('resize', function(){
		persistantHeaders.resize();
	});
	
});