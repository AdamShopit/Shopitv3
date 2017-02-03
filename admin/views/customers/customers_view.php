<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="4">
					<h2>Customer Details <a href="<?=$redirect_link;?>">Back to customers list</a></h2>
				</td>
			</tr>
		</thead>
	
		<tbody>
			<tr>
				<td width="25%"><strong>Full Name:</strong></td>
				<td width="25%"><?=$customer->billing_title;?> <?=$customer->billing_firstname;?> <?=$customer->billing_surname;?></td>
				<td width="25%">&nbsp;</td>
				<?php if ($customer->account_id > 0 && library_exists('myaccount')) { ?>
				<th width="25%"><a href="<?=site_url("customers/edit/$customer->account_id/$redirect");?>">Account ID: <?=$customer->account_id;?></a></th>
				<?php } else { ?>
				<td width="25%">&nbsp;</td>
				<?php } ?>
			</tr>
			<tr>
				<td><strong>Address:</strong></td>
				<td><?=$customer->billing_address1;?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php if ($customer->billing_address2 != '') { ?>
			<tr>
				<td>&nbsp;</td>
				<td><?=$customer->billing_address2;?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php } ?>
			<tr>
				<td><strong>Town/City:</strong></td>
				<td><?=$customer->billing_city;?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><strong>Postal Code:</strong></td>
				<td><?=$customer->billing_postcode;?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><strong>Country:</strong></td>
				<td><?=$customer->billing_country;?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><strong>Phone:</strong></td>
				<td><?=$customer->customer_phone;?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><strong>Email:</strong></td>
				<td><a href="mailto:<?=$customer->customer_email;?>"><?=$customer->customer_email;?></a></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
	
	<table cellpadding="0" cellspacing="0" border="0" class="fullwidth">
		<thead>
			<tr>
				<td colspan="7"><h2>This customer's Orders</h2></td>
			</tr>
			<tr>
				<th width="25%">Order No</th>
				<th width="20">Site</th>
				<th width="10%"><center>Status</center></th>
				<th width="45%">Date</th>
				<th width="20%">Total</th>
				<th width="1">&nbsp;</th>
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
					$order_date = is_today($item->order_date,true);
				} else {
					$highlight_today_class = '';
					$order_date = nice_date($item->order_date);
				}
	
				//List refunds
				$refunds = $this->orders_model->getRefunds($item->order_ref);
				$count_refunds = count($refunds);
				
				//Create a class to highlight the refund's parent order
				$refunds_class = ($refunds > 0) ? "even" : "";
				
				//Order Reference
				$order_ref = ($item->order_ref == '' || $item->order_status == '') ? "" : $item->order_ref;
				
				//Unprocessed CSS class
				if ($item->order_ref == '' || $item->order_status == '') {
					$unprocessed_class = ' unprocessed';
					$highlight_today_class = '';
				} else {
					$unprocessed_class = '';
				}
		?>
			
			<tr class="<?=$refunds_class;?> <?=$highlight_today_class;?><?=$unprocessed_class;?>" id="<?=$item->order_id;?>">
				<td><a href="<?=site_url('orders/view/'.$item->order_id . $redirect);?>"><?=$order_ref;?></a></td>
				<td align="center"><?=site_icon($item->site);?></td>
				<td align="center"><?=order_status($item->order_status);?></td>
				<td><?=$order_date;?></td>
				<td><?=$this->config->item('currency');?><?=number_format($item->total,2);?></td>
				<td>
					<ul class="actions">
						<li><a href="#" class="btn-action"><img src="<?=template_directory("assets/images/btn-action-arrow-down.png");?>" alt=""/></a>
							<ul>
								<li><a href="<?=site_url('orders/view/'.$item->order_id . $redirect);?>">View order</a></li>
								<li><a href="<?=site_url('orders/edit/'.$item->order_id . $redirect);?>">Edit order</a></li>
								<?php if ($item->order_status != 'Dispatched' && empty($item->dispatch_date)) {?>
								<li class="markasdispatched"><a href="<?=site_url('orders/markasdispatched/'.$item->order_id.'/Dispatched');?>">Mark as dispatched</a></li>
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
			</tr>
			<?php
			$r = 0;
			if ($refunds):
			?>
			<tr class="subheadings">
				<td>Refund Date</td>
				<td colspan="3">Item(s)</td>
				<td>Total</td>
				<td>&nbsp;</td>
			</tr>
			<?php
			//Display the refunds
			foreach($refunds as $refund) {
			
				$r++;
				$array_product_name = array();
				
				//Add a "last" class
				$last_subrow_class = ($count_refunds == $r) ? ' class="last-subrow"' : "";
	
				$pieces = explode(';', trim($refund->inventory));
	
				foreach ($pieces as $refund_inv) {
	
					//Create an array of products if product_name exists
					if (!empty($refund_inv)) {
						$array_product_name[] = "&mdash; $refund_inv";
					}
				
				}
	
				//Create a list of products e.g. item 1, item 2, item 3, etc.
				$product_names = implode('<br/>', $array_product_name);
			?>
			<tr<?=$last_subrow_class;?> id="<?=$refund->refund_id;?>">
				<td valign="top">
					<a href="<?=site_url("orders/editrefund/$refund->refund_id$redirect");?>" class="valign"><?=nice_date($refund->refund_date);?></a>
				</td>
				<td valign="top" colspan="3"><?=$product_names;?></td>
				<td valign="top" class="redtext"><?=money($refund->total);?></td>
				<td valign="top" align="center"><a href="<?=site_url("orders/deleterefund/$refund->refund_id");?>" class="ajaxdelete valign" rel="Are you sure you want to delete this refund?"><img src="<?=template_directory('assets/images/icon-cross.png');?>" alt="Delete" title="Delete" /></a></td>
			</tr>
			<?php
			}
			endif;
			?>
			<?php 
			endforeach; 
			} else { 
			?>
			<tr>
				<td colspan="6" align="center">No orders could be found for this customer.</td>
			</tr>
			<?php } ?>
		</tbody>
	
		<tfoot>
			<tr>
				<td colspan="6"><?=$this->pagination->create_links();?></td>
			</tr>
		</tfoot>
	
	</table>

</div>

<div id="sidebar">
	<h3>View customer</h3>
	<p>From this page you can view a customer's details as well as all their orders.</p>
</div>