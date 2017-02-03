<h1><?=$report_title;?></h1>

<?php 
#------------------------------------------------------
# Report results
#------------------------------------------------------
?>
	
<?php if ($sales > 0) { ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<th class="left">Year</th>
			<th>Orders</th>
			<th>Item Total</th>
			<th>Shipping</th>
			<th>Tax</th>
			<th>Order Total</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($sales as $sale): ?>
		<tr>
			<td class="left"><?=$sale->year_no;?></td>
			<td><?=$sale->no_orders;?></td>
			<td><?=money($sale->order_total);?></td>
			<td><?=money($sale->order_shipping);?></td>
			<td><?=money($sale->order_vat);?></td>
			<td><?=money($sale->total);?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
	<tfoot>
		<tr>
			<td class="left">Totals:</td>
			<td><?=$totals->no_orders;?></td>
			<td><?=money($totals->items);?></td>
			<td><?=money($totals->shipping);?></td>
			<td><?=money($totals->tax);?></td>
			<td><?=money($totals->items + $totals->shipping + $totals->tax);?></td>
		</tr>
	</tfoot>
</table>
<?php } else { ?>
	<p>There are no orders.</p>
<?php } ?>