<?php
if ( $this->config->item('can_access_dashwidgets_orders') ) {
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="widget">
	<thead>
		<tr>
			<td colspan="5"><h2>Undispatched Orders</h2></td>
		</tr>
	<?php if ($undispatchedOrders[0] != ''): ?>
		<tr>
			<th>Customer</th>
			<th width="18"><center>Status</center></th>
			<th>Order Date</th>
			<th>Amount</th>
		</tr>
	<?php endif; ?>
	</thead>
	
	<tbody>
	<?php 
	$i = 0;
	if ($undispatchedOrders[0] != ''):
	foreach($undispatchedOrders as $order):

		$i++; //(A)
		
		if ($i&1) { $post = 'odd'; } 
		else { $post = 'even'; } //(A);
	
		$is_today = is_today($order->order_date);
		if ($is_today == true) {
			$highlight_today_class = 'highlight-today';
			$order_date = is_today($order->order_date,true);
		} else {
			$highlight_today_class = '';
			$order_date = nice_date($order->order_date);
		}
	
	?>
		
		<tr class="<?=$post;?> <?=$highlight_today_class;?>">
			<td><a href="<?=site_url('orders/view/'.$order->order_id);?>"><?=capitalise($order->billing_firstname);?> <?=capitalise($order->billing_surname);?></a></td>
			<td align="center"><?=order_status($order->order_status);?></td>
			<td><?=$order_date;?></td>
			<td><?=money($order->total);?></td>
		</tr>
	
	<?php 
	endforeach;
	else: 
	?>
	
		<tr>
			<td colspan="5"><center>You have no orders waiting to be dispatched.</center></td>
		</tr>
	
	</tbody>
<?php endif; ?>
</table>
<?php } ?>