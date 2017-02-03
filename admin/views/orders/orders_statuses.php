<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="13">
					<h2><?=$title;?></h2>
				</td>
			</tr>
			<tr>
				<th width="10">&nbsp;</th>
				<th>Label</th>
				<th>State</th>
				<th>Label Colour</th>
				<th>Flow</th>
				<th title="Unprocessed">Unpr</th>
				<th title="Pending">Pend</th>
				<th title="Paid">Paid</th>
				<th title="Cancelled">Canc</th>
				<th title="Dispatched">Disp</th>
				<th title="Failed">Fail</th>
				<th title="Refunded">Refu</th>
				<th title="Delete"><center>Del?</center></th>
			</tr>
		</thead>
	
		<tbody class="order-status-group">
			<?php
			if ($statuses > 0) {
			foreach($statuses as $status) {
			?>
			<tr>
				<td align="center">
					<i class="valign draggable fa fa-bars"> </i>
				</td>
				<td>
					<input type="hidden" name="id[]" value="<?=$status->id;?>" />
					<input type="text" name="label[<?=$status->id;?>]" value="<?=$status->label;?>" class="textbox" style="width:89%;" maxlength="100" />
				</td>
				<td>
					<select name="type[<?=$status->id;?>]">
						<option value="0" <?=is_selected(0, $status->type);?>>Unprocessed</option>
						<option value="1" <?=is_selected(1, $status->type);?>>Failed</option>
						<option value="2" <?=is_selected(2, $status->type);?>>Processing</option>
					</select>
				</td>
				<td style="white-space:nowrap;">
					<select id="status-color" onchange="$(this).next('.light').css('background-color',$(this).val());" name="color[<?=$status->id;?>]">
						<option value="#d7d7d7" <?=is_selected('#d7d7d7', $status->color);?>>Grey</option>
						<option value="#d8242b" <?=is_selected('#d8242b', $status->color);?>>Dark Red</option>
						<option value="#ff5454" <?=is_selected('#ff5454', $status->color);?>>Light Red</option>
						<option value="#ff7733" <?=is_selected('#ff7733', $status->color);?>>Red Orange</option>
						<option value="#f7941d" <?=is_selected('#f7941d', $status->color);?>>Orange</option>
						<option value="#83bf42" <?=is_selected('#83bf42', $status->color);?>>Light Green</option>
						<option value="#4a8646" <?=is_selected('#4a8646', $status->color);?>>Dark Green</option>
						<option value="#7fbfbf" <?=is_selected('#7fbfbf', $status->color);?>>Green Blue</option>
						<option value="#448ccb" <?=is_selected('#448ccb', $status->color);?>>Blue</option>
						<option value="#6dcff6" <?=is_selected('#6dcff6', $status->color);?>>Light Blue</option>
						<option value="#a864a8" <?=is_selected('#a864a8', $status->color);?>>Purple</option>
						<option value="#998675" <?=is_selected('#998675', $status->color);?>>Brown</option>
						<option value="#f7d737" <?=is_selected('#f7d737', $status->color);?>>Yellow</option>
						<option value="#dbd00c" <?=is_selected('#dbd00c', $status->color);?>>Mustard</option>
						<option value="#000000" <?=is_selected('#000000', $status->color);?>>Black</option>
					</select>
					<span class="light" style="background-color:<?=$status->color;?>;"></span>
				</td>
				<td align="center">
					<input type="checkbox" name="flow[<?=$status->id;?>]" value="1" <?=is_checked(1, $status->flow);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_unprocessed" title="Unprocessed" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_unprocessed);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_pending" title="Pending" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_pending);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_completed" title="Completed" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_completed);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_cancelled" title="Cancelled" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_cancelled);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_dispatched" title="Dispatched" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_dispatched);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_failed" title="Failed" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_failed);?> />
				</td>
				<td align="center">
					<input type="radio" name="flag_refunded" title="Refunded" value="<?=$status->id;?>" <?=is_checked('1', $status->flag_refunded);?> />
				</td>
				<td align="center">
					<?php if ($status->locked == 0) { ?>
					<input type="checkbox" name="delete[]" value="<?=$status->id;?>" />
					<?php } else { ?>
					<img src="<?=template_directory('assets/images/icon-archived.png');?>" alt="Locked" />
					<?php } ?>
				</td>
			</tr>
			<?php 
			}
			}
			?>
		</tbody>
		
		<!-- Add new status -->
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input type="text" name="new_status_label" value="" class="textbox" style="width:89%;" placeholder="Add another status" maxlength="100" />
				</td>
				<td>
					<select name="new_status_type">
						<option value="0">Unprocessed</option>
						<option value="1">Failed</option>
						<option value="2">Processing</option>
					</select>
				</td>
				<td colspan="6">
					<select id="new-status-color" onchange="$(this).next('.light').css('background-color',$(this).val());" name="new_status_color">
						<option value="#d7d7d7">Grey</option>
						<option value="#d8242b">Dark Red</option>
						<option value="#ff5454">Light Red</option>
						<option value="#ff7733">Red Orange</option>
						<option value="#f7941d">Orange</option>
						<option value="#83bf42">Light Green</option>
						<option value="#4a8646">Dark Green</option>
						<option value="#7fbfbf">Green Blue</option>
						<option value="#448ccb">Blue</option>
						<option value="#6dcff6">Light Blue</option>
						<option value="#a864a8">Purple</option>
						<option value="#998675">Brown</option>
						<option value="#f7d737">Yellow</option>
						<option value="#dbd00c">Mustard</option>
						<option value="#000000">Black</option>
					</select>
					<span class="light" style="background-color:#d7d7d7;"></span>
				</td>
			</tr>
		</tfoot>
	
	</table>

</div>

<div id="sidebar">
	<h3>Order Statuses</h3>
	
	<p>This feature enables you to create your own customised order statuses.</p>
	
	<p>Statuses can be dragged to your preferred order.</p>
	
	<p><strong>Definitions</strong></p>
	
	<ul>
		<li>Label - <span class="smallprint">A title for the status</span></li>
		<li>State - <span class="smallprint">The type of status</span></li>
		<li>Label Colour - <span class="smallprint">Pick a status colour</span></li>
		<li>Flow - <span class="smallprint">Display as a stage in order process</span></li>
		<li>Unpr - <span class="smallprint">Default for unprocessed orders</span></li>
		<li>Pend - <span class="smallprint">Default for pending payments</span></li>
		<li>Paid - <span class="smallprint">Default for paid orders</span></li>
		<li>Canc - <span class="smallprint">Default for cancelled orders</span></li>
		<li>Disp - <span class="smallprint">Default for dispatched orders</span></li>
		<li>Fail - <span class="smallprint">Default for failed orders</span></li>
		<li>Refu - <span class="smallprint">Default for refunds</span></li>
		<li>Del? - <span class="smallprint">Tick to delete the status</span></li>
	</ul>
	
</div>