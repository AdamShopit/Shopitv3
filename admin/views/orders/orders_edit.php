<div id="content">

	<input type="hidden" name="refund" value="0" />

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="2"><h2>Order Details</h2></td>
			</tr>
		</thead>
		<tbody>
			<?php if (validation_errors()) { ?>
			<tr>
				<td colspan="2">
					<span class="error_notice" style="padding-left:0px;">Sorry, we found some errors with your order information. You must have items in the inventory below.</span>				
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td width="50%"><label><strong>Order No:</strong></label> <span class="valign"><?=$order->order_ref;?></span></td>
				<td width="50%"><label><strong>Order Date:</strong></label> <span class="valign"><?=nice_date($order->order_date);?></span></td>
			</tr>
			<tr>
				<?php 
				if ($order->order_status == "") {
					$str_order_status = "Unprocessed";
				} else {
					$str_order_status = $order->order_status;
				} 
				?>
				<td width="50%"><label><strong>Order Status:</strong></label> <span class="valign"><?=$str_order_status;?> <?=order_status($order->order_status);?></span></td>
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
						<?php if ($this->config->item('payment_sagepay') == 'true') { ?>
						<option value="SagePay"<?=is_selected('SagePay',set_value('transaction_type',$transaction_type));?>>SagePay</option>
						<?php } ?>
						<?php if ($this->config->item('payment_cardsave') == 'true') { ?>
						<option value="CardSave"<?=is_selected('CardSave',set_value('transaction_type',$transaction_type));?>>CardSave</option>
						<?php } ?>
						<?php if ($this->config->item('payment_worldpay') == 'true') { ?>
						<option value="WorldPay"<?=is_selected('WorldPay',set_value('transaction_type',$transaction_type));?>>WorldPay</option>
						<?php } ?>
						<?php if ($this->config->item('payment_barclaycard') == 'true') { ?>
						<option value="Barclaycard"<?=is_selected('Barclaycard',set_value('transaction_type',$transaction_type));?>>Barclaycard</option>
						<?php } ?>
						<option value="Amazon" <?=is_selected('Amazon',set_value('transaction_type',$transaction_type));?>>Amazon</option>
						<option value="BACS"<?=is_selected('BACS',set_value('transaction_type',$transaction_type));?>>BACS</option>
						<option value="Cash"<?=is_selected('Cash',set_value('transaction_type',$transaction_type));?>>Cash</option>
						<option value="Cheque"<?=is_selected('Cheque',set_value('transaction_type',$transaction_type));?>>Cheque</option>
						<option value="Credit"<?=is_selected('Credit',set_value('transaction_type',$transaction_type));?>>Credit</option>
						<option value="Invoice"<?=is_selected('Invoice',set_value('transaction_type',$transaction_type));?>>Invoice</option>
						<option value="PayPal" <?=is_selected('PayPal',set_value('transaction_type',$transaction_type));?>>PayPal</option>
						<option value="Sale or Return"<?=is_selected('Sale or Return',set_value('transaction_type',$transaction_type));?>>Sale or Return</option>
						<option value="Unpaid"<?=is_selected('Unpaid',set_value('transaction_type',$transaction_type));?>>Unpaid</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<label><strong>Transaction ID:</strong></label>
					<input name="transaction_id" class="textbox" size="50" value="<?=$order->transaction_id;?>" placeholder="(Optional) Would be provided by payment gateway" />
				</td>
				<td>
					<?php
					// If this is the order builder, display the channel dropdown
					// else add the stored channel data as a hidden input. We'll be
					// using this field for the inventory lookup, to return the 
					// correct pricing based on the selected channel for this order.
					if ($this->uri->segment(2) === 'build') {
					?>
					<label><strong>Channel:</strong></label>
					<select name="site" class="dropdown">
					<?php
					foreach($locations as $channel) {
						echo sprintf( '<option value="%s" %s>%s</option>', $channel->shortname, is_selected($channel->shortname, set_value('site', $order->site)), $channel->name );
					}
					?>
					</select>
					<?php 
					} else {
						echo sprintf('<input type="hidden" name="site" value="%s" readonly />', $order->site);
					}
					?>
				</td>
			</tr>
		</tbody>
		
	</table>
	
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td width="50%"><h2>Billing Address <a href="<?=site_url('customers/lookup/billing');?>" class="customerLookup">Find</a></h2></td>
				<td width="50%"><h2>Delivery Address <a href="<?=site_url('customers/lookup/delivery');?>" class="customerLookup">Find</a></h2></td>
			</tr>
		</thead>
	
		<tbody>
			<tr>
				<td>
					<input type="hidden" name="account_id" value="<?=set_value('account_id', $order->account_id);?>" autocomplete="off" />
					<label>Title:</label>
					<?php
					$billing_title = array(
					                  ''  	 => 'Please select',
					                  'Mr'   => 'Mr',
					                  'Ms'   => 'Ms',
					                  'Mrs'  => 'Mrs',
					                  'Miss' => 'Miss',
					                  'Dr'	 => 'Dr',
					                  'Lord' => 'Lord',
					                  'Lady' => 'Lady',
					                  'Sir'	 => 'Sir',
					                );
					
					echo form_dropdown('billing_title', $billing_title, set_value('billing_title', $order->billing_title), 'class="dropdown" tabindex="1"');
					?>
				</td>
				<td>
					<label>Title:</label>
					<?php
					$delivery_title = array(
					                  ''  	 => 'Please select',
					                  'Mr'   => 'Mr',
					                  'Ms'   => 'Ms',
					                  'Mrs'  => 'Mrs',
					                  'Miss' => 'Miss',
					                  'Dr'	 => 'Dr',
					                  'Lord' => 'Lord',
					                  'Lady' => 'Lady',
					                  'Sir'	 => 'Sir',
					                );
					
					echo form_dropdown('delivery_title', $delivery_title, set_value('delivery_title', $order->delivery_title), 'class="dropdown" tabindex="9"');
					?>
				</td>
			</tr>
			<tr>
				<td><label>Firstname:</label>
					<input type="text" name="billing_firstname" value="<?=set_value('billing_firstname', capitalise($order->billing_firstname));?>" size="40" maxlength="30" class="textbox capitalise" tabindex="2" />
					<?=form_error('billing_firstname');?>
				</td>
				<td>
					<label>Firstname:</label>
					<input type="text" name="delivery_firstname" value="<?=set_value('delivery_firstname', capitalise($order->delivery_firstname));?>" size="40" maxlength="30" class="textbox capitalise" tabindex="10" />
					<?=form_error('delivery_firstname');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Surname:</label>
					<input type="text" name="billing_surname" value="<?=set_value('billing_surname', capitalise($order->billing_surname));?>" size="40" maxlength="30" class="textbox capitalise" tabindex="3" />
					<?=form_error('billing_surname');?>
				</td>
				<td>
					<label>Surname:</label>
					<input type="text" name="delivery_surname" value="<?=set_value('delivery_surname', capitalise($order->delivery_surname));?>" size="40" maxlength="30" class="textbox capitalise" tabindex="11" />
					<?=form_error('delivery_surname');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Company:</label>
					<input type="text" name="billing_company" value="<?=set_value('billing_company', capitalise($order->billing_company));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="4" />
					<?=form_error('billing_company');?>
				</td>
				<td>
					<label>Company:</label>
					<input type="text" name="delivery_company" value="<?=set_value('delivery_company', capitalise($order->delivery_company));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="12" />
					<?=form_error('delivery_company');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Address:</label>
					<input type="text" name="billing_address1" value="<?=set_value('billing_address1', capitalise($order->billing_address1));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="4" />
					<?=form_error('billing_address1');?>
				</td>
				<td>
					<label>Address:</label>
					<input type="text" name="delivery_address1" value="<?=set_value('delivery_address1', capitalise($order->delivery_address1));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="12" />
					<?=form_error('delivery_address1');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>&nbsp;</label>
					<input type="text" name="billing_address2" value="<?=set_value('billing_address2', capitalise($order->billing_address2));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="5" />
					<?=form_error('billing_address2');?>
				</td>
				<td>
					<label>&nbsp;</label>
					<input type="text" name="delivery_address2" value="<?=set_value('delivery_address2', capitalise($order->delivery_address2));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="13" />
					<?=form_error('delivery_address2');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Town/City:</label>
					<input type="text" name="billing_city" value="<?=set_value('billing_city', capitalise($order->billing_city));?>" size="40" maxlength="50" class="textbox capitalise"  tabindex="6"/>
					<?=form_error('billing_city');?>
				</td>
				<td>
					<label>Town/City:</label>
					<input type="text" name="delivery_city" value="<?=set_value('delivery_city', capitalise($order->delivery_city));?>" size="40" maxlength="50" class="textbox capitalise" tabindex="14" />
					<?=form_error('delivery_city');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Postal Code:</label>
					<input type="text" name="billing_postcode" value="<?=set_value('billing_postcode', uppercase($order->billing_postcode));?>" size="40" maxlength="15" class="textbox uppercase" tabindex="7" />
					<?=form_error('billing_postcode');?>
				</td>
				<td>
					<label>Postal Code:</label>
					<input type="text" name="delivery_postcode" value="<?=set_value('delivery_postcode', uppercase($order->delivery_postcode));?>" size="40" maxlength="15" class="textbox uppercase" tabindex="15" />
					<?=form_error('delivery_postcode');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Country:</label>
					<select name="billing_country" class="dropdown" tabindex="8">
					<?php
					foreach ($countries as $country) {
						if (empty($order->billing_country)) {
							$billing_country = default_country();
						} else {
							$billing_country = $order->billing_country;
						}
					?>
						<option value="<?=$country->country_name;?>" <?=is_selected($billing_country, $country->country_name);?>><?=$country->country_name;?></option>
					<?php } ?>
					</select>
				</td>
				<td>
					<label>Country:</label>
					<select name="delivery_country" class="dropdown" tabindex="16">
					<?php 
					foreach ($countries as $country) { 
						if (empty($order->delivery_country)) {
							$delivery_country = default_country();
						} else {
							$delivery_country = $order->delivery_country;
						}
					?>
						<option value="<?=$country->country_name;?>" <?=is_selected($delivery_country, $country->country_name);?>><?=$country->country_name;?></option>
					<?php } ?>
					</select>
				</td>
			</tr>
			<?php if ($this->uri->segment(2) == "build") { ?>
			<tr>
				<td colspan="2">
					<label>&nbsp;</label>
					<input type="checkbox" name="samefordelivery" id="samefordelivery" value="yes" /> <label style="float:none;display:inline;text-transform:none;" for="samefordelivery">Use the same address for delivery</label>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<label>Email:</label> <input type="text" name="customer_email" value="<?=set_value('customer_email', $order->customer_email);?>" size="40" maxlength="55" class="textbox" tabindex="17" />
					<?=form_error('customer_email');?>
				</td>
			</tr>
			<tr>
				<td>
					<label>Phone:</label> <input type="text" name="customer_phone" value="<?=set_value('customer_phone', $order->customer_phone);?>" size="40" maxlength="55" class="textbox" tabindex="18" />
					<?=form_error('customer_phone');?>
				</td>
			</tr>
		</tbody>
	</table>
	
	
	<table cellpadding="0" cellspacing="0" border="0" id="tblOrderInventory">
		<thead>
			<tr>
				<td colspan="5"><h2>Order Inventory</h2></td>
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
					   list($produce_id_key, $product_id)=each($_POST['product_id']) and 
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
					
					//Get the cat_id for this product
					$cat_id = $this->shipping_model->getCatId($product_id);
			?>
			
			<tr class="<?=$post;?>">
				<td>
					<input type="hidden" name="order_inventory_id[]" value="<?=$order_inventory_id;?>" />
					<input type="hidden" name="product_id[]" value="<?=$product_id;?>" />
					<input type="hidden" name="cat_id[]" value="<?=$cat_id;?>" />
					<input type="text" name="product_no[]" value="<?=$product_no;?>" class="textbox" size="15" />
				</td>
				<td>
					<input name="product_name[]" value="<?=$product_name;?>" class="textbox" size="50" />
				</td>
				<td align="center">
					<input type="text" name="product_qty[]" value="<?=$product_qty;?>" size="3" class="textbox centered" />
				</td>
				<td><input type="text" name="product_price[]" value="<?=number_format($product_price,2,'.','');?>" size="10" class="textbox" /></td>
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
				if ($order_inventory) {
				//This is the initial loop which checks through each inventory item for the
				//free qty. If there is one on an item, the item details are duplicated and 
				//appended to the array again ready to be looped through for display				
				foreach ($order_inventory as $item) {
				
					if (!empty($item->product_name)) {
	
					$product_options = str_replace('@','-', $item->product_options);
					$product_options = str_replace('{', '', $product_options);
					$product_options = str_replace('}', ', ', $product_options);
					$product_options = trim($product_options);
					$product_options = substr($product_options, 0, -1); //Remove the last comma
					
					if ($item->free_qty > 0){
						$duplicate_item = array(
							'product_id' 		=> $item->product_id,
							'product_no'		=> $item->product_no,
							'product_name'		=> "FREE - $item->product_name",
							'product_qty'		=> $item->free_qty,
							'product_price'		=> "0.00",
							'product_options' 	=> $item->product_options,
							'free_qty'			=> 0,
						);
						
						array_push($order_inventory, $duplicate_item);
					}
				
					}
				}

				//This is the display loop using the modified array above. This will generate the 
				//inventory on the page.
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

					//Get the cat_id for this product
					$item->cat_id = $this->shipping_model->getCatId($item->product_id);
			
			?>
				
			<tr class="<?=$post;?>">
				<td>
					<input type="hidden" name="order_inventory_id[]" value="<?=$item->id;?>" />
					<input type="hidden" name="product_id[]" value="<?=$item->product_id;?>" />
					<input type="hidden" name="cat_id[]" value="<?=$item->cat_id;?>" />
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
					<input name="product_name[]" value="<?=str_replace('"','',$item->product_name.$product_opts);?>" class="textbox" size="50" />
				</td>
				<td align="center">
					<input type="text" name="product_qty[]" value="<?=$item->product_qty;?>" size="3" class="textbox centered" />
				</td>
				<td>
					<input type="text" name="product_price[]" value="<?=number_format($item->product_price,2,'.','');?>" size="10" class="textbox" />
					<input type="hidden" name="product_weight[]" value="<?=$this->inventory_model->getWeight($item->product_id);?>" />
				</td>
				<td>
					<a href="#" class="button removeitemfromorder">Remove item</a>
					<input type="hidden" name="remove[]" value="no" />
				</td>
			</tr>
					
			<?php
					}
				}
			}
			}
			?>
		</tbody>
			
		<tfoot>
			<tr>
				<td>
					<input type="hidden" name="new_product_id" value="" />
					<input type="hidden" name="new_cat_id" value="" />
					<input type="hidden" name="new_product_no" class="textbox" size="15" placeholder="Product code" />
				</td>
				<td class="nowrap">
					<input name="new_product_name" class="textbox" size="50" placeholder="Enter product name or lookup by name/number" <?=tooltip('Enter the product name or enter keyword/product number and click the lookup button to search the inventory.');?> /> 
					<a href="<?=site_url('inventory/lookup');?>" data-url="<?=site_url('inventory/lookup');?>" class="button" id="productLookup">Lookup</a>
				</td>
				<td><input name="new_product_qty" class="textbox centered" size="3" placeholder="1" data-product_id="" /></td>
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
				<td>&nbsp;</td>
				<td colspan="2" align="right">
					<a href="<?=site_url('shipping/lookup/' . urlencode($delivery_country));?>" id="getShippingOptions" class="button">Get shipping options</a>
				</td>
				<td>Shipping:</td>
				<td class="nowrap">
					<?=$this->config->item('currency');?> <input name="order_shipping" value="<?=set_value('order_shipping',number_format($order->order_shipping,2,'.',''));?>" size="8" class="textbox" />
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
				<td colspan="5" align="center"><strong>Please note: The order total will be calculated automatically on save so there is no need to recalculate the total first.</strong></td>
			</tr>
				
		</tfoot>
		
	</table>
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td><h2>Additional Instructions</h2></td>
			</tr>
		</thead>
		
		<tbody>
			<tr>
				<td><textarea name="instructions" class="textbox" rows="5"><?=set_value('instructions', capfirst(nl2br($order->instructions)));?></textarea></td>
			</tr>
		</tbody>
	</table>

</div>

<?php if (count($custom_field_templates) <= 4):?>
<div id="fixed-sidebar">
<?php endif; ?>
<div id="sidebar">
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
	<p style="margin-bottom:10px;">
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
	</div>
	
	<p><strong>Attach a note</strong> (for office use only):</p>
	<p><textarea name="s_ordernotes" id="s_ordernotes" class="textbox" rows="4"><?=set_value('s_ordernotes');?></textarea></p>
	<p align="right">
		<a href="<?=$redirect_link;?>">Back to orders</a>
		<input type="hidden" name="s_orderid" value="<?=$order->order_id;?>" />
		<input type="hidden" name="order_changes" id="order_changes" value="" /><!-- used to identify edit changes -->
	</p>

	<?php if (count($custom_field_templates) > 0):?>
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
