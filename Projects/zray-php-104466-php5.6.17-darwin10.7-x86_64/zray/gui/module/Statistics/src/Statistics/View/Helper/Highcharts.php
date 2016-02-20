<?php
namespace Statistics\View\Helper;

use Zend\View\Helper\AbstractHelper,
	Application\Module,
	Statistics\Container,
	Statistics\Model;

class Highcharts extends AbstractHelper {
	
	/**
	 * @param string $graphId
	 * @param Statistics\Container $container 
	 * @return string
	 */
	public function __invoke($graphId, $container, $title = null, $width = null, $onlyIncludes = false) {
		$basePath = $this->getView()->basePath();
		$this->view->plugin('headScript')->appendFile($basePath . '/js/highcharts/adapters/mootools-adapter.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/highcharts/highcharts.js');
		$this->view->plugin('headScript')->appendFile($basePath . '/js/charts.js');
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/zgrid.css');
		$this->view->plugin('headLink')->appendStylesheet($basePath . '/css/charts.css');
		if ($onlyIncludes) return '';
		
		$chartCode = $this->getChartCode($graphId, $container, $title);
		
		if (is_null($width)) {
			$width = '33%';
		}
		
		return <<<CHART
{$chartCode}
<div id="{$graphId}" class="main-apps-graph" style="width: {$width}; float: left;"></div>
CHART;
	}
	
	/**
	 * @param string $graphId
	 * @param \Statistics\Container $container
	 * @return string
	 */
	private function getChartCode($graphId, $container, $title) {
		if (is_array($container)) {
			$chartType = current($container)->getChartType();
		} else {
			$chartType = $container->getChartType();
		}
		
		if ($chartType == \Statistics\Container::TYPE_LINE) {
			return $this->getLineChart($graphId, $container, $title);
		} elseif ($chartType == \Statistics\Container::TYPE_PIE) {
			return $this->getPieChart($graphId, $container, $title);
		} elseif ($chartType == \Statistics\Container::TYPE_LAYERED_LINE) {
			return $this->getLineChart($graphId, $container, $title);
		}
	}
	
	private function getPieChart($graphId, $container, $title) {
		if (is_array($container)) {
			$container = current($container);
		}
		
		$theTitle = $container->getTitle();
		$pollUrl = "{$this->getView()->basePath()}/Api/statisticsGetSeries?type=";
		
		$colors = '';
		$events = '';
		$tooltipEnabled = 'false';
		if ($container->getCounterId() == Model::STATS_EVENTS_PIE) {
			$colors = 'colors: [\'#92c4d6\', \'#f23737\', \'#ffd617\'],';
			$events = <<<EVENTS
			click: function() {
				var filterId = 'Performance%20Issues';  
				if (this.name == 'Errors') {
					filterId = "Errors%20Issues";
				} else if (this.name == 'Resources') {
					filterId = "Resources%20Issues";
				}
					
				var applicationId = '';
				if (zendCharts.getCurrAppId() != 0) {
					applicationId = '&applicationIds=' + zendCharts.getCurrAppId();
				}
					
				window.location.href = baseUrl() + "/IssueList#filterId=" + filterId + "&timeRange=" + timeRangeDictionary[$('time-range').get('value')] + applicationId;
			}
EVENTS;
		} elseif ($container->getCounterId() == Model::STATS_BROWSERS_PIE ||
				  $container->getCounterId() == Model::STATS_OS_PIE || 
				  $container->getCounterId() == Model::STATS_MOBILE_OS_PIE) {
			$tooltipEnabled = 'true';
		}
		
		return <<<JAVASCRIPT
<script type="text/javascript">
var timeRangeDictionary = {'2h': '2hours', '1d': 'day', '7d': 'week', '14d': '2weeks', '1m': 'month', '3m': '3months', '6m': '6months', '12m': 'year', 'e': 'all'};

window.addEvent("domready", function() {

	$('{$graphId}').graph = 
	new Highcharts.Chart({
		dataUrl: '{$pollUrl}',
		counterId: {$container->getCounterId()},
		chart: {
			renderTo: '{$graphId}',
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false
		},
		lang: {
			loading: ''
		},
		loading: {
            labelStyle: {}
        },
		credits: {
			enabled: false
		},
		title: {
			text: '{$theTitle}'
		},
		tooltip: {
			enabled: {$tooltipEnabled},
			formatter: function() {
				if (this.point.name == 'No Data') {
					return '<b>'+ this.point.name +'</b>';
				}
			    return '<b>'+ this.point.name +'</b>: '+ this.point.y +' requests';
			},
            percentageDecimals: 1
		},
		{$colors}
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				borderWidth: 0,
				dataLabels: {
					distance: 10,
					enabled: true,
					color: '#000000',
					connectorColor: '#000000',
					formatter: function() {
						return '<b>'+ this.point.name +'</b>: '+ (this.percentage).toPrecision(3) +' %';
					}
				}
			},
			series: {
            	cursor: 'pointer',
            	point: {
	                events: {
                    	{$events}
                	}
            	}
        	}
		},
		exporting: {
			enabled: false
		},
		series: [{
			type: 'pie',
			data: []
		}]	    	
	});
});
</script>
JAVASCRIPT;
	}
	
	private function getLineChart($graphId, $container, $title) {
		$stacking = 'normal';
		
		$colors = 'colors: [\'#92c4d6\', \'#f23737\', \'#ffd617\', \'#ff65d5\', \'#51d75e\'],';
		if (is_array($container)) {
			$theTitle = $title;
			$yTitle = $container[0]->getYTitle();
			$valueType = $container[0]->getValueType();
			$legend = 'true';
			$values = array();
			foreach ($container as $cont) {
				$values[] = array('name' => $cont->getName(), 'data' => $cont->getData());
			}
			$values = json_encode($values);
			$fillColor = '';
			$container = current($container);
			$series = '{data: []}';
		} elseif ($container->getCounterId() == Model::TYPE_AVG_PROC_TIME || $container->getCounterId() == Model::TYPE_MOBILE_AVG_PROC_TIME) {
			$theTitle = $container->getTitle();
			$yTitle = $container->getYTitle();
			$legend = 'true';
			$valueType = $container->getValueType();
			$values = json_encode(array(array('name' => $container->getName(), 'data' => $container->getData())));
			$fillColor = '';
			$series = '{name: "Output", data: []},{name: "Database", data: []},{name: "Network", data: []},{name: "Disk", data: []},{name: "PHP", data: []}';
		} elseif ($container->getCounterId() == Model::TYPE_TREND_MOBILE_USAGE_LAYERED) {
			$theTitle = $container->getTitle();
			$yTitle = $container->getYTitle();
			$legend = 'true';
			$valueType = $container->getValueType();
			$values = json_encode(array(array('name' => $container->getName(), 'data' => $container->getData())));
			$fillColor = '';
			$series = '{name: "Desktop", data: []},{name: "Mobile", data: []}';
			$stacking = 'percent';
		} else {
			$statisticsModel = new Model();
			$multipleData = $statisticsModel->getMultipleChartData($container->getCounterId());
			
			if (! is_null($multipleData)) {
				$theTitle = $container->getTitle();
				$yTitle = $container->getYTitle();
				$legend = 'true';
				$valueType = $container->getValueType();
				$values = json_encode(array(array('name' => $container->getName(), 'data' => $container->getData())));
				$fillColor = '';
				
				$series = array();
				foreach ($multipleData['types'] as $multipleKey => $multipleRow) {
					$series[] = json_encode(array('name' => $multipleKey, 'data' => array()));
				}
				
				$series = implode(',', $series);
			} else {			
				$theTitle = $container->getTitle();
				$yTitle = $container->getYTitle();
				$legend = 'false';
				$valueType = $container->getValueType();
				$values = json_encode(array(array('name' => $container->getName(), 'data' => $container->getData())));
				$fillColor = $this->getGradientFillColor();
				$series = '{data: []}'; 
			}
		}
		
		$max = ($valueType == '%') ? 'max: 100,' : '';
		$min = ($valueType == '%' || $valueType == 'ms') ? 'min: 0,' : ''; 
		
		$pollUrl = "{$this->getView()->basePath()}/Api/statisticsGetSeries?type=";
		
		$allowDecimal = '';
		if ($container->getYAxisType() == \Statistics\Container::YAXIS_INTEGER) {
			$allowDecimal = 'allowDecimals: false,';
		} 
		
		return <<<JAVASCRIPT
<script type="text/javascript">
window.addEvent("domready", function() {

	$('{$graphId}').graph = 
	new Highcharts.Chart({
		dataUrl: '{$pollUrl}',
		counterId: {$container->getCounterId()},
		chart: {
			animation: true,
			zoomType: 'x',
			renderTo: '{$graphId}',
			defaultSeriesType: 'line',
			type: 'area',
			events:{              
				selection:function(params){
					if (typeof zendCharts === 'object') {
						if (params.resetSelection != undefined) {
							zendCharts.setInZoomMode(false);
						} else {
							zendCharts.setInZoomMode(true);
						}
					}
				}
			}
		},
		lang: {
			loading: ''
		},
		loading: {
            labelStyle: {}
        },
		credits: {
			enabled: false
		},
		title: {
			text: '{$theTitle}',
			style: {
				color: '#355968'
			}
		},
		xAxis: {
			type: 'datetime',
			dateTimeLabelFormats: {
	            minute: '%H:%M',
	            hour: '%H:%M',
	            month: '%e. %b',
	            year: '%b. %y'
	        },
	        events:{              
				afterSetExtremes:function(){
					if (typeof zendCharts === 'object') {
						// TODO: add check for no-data graphs since they change the zoom
                    	zendCharts.chartZoomChanges(this.chart.xAxis[0].min, this.chart.xAxis[0].max);
                    }
				}
			}
		},
		yAxis: {
		 	{$max}
		 	{$min}
		 	title: {
				text: ''
			},
			{$allowDecimal}
			labels: {
				formatter: function() {
					var val = this.value;
					switch ('{$valueType}') {
						case 'mb':
							val = formatFileSize(val * 1024 * 1024);
							break;
						case '%':
						case '%%':
							val = val + '%';
							break;
						case 'ms':
							val = formatMiliseconds(val);
							break;	
						default:
							val = formatSize(val);
							break;
					}
					return val;
				}
			}
		},
		legend: {
			enabled: {$legend},
		 	align: 'right',
		 	verticalAlign: 'top',
		 	layout: 'vertical',
		 	x: 0,
            y: 40,
            itemStyle: {
                paddingBottom: '10px'
            }
		},
		tooltip: {
			enabled: true,
			formatter: function() {
				var val = this.y;
				switch ('{$valueType}') {
					case 'mb':
						val = formatFileSize(val * 1024 * 1024);
						break;
					case '%':
						val = val + '%';
						break;
					case 'ms':
						val = formatMiliseconds(val);
						break;
					case '%%':
						val = this.percentage.toPrecision(3) + '%';
						break;
					default:
						val = formatSize(val);
						break;
				}
				
				// remove twice
				var currDate = removeServerTimezoneOffset(removeTimezoneOffset(new Date(this.x)));
				
				return val + ' | ' + formatDate(currDate, "%d.%m.%y %H:%M");
			}
		},
		{$colors}
		plotOptions: {
			series: {
	            animation: false,
           		shadow: false,
	            marker: {
	                enabled: false
	            }
	        },
	        area: {
	        	stacking: '{$stacking}',
	        	{$fillColor}
	            lineColor: '#688625',
	            states: {
	                hover: {
	                    enabled: false
	                }
	            }
	        }
		},
	    exporting: {
			enabled: false
		},
	    series: [{$series}]	
	}, function(chart) { 
        syncronizeCrossHairs(chart);
    });
});
</script>
JAVASCRIPT;
	}
	
	private function getGradientFillColor() {
		return "color: '#bdd980',";
		
		return <<<FILLCOLOR
fillColor: {
	linearGradient: [0, 0, 0, 300],
	stops: [
		[0, 'rgb(172, 198, 80)'],
	    [1, 'rgba(234,242,204,0)']
	]
},
FILLCOLOR;
	}
}

