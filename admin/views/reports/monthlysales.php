<h1><?=$report_title;?></h1>

<?php 
#------------------------------------------------------
# Date selection
#------------------------------------------------------
if ($this->input->post('s_from_year') == '') { 
?>
	<form action="<?=current_url();?>" method="post" id="reportDateSelect">
		<p><strong>Please select a year to view monthly sales.</strong></p>
		<p>
			<label>Year:</label>
			<select name="s_from_year">
				<?php 
				if ($s_from_year == null):
					$s_from_year = date('Y');
				endif;
				for($i = (date('Y')); $i >= date('Y')-5; $i--) { 
				?>
				<option value="<?=$i;?>"<?=is_selected($i,$s_from_year);?>><?=$i;?></option>
				<?php } ?>
			</select>
		</p>
		
		<p>
			<label>&nbsp;</label>
			<input type="submit" name="submit" value="Run report" class="button" />
		</p>

	</form>
<?php 
} else { 
#------------------------------------------------------
# Report results
#------------------------------------------------------
?>

	<p>From <?=nice_date($from, 'date');?> to <?=nice_date($to, 'date');?></p>
	
	<?php if ($sales > 0) { ?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<th class="left">Month</th>
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
				<td class="left"><?=$sale->month_name;?></td>
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
				<td align="right">Totals:</td>
				<td><?=$totals->no_orders;?></td>
				<td><?=money($totals->items);?></td>
				<td><?=money($totals->shipping);?></td>
				<td><?=money($totals->tax);?></td>
				<td><?=money($totals->items + $totals->shipping + $totals->tax);?></td>
			</tr>
		</tfoot>
	</table>
	<?php } else { ?>
		<p>There were no orders for the selected month.</p>
	<?php } ?>

<?php } ?>