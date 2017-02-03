<h1><?=$report_title;?></h1>

<?php 
#------------------------------------------------------
# Date selection
#------------------------------------------------------
if ($this->input->post('s_from_month') == '') { 
?>
	<form action="<?=current_url();?>" method="post" id="reportDateSelect">
		<p><strong>Please select a month to view weekly sales.</strong></p>
		<p>
			<label>From:</label>
			<?php
				if($s_from_month == null):
					$s_from_month = date('m'); 
				endif;
			?>
			<select name="s_from_month">
				<option value="01"<?=is_selected('01',$s_from_month);?>>January</option>
				<option value="02"<?=is_selected('02',$s_from_month);?>>February</option>
				<option value="03"<?=is_selected('03',$s_from_month);?>>March</option>
				<option value="04"<?=is_selected('04',$s_from_month);?>>April</option>
				<option value="05"<?=is_selected('05',$s_from_month);?>>May</option>
				<option value="06"<?=is_selected('06',$s_from_month);?>>June</option>
				<option value="07"<?=is_selected('07',$s_from_month);?>>July</option>
				<option value="08"<?=is_selected('08',$s_from_month);?>>August</option>
				<option value="09"<?=is_selected('09',$s_from_month);?>>September</option>
				<option value="10"<?=is_selected('10',$s_from_month);?>>October</option>
				<option value="11"<?=is_selected('11',$s_from_month);?>>November</option>
				<option value="12"<?=is_selected('12',$s_from_month);?>>December</option>
			</select>
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
				<th class="left">Week</th>
				<th class="left">Week Commencing</th>
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
				<td class="left"><?=$sale->week_no;?></td>
				<td class="left"><?=nice_date($sale->week_monday, 'date');?></td>
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
				<td>&nbsp;</td>
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