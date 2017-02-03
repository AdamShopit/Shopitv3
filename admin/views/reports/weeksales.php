<h1><?=$report_title;?></h1>

<p>From <?=nice_date($from, 'date');?> to <?=nice_date($to, 'date');?></p>

<?php if ($sales > 0) { ?>
<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th class="left">Order Date</th>
			<th class="left">Order No</th>
			<th>Item Total</th>
			<th>Shipping</th>
			<th>Tax</th>
			<th>Order Total</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($sales as $sale): ?>
		<tr>
			<td class="left"><?=nice_date($sale->order_date);?></td>
			<td class="left"><?=$sale->order_ref;?></td>
			<td><?=money($sale->order_total);?></td>
			<td><?=money($sale->order_shipping);?></td>
			<td><?=money($sale->order_vat);?></td>
			<td><?=money($sale->total);?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
	<tfoot>
		<tr>
			<td>&nbsp;</td>
			<td align="right">Totals:</td>
			<td><?=money($totals->items);?></td>
			<td><?=money($totals->shipping);?></td>
			<td><?=money($totals->tax);?></td>
			<td><?=money($totals->items + $totals->shipping + $totals->tax);?></td>
		</tr>
	</tfoot>
</table>
<?php } else { ?>
	<p>You have no orders this week.</p>
<?php } ?>