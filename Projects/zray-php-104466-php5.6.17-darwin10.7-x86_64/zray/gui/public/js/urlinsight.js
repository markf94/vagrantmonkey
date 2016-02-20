/**
 * UrlInsight page implementation
 */

(function() {
	
	// get top bar filter values (applicationId, period);
	var getFilterValues = function() {
		return {
			applicationId: $('application-select').get('value'), 
			period: $('period-select').get('value')
		};
	};
	
	// 123456788 to "3 hours ago"
	var timestampToPrettyString = function(ts) {
		var period = currentTimestamp - ts;
		
		var days = parseInt(period / (3600 * 24));
		period-= (days * 3600 * 24);
		var hours = parseInt(period / 3600);
		period-= (hours * 3600);
		var minutes = parseInt(period / (60));
		
		var timeElements = [];
		if (days > 0) {
			timeElements.push(days + ' day' + (days != 1 ? 's' : ''));
		}
		if (hours > 0) {
			timeElements.push(hours + ' hour' + (hours != 1 ? 's' : ''));
		}
		if (minutes > 0) {
			timeElements.push(minutes + ' minute' + (minutes != 1 ? 's' : ''));
		}
		if (timeElements.length == 0) {
			timeElements.push('less than a minute');
		}
		
		return timeElements.slice(0, 2).join(' and ') + ' ago';
	};
	
	var stripTags = function(str) {
		if (!str || !str.replace) return str;
		return str.replace(/<.*?>/g, '');
	};
	
	/**
	 * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
	 * @param obj1
	 * @param obj2
	 * @returns obj3 a new object based on obj1 and obj2
	 */
	function merge_options(obj1,obj2){
	    var obj3 = {};
	    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
	    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
	    return obj3;
	}
	
	// load tables data from the server
	// 1 - most time consuming, 2 - slowest response, 3 - number of hits
	var loadData = function(reportNumber, callbackFn, failureCallbackFn) {
		var request = new Request.WebAPI({
			url: urlinsightGetUrlsUrl, // `urlinsightGetUrlsUrl` defined in view script
			data: {
				applicationId: getFilterValues().applicationId,
				filter: reportNumber,
				period: getFilterValues().period,
				limit: 10
			},
			onSuccess: function(response) {
				// update total time consumption
				if (response && response.responseData && typeof(callbackFn) == 'function') {
					callbackFn(response.responseData);
				} else {
					console.error('response error from urlinsightGetUrls (report:'+reportNumber+')');
				}
			},
			onFailure: function() {
				if (typeof(failureCallbackFn) == 'function') {
					failureCallbackFn();
				} else {
					console.error('response failure from urlinsightGetUrls (report:'+reportNumber+')');
				}
			}
		}).get();
	};
	
	// load one URL info + requests list
	var loadUrlInfo = function(resourceId, callbackFn, failureCallbackFn) {
		var request = new Request.WebAPI({
			url: urlinsightGetUrlInfoUrl, // `urlinsightGetUrlInfoUrl` defined in view script
			data: {
				id: resourceId,
				order: 'from_time desc',
				period: getFilterValues().period
			},
			onSuccess: function(response) {
				// update total time consumption
				if (response && response.responseData && typeof(callbackFn) == 'function') {
					callbackFn(response.responseData);
				} else {
					console.error('response error from urlinsightGetUrls (resourceId:'+resourceId+')');
				}
			},
			onFailure: function() {
				if (typeof(failureCallbackFn) == 'function') {
					failureCallbackFn();
				} else {
					console.error('response failure from urlinsightGetUrls (resourceId:'+resourceId+')');
				}
			}
		}).get();
	};
	
	// disable UTC support
    Highcharts.setOptions({
        global : {
            useUTC : false
        }
    });

    // graph adds timezone offset automatically, so we substruct client's timezone from it.
    var getTimestampForGraph = function(mcTs) {
    	return mcTs + (serverTimezoneOffset * 1000) - (clientTimezoneOffset * 1000);
    }

	// ..
	var displayGraph = function(resourceId) {

		// set the maximum number of points on the graph,
		// if there are more points, do the aggregation
		var maxPointsOnGraph = 100;
		
		// display the graph
		loadUrlInfo(resourceId, function(res) {
			
			// graph data
			var requestsPerSecond = [];
			var responseTime = [];
			
			var filterPeriod = getFilterValues().period;
			var timeInterval = filterPeriod < 24 + 1 && filterPeriod > 0 ? 
					intervals.daily : 
					(filterPeriod < 24 * 14 + 1 && filterPeriod > 0 ? intervals.weekly : intervals.monthly);
			var mcTimeInterval = timeInterval * 1000;
			var graphTs;
			
			if (res && res.requests && res.requests.length) {
				hideDummyGraph();
				
				var reversedRequests = res.requests.reverse();
				var timeNow = currentTimestamp;
				var mcTimeNow = timeNow * 1000;
				var mcLastPoint = mcTimeNow;
				
				// the first point. 2 hours before, or 24 hours before, .. depends on the filter
				var mcFirstPeriodPoint = mcTimeNow - filterPeriod * 60 * 60 * 1000;
				
				// check what is the min value for `maxPointsOnGraph`
				var maxNumOfPoints =  Math.floor((mcTimeNow - mcFirstPeriodPoint) / mcTimeInterval);
				if (maxPointsOnGraph > maxNumOfPoints) maxPointsOnGraph = maxNumOfPoints;
				
				// do the aggregation in case when there are more points than `maxPointsOnGraph`
				var aggPoints = [];
				var mcFirstPoint = reversedRequests[0].fromTime * 1000;
				var aggTimeInterval = Math.floor((mcTimeNow - mcFirstPeriodPoint) / maxPointsOnGraph);
				
				// prepare aggPoints - fill zeros
				for (var i=0; i<maxPointsOnGraph; i++) {
					if (!aggPoints[i]) {
						aggPoints[i] = {
							mcTimestamp: mcFirstPeriodPoint + (i * aggTimeInterval),
							pointsAggregated: 0,
							requestsPerSecond: 0,
							responseTime: 0
						};							
					}
				}
				
				// loop thru requests, and integrate the request to the proper sector in `aggPoints`
				reversedRequests.forEach(function(req, i) {
					var reqTimestamp = req.fromTime;
					var mcReqTimestamp = reqTimestamp * 1000;
					
					// calculate which sector the request fits
					var properIndexForTheRequest = Math.floor((mcReqTimestamp - mcFirstPeriodPoint) / aggTimeInterval);
					if (!aggPoints[properIndexForTheRequest]) return;
					
					// calculate points on the graph for this request
					var requestsPerSecondValue = req.samples / (req.untilTime - req.fromTime);
					var requestResponseTime = Math.round(req.avgTime / 1000);
					
					// add the calculated values to the current sector
					var newAverageRequestsPerSecond = 
						(aggPoints[properIndexForTheRequest]['requestsPerSecond'] * aggPoints[properIndexForTheRequest]['pointsAggregated'] + requestsPerSecondValue) 
						/ 
						(aggPoints[properIndexForTheRequest]['pointsAggregated'] + 1);
					aggPoints[properIndexForTheRequest]['requestsPerSecond'] = newAverageRequestsPerSecond;
					
					var newAverageResponseTime = 
						(aggPoints[properIndexForTheRequest]['responseTime'] * aggPoints[properIndexForTheRequest]['pointsAggregated'] + requestResponseTime) 
						/ 
						(aggPoints[properIndexForTheRequest]['pointsAggregated'] + 1);
					aggPoints[properIndexForTheRequest]['responseTime'] = Math.round(newAverageResponseTime);
					
					aggPoints[properIndexForTheRequest]['pointsAggregated']++;
				});
				
				// Stretch the points to fill all the interval
				// (e.g. when dividing a period by 4, we get 4 segments, but 5 points. 
				// In the next code, I stretch the 4 points to fill all the area ZSRV-14230)
				var stretchedTimeInterval = Math.floor((mcTimeNow - mcFirstPeriodPoint) / (maxPointsOnGraph - 1));
				var intervalDiff = stretchedTimeInterval - aggTimeInterval;
				aggPoints.forEach(function(aggPoint, idx) {
					aggPoints[idx].mcTimestamp += intervalDiff * idx;
				});

				// add the points to the graph
				aggPoints.forEach(function(aggPoint) {
					requestsPerSecond.push([aggPoint.mcTimestamp, aggPoint.requestsPerSecond]);
					responseTime.push([aggPoint.mcTimestamp, aggPoint.responseTime]);
				});

			} else {
				displayDummyGraph();
				return;
			}
			
			// clear the graph
			$('z-url-trend').empty();
			
			// draw the new graph
			var graph = new Highcharts.Chart({
				chart: {
					zoomType: 'x',
					renderTo: 'z-url-trend'
				},
				credits: {
					enabled: false
				},

				title: {
					text: null // 'URL\'s trend'
				},
				subtitle: {
					text: null // 'Requests per second vs Average response time'
				},
				xAxis: {
					type: 'datetime',
					dateTimeLabelFormats: {
						millisecond: '%H:%M:%S',
						second: '%H:%M:%S',
			            minute: '%H:%M',
			            hour: '%H:%M',
			            month: '%e. %b',
			            year: '%b. %y'
			        }
				},				
				yAxis: [{ // Primary yAxis
					min: 0,
					labels: {
						format: '{value}',
						style: {
							color: '#43AFE0' // #2774A0
						}
					},
					title: {
						text: null, // Requests per second
					}
				}, { // Secondary yAxis
					min: 0,
					labels: {
						format: '{value}ms',
						style: {
							color: '#FFA500'
						}
					},
					title: {
						text: null, // Response time
					},
					opposite: true
				}],
				tooltip: {
					shared: true,
					dateTimeLabelFormats: {
					    day:"%A, %b %e, %Y, %H:%M",
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 100,
					verticalAlign: 'top',
					y: 10,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
		        plotOptions: {
		            series: {
		                marker: {
		                    enabled: false
		                }
		            }
		        },
		        series: [{
					name: 'Response Time',
					type: 'spline',
					yAxis: 1,
					data: responseTime,
					color: '#FFA500',
					tooltip: {
						valueSuffix: 'ms'
					}
				
				}, {
					name: 'Requests per second',
					type: 'spline',
					color: '#43AFE0',
					data: requestsPerSecond,
					tooltip: {
						pointFormat: "{point.y:.2f} Requests / Second"
					}
				}]
			});
		
		});
	};

	var hideDummyGraph = function() {
		// remove the class
		var graphContainer = document.getElementById('z-url-trend');
		graphContainer.classList.remove('z-empty-container');
		
		// remove the curtain
		var curtainBg = graphContainer.getElementsByClassName('z-empty-container-curtain');
		if (curtainBg && curtainBg[0]) {
			curtainBg[0].parentNode.removeChild(curtainBg[0]);
		}
		
		// remove curtain text container
		var curtainText = graphContainer.getElementsByClassName('z-empty-container-curtain-text');
		if (curtainText && curtainText[0]) {
			curtainText[0].parentNode.removeChild(curtainText[0]);
		}
		
	}
	
	var displayDummyGraph = function() {
		var graphContainer = document.getElementById('z-url-trend');
		graphContainer.empty();
		graphContainer.classList.add('z-empty-container');
		
		var curtain = document.createElement('div');
		curtain.classList.add('z-empty-container-curtain');
		graphContainer.appendChild(curtain);
		
		var curtainText = document.createElement('div');
		curtainText.classList.add('z-empty-container-curtain-text');
		curtainText.textContent = 'No URL data';
		graphContainer.appendChild(curtainText);
		
	}
	
	// create one row of a table
	// 1st col shows property1 from the `record`, property2 - 2nd column.
	var createRow = function(property1, property2, record) {
		var tr = new Element('tr');
		tr.record = record;
		tr.addEvent('click', urlsTables.rowClick);
		
		var td1Value = (typeof(property1) == 'function') ? property1(record) : record[property1];
		var toolTip = property1.toLowerCase() == 'url' && record.urlTooltip ? record.urlTooltip : stripTags(td1Value);
		var td1 = new Element('td', {
			styles: {
				width: '80%'
			},
			title: toolTip,
			html: td1Value
		});
		td1.inject(tr);
		
		var td2Value = (typeof(property2) == 'function') ? property2(record) : record[property2];
		var td2 = new Element('td', {
			styles: {
				width: '20%'
			},
			title: stripTags(td2Value),
			html: td2Value
		});
		td2.inject(tr);
		
		return tr;
	};
	
	window.urlsTables = {
			parsers: {
				time: function(value) {
					value = value / 1000;
					if (value > 1000) {
						value = value / 1000;
						return value.toFixed(2) + ' <span class="z-measure-unit">s<span>';
					} else {
						return value.toFixed(2) + ' <span class="z-measure-unit">ms<span>';
					}
				},
				memory: function(value) {
					value = value / (1024 * 1024);
					return value.toFixed(2) + ' <span class="z-measure-unit">MB<span>';
				}
			},
			rowClick: function(evt) {
				var tr = evt.target;
				while (tr && tr.tagName != 'TR') tr = tr.parentNode;
				if (!tr) return;
				
				// remove currently active row
				var activeRow = document.querySelector('.z-table tr.z-active');
				activeRow && activeRow.classList.remove('z-active');
				tr.classList.add('z-active');
				
				// retrieve the record
				var record = tr.record;
				
				// update url info table
				urlsTables.updateUrlInfo({
					samples: record.samples,
					minTime: record.minTime,
					maxTime: record.maxTime,
					avgTime: record.avgTime,
					maxMemory: record.maxMemory,
					avgMemory: record.avgMemory,
					urlExample: record.urlExample
				});
				
				displayGraph(record.resourceId);
				
				// update ZRay snapshots area
				zraySnapshots.load(record.resourceId, function(snapshots) {

					// clear snapshots list
					var snapshotsList = $('z-ray-snapshots-list');
					snapshotsList.empty();
					
					// fill snapshots list
					if (snapshots.length) {
						snapshots.forEach(function(snapshot, idx) {
							
							var link = new Element('a', {
								pageId: snapshot.pageId,
								href: 'javascript:void(0);'
							});
							if (idx == 0) {
								link.addClass('z-active');
							}
							// fixTimeZone
							link.set('text', timestampToPrettyString(snapshot.requestTime));
							link.addEvent('click', function(event) {
								$('z-ray-snapshots-list').getElement('a.z-active').removeClass('z-active');
								$(event.target).addClass('z-active');
								
								var pageId = event.target.get('pageId');
								zraySnapshots.render(pageId);
								return false;
							});
							var li_item = new Element('li');
							
							link.inject(li_item);
							li_item.inject(snapshotsList);
						});
					} else {
						var li_item = new Element('li');
						li_item.setStyle('margin-left', '10px');
						li_item.set('html', '<strong>Snapshots of Z-Ray have not been recorded yet.</strong>'+
								'<p>Snapshots of Z-Ray are collected for the requests listed in the tables above. Every request can have up to 5 snapshots. The time interval between the snapshots can be configured ' + 
								'<a style="padding:0; border: 0; text-decoration: underline;" href="/ZendServer/ZendComponents/#search=zend_url_insight.zray_dumps_interval">here</a>' +
								'</p>');
						li_item.inject(snapshotsList);
					}
					
					var firstSnapshotPageId = snapshots && snapshots.length ? snapshots[0].pageId : '';
					zraySnapshots.render(firstSnapshotPageId);
				});
			},
			drawMostTimeConsuming: function(data) {
				var tableBody = $('z-data-table').getElement('tbody');
				tableBody.empty();
				if (data && data.urls) {
					
					// calculate total percentage for further fix
					var totalPercentage = 0.0;
					data.urls.forEach(function(record, idx) {
						var percent = ((record.samples * record.avgTime) / data.totalTimeConsumption) * 100;
						percent = (percent > 1) ? Math.round(percent) : percent.toFixed(1);
						totalPercentage+= parseFloat(percent);
					});
					
					// indicator if the percent was fixed to complete 100.0%
					var fixedPercent = false;
					
					// create rows
					data.urls.forEach(function(record, idx) {
						tableBody.adopt(createRow('url', function(recData) {
							var percent = ((recData.samples * recData.avgTime) / data.totalTimeConsumption) * 100;
							percent = (percent > 1) ? Math.round(percent) : percent.toFixed(1);
							
							// calculate percentage of the next element (the line below this one)
							var nextPercent = 0;
							if (idx < data.urls.length - 1) {
								var nextRecData = data.urls[idx + 1];
								var nextPercent = ((nextRecData.samples * nextRecData.avgTime) / data.totalTimeConsumption) * 100;
								nextPercent = (nextPercent > 1) ? Math.round(nextPercent) : nextPercent.toFixed(1);
							}
							
							// fix percent on the first* record
							// (if the value is less than the next line, don't fix the percent on this line, 
							// it will be done on the next line)
							if (!fixedPercent && (totalPercentage > 100.0 || data.urls.length < 10))  {
								var newPercent = percent+= (100.0 - parseFloat(totalPercentage));
								if (newPercent > nextPercent) {
									percent = newPercent.toFixed(1);
									fixedPercent = true;
								}
							}
							
							return percent + '%';
						}, record));
					});
				}
				
				// change the icon over the data column
				if (!document.getElementById('z-data-header-icon').classList.contains('z-pie')) {
					document.getElementById('z-data-header-icon').classList.add('z-pie');
				}
				if (document.getElementById('z-data-header-icon').classList.contains('z-stopper')) {
					document.getElementById('z-data-header-icon').classList.remove('z-stopper');
				}
				if (document.getElementById('z-data-header-icon').classList.contains('z-abacus')) {
					document.getElementById('z-data-header-icon').classList.remove('z-abacus');
				}			
			},
			
			drawSlowestResponse: function(data) {
				var tableBody = $('z-data-table').getElement('tbody');
				tableBody.empty();
				if (data && data.urls) {
					data.urls.forEach(function(record) {
						tableBody.adopt(createRow('url', function(recData) {
							return urlsTables.parsers.time(recData.avgTime);
						}, record));
					});
				}
				
				if (!document.getElementById('z-data-header-icon').classList.contains('z-abacus')) {
					document.getElementById('z-data-header-icon').classList.add('z-abacus');
				}
				
				// change the icon over the data column
				if (document.getElementById('z-data-header-icon').classList.contains('z-pie')) {
					document.getElementById('z-data-header-icon').classList.remove('z-pie');
				}
				if (!document.getElementById('z-data-header-icon').classList.contains('z-stopper')) {
					document.getElementById('z-data-header-icon').classList.add('z-stopper');
				}
				if (document.getElementById('z-data-header-icon').classList.contains('z-abacus')) {
					document.getElementById('z-data-header-icon').classList.remove('z-abacus');
				}			
			},
			
			drawHits: function(data) {
				var tableBody = $('z-data-table').getElement('tbody');
				tableBody.empty();
				if (data && data.urls) {
					data.urls.forEach(function(record) {
						tableBody.adopt(createRow('url', 'samples', record));
					});
				}
				
				// change the icon over the data column
				if (document.getElementById('z-data-header-icon').classList.contains('z-pie')) {
					document.getElementById('z-data-header-icon').classList.remove('z-pie');
				}
				if (document.getElementById('z-data-header-icon').classList.contains('z-stopper')) {
					document.getElementById('z-data-header-icon').classList.remove('z-stopper');
				}
				if (!document.getElementById('z-data-header-icon').classList.contains('z-abacus')) {
					document.getElementById('z-data-header-icon').classList.add('z-abacus');
				}			
			},
			
			drawNoData: function() {
				var tableBody = $('z-data-table').getElement('tbody');
				tableBody.empty();
				
				var tr = new Element('tr');
				var td = new Element('td', {
					title: 'No data',
					html: 'No data'
				});
				td.inject(tr);
				
				tableBody.adopt(tr);
			},
			
			// newDimension is between 1 and 9
			fixUrlsTableWidths: function(newDimension) {
				
				// fix titles widths
				document.getElementById('z-under-tab-urls').style.width = (8.3333 * newDimension) + '%'
				document.getElementById('z-under-tab-trends').style.width = (8.3333 * (10 - newDimension)) + '%';
				
				// fix column widths
				document.getElementById('z-content-area').children[0].setAttribute('class', 'z-col-' + newDimension);
				document.getElementById('z-content-area').children[1].setAttribute('class', 'z-col-' + (10 - newDimension));
				
				// fix table TD widths
				var firstColWidth = newDimension <= 3 ? 75 : 80;
				[].forEach.call(document.querySelectorAll('#z-data-table tr'), function(tr) {
					var TDs = tr.getElementsByTagName('td');
					TDs[0] && TDs[0].setAttribute('width', firstColWidth + '%');
					TDs[1] && TDs[1].setAttribute('width', (100 - firstColWidth) + '%');
				});
			},
			
			hideContentAreaCurtain: function() {
				var curtain = document.getElementById('z-content-area-curtain');
				if (curtain) {
					curtain.style.display = 'none';
				}
			},
			displayContentAreaCurtain: function() {
				var curtain = document.getElementById('z-content-area-curtain');
				if (!curtain) {
					var curtainHeight = document.getElementById('z-content-area').clientHeight + 'px';
					
					curtain = document.createElement('div');
					curtain.textContent = 'Loading...';
					curtain.setAttribute('id', 'z-content-area-curtain');
					curtain.style.height = curtainHeight;
					curtain.style.lineHeight = curtainHeight;
				}
				curtain.style.display = 'block';
				
				document.getElementById('z-content-area').appendChild(curtain);
			},
			
			
			
			load: function() {
				var that = this;
				var selectedTab = urlsTables.getSelectedTab();
				if (selectedTab == -1) return;
				
				
				this.displayContentAreaCurtain();
				loadData(selectedTab, function(data) {
					that.hideContentAreaCurtain();
					
					// calculate max URL length, and correct the table width accordingly
					if (data && data.urls && data.urls.length) {
						var maxLength = 0;
						data.urls.forEach(function(urlObject) {
							if (urlObject.url && urlObject.url.length > maxLength) {
								maxLength = urlObject.url.length;
							}
						});

						// fix URLs section width
						if (maxLength > 70) {
							that.fixUrlsTableWidths(5);
						} else if (maxLength > 50) {
							that.fixUrlsTableWidths(4);
						} else {
							that.fixUrlsTableWidths(3);
						} 
					}
				
					// draw the table
					if (selectedTab == 1) {
						urlsTables.drawMostTimeConsuming(data);
						document.getElementById('z-data-header-icon').setAttribute('title', 'Time Consuming')
					} else if (selectedTab == 2) {
						urlsTables.drawSlowestResponse(data);
						document.getElementById('z-data-header-icon').setAttribute('title', 'Response Time')
					} else if (selectedTab == 3) {
						urlsTables.drawHits(data);
						document.getElementById('z-data-header-icon').setAttribute('title', 'Number Of Requests')
					}
					
					if (data.urls.length > 0) {
						hideDummyGraph();
						
						// emitate click on the first row
						urlsTables.rowClick({
							target: document.querySelector('#z-data-table tr')
						});
					} else {
						urlsTables.drawNoData();
						urlsTables.updateUrlInfo();
						displayDummyGraph();
					}
				});
			},
			getSelectedTab: function() {
				var activeTab = document.querySelector('.z-active-tab');
				if (activeTab) {
					if (activeTab.getAttribute('id') == 'z-tab-consuming-time') return 1;
					if (activeTab.getAttribute('id') == 'z-tab-slowest') return 2;
					if (activeTab.getAttribute('id') == 'z-tab-most-popular') return 3;
				} 
				return -1;
			},
			updateUrlInfo: function(data) {
				data = merge_options({
					'samples': '0',
					'minTime': '0',
					'maxTime': '0',
					'avgTime': '0',
					'avgMemory': '0',
					'maxMemory': '0',
					'urlExample': '-',
				}, data);
				
				// update url info table
				$('z-url-info-samples').set('html', data['samples']);
				$('z-url-info-min-time').set('html', urlsTables.parsers.time(data['minTime']));
				$('z-url-info-max-time').set('html', urlsTables.parsers.time(data['maxTime']));
				$('z-url-info-avg-time').set('html', urlsTables.parsers.time(data['avgTime']));
				$('z-url-info-avg-memory').set('html', urlsTables.parsers.memory(data['avgMemory']));
				$('z-url-info-max-memory').set('html', urlsTables.parsers.memory(data['maxMemory']));
				var linkHtml = data['urlExample'] && data['urlExample'].length && data['urlExample'] != '-' ?
						'<a href="' + data['urlExample'] + '" title="' + data['urlExample'] + '" target="_blank">' + data['urlExample'] + '</a>' : '-';
				$('z-full-url-url').set('html', linkHtml);
			},
			initTabs: function() {
				var markTab = function(tabNum) {
					var activeTab = document.querySelector('.z-active-tab');
					activeTab && activeTab.classList.remove('z-active-tab');
					
					var tabToSelect = document.querySelectorAll('#z-tabs li')[tabNum - 1];
					tabToSelect && tabToSelect.classList.add('z-active-tab');
				};
				document.querySelector('#z-tab-consuming-time').addEvent('click', function() {
					if (document.querySelector('#z-tab-consuming-time').classList.contains('z-active-tab')) return;
					markTab(1);
					urlsTables.load();
				});
				document.querySelector('#z-tab-slowest').addEvent('click', function() {
					if (document.querySelector('#z-tab-slowest').classList.contains('z-active-tab')) return;
					markTab(2);
					urlsTables.load();
				});
				document.querySelector('#z-tab-most-popular').addEvent('click', function() {
					if (document.querySelector('#z-tab-most-popular').classList.contains('z-active-tab')) return;					
					markTab(3);
					urlsTables.load();
				});
			}
	};
	
	
	var zraySnapshots = (function() {
		var storedSnapshots = [];
		var currentPageId = '';
		var self = {
			load: function(resourceId, callbackFn) {
				var request = new Request.WebAPI({
					url: urlinsightGetZraySnapshotsUrl,
					data: {
						resource_id: resourceId
					},
					onSuccess: function(response) {
						// update total time consumption
						if (response && response.responseData && response.responseData.zraySnapshots) {
							if (typeof(callbackFn) == 'function') {
								callbackFn(response.responseData.zraySnapshots);
							}
						} else {
							console.error('response error from urlinsightGetUrls');
						}
					},
					onFailure: function() {
						console.error('got error response from urlinsightGetUrls');
					}
				}).get();
			},

			render: function(pageId) {
				if (pageId == currentPageId) return;
				currentPageId = pageId;
				if (pageId) {
					$$('.z-zray-snapshot-curtain, .z-zray-snapshot-curtain-text').setStyle('display', 'block');
					$('z-zray-snapshot').getElement('iframe').onload = function() {
						setTimeout(function() {
							$$('.z-zray-snapshot-curtain, .z-zray-snapshot-curtain-text').setStyle('display', 'none');						
						}, 1000);
					}
					$('z-zray-snapshot').getElement('iframe').setStyle('visibility', 'visible');
					$('z-zray-snapshot').getElement('iframe').set('src', '/ZendServer/Z-Ray/Zray-Inject/?pageId=' + pageId);
				} else {
					$('z-zray-snapshot').getElement('iframe').setStyle('visibility', 'hidden');
				}
			}
		};
		
		return self;
	})();
	
	var fixTableWidths = function() {
		var wrapperDiv = document.getElementsByClassName('z-urls-tables')[0];
		if (!wrapperDiv) return;
		
		var tables = wrapperDiv.getElementsByClassName('z-table');
		if (!tables) return;
		
		for (var i = 0; i < tables.length; i++) {
			tables[i].style.width = tables[i].parentNode.clientWidth + 'px';
		}
	}
	fixTableWidths();
	window.addEvent('resize', fixTableWidths);
	
	window.addEvent('domready', function() {
		urlsTables.load();
		
		urlsTables.initTabs();
	});
	
})();
