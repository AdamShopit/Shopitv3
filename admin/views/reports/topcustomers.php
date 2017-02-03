<h1><?=$report_title;?></h1>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<th class="left" width="5%">#</th>
			<th class="left" width="30%">Customer</th>
			<th class="left" width="30%">Location</th>
			<th class="left" width="10%">Orders</th>
			<th width="25%">Last Order</th>
		</tr>
	</thead>
	<tbody>
	<?php
	if ($customers > 0) {
		$i = 1;
		foreach($customers as $item): 
	?>
		<tr>
			<td class="left"><?=$i++;?></td>
			<td class="left"><?=$item->billing_name;?></td>
			<td class="left"><?=$item->billing_city;?></td>
			<td class="left"><?=$item->orders;?></td>
			<td><?=nice_date($item->last_order);?></td>
		</tr>
	<?php 
		endforeach; 
	} else {
	?>
	<tr>
		<td colspan="4"><center>No stats are available just yet, but don't worry, give it a little more time.</center></td>
	</tr>
	<?php } ?>
	</tbody>
</table>