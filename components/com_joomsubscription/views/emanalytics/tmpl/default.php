<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');
?>

<?php echo $this->menu->render(NULL); ?>

<div class="page-header">
	<h1>
        <img src="<?php echo JUri::root(TRUE); ?>/components/com_joomsubscription/images/cpanel/analytics.png" />
        <?php echo JText::_('E_ANALYTICS'); ?>
    </h1>
</div>

<div style="height: 250px" id="chart-sales">
	<div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"><?php echo JText::_('ELOADINGCHART'); ?></div>
	</div>
</div>

<div style="height: 200px" id="chart-counts">
	<div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"><?php echo JText::_('ELOADINGCHART'); ?></div>
	</div>
</div>

<div style="height: 250px" id="chart-sales-plans">
	<div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"><?php echo JText::_('ELOADINGCHART'); ?></div>
	</div>
</div>
<!--
<div style="height: 250px" id="chart-count-plans">
	<div class="progress progress-striped active">
		<div class="bar" style="width: 100%;"><?php echo JText::_('ELOADINGCHART'); ?></div>
	</div>
</div>
-->
<hr>
<div style="height: 550px" id="chart-pie-sales">
	<div class="progress progress-striped active">
		<div class="bar"
			 style="width: 100%;"><?php echo JText::_('ELOADINGCHART'); ?></div>
	</div>
</div>
<hr>
<div style="height: 550px" id="chart-pie-counts">
	<div class="progress progress-striped active">
		<div class="bar"
			 style="width: 100%;"><?php echo JText::_('ELOADINGCHART'); ?></div>
	</div>
</div>

<script type="text/javascript">
(function($) {
	/*Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
	 return {
	 radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
	 stops:          [
	 [0, color],
	 [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
	 ]
	 };
	 });*/

	$.getJSON('<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emcharts.stack'); ?>', function(data) {

		if(!data.success || !data.result) {
			$('#chart-sales-plans').html(data.error);
			return;
		}

		var series = [];
		$.each(data.result, function(k, v) {
			var line = [];
			$.each(v.list, function(k2, v2) {
				line.push(parseFloat(v2));
			});
			//console.log(line);
			series.push({
				name:          v.name,
				yAxis:         0,
				pointInterval: 24 * 3600 * 1000,
				pointStart:    new Date().getTime() - (24 * 3600 * 4 * 1000),
				data:          line
			});
		})

		$('#chart-sales-plans').highcharts({
				chart: {
					type: 'spline'
				},
				title: {
					text: '<?php echo JText::_('ETITLASTBYPLAN'); ?>'
				},
				xAxis: {
					type:         'datetime',
					title:        {
						text: null
					},
					tickPosition: 'inside',
					offset:       10
				},
				yAxis: {
					min:           0,
					title:         {
						text: '<?php echo JText::_('EA_SALESTOT'); ?>'
					},
					stackLabels:   {
						enabled: true,
						style:   {
							fontWeight: 'bold'
						}
					},
					gridLineColor: '#EEEEEE',
					labels:        {formatter: function() {
						return this.value.toFixed(2) + '$';
					}
				}
			},
			tooltip
		:
		{
			formatter: function() {
				return this.series.name + ': ' + this.y.toFixed(2) + '$';
			}
		}
		,
		plotOptions: {
			column: {
				stacking:   'normal',
					dataLabels
			:
				{
					enabled: true,
						color
				:
					(Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
				}
			}
		}
		,
		series:      series
	});
});


$.getJSON('<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emcharts.sales'); ?>', function(data) {

	if(!data.success || !data.result) {
		$('#chart-sales').html(data.error);
		return;
	}

	options_main.series[0].data = [];
	$.each(data.result, function(k, v) {
		options_main.series[0].data.push(parseFloat(v))
	});

	options_main.yAxis[0].labels.formatter = function() {
		return this.value.toFixed(2) + '$';
	}

	options_main.yAxis[0].tickInterval = null;
	options_main.title.text = '<?php echo htmlspecialchars(JText::_('ESALESPROGRESS'), ENT_QUOTES, 'UTF-8'); ?>';
	options_main.yAxis[0].title.text = '<?php echo JText::_('EA_SALESTOT'); ?>';
	options_main.series[0].name = '<?php echo JText::_('EA_SALESTOT'); ?>';

	$('#chart-sales').highcharts(options_main);
});

$.getJSON('<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emcharts.counts'); ?>', function(data) {

	if(!data.success || !data.result) {
		$('#chart-counts').html(data.error);
		return;
	}

	options_main.series[0].data = [];
	$.each(data.result, function(k, v) {
		options_main.series[0].data.push(parseFloat(v))
	});

	options_main.yAxis[0].labels.formatter = function() {
		return this.value;
	}
	options_main.yAxis[0].tickInterval = 1;
	options_main.title.text = '<?php echo htmlspecialchars(JText::_('ESALESPCOUNTSS'), ENT_QUOTES, 'UTF-8'); ?>';
	options_main.yAxis[0].title.text = '<?php echo JText::_('EA_SALESCOUNT'); ?>';
	options_main.series[0].name = '<?php echo JText::_('EA_SALESCOUNT'); ?>';

	$('#chart-counts').highcharts(options_main);
});

options_pie.series[0].name = '<?php echo JText::_('COM_JOOMSUBSCRIPTION_GROUPS'); ?>';
options_pie.series[1].name = '<?php echo JText::_('E_PLAN'); ?>';

$.getJSON('<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emcharts.piemain'); ?>', function(data) {
	if(!data.success || !data.result) {
		$('#chart-pie-sales').html(data.error);
		return;
	}

	var colors = Highcharts.getOptions().colors;
	var groupsData = [];
	var plansData = [];
	var i = 0;

	$.each(data.result.groups.list, function(k, v) {
		groupsData.push({
			name:  v.name,
			y:     v.percent_sales,
			color: colors[i],
			count: v.sum
		});

		var j = 0;
		$.each(v.plans, function(k2, v2) {
			var brightness = -0.1 - (v2.sum / v.sum) / 5;
			plansData.push({
				name:  v2.name,
				y:     v2.percent_sales,
				color: Highcharts.Color(colors[i]).brighten(brightness).get(),
				count: v2.sum
			});
		});

		i++;
	});

	options_pie.title.text = '<?php echo JText::_('EPIETITLE'); ?>';
	options_pie.tooltip.formatter = function() {
		return '<b>' + this.series.name + '</b>: ' + this.point.name + '<br><?php echo JText::_('ESUBSCRIPTIONS'); ?>: <b>' + this.y.toFixed(2) +
			'%</b> (' + this.point.count.toFixed(2) + '$)';
	}
	options_pie.series[0].data = groupsData;
	options_pie.series[1].data = plansData;

	$('#chart-pie-sales').highcharts(options_pie);
});

$.getJSON('<?php echo JRoute::_('index.php?option=com_joomsubscription&task=emcharts.piemain'); ?>', function(data) {
	if(!data.success || !data.result) {
		$('#chart-pie-counts').html(data.error);
		return;
	}

	var colors = Highcharts.getOptions().colors;
	var groupsData = [];
	var plansData = [];
	var i = 0;

	$.each(data.result.groups.list, function(k, v) {
		groupsData.push({
			name:  v.name,
			y:     v.percent_count,
			color: colors[i],
			count: v.count
		});

		var j = 0;
		$.each(v.plans, function(k2, v2) {
			var brightness = -0.1 - (v2.count / v.count) / 5;
			plansData.push({
				name:  v2.name,
				y:     v2.percent_count,
				color: Highcharts.Color(colors[i]).brighten(brightness).get(),
				count: v2.count
			});
		});

		i++;
	});

	options_pie.title.text = '<?php echo JText::_('EPIETITLECOUNT'); ?>';
	options_pie.tooltip.formatter = function() {
		return '<b>' + this.series.name + '</b>: ' + this.point.name + '<br><b>' + this.y.toFixed(2) +
			'%</b> (<?php echo JText::_('EMR_INVOICETOTAL'); ?>: ' + this.point.count + ')';
	}
	options_pie.series[0].data = groupsData;
	options_pie.series[1].data = plansData;

	$('#chart-pie-counts').highcharts(options_pie);
});
}
(jQuery)
)
</script>