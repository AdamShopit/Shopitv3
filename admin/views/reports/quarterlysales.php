<h1><?=$report_title;?></h1>

<?php 
#------------------------------------------------------
# Year selection
#------------------------------------------------------
if ($this->input->post('s_from_year') == '') { 
?>
	<form action="<?=current_url();?>" method="post" id="reportDateSelect">
		<p><strong>Please select a financial year.</strong></p>
		<p>
			<label>Year:</label>
			<select name="s_from_year">
				<?php 
				if ($s_from_year == null):
					$s_from_year = date('Y');
				endif;
				for($i = (date('Y')); $i >= date('Y')-7; $i--) {
				?>
				<option value="<?=$i;?>"<?=is_selected($i,$s_from_year);?>><?=$i-1;?> - <?=$i;?></option>
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
<?php } ?>