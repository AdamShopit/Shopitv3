<div id="content">
	<form name="formCheck" id="formCheck" method="post" action="<?=site_url('orders/process');?>">
	
	<table cellpadding="0" cellspacing="0" border="0" class="sticky">
		<thead>
			<tr>
				<td colspan="9">
					<h2>
					<?=$title;?>
					<?php if ($todaysOrderCount > 0) { ?><a href="<?=site_url('orders/printall');?>" target="_blank">Print today's orders</a><?php } ?>
					</h2>
				</td>
			</tr>
			<tr>
				<th width="20%">Order No</th>
				<th width="20">Site</th>
				<th width="15%"><center>Status<?=table_sort('/orders/all','order_status');?></center></th>
				<th width="10%"><center>Date<?=table_sort('/orders/all','order_date',true,'asc');?></center></th>
				<th width="40%">Customer</th>
				<th width="15%">Total<?=table_sort('/orders/all','total');?></th>
				<th width="10"><center><img src="<?=template_directory('assets/images/icon-print.png');?>" alt="Printed" title="Printed" /></center></th>
				<th width="1">&nbsp;</th>
				<th width="1"><center><input type="checkbox" name="checkall" value="yes" /></th>
			</tr>
		</thead>
	
		<tbody>
		<?php
		$i = 0; //(A) used for background colour 

		if ($orders > 0) {
		
		foreach($orders as $item): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);

			$is_today = is_today($item->order_date);
			if ($is_today == true) {
				$highlight_today_class = 'highlight-today';
			} else {
				$highlight_today_class = '';
			}

			$order_date = nice_date($item->order_date, 'time');

			//List refunds
			$refunds = $this->orders_model->getRefunds($item->order_ref);
			$count_refunds = count($refunds);
			
			//Create a class to highlight the refund's parent order
			$refunds_class = ($count_refunds > 0) ? "even" : "";
			
			//Order Reference
			$order_ref = ($item->order_ref == '' || $item->order_status == '') ? "" : $item->order_ref;
			
			//Unprocessed CSS class
			if ($item->order_ref == '' || $item->order_status == '') {
				$unprocessed_class = ' unprocessed';
				$unprocessed_flag = TRUE;
				$highlight_today_class = '';
			} else {
				$unprocessed_class = '';
				$unprocessed_flag = FALSE;
			}
			
			// Check the date of this order with the previous. If it's not the same
			// display a sub-heading
			if (date('Y-m-d', strtotime($item->order_date)) != $this_date) {
				echo '<tr class="subheadings">';
				if ($is_today) {
					$date_heading = 'Today &mdash; ';
				} else {
					$date_heading = '';
				}
				echo sprintf('<td colspan="9">%s%s</td>', $date_heading, date('l, j F Y', strtotime($item->order_date)));
				echo '</tr>';
			}
		?>
		
		<tr class="<?=$refunds_class;?> <?=$highlight_today_class;?><?=$unprocessed_class;?>" id="<?=$item->order_id;?>">
			<td style="white-space:nowrap;">
				<a href="<?=site_url('orders/view/'.$item->order_id . $redirect);?>"><?=$order_ref;?></a>
			</td>
			<td align="center"><?=site_icon($item->site);?></td>
			<td align="center"><?=order_status($item->order_status);?></td>
			<td align="center"><?=$order_date;?></td>
			<td>
				<?=$item->billing_title;?> <?=capitalise($item->billing_firstname);?> <?=capitalise($item->billing_surname);?>
				<?php if ($item->orders > 1){ echo '<span class="flag" title="Repeat customer">R</span>'; } ?>
			</td>
			<td><?=$this->config->item('currency');?><?=number_format($item->total,2);?></td>
			<td align="center">
				<?php
				// Display "printed" icon
				if ($item->printed == 1) {
					echo sprintf('<img src="%s" alt="Printed" title="Printed" />', template_directory('assets/images/icon-print.png'));
				} else {
					echo "&nbsp;";
				}
				?>
			</td>
			<td>
				<ul class="actions">
					<li><a href="#" class="btn-action"><img src="<?=template_directory("assets/images/btn-action-arrow-down.png");?>" alt=""/></a>
						<ul>
							<li><a href="<?=site_url('orders/view/'.$item->order_id . $redirect);?>">View order</a></li>
							<li><a href="<?=site_url('orders/edit/'.$item->order_id . $redirect);?>">Edit order</a></li>
							<?php if ($item->order_status != 'Dispatched' && empty($item->dispatch_date) && !$unprocessed_flag) {?>
							<li class="markasdispatched"><a href="<?=site_url("orders/markasdispatched/$item->order_id");?>">Mark as dispatched</a></li>
							<?php } ?>
							<?php
							echo '<li class="nav-separator">Print</li>';
							// Default the site to 'website', if this is 'mobile' (as it won't have it's own channel)
							$site = ($item->site == 'mobile') ? 'website' : $item->site;
							// Get list of available templates
							if (!empty($templates[$site])) {
								foreach($templates[$site] as $template) {
									$template = (object)$template;
									echo sprintf('<li><a href="%s/%d%s">%s</a></li>', site_url("orders/printnote/$item->order_id"), $template->id, $redirect, $template->title);
								}
							}
							echo sprintf('<li><a href="%s">Create new template</a></li>', site_url('orders/templates'));
							?>
						</ul>
					</li>
				</ul>
			</td>
			<td>
				<?php
				// Re-tick the checkbox if it exists in our flashdata
				$checkall_checked = ($this->session->flashdata('checked_orderids') !== FALSE and in_array($item->order_id, $this->session->flashdata('checked_orderids'))) ? 'checked="checked"' : '';
				?>
				<input type="checkbox" name="order_id[]" class="checkall" value="<?=$item->order_id;?>" <?=$checkall_checked;?> />
			</td>
		</tr>
		<?php
		$r = 0;
		if ($refunds):

		//!Display the refunds
		foreach($refunds as $refund) {
		
			$r++;
			$array_product_name = array();
			
			//Add a "last" class
			$last_subrow_class = ($count_refunds == $r) ? 'last-subrow' : "";

			// Organise the product names so they display better
			$pieces = explode(';', trim($refund->inventory));

			foreach ($pieces as $refund_item) {

				//Create an array of products if product_name exists
				if (!empty($refund_item)) {
					$array_product_name[] = "&mdash; $refund_item";
				}
			
			}

			//Create a list of products e.g. item 1, item 2, item 3, etc.
			$product_names = implode('<br/>', $array_product_name);
		?>
		<tr class="highlight-refund" id="<?=$refund->refund_id;?>">
			<td valign="top" colspan="2">
				<img src="<?=template_directory('assets/images/refund-marker2.png');?>" alt="" />
				<a href="<?=site_url("orders/editrefund/$refund->refund_id$redirect");?>" class="valign"><?=nice_date($refund->refund_date, 'date');?></a>
			</td>
			<td valign="top" align="center"><?=order_status($refund->order_status);?></td>
			<td valign="top" colspan="2" class="redtext"><?=$product_names;?></td>
			<td valign="top" class="redtext">(<?=money($refund->total);?>)</td>
			<td>&nbsp;</td>
			<td valign="top" align="center"><a href="<?=site_url("orders/deleterefund/$refund->refund_id");?>" class="ajaxdelete valign" rel="Are you sure you want to delete this refund?"><img src="<?=template_directory('assets/images/icon-cross.png');?>" alt="Delete" title="Delete" /></a></td>
			<td>&nbsp;</td>
		</tr>
		<?php
		}
		endif;
		?>
		<?php 
			
			// Record the date of this order so we can compare 
			// with the next in loop
			$this_date = date('Y-m-d', strtotime($item->order_date));
			
		endforeach; 
		} else { 
		?>
		<tr>
			<td colspan="9" align="center">No orders could be found.</td>
		</tr>
		<?php } ?>
		</tbody>
	
		<tfoot>
			<tr>
				<td colspan="9" align="right">
					<select name="action">
						<option value="">What would you like to do?</option>
						<option value="" disabled="disabled">-----------------------------------</option>
						<optgroup label="Perform common tasks on selected orders">
							<?php
							foreach($template_types as $template_type) {
								echo sprintf('<option value="printall-%s">Print %s notes</option>', $template_type->type, $template_type->type);
							}
							?>
						</optgroup>
						<optgroup label="Change status of selected orders to">
							<?php
							foreach($statuses as $status) {
							?>
							<option value="status-<?=$status->value;?>"><?=$status->label;?></option>
							<?php } ?>
						</optgroup>
					</select>
					<input type="submit" name="submitcheck" value="Submit" class="button" disabled="disabled" />
					<input type="hidden" name="redirect" value="<?=current_url();?>" />
				</td>
			</tr>
			<tr>
				<td colspan="8"><?=$this->pagination->create_links();?></td>
			</tr>
		</tfoot>
	
	</table>
	
	</form>
</div>

<div id="sidebar">
	<?php $this->load->view('orders/orders_sidebar'); ?>
</div>