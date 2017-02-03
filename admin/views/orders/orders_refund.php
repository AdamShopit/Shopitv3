<div id="content">
	
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="2"><h2>Refund Details</h2></td>
			</tr>
		</thead>
		<tbody>
			<?php if (validation_errors()) { ?>
			<tr>
				<td colspan="2">
					<span class="error_notice" style="padding-left:0px;">Sorry, we found some errors with your Refund information. You must have items in the inventory below.</span>				
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td width="50%"><strong>Refund Ref:</strong> <?=$order->order_ref;?></td>
				<td width="50%"><strong>Refund Date:</strong>
				<?php 
				//If order_date is null, display datepicker input else display the date
				if ($order->order_date == NULL) {
					$now = date('Y-m-d', time());
				?>
					<input type="text" name="order_date" value="<?=$now;?>" class="textbox jqueryui-date-picker" readonly="readonly" /> 
				<?php
				} else {
					echo nice_date($order->order_date);
				}
				?>
				</td>
			</tr>
			<tr>
				<td width="50%"><strong>Refund Status:</strong> <?=order_status($order->order_status);?></td>
				<td width="50%">
					<?php 
					if (empty($order->transaction_type)) {
						$transaction_type = 'Unpaid';
					} else {
						$transaction_type = $order->transaction_type;
					}
					?>
					<label><strong>Payment via:</strong></label>
					<select name="transaction_type" class="dropdown">
						<?php if ($this->config->item('payment_paypal') == 'true') { ?>
						<option value="PayPal" <?=is_selected('PayPal',set_value('transaction_type',$transaction_type));?>>PayPal</option>
						<?php } ?>
						<?php if ($this->config->item('payment_sagepay') == 'true') { ?>
						<option value="SagePay"<?=is_selected('SagePay',set_value('transaction_type',$transaction_type));?>>SagePay</option>
						<?php } ?>
						<?php if ($this->config->item('payment_cardsave') == 'true') { ?>
						<option value="CardSave"<?=is_selected('CardSave',set_value('transaction_type',$transaction_type));?>>CardSave</option>
						<?php } ?>
						<?php if ($this->config->item('payment_worldpay') == 'true') { ?>
						<option value="WorldPay"<?=is_selected('WorldPay',set_value('transaction_type',$transaction_type));?>>WorldPay</option>
						<?php } ?>
						<option value="BACS"<?=is_selected('BACS',set_value('transaction_type',$transaction_type));?>>BACS</option>
						<option value="Cash"<?=is_selected('Cash',set_value('transaction_type',$transaction_type));?>>Cash</option>
						<option value="Cheque"<?=is_selected('Cheque',set_value('transaction_type',$transaction_type));?>>Cheque</option>
						<option value="Credit"<?=is_selected('Credit',set_value('transaction_type',$transaction_type));?>>Credit</option>
						<option value="Invoice"<?=is_selected('Invoice',set_value('transaction_type',$transaction_type));?>>Invoice</option>
						<option value="Sale or Return"<?=is_selected('Sale or Return',set_value('transaction_type',$transaction_type));?>>Sale or Return</option>
						<option value="Unpaid"<?=is_selected('Unpaid',set_value('transaction_type',$transaction_type));?>>Unpaid</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<strong>Transaction ID:</strong>
					<input name="transaction_id" class="textbox" size="50" value="<?=$order->transaction_id;?>" placeholder="(Optional) Would be provided by payment gateway" />
				</td>
			</tr>
		</tbody>
		
	</table>
	
	<!-- Billing/delivery address details -->
	<input type="hidden" name="billing_title" value="<?=$order->billing_title;?>" />
	<input type="hidden" name="billing_firstname" value="<?=$order->billing_firstname;?>" />
	<input type="hidden" name="billing_surname" value="<?=$order->billing_surname;?>" />
	<input type="hidden" name="billing_company" value="<?=$order->billing_company;?>" />
	<input type="hidden" name="billing_address1" value="<?=$order->billing_address1;?>" />
	<input type="hidden" name="billing_address2" value="<?=$order->billing_address2;?>" />
	<input type="hidden" name="billing_city" value="<?=$order->billing_city;?>" />
	<input type="hidden" name="billing_postcode" value="<?=$order->billing_postcode;?>" />
	<input type="hidden" name="billing_country" value="<?=$order->billing_country;?>" />
	<input type="hidden" name="delivery_title" value="<?=$order->delivery_title;?>" />
	<input type="hidden" name="delivery_firstname" value="<?=$order->delivery_firstname;?>" />
	<input type="hidden" name="delivery_surname" value="<?=$order->delivery_surname;?>" />
	<input type="hidden" name="delivery_company" value="<?=$order->delivery_company;?>" />
	<input type="hidden" name="delivery_address1" value="<?=$order->delivery_address1;?>" />
	<input type="hidden" name="delivery_address2" value="<?=$order->delivery_address2;?>" />
	<input type="hidden" name="delivery_city" value="<?=$order->delivery_city;?>" />
	<input type="hidden" name="delivery_postcode" value="<?=$order->delivery_postcode;?>" />
	<input type="hidden" name="delivery_country" value="<?=$order->delivery_country;?>" />
	<input type="hidden" name="customer_email" value="<?=$order->customer_email;?>" />
	<input type="hidden" name="customer_phone" value="<?=$order->customer_phone;?>" />
	
	<input type="hidden" name="order_ref" value="<?=$order->order_ref;?>" />
	<input type="hidden" name="refund" value="1" />
	
	<table cellpadding="0" cellspacing="0" border="0" id="tblOrderInventory">
		<thead>
			<tr>
				<td colspan="5"><h2>Refund Inventory</h2></td>
			</tr>
			<tr>
				<th width="15%">Product No</th>
				<th width="50%">Product</th>
				<th width="5%"><center>Qty</center></th>
				<th width="15%">Item Price</th>
				<th width="15%">Total</th>
			</tr>
		</thead>
		
		<tbody>
			<?php
			//Check if an array of inventory items has been posted, and use
			//this array data instead
			if (!empty($_POST['product_no']) && !empty($_POST['product_name'])) {

				$i = 0; //(A) used for background colour 

				while( 
				   	   list($order_inventory_id_key, $order_inventory_id)=each($_POST['order_inventory_id']) and
					   list($product_id_key, $product_id)=each($_POST['product_id']) and 
					   list($product_no_key, $product_no)=each($_POST['product_no']) and
					   list($product_name_key, $product_name)=each($_POST['product_name']) and
					   list($product_qty_key, $product_qty)=each($_POST['product_qty']) and
					   list($product_price_key, $product_price)=each($_POST['product_price'])
					 )
				{

					$i++; //(A)
					
					if ($i&1) { $post = 'odd'; } 
					else { $post = 'even'; } //(A);
					
					if (empty($product_qty)) {
						$product_qty = 0;
					}
					
					if (empty($product_price)) {
						$product_price = '0.00';
					}
			?>
			
			<tr class="<?=$post;?>">
				<td>
					<input type="hidden" name="order_inventory_id[]" value="<?=$order_inventory_id;?>" />
					<input type="hidden" name="product_id[]" value="<?=$product_id;?>" />
					<input type="text" name="product_no[]" value="<?=$product_no;?>" class="textbox" size="15" />
				</td>
				<td>
					<input name="product_name[]" value="<?=$product_name;?>" class="textbox" size="50" />
				</td>
				<td align="center"><input type="text" name="product_qty[]" value="<?=$product_qty;?>" size="3" class="textbox centered" /></td>
				<td>
					<input type="text" name="product_price[]" value="<?=number_format($product_price,2,'.','');?>" size="10" class="textbox" />
					<input type="hidden" name="product_weight[]" value="<?=$this->inventory_model->getWeight($product_id);?>" />
				</td>
				<td>
					<a href="#" class="button removeitemfromorder">Remove item</a>
					<input type="hidden" name="remove[]" value="no" />
				</td>
			</tr>
			
			<?php
				}
			
			//Else no post array data exists so pull the inventory data
			//from the order details in the db
			} else {
				$i = 0; //(A) used for background colour
				
				foreach ($order_inventory as $item) {
	
					$i++; //(A)
					
					$post = ($i&1) ? 'odd' : 'even';
				
					if (!empty($item->product_name)) {
	
					$product_options = str_replace('@','-', $item->product_options);
					$product_options = str_replace('{', '', $product_options);
					$product_options = str_replace('}', ', ', $product_options);
					$product_options = trim($product_options);
					$product_options = substr($product_options, 0, -1); //Remove the last comma
					
			?>
				
			<tr class="<?=$post;?>">
				<td>
					<input type="hidden" name="order_inventory_id[]" value="<?=$item->id;?>" />
					<input type="hidden" name="product_id[]" value="<?=$item->product_id;?>" />
					<input type="text" name="product_no[]" value="<?=$item->product_no;?>" class="textbox" size="15" />
				</td>
				<td>
					<?php 
					if (!empty($product_options)) {
						$product_opts = ' (' . $product_options . ')';	
					} else {
						$product_opts = '';
					} 
					?>
					<input name="product_name[]" value="<?=str_replace('"', '', $item->product_name.$product_opts);?>" class="textbox" size="50" />
				</td>
				<td align="center"><input type="text" name="product_qty[]" value="<?=$item->product_qty;?>" size="3" class="textbox centered" /></td>
				<td>
					<input type="text" name="product_price[]" value="<?=number_format($item->product_price, 2, '.', '');?>" size="10" class="textbox" />
					<input type="hidden" name="product_weight[]" value="<?=$this->inventory_model->getWeight($item->product_id);?>" />
				</td>
				<td>
					<a href="#" class="button removeitemfromorder">Remove item</a>
					<input type="hidden" name="remove[]" value="false" />
				</td>
			</tr>
					
			<?php
					}
				}
			}	
			?>
		</tbody>
			
		<tfoot>
			<tr>
				<td>
					<input type="hidden" name="new_product_id" value="" />
					<input name="new_product_no" class="textbox" size="15" placeholder="Product code" />
				</td>
				<td class="nowrap"><input name="new_product_name" class="textbox" size="50" placeholder="Product name" <?=tooltip('Enter the product name or enter keyword/product number and click the lookup button to search the inventory.');?> /> <a href="<?=site_url('inventory/lookup');?>" class="button" id="productLookup">Lookup</a></td>
				<td><input name="new_product_qty" class="textbox centered" size="3" placeholder="1" /></td>
				<td>
					<input name="new_product_price" class="textbox" size="10" placeholder="0.00" <?=tooltip("Don't forget to click the 'Add to order' button!");?> />
					<input type="hidden" name="new_product_weight" value="" />
				</td>
				<td><a href="#" class="button" id="additemtoorder">Add to order</a></td>
			</tr>

			<tr>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule">Discount:</td>
				<td class="table-rule" class="nowrap"><?=$this->config->item('currency');?> <input name="order_discount" value="<?=set_value('order_discount', number_format($order->order_discount,2)); ?>" size="8" class="textbox" /></td>
			</tr>
	
			<tr>
				<td class="table-rule">&nbsp;</td>
				<td class="table-rule" colspan="2" align="right">
					<a href="<?=site_url('shipping/lookup/' . urlencode($delivery_country));?>" id="getShippingOptions" class="button">Get shipping options</a>
				</td>
				<td class="table-rule">Shipping:</td>
				<td class="table-rule nowrap">
					<?=$this->config->item('currency');?> <input name="order_shipping" value="<?=set_value('order_shipping',number_format($order->order_shipping,2));?>" size="8" class="textbox" />
					<input type="hidden" name="shipping_method" value="<?=set_value('shipping_method', $order->shipping_method);?>" />
					<?=form_error('order_shipping');?>
				</td>
			</tr>
	
			<tr>
				<td>&nbsp;</td>
				<td colspan="2" align="right">
					<span class="smallprint">(If ticked, this will automatically recalculate VAT on save)</span>
					<input type="checkbox" name="auto_vat" id="auto_vat" value="true" /> <label for="auto_vat" style="float:none;display:inline;text-transform:none;">Calculate VAT for me</label>
				</td>
				<td>VAT:</td>
				<td class="nowrap"><?=$this->config->item('currency');?> <input name="order_vat" value="<?=set_value('order_vat', number_format($order->order_vat,2)); ?>" size="8" class="textbox" /></td>
			</tr>
			
			<tr>
				<td>&nbsp;</td>
				<td colspan="2" align="right">
					<span class="smallprint">(This will calculate the order total, including VAT if ticked above)</span>
					<a href="<?=site_url('orders/gettotal');?>" class="button" id="getTotal">Recalculate Total</a>
				</td>
				<td><strong>Total:</strong></td>
				<td><strong id="orderTotal"><?=$this->config->item('currency');?><?=number_format($order->order_total + $order->order_shipping + $order->order_vat,2);?></strong></td>
			</tr>
			
			<tr>
				<td colspan="5" align="center"><strong>Please note: The refund total will be calculated automatically on save so there is no need to recalculate the total first.</strong></td>
			</tr>
				
		</tfoot>
		
	</table>

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
				<td valign="top"><?=$date;?></td>
				<?php } else { ?>
				<td valign="top">&nbsp;</td>
				<?php } ?>
				<td valign="top"><?=$note->author;?></td>
				<td valign="top"><?=$note_text;?></td>
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
	<h3>Update order</h3>
	<input type="hidden" name="s_orderstatus" value="<?=$order->order_status;?>" />
	<?php if (!empty($order->dispatch_date)): ?>
	<p style="padding-bottom:10px;"><label for="s_unmarkdispatched"><input type="checkbox" name="s_unmarkdispatched" id="s_unmarkdispatched" value="true" />Unmark as dispatched</label></p>
	<?php endif; ?>
	<?php
	if ($order->order_status == 'Dispatched' || $this->input->post('s_orderstatus') == 'Dispatched' || $order->dispatch_date != '') {
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
	<p>
		<select name="s_dispatch_day">
			<?php 
			if($s_dispatch_day == null):
				$s_dispatch_day = date('d'); 
			endif;
			for($i = 1; $i <= 31; $i++) { 
			$day = ($i<10) ? "0$i" : $i; //add the leading 0 if missing
			?>
			<option value="<?=$day;?>"<?=is_selected($day,set_value('s_dispatch_day', $s_dispatch_day));?>><?=$day;?></option>
			<?php } ?>
		</select>
		<?php
			if($s_dispatch_month == null):
				$s_dispatch_month = date('m'); 
			endif;
		?>
		<select name="s_dispatch_month">
			<option value="01"<?=is_selected('01',set_value('s_dispatch_month', $s_dispatch_month));?>>January</option>
			<option value="02"<?=is_selected('02',set_value('s_dispatch_month', $s_dispatch_month));?>>February</option>
			<option value="03"<?=is_selected('03',set_value('s_dispatch_month', $s_dispatch_month));?>>March</option>
			<option value="04"<?=is_selected('04',set_value('s_dispatch_month', $s_dispatch_month));?>>April</option>
			<option value="05"<?=is_selected('05',set_value('s_dispatch_month', $s_dispatch_month));?>>May</option>
			<option value="06"<?=is_selected('06',set_value('s_dispatch_month', $s_dispatch_month));?>>June</option>
			<option value="07"<?=is_selected('07',set_value('s_dispatch_month', $s_dispatch_month));?>>July</option>
			<option value="08"<?=is_selected('08',set_value('s_dispatch_month', $s_dispatch_month));?>>August</option>
			<option value="09"<?=is_selected('09',set_value('s_dispatch_month', $s_dispatch_month));?>>September</option>
			<option value="10"<?=is_selected('10',set_value('s_dispatch_month', $s_dispatch_month));?>>October</option>
			<option value="11"<?=is_selected('11',set_value('s_dispatch_month', $s_dispatch_month));?>>November</option>
			<option value="12"<?=is_selected('12',set_value('s_dispatch_month', $s_dispatch_month));?>>December</option>
		</select>
		<select name="s_dispatch_year">
			<?php 
			if ($s_dispatch_year == null):
				$s_dispatch_year = date('Y');
			endif;
			for($i = (date('Y')-5); $i <= date('Y'); $i++) { 
			?>
			<option value="<?=$i;?>"<?=is_selected($i,set_value('s_dispatch_year', $s_dispatch_year));?>><?=$i;?></option>
			<?php } ?>
		</select>
	</p>
	<ul>
		<li><label for="s_email" ><input type="checkbox" name="s_email" id="s_email" value="true"<?=is_checked('true',$s_email);?>/>Send dispatch email to customer</label></li>
		<?php if ($order->dispatch_email != ""): ?>
		<li class="smallprint"><center>Last email sent: <?=nice_date($order->dispatch_email,'date');?></center></li>
		<?php endif; ?>
	</ul>
	</div>
	
	<p><strong>Attach a note</strong> (for office use only):</p>
	<p><textarea name="s_ordernotes" id="s_ordernotes" class="textbox" rows="4"><?=set_value('s_ordernotes');?></textarea></p>
	<p align="right">
		<a href="<?=$redirect_link;?>">Back to orders</a>
		<input type="hidden" name="s_orderid" value="<?=$order->order_id;?>" />
		<input type="hidden" name="order_changes" id="order_changes" value="" /><!-- used to identify edit changes -->
	</p>

	<?php if ($custom_field_templates > 0):?>
	<h3>Custom Fields</h3>
	
	<?php 
	foreach ($custom_field_templates as $custom_field) {
		$custom_field_data = $this->settings_model->getCustomFieldData($order->order_id, $custom_field->custom_field_label);
	?>
	<p><?=$custom_field->custom_field_title;?>:</p>
	<p>
		<input type="hidden" name="custom_field_id[]" value="<?=$custom_field_data->custom_field_id;?>" />
		<input type="hidden" name="custom_field_label[]" value="<?=$custom_field->custom_field_label;?>" />
		<?php if ($custom_field->custom_field_type == "multi") { ?>
		<textarea name="custom_field_data[]" class="textbox" rows="3"><?=set_value('custom_field_data[]', $custom_field_data->custom_field_data);?></textarea>
		<?php } elseif($custom_field->custom_field_type == "date") { ?>
		<input name="custom_field_data[]" value="<?=set_value('custom_field_data[]', $custom_field_data->custom_field_data);?>" class="textbox jqueryui-date-picker" size="75" />
		<?php } elseif($custom_field->custom_field_type == "yes/no") { ?>
		<select name="custom_field_data[]" class="dropdown">
			<option value="No"<?=is_selected('No',set_value('custom_field_data[]', $custom_field_data->custom_field_data));?>>No</option>
			<option value="Yes"<?=is_selected('Yes',set_value('custom_field_data[]', $custom_field_data->custom_field_data));?>>Yes</option>
		</select>
		<?php 
		} elseif($custom_field->custom_field_type == "option") { 
			$custom_field_options = explode("\n",$custom_field->custom_field_default);
		?>
		<select name="custom_field_data[]" class="dropdown">
			<?php foreach ($custom_field_options as $opt) { 
			if (!empty($opt)) {
			?>
			<option value="<?=$opt;?>"<?=is_selected($opt,set_value('custom_field_data[]', $custom_field_data->custom_field_data));?>><?=$opt;?></option>
			<?php } } ?>
		</select>
		<?php } else { ?>
		<input name="custom_field_data[]" value="<?=set_value('custom_field_data[]', $custom_field_data->custom_field_data);?>" class="textbox" size="75" />
		<?php } ?> 
	</p>
	<?php } 
	endif;
	?>

</div>
<?php if ($custom_field_templates == 0):?>
</div>
<?php endif; ?>
