<?php
if ( $this->config->item('can_access_dashwidgets_sales') ) {
?>
<script type="text/javascript" src="<?=template_directory('assets/scripts/chart.min.js');?>"></script>

<div id="chart_container" class="table" style="padding-bottom:0px;">
	<h2><?=date('Y');?> Sales</h2>
	<div id="chart_legend">
		<label class="badge badge-green"><?=date('Y');?></label>
		<label class="badge badge-grey"><?=date('Y')-1;?></label>
	</div>
	<canvas id="Chart" width="930" height="175"></canvas>

	<table cellpadding="0" cellspacing="0" border="0" style="width:100%;margin:10px 0 0 0;">
		<tbody>
			<tr class="subheadings">
				<td width="33%"><strong>Products sold</strong> this month</td>
				<td width="33%"><strong>Orders</strong> this month</td>
				<td width="33%"><strong>Revenue</strong> this month (Exc VAT)</td>
			</tr>
			<tr>
				<td align="" class="stat-number-medium">
					<?=number_format($total_thismonth->products);?>
					<br/>
					<?php
					$percent_products_color = ($total_thismonth->products >= $total_lastmonth->products) ? 'green' : 'lred';
					?>
					<span class="stat-note valign"><span class="badge badge-<?=$percent_products_color;?> percent valign"><?=percentify_diff($total_thismonth->products, $total_lastmonth->products);?></span> from previous month of <?=$total_lastmonth->products;?></span>
				</td>
				<td align="" class="stat-number-medium">
					<?=$total_thismonth->no_orders;?>
					<br/>
					<?php
					$percent_orders_color = ($total_thismonth->no_orders >= $total_lastmonth->no_orders) ? 'green' : 'lred';
					?>
					<span class="stat-note valign"><span class="badge badge-<?=$percent_orders_color;?> percent valign"><?=percentify_diff($total_thismonth->no_orders, $total_lastmonth->no_orders);?></span> from previous month of <?=$total_lastmonth->no_orders;?></span>
				</td>
				<td align="" class="stat-number-medium">
					<?=money($thismonth->items + $thismonth->shipping);?>
					<br/>
					<?php
					$percent_revenue_thismonth = $thismonth->items + $thismonth->shipping;
					$percent_revenue_lastmonth = $lastmonth->items + $lastmonth->shipping;					
					$percent_revenue_color = ($percent_revenue_thismonth >= $percent_revenue_lastmonth) ? 'green' : 'lred';
					?>
					<span class="stat-note valign"><span class="badge badge-<?=$percent_revenue_color;?> percent valign"><?=percentify_diff($percent_revenue_thismonth, $percent_revenue_lastmonth);?></span> from previous month of <?=money($percent_revenue_lastmonth);?></span>
				</td>
			</tr>
		</tbody>
	</table>

</div>

<script type="text/javascript">
var myData = {
	labels : ["JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC"],
	datasets : [
		//Last year
		{
			fillColor : "rgba(220,220,220,0.5)",
			strokeColor : "rgba(220,220,220,0.75)",
			pointColor : "rgba(220,220,220,1)",
			pointStrokeColor : "#fff",
			data : [<?=$chart_last_year;?>]
		},
		//This year
		{
			fillColor : "rgba(196,223,115,0.5)",
			strokeColor : "rgba(196,223,115,0.75)",
			pointColor : "rgba(196,223,115,1)",
			pointStrokeColor : "#fff",
			data : [<?=$chart_this_year;?>]
		}
	]
}

var options = {
				
	//Boolean - If we show the scale above the chart data			
	scaleOverlay : false,
	
	//Boolean - If we want to override with a hard coded scale
	scaleOverride : false,
	
	//** Required if scaleOverride is true **
	//Number - The number of steps in a hard coded scale
	scaleSteps : null,
	//Number - The value jump in the hard coded scale
	scaleStepWidth : null,
	//Number - The scale starting value
	scaleStartValue : null,

	//String - Colour of the scale line	
	scaleLineColor : "#f3f0eb",
	
	//Number - Pixel width of the scale line	
	scaleLineWidth : 1,

	//Boolean - Whether to show labels on the scale	
	scaleShowLabels : true,
	
	//Interpolated JS string - can access value
	scaleLabel : "<%=value%>",
	
	//String - Scale label font declaration for the scale label
	scaleFontFamily : "'Helvetica Neue'",
	
	//Number - Scale label font size in pixels	
	scaleFontSize : 11,
	
	//String - Scale label font weight style	
	scaleFontStyle : "600",
	
	//String - Scale label font colour	
	scaleFontColor : "#555",
	
	///Boolean - Whether grid lines are shown across the chart
	scaleShowGridLines : true,
	
	//String - Colour of the grid lines
	scaleGridLineColor : "#f3f0eb",
	
	//Number - Width of the grid lines
	scaleGridLineWidth : 1,	

	//Boolean - If there is a stroke on each bar	
	barShowStroke : false,
	
	//Number - Pixel width of the bar stroke	
	barStrokeWidth : 2,
	
	//Number - Spacing between each of the X value sets
	barValueSpacing : 5,
	
	//Number - Spacing between data sets within X values
	barDatasetSpacing : 1,
	
	//Boolean - Whether to animate the chart
	animation : true,

	//Number - Number of animation steps
	animationSteps : 60,
	
	//String - Animation easing effect
	animationEasing : "easeOutQuart",

	//Function - Fires when the animation is complete
	onAnimationComplete : null
}

//Get the context of the canvas element we want to select
var ctx = document.getElementById("Chart").getContext("2d");
new Chart(ctx).Line(myData,options);
</script>
<?php } ?>