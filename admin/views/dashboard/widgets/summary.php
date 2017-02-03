<?php
if ( $this->config->item('can_access_dashwidgets_sales') ) {
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="widget">
	<thead>
		<tr>
			<td colspan="4"><h2>Sales Summary</h2></td>
		</tr>
	</thead>

	<tbody>
		<tr class="odd">
			<td width="25%">Today:</td>
			<td width="25%"><strong><?=money($today->items + $today->shipping + $today->tax);?></strong></td>
			<td width="25%">Yesterday:</td>
			<td width="25%"><strong><?=money($yesterday->items + $yesterday->shipping + $yesterday->tax);?></strong></td>
		</tr>
	
		<tr class="even">
			<td>This week:</td>
			<td><strong><?=money($thisweek->items + $thisweek->shipping + $thisweek->tax);?></strong></td>
			<td>Last week:</td>
			<td><strong><?=money($lastweek->items + $lastweek->shipping + $lastweek->tax);?></strong></td>
		</tr>
	
		<tr class="odd">
			<td>This month:</td>
			<td><strong><?=money($thismonth->items + $thismonth->shipping + $thismonth->tax);?></strong></td>
			<td>Last month:</td>
			<td><strong><?=money($lastmonth->items + $lastmonth->shipping + $lastmonth->tax);?></strong></td>
		</tr>
	</tbody>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="widget">
	<thead>
		<tr>
			<td colspan="4"><h2>All time sales</h2></td>
		</tr>
	</thead>
	<tbody>
		<tr class="odd">
			<td width="25%">Orders:</td>
			<td width="25%"><strong><?=number_format($total_alltime->no_orders);?></strong></td>
			<td width="25%">Item total:</td>
			<td width="25%"><strong><?=money($total_alltime->items);?></strong></td>
		</tr>
	
		<tr class="even">
			<td>Shipping:</td>
			<td><strong><?=money($total_alltime->shipping);?></strong></td>
			<td>Tax:</td>
			<td><strong><?=money($total_alltime->tax);?></strong></td>
		</tr>
	
		<tr class="odd">
			<td>Order total:</td>
			<td colspan="3"><strong><?=money($total_alltime->items + $total_alltime->shipping + $total_alltime->tax);?></strong></td>
		</tr>
	</tbody>
</table>
<?php } ?>