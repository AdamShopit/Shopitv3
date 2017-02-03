<h1><?=$report_title;?></h1>

<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="left" width="25%">&nbsp;</th>
			<th width="15%">Orders</th>
			<th width="15%">Item Total</th>
			<th width="15%">Shipping</th>
			<th width="15%">Tax</th>
			<th width="15%">Order Total</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="left">Today</td>
			<td><?=number_format($today->no_orders);?></td>
			<td><?=money($today->items);?></td>
			<td><?=money($today->shipping);?></td>
			<td><?=money($today->tax);?></td>
			<td><?=money($today->items + $today->shipping + $today->tax);?></td>
		</tr>
		<tr>
			<td class="left">Yesterday</td>
			<td><?=number_format($yesterday->no_orders);?></td>
			<td><?=money($yesterday->items);?></td>
			<td><?=money($yesterday->shipping);?></td>
			<td><?=money($yesterday->tax);?></td>
			<td><?=money($yesterday->items + $yesterday->shipping + $yesterday->tax);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="left">This week</td>
			<td><?=number_format($thisweek->no_orders);?></td>
			<td><?=money($thisweek->items);?></td>
			<td><?=money($thisweek->shipping);?></td>
			<td><?=money($thisweek->tax);?></td>
			<td><?=money($thisweek->items + $thisweek->shipping + $thisweek->tax);?></td>
		</tr>
		<tr>
			<td class="left">Last week</td>
			<td><?=number_format($lastweek->no_orders);?></td>
			<td><?=money($lastweek->items);?></td>
			<td><?=money($lastweek->shipping);?></td>
			<td><?=money($lastweek->tax);?></td>
			<td><?=money($lastweek->items + $lastweek->shipping + $lastweek->tax);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="left">This month</td>
			<td><?=number_format($thismonth->no_orders);?></td>
			<td><?=money($thismonth->items);?></td>
			<td><?=money($thismonth->shipping);?></td>
			<td><?=money($thismonth->tax);?></td>
			<td><?=money($thismonth->items + $thismonth->shipping + $thismonth->tax);?></td>
		</tr>
		<tr>
			<td class="left">Last month</td>
			<td><?=number_format($lastmonth->no_orders);?></td>
			<td><?=money($lastmonth->items);?></td>
			<td><?=money($lastmonth->shipping);?></td>
			<td><?=money($lastmonth->tax);?></td>
			<td><?=money($lastmonth->items + $lastmonth->shipping + $lastmonth->tax);?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>

<h1>Total Revenues</h1>

<p>From <?=nice_date($thisyear_from, 'date');?> to <?=nice_date($thisyear_to, 'date');?></p>

<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="left" width="25%">&nbsp;</th>
			<th width="15%">Orders</th>
			<th width="15%">Item Total</th>
			<th width="15%">Shipping</th>
			<th width="15%">Tax</th>
			<th width="15%">Order Total</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="left">Q1</td>
			<td><?=number_format($total_q1->no_orders);?></td>
			<td><?=money($total_q1->items);?></td>
			<td><?=money($total_q1->shipping);?></td>
			<td><?=money($total_q1->tax);?></td>
			<td><?=money($total_q1->items + $total_q1->shipping + $total_q1->tax);?></td>
		</tr>
		<tr>
			<td class="left">Q2</td>
			<td><?=number_format($total_q2->no_orders);?></td>
			<td><?=money($total_q2->items);?></td>
			<td><?=money($total_q2->shipping);?></td>
			<td><?=money($total_q2->tax);?></td>
			<td><?=money($total_q2->items + $total_q2->shipping + $total_q2->tax);?></td>
		</tr>
		<tr>
			<td class="left">Q3</td>
			<td><?=number_format($total_q3->no_orders);?></td>
			<td><?=money($total_q3->items);?></td>
			<td><?=money($total_q3->shipping);?></td>
			<td><?=money($total_q3->tax);?></td>
			<td><?=money($total_q3->items + $total_q3->shipping + $total_q3->tax);?></td>
		</tr>
		<tr>
			<td class="left">Q4</td>
			<td><?=number_format($total_q4->no_orders);?></td>
			<td><?=money($total_q4->items);?></td>
			<td><?=money($total_q4->shipping);?></td>
			<td><?=money($total_q4->tax);?></td>
			<td><?=money($total_q4->items + $total_q4->shipping + $total_q4->tax);?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td class="left">Total</td>
			<td><?=number_format($total_thisyear->no_orders);?></td>
			<td><?=money($total_thisyear->items);?></td>
			<td><?=money($total_thisyear->shipping);?></td>
			<td><?=money($total_thisyear->tax);?></td>
			<td><?=money($total_thisyear->items + $total_thisyear->shipping + $total_thisyear->tax);?></td>
		</tr>
	</tfoot>
</table>

<p>&nbsp;</p>

<h1>All Time Sales</h1>

<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="left" width="25%">&nbsp;</th>
			<th width="15%">Orders</th>
			<th width="15%">Item Total</th>
			<th width="15%">Shipping</th>
			<th width="15%">Tax</th>
			<th width="15%">Order Total</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="left">All time</td>
			<td><?=number_format($total_alltime->no_orders);?></td>
			<td><?=money($total_alltime->items);?></td>
			<td><?=money($total_alltime->shipping);?></td>
			<td><?=money($total_alltime->tax);?></td>
			<td><?=money($total_alltime->items + $total_alltime->shipping + $total_alltime->tax);?></td>
		</tr>
	</tbody>
</table>
