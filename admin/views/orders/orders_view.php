<div id="content">
	<div id="orderprocess">
	<?php
	// Set some counters
	$flow_counter = 0;
	$flow_total = count($order_flow);

	// Work out the widths
	$flow_widths = 100 / $flow_total;
	
	foreach($order_flow as $flow) {
		$flow_counter++;
		
		// Some css styling depending on active status
		if ($order->order_status == $flow->value) {
			$flow_active  = 'active';
			$flow_bgcolor = "background-color:$flow->color;";
			$flow_color   = "color:$flow->color;";
			$flow_border  = "border-bottom:3px solid $flow->color;";
		} else {
			$flow_active  = '';
			$flow_bgcolor = '';
			$flow_color   = '';
			$flow_border  = '';
		}
		
	?>
		<div class="<?=$flow_active;?>" id="orderprocess-step<?=$flow_counter;?>" style="width:<?=$flow_widths;?>%;<?=$flow_border;?>">
			<span style="<?=$flow_bgcolor;?>"><?=$flow_counter;?></span>
			<label style="<?=$flow_color;?>"><?=$flow->label;?></label>
		</div>
	<?php } ?>
	</div>
	
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="2">
					<h2>Order Details 
						<?php
						// Default the site to 'website', if this is 'mobile' (as it won't have it's own channel)
						$site = ($order->site == 'mobile') ? 'website' : $order->site;
						// Get list of available templates
						if (!empty($templates[$site])) {
							foreach($templates[$site] as $template) {
								$template = (object)$template;
								echo sprintf('<a href="%s/%d%s">Print %s</a>', site_url("orders/printnote/$order->order_id"), $template->id, redirect_create_manual('orders/view/'.$this->uri->segment(3).$redirect_query_string), $template->title);
							}
						} else {
							echo sprintf('<a href="%s">Create new template</a>', site_url('orders/templates'));
						}
						?>
					</h2>
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td width="50%"><strong>Order Ref:</strong> <?=$order->order_ref;?></td>
				<td width="50%"><strong>Order Date:</strong> <?=nice_date($order->order_date);?> <span class="smallprint">via <?=$channel;?></span></td>
			</tr>
			<tr>
				<td width="50%"><strong>Order Status:</strong> <?=order_status($order->order_status);?></td>
				<td width="50%"><strong>Payment via:</strong> <?=$order->transaction_type;?></td>
			</tr>
			<tr>
				<td><strong>Transaction ID:</strong> <?=$order->transaction_id;?></td>
				<td>
					<?php if ($order->coupon_code) { ?>
					<strong>Coupon:</strong> <?=$order->coupon_code;?>
					<?php } else {
					echo "&nbsp;";
					} ?>
				</td>
			</tr>
		</tbody>
		
	</table>
	
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td width="50%"><h2>Billing Address</h2></td>
				<td width="50%"><h2>Delivery Address <a href="<?=site_url('orders/edit/'.$this->uri->segment(3).$redirect_query_string);?>">Edit</a></h2></td>
			</tr>
		</thead>
	
		<tbody>
			<tr>
				<td valign="top">
					<?=$order->billing_title;?> <?=capitalise($order->billing_firstname);?> <?=capitalise($order->billing_surname);?><br/>
					<?php
					if ($order->billing_company != ''){
						print capitalise($order->billing_company) . '<br/>';
					}
					?>
					<?=capitalise($order->billing_address1);?><br/>
					<?php 
					if ($order->billing_address2 != '') {
						print capitalise($order->billing_address2) . '<br/>';
					}
					?>
					<?=capitalise($order->billing_city);?><br/>
					<?=uppercase($order->billing_postcode);?><br/>
					<?=$order->billing_country;?><br/><br/>
					<strong>Email:</strong> <a href="mailto:<?=$order->customer_email;?>"><?=$order->customer_email;?></a><br/>
					<strong>Phone:</strong> <?=$order->customer_phone;?><br/>
				</td>
				<td valign="top">
					<?=$order->delivery_title;?> <?=capitalise($order->delivery_firstname);?> <?=capitalise($order->delivery_surname);?><br/>
					<?php
					if ($order->delivery_company != ''){
						print capitalise($order->delivery_company) . '<br/>';
					}
					?>
					<?=capitalise($order->delivery_address1);?><br/>
					<?php 
					if ($order->delivery_address2 != '') {
						print capitalise($order->delivery_address2) . '<br/>';
					}
					?>
					<?=capitalise($order->delivery_city);?><br/>
					<?=uppercase($order->delivery_postcode);?><br/>
					<?=$order->delivery_country;?><br/>
				</td>
			</tr>
		</tbody>
	</table>
	
	<form method="post"	action="<?=site_url('orders/createrefund/'.$this->uri->segment(3).$redirect_query_string);?>">	
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="6"><h2>Order Inventory <a href="<?=site_url('orders/edit/'.$this->uri->segment(3).$redirect_query_string);?>">Edit</a></h2></td>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<th width="15%">Product No</th>
				<th width="40%">Product</th>
				<th width="10%"><center>Qty</center></th>
				<th width="10%">Item Price</th>
				<th width="10%">Total</th>
				<th width="10%"><center>Refund?</center></th>
			</tr>
			<?php
			$i = 0; //(A) used for background colour 
			
			foreach ($order_inventory as $item) {

				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			
				if (!empty($item->product_name)) {

				$product_options = str_replace('@',': ', $item->product_options);
				$product_options = str_replace('{', '', $product_options);
				$product_options = str_replace('}', ' | ', $product_options);
				$product_options = preg_replace('@ \|\ $@','', $product_options); //Remove the last pipeline symbol
				$product_options = trim($product_options);
				
				$all_product_nos[] = $item->product_no; 

				//Recreate the string for the refund form, but with negative prices. Url encode it to protect the data.
#				$refund_string = urlencode("$product_id:$product_no:$product_name:$product_qty:-$product_price|");
				$refund_string = $item->id;
			?>
				
			<tr class="<?=$post;?>">
				<td><a href="<?=site_url("inventory/index/0/filter=true&s_productno=$item->product_no");?>"><?=$item->product_no;?></a></td>
				<td><?=$item->product_name;?><br/><span class="smallprint redtext"><?=$product_options;?></span></td>
				<td align="center"><?=$item->product_qty;?></td>
				<td><?=money($item->product_price);?></td>
				<td><?=money($item->linetotal);?></td>
				<td align="center"><input type="checkbox" name="refund[]" value="<?=$refund_string;?>" /></td>
			</tr>
					
			<?php
				}
			};			
	
			if (!empty($order->shipping_method)) :
				$shipping_method = '<br/><span class="smallprint redtext">(' . trim($order->shipping_method) . ')</span>';				
			endif;
			
			// Create list of all product numbers
			if (!empty($all_product_nos)) {
				$all_products = implode(',', $all_product_nos);
			}
			?>
			
		</tbody>
		
		<tfoot>
			<tr>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule">Discount:</td>
				<td class="table-rule redtext">(<?=money($order->order_discount);?>)</td>
				<td class="table-rule" valign="top" rowspan="3"><input class="button" type="submit" name="submit" value="Refund Items" /></td>
			</tr>
	
			<tr>
				<td valign="top"><small><a href="<?=site_url("inventory/index/0/filter=true&s_productno=$all_products");?>">Go to products</a></small></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td valign="top">Shipping: <?=$shipping_method;?></td>
				<td valign="top"><?=$this->config->item('currency').number_format($order->order_shipping,2);?></td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>VAT:</td>
				<td><?=$this->config->item('currency').number_format($order->order_vat,2); ?></td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><strong>Total:</strong></td>
				<td><strong><?=$this->config->item('currency').number_format($order->order_total + $order->order_shipping + $order->order_vat,2);?></strong></td>
			</tr>
				
		</tfoot>
		
	</table>
	</form>
	
	<?php if (!empty($order->instructions)): ?>
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td><h2>Additional Instructions <a href="<?=site_url('orders/edit/'.$this->uri->segment(3));?>">Edit</a></h2></td>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<td><?=capfirst(nl2br($order->instructions));?></td>
			</tr>
		</tbody>
	</table>
	<?php endif; ?>

	<?php 
	if ($notes > 0): 
		$i = 0; //(A) used for background colour 
	?>
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="5"><h2>Notes</h2></td>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<th width="20%">Date</th>
				<th width="15%">User</th>
				<th width="65%">Note</th>
			</tr>
			<?php 
			foreach ($notes as $note) { 
				if ($prev_date != nice_date($note->date)) {
				$i++; //(A)
				}
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even-noborder'; } //(A);
				
				if ($prev_post != $post) {
					$border = " border";
				} else {
					$border = '';
				}
				
				if (is_today($note->date)) {
					$date = is_today($note->date,true);
				} else {
					$date = nice_date($note->date);
				}

				//Clean up the note
				$note_text = str_replace('\n', "\n", $note->note);
				$note_text = nl2br(stripslashes($note_text));
			?>
			<tr class="<?=$post;?><?=$border;?>">
				<?php if ($prev_date != nice_date($note->date)) { ?>
				<td valign="middle"><?=$date;?></td>
				<?php } else { ?>
				<td valign="middle">&nbsp;</td>
				<?php } ?>
				<td valign="middle">
					<?=$this->shopit->gravatar($note->email, 20);?> <span class="valign"><?=$note->author;?></span>
				</td>
				<td valign="middle"><?=$note_text;?></td>
			</tr>
			<?php 
				//By running these last, it will store the data output so
				//it's available in the next loop
				$prev_date = nice_date($note->date); 
				$prev_post = $post;
			} 
			?>
		</tbody>
	</table>
	<?php endif; ?>

</div>

<?php if ($custom_field_templates == 0):?>
<div id="fixed-sidebar">
<?php endif; ?>
<div id="sidebar">
	<form method="post" action="<?=current_url().$redirect_query_string;?>">
	<h3>Update order</h3>
	<p>Change this orders status to:</p>
	<p style="margin-bottom:10px;">
	<select name="s_orderstatus" id="s_orderstatus" class="dropdown">
		<?php
		foreach($statuses as $status) {
		?>
		<option value="<?=$status->value;?>" <?=is_selected($status->value, $order->order_status);?>><?=$status->label;?></option>
		<?php } ?>
	</select>
	</p>
	<?php
	if ($order->order_status == 'Dispatched' || $order->dispatch_date != '') {
		$set_dispatched = '';
	} else {
		$set_dispatched = ' style="display:none;"';
	}

	if ($order->dispatch_date != ""){
		$dispatch_date = explode('-',$order->dispatch_date);
	
		$s_dispatch_year = $dispatch_date[0];
		$s_dispatch_month = $dispatch_date[1];
		$s_dispatch_day = $dispatch_date[2];
	}
	
	?>
	<div id="set_dispatched"<?=$set_dispatched;?>>
		<p>Date of dispatch:</p>
		<p style="margin-bottom:10px;">
			<select name="s_dispatch_day">
				<?php 
				if($s_dispatch_day == null):
					$s_dispatch_day = date('d'); 
				endif;
				for($i = 1; $i <= 31; $i++) { 
				$day = ($i<10) ? "0$i" : $i; //add the leading 0 if missing
				?>
				<option value="<?=$day;?>"<?=is_selected($day,$s_dispatch_day);?>><?=$day;?></option>
				<?php } ?>
			</select>
			<?php
				if($s_dispatch_month == null):
					$s_dispatch_month = date('m'); 
				endif;
			?>
			<select name="s_dispatch_month">
				<option value="01"<?=is_selected('01',$s_dispatch_month);?>>January</option>
				<option value="02"<?=is_selected('02',$s_dispatch_month);?>>February</option>
				<option value="03"<?=is_selected('03',$s_dispatch_month);?>>March</option>
				<option value="04"<?=is_selected('04',$s_dispatch_month);?>>April</option>
				<option value="05"<?=is_selected('05',$s_dispatch_month);?>>May</option>
				<option value="06"<?=is_selected('06',$s_dispatch_month);?>>June</option>
				<option value="07"<?=is_selected('07',$s_dispatch_month);?>>July</option>
				<option value="08"<?=is_selected('08',$s_dispatch_month);?>>August</option>
				<option value="09"<?=is_selected('09',$s_dispatch_month);?>>September</option>
				<option value="10"<?=is_selected('10',$s_dispatch_month);?>>October</option>
				<option value="11"<?=is_selected('11',$s_dispatch_month);?>>November</option>
				<option value="12"<?=is_selected('12',$s_dispatch_month);?>>December</option>
			</select>
			<select name="s_dispatch_year">
				<?php 
				if ($s_dispatch_year == null):
					$s_dispatch_year = date('Y');
				endif;
				for($i = (date('Y')-5); $i <= date('Y'); $i++) { 
				?>
				<option value="<?=$i;?>"<?=is_selected($i,$s_dispatch_year);?>><?=$i;?></option>
				<?php } ?>
			</select>
		</p>
	</div>
	
	<p><strong>Attach a note</strong> (for office use only):</p>
	<p><textarea name="s_ordernotes" id="s_ordernotes" class="textbox" rows="4"></textarea></p>
	<p align="right">
		<a href="<?=$redirect_link;?>">Back to orders</a>
		<input type="hidden" name="s_orderid" value="<?=$order->order_id;?>" />
		<input type="submit" name="submit" value="Update Order" class="button" />
	</p>
	</form>

	<?php if ($custom_field_templates > 0):?>
	<h3>Custom Fields</h3>
	
	<?php 
	foreach ($custom_field_templates as $custom_field) {
		$custom_field_data = $this->settings_model->getCustomFieldData($order->order_id, $custom_field->custom_field_label);
	?>
	<p><strong><?=$custom_field->custom_field_title;?>:</strong> 
	<?=$custom_field_data->custom_field_data;?></p>
	<?php } 
	endif;
	?>

</div>
<?php if ($custom_field_templates == 0):?>
</div>
<?php endif; ?>