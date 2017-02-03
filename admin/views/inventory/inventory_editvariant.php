<div id="content">

	<div class="table">
	<h2><?=$form_title;?></h2>

	<?php if (validation_errors()) { ?>
	<p class="error_notice">Sorry, we found some errors with your product information. Please check below.</p>
	<?php } ?>

	<div id="section-information" class="section">

		<div class="table-row">
			<h3>Product Information</h3>
		</div>
	
		<div class="table-row">
			<label>Disable product?</label>
			<select name="product_disabled" id="product_disabled" class="dropdown">
				<option value="0"<?=is_selected('0',set_value('product_disabled',$item->product_disabled));?>>No</option>
				<option value="1"<?=is_selected('1',set_value('product_disabled',$item->product_disabled));?>>Yes</option>
			</select>
		</div>

		<?php
		$hide_variant_name_style = '';
		if ($this->config->item('variation_attributes') == 'true'){
			// Check if product name contains serialized data
			$product_name = unserialize($item->product_name);
			
			// If returns an array, show the attribute form 
			// otherwise display product name field as normal
			if (is_array($product_name) || ($has_variant_attributes)) {
				$hide_variant_name_style = 'style="display:none;"';
			}
			
			$product_name_required_class = ($has_variant_attributes) ? '' : 'required';
		}
		?>
		<div id="variant-name" class="table-row" <?=$hide_variant_name_style;?>>
			<label>Product Name: <span class="red">*</span></label>
			<input name="product_name" id="product_name" value="<?=set_value('product_name',$item->product_name);?>" class="textbox <?=$product_name_required_class;?>" size="75" />
			&nbsp;
			<?php
			if ($this->config->item('variation_attributes') == 'true') {
			?>
			<a id="btn-splitvariantname" href="#"><img src="<?=template_directory('assets/images/icon-split.png');?>" alt="Split name to attributes" title="Split name to attributes" class="valign" /></a>
			<?php } ?>
			<?=form_error('product_name');?>
		</div>
		
		<?php
		if ($this->config->item('variation_attributes') == 'true') {
		?>
		<div id="ajax-splitvariantname">
			<!-- Variant name attribute fields will be loaded here -->
			<?php
			if (is_array($product_name) || $has_variant_attributes) {
				$data['variant_attributes'] = $product_name;
				$this->load->view('inventory/inventory_editvariant_splitvariantname', $data);
			}
			?>
		</div>
		<?php } ?>
	
		<div class="table-row">
			<label>Product No: <span class="red">*</span></label>
			<input name="product_no" id="product_no" value="<?=set_value('product_no',$item->product_no);?>" class="textbox required" size="25" <?=tooltip("We recommend entering a unique product number to help identify your stock.");?> />
			<?=form_error('product_no');?>
		</div>

		<?php
		if ( ($item->product_mpn != "" || $this->input->post('product_mpn') != "") || ($item->product_upc != "" || $this->input->post('product_upc') != "") ) {
			$more_mpnupc = "";
			$show_more_mpnupc_option = false;
		} else {
			$more_mpnupc = "more-mpnupc";
			$show_more_mpnupc_option = true;
		}
		?>
	
		<div class="table-row">
			<label>EAN Number:</label>
			<input name="product_ean" id="product_ean" size="25" maxlength="25" value="<?=set_value('product_ean',$item->product_ean);?>" placeholder="Typically 13 numeric digits" class="textbox" <?=tooltip("<strong>European Article Number</strong> - A unique numerical identifier for commercial products that's usually associated with a barcode printed on retail merchandise (primarily outside of North America). Typically 13 numeric digits (can occasionally be either eight or 14 numeric digits).");?> /> 
			<?php
			if ($show_more_mpnupc_option) {
			?>
			<a href="#" id="more-mpnupc" class="form-link">Need to add MPN/UPC numbers?</a>
			<?php } ?>
			<?=form_error('product_ean');?>
		</div>

		<div class="table-row <?=$more_mpnupc;?>">
			<label>MPN Number:</label>
			<input name="product_mpn" id="product_mpn" size="25" maxlength="25" value="<?=set_value('product_mpn',$item->product_mpn);?>" class="textbox" placeholder="Unlimited alphanumeric digits" <?=tooltip("<strong>Manufacturer Part Number</strong> - The global number which uniquely identifies the product to its manufacturer. Alphanumeric digits (various lengths).");?> />
			<?=form_error('product_mpn');?>
		</div>

		<div class="table-row <?=$more_mpnupc;?>">
			<label>UPC Number:</label>
			<input name="product_upc" id="product_upc" size="25" maxlength="25" value="<?=set_value('product_upc',$item->product_upc);?>" class="textbox" placeholder="12 numeric digits" <?=tooltip("<strong>Universal Product Code</strong> - A unique numerical identifier for commercial products that's usually associated with a barcode printed on retail merchandise (primarily North America). 12 numeric digits.");?> />
			<?=form_error('product_upc');?>
		</div>
	
		<div class="table-row">
			<label>Supplier Code:</label>
			<input name="supplier_code" id="supplier_code" size="25" maxlength="25" value="<?=set_value('supplier_code',$item->supplier_code);?>" class="textbox" autocomplete="off" />
			<?=form_error('supplier_code');?>
			<div id="supplier_code_lookup" class="lookup-autocomplete"></div>
		</div>
			
		<div class="table-row">
			<label>Weight (KG):</label>
			<input name="product_weight" id="product_weight" value="<?=set_value('product_weight',$item->product_weight);?>" class="textbox number" size="25"/>
			<?=form_error('product_weight');?>
		</div>

	</div>
	
	<div id="section-pricing" class="section section-closed">

		<div class="table-row">
			<h3>Prices &amp; Stock</h3>
		</div>

		<div class="table-row">
			<label>Cost Price (<?=$this->config->item('currency');?>):</label>
			<input name="product_costprice" id="product_costprice" value="<?=set_value('product_costprice',$item->product_costprice);?>" class="textbox number" size="25"/>
			<span class="smallprint">(Exclude VAT)</span>
			<?=form_error('product_costprice');?>
		</div>
	
		<div class="table-row">
			<p align="right" class="smallprint">(Exclude VAT on all prices)</p>
		</div>
		
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 10px 10px 10px;">
			<head>
				<tr>
					<th width="35%">Sales Channel</th>
					<th width="35%">Channel Note</th>
					<th width="10%"><center>Price (<?=$this->config->item('currency');?>)</center></th>
					<th width="10%"><center>Sale Price (<?=$this->config->item('currency');?>)</center></th>
					<th width="10%"><center>Stock</center></th>
				</tr>
			</head>
			<tbody>
				<?php
				// Loop through each location/sales channel
				foreach($locations as $location) {
					
					//Set the location field name
					$location_field = "location_$location->id";

					//If this location is using global stock setting (1), then disable it (readonly)
					//and set its value to 0
					if ($location->use_global_stock == 1 && $item->$location_field == 0) {
						$location_field_status = 'readonly="readonly"';
						$location_field_status_class = '';
						$item->$location_field = 0;
					} else {
						$location_field_status = '';
						$location_field_status_class = 'required';
					}
					
					// Set the correct price field names
					if ($location->id == 1) {
						$product_price_field = 'product_price';
						$product_saleprice_field = 'product_saleprice';
					} else {
						$product_price_field = 'channel_'.$location->id.'_product_price';
						$product_saleprice_field = 'channel_'.$location->id.'_product_saleprice';
					}

					// Set the channel field name
					$channel_field = "channel_$location->id";
					
					// Default as ticked, if this is an "Add new item" form
					$channel_field_value = ($item->$channel_field == NULL) ? 1 : $item->$channel_field;
				?>
				<tr>
					<td><?=$location->name;?></td>
					<td><span class="smallprint"><?=$location->note;?></span></td>
					<?php
					// Set the 'required' class by default on the product_price field. 
					$product_price_field_class = 'required';
					?>
					<td align="center">
						<input name="<?=$product_price_field;?>" id="<?=$product_price_field;?>" value="<?=set_value($product_price_field, $item->$product_price_field);?>" class="textbox number required centered" size="8"/>
						<?=form_error($product_price_field);?>
					</td>
					<td align="center">
						<input name="<?=$product_saleprice_field;?>" id="<?=$product_saleprice_field;?>" value="<?=set_value($product_saleprice_field, $item->$product_saleprice_field);?>" class="textbox number centered" size="8"/>
						<?=form_error($product_saleprice_field);?>
					</td>
					<td align="center">
						<input name="<?=$location_field;?>" value="<?=set_value($location_field, $item->$location_field);?>" class="textbox <?=$location_field_status_class;?> digits centered" size="5" <?=$location_field_status;?> />
						<?=form_error($location_field);?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	
	<div id="section-offers" class="section section-closed">

		<?php if (count($coupons) > 0) { ?>
		<div class="table-row">
			<h3>Coupons</h3>
		</div>
		<div class="table-row">
			<label>Apply Coupons:</label>
			<fieldset>
				<legend>Select all coupons that apply</legend>
				<?php
				foreach($coupons as $coupon) {
					$coupon_colname = $coupon->field_name;
				?>
				<label for="<?=$coupon_colname;?>">
					<input type="checkbox" name="<?=$coupon_colname;?>" id="<?=$coupon_colname;?>" value="1" <?=is_checked(1, set_value($coupon_colname, $item->$coupon_colname));?> />
					<?=$coupon->label;?>
				</label>
				<?php } ?>
			</fieldset>
		</div>
		<?php } ?>
	
	</div>

	<?php if ($custom_field_templates > 0):?>
	<div id="section-customfields" class="section section-closed">

		<div class="table-row">
			<h3>Custom Fields</h3>
		</div>
		
		<?php 
		foreach ($custom_field_templates as $custom_field) {
			$custom_field_data = $this->settings_model->getCustomFieldData($item->product_id, $custom_field->custom_field_label);
		?>
		<div class="table-row">
			<label><?=$custom_field->custom_field_title;?>:</label>
			<input type="hidden" name="custom_field_id[]" value="<?=$custom_field_data->custom_field_id;?>" />
			<input type="hidden" name="custom_field_label[]" value="<?=$custom_field->custom_field_label;?>" />
			<?php if ($custom_field->custom_field_type == "multi") { ?>
			<textarea name="custom_field_data[]" class="textbox" rows="3"><?=set_value('custom_field_data[]', $custom_field_data->custom_field_data);?></textarea>
			<?php } elseif ($custom_field->custom_field_type == "editor") { ?>
			<div class="editor">
			<textarea name="custom_field_data[]" class="textbox <?=codebox();?>" id="<?=$custom_field->custom_field_label;?>" rows="6"><?=set_value('custom_field_data[]', $custom_field_data->custom_field_data);?></textarea>
			</div>
			<?php } elseif ($custom_field->custom_field_type == "image") { ?>
			<div class="editor-image">
			<textarea name="custom_field_data[]" class="textbox <?=codebox();?>" id="<?=$custom_field->custom_field_label;?>" rows="2"><?=set_value('custom_field_data[]', $custom_field_data->custom_field_data);?></textarea>
			</div>
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
		</div>
		<?php } ?>
	
	</div>
	<?php
	endif;
	?>
	
	<div id="section-options" class="section section-closed">
		
		<!-- Product options -->
		<div class="table-row">
			<h3>Product Options</h3>
			<p>Enter product options below. E.g. Available sizes, colours, etc. You can <strong>drag</strong> each option to your preferred order.</p>
		</div>
	
		<ul id="sortable-productoptions">
		<?php 
		if ($productoptions > 0):
		foreach ($productoptions as $option):
		?>
			<li class="table-row product-option">
				<label>&nbsp;</label>
				<input name="option_label[]" type="text" value="<?=$option->option_label;?>" class="textbox" size="20" />
				<input name="option_criteria[]" type="text" value="<?=$option->option_criteria;?>" class="textbox" size="20" />
				<input name="option_price[]" type="text" value="<?=$option->option_price;?>" class="textbox number" size="20" />
				<input name="option_id[]" value="<?=$option->id;?>" type="hidden" />
				<input name="option_delete[]" type="hidden" value="false" />
				<a href="#" class="button removeproductoption">X</a>
			</li>
		<?php 
		endforeach; 
		endif;
		?>
		</ul>
	
		<div class="table-row minpadding">
			<label>&nbsp;</label>
			<label for="option_label" style="width:165px;"><strong>Group Label</strong></label>
			<label for="option_criteria" style="width:165px;"><strong>Option</strong></label>
			<label for="option_price"><strong>Price</strong></label>
			<br clear="all" />
		</div>
	
		<div class="table-row minpadding">
			<label>Enter Option:</label>
			<input name="option_label_new" type="text" value="" class="textbox" size="20" <?=tooltip("Enter a label e.g. Size");?> />
			<input name="option_criteria_new" type="text" value="" class="textbox" size="20" <?=tooltip("Enter an option e.g. Small");?> />
			<input name="option_price_new" type="text" value="" class="textbox number" size="20" <?=tooltip("Enter this option's price difference e.g. 2.00");?> />
			<a href="#" class="button addproductoption">Add option</a>
		</div>

		<?php
		if (empty($productoptions)):
		if ($productoption_sets > 0): 
		?>
		<div class="table-row">
			<label>Or</label>
			<select name="productoption_set" class="dropdown" id="loadProductOptionSet">
				<option value="">Select a product option set</option>
				<?php foreach ($productoption_sets as $set): ?>
				<option value="<?=$set->option_set_id;?>"><?=$set->option_set_label;?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; 
		endif; ?>

	</div>

	<input type="hidden" name="product_id" value="<?=$item->product_id;?>" />
	<input type="hidden" name="cat_id" value="0" />
	<input type="hidden" name="existing_url" value="<?=get_product_slug($item->product_id)?>" />

	</div>

</div>

<div id="fixed-sidebar">
<div id="sidebar">
	<h3>Edit product information</h3>
	<p>Choose the section to edit:</p>
	<ul id="sections">
		<li><a href="#section-information" class="button">Product information</a></li>
		<li><a href="#section-pricing" class="button button-off">Pricing &amp; Stock</a></li>
		<li><a href="#section-offers" class="button button-off">Coupons &amp; Special Offers</a></li>
		<?php if ($custom_field_templates > 0) { ?>
		<li><a href="#section-customfields" class="button button-off">Custom Fields</a></li>
		<?php } ?>
		<li><a href="#section-options" class="button button-off">Product options</a></li>
	</ul>
</div>
</div>