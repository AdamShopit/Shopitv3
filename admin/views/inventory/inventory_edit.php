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
	
		<div class="table-row">
			<label>Main Category:</label>
			<select name="cat_id" id="cat_id" class="dropdown">
				<?php foreach($categories as $category):?>
				<option value="<?=$category->cat_id;?>"<?=is_selected($category->cat_id,set_value('cat_id',$item->cat_id));?>><?=str_replace(' & ',' &amp; ',$category->cat_name);?></option>
				<?php endforeach;?>
			</select>
		</div>
		
		<div class="table-row">
			<label>Product Name: <span class="red">*</span></label>
			<input name="product_name" id="product_name" value="<?=set_value('product_name',$item->product_name);?>" class="textbox required google-preview" size="75"/>
			<?=form_error('product_name');?>
		</div>
	
		<?php if ($this->uri->segment(2) != 'add' && $this->uri->segment(2) != 'insert'):?>
		<div class="table-row">
			<label>Page slug (URL): <span class="red">*</span></label> <?=site_root();?>.../ 
			<input name="product_slug" id="product_slug" value="<?=set_value('product_slug',$item->product_slug);?>" class="textbox required" size="50" <?=tooltip("The product slug is automatically created based on the product's name but can be changed if required. Only alphanumeric characters are permitted.");?>/>
			<?=form_error('product_slug');?>
		</div>
		<?php endif; ?>
	
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
			<input name="product_ean" id="product_ean" size="25" maxlength="25" value="<?=set_value('product_ean',$item->product_ean);?>" class="textbox" placeholder="Typically 13 numeric digits" <?=tooltip("<strong>European Article Number</strong> - A unique numerical identifier for commercial products that's usually associated with a barcode printed on retail merchandise (primarily outside of North America). Typically 13 numeric digits (can occasionally be either eight or 14 numeric digits).");?> />
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
			<label>Brand/Manufacturer:</label>
			<input name="product_brand" id="product_brand" value="<?=set_value('product_brand',$item->product_brand);?>" class="textbox" size="25" autocomplete="off" />
			<?=form_error('product_brand');?>
			<div id="product_brand_lookup" class="lookup-autocomplete"></div>
		</div>
	
		<div class="table-row">
			<label>Description:</label>
			<div class="editor">
				<textarea name="product_description" id="product_description" class="textbox tinymce google-preview <?=codebox();?>"><?=set_value('product_description', hidep($item->product_description));?></textarea>
			</div>
			<?=form_error('product_description');?>
		</div>

		<div class="table-row">
			<label>Excerpt:</label>
			<div class="editor">
				<textarea name="product_excerpt" id="product_excerpt" class="textbox tinymce tinymce-short <?=codebox();?>"><?=set_value('product_excerpt', hidep($item->product_excerpt));?></textarea>
			</div>
			<?=form_error('product_excerpt');?>
		</div>

		<?php if (!empty($item->product_file)):?>
		<div class="table-row">
			<label>Current Attached File:</label>
			<a href="<?=site_root('docs/' . $item->product_file);?>" target="_blank" class="table-row-link" ><?=$item->product_file;?></a> <label for="delete_product_file" style="float:none;padding-left:10px;vertical-align:middle;" class="red"><input type="checkbox" name="delete_product_file" id="delete_product_file" value="true" /> Remove file?</label>
		</div>
		<?php endif; ?>
	
		<div class="table-row">
			<?php if (!empty($item->product_file)):?>
			<label>Replace attached file with:</label>
			<?php else: ?>
			<label>Attach File:</label>
			<?php endif; ?>
			<input type="file" name="product_file" id="product_file" class="uploadbox" /> <span class="smallprint">(PDF, Doc or XLS files permitted only)</span>
		</div>

		<div class="table-row">
			<label>Condition:</label>
			<select name="product_condition" id="product_condition" class="dropdown">
				<option value="new"<?=is_selected('new',set_value('product_condition',$item->product_condition));?>>New</option>
				<option value="used"<?=is_selected('used',set_value('product_condition',$item->product_condition));?>>Used</option>
				<option value="refurbished"<?=is_selected('refurbished',set_value('product_condition',$item->product_condition));?>>Refurbished</option>
			</select>
		</div>
				
		<div class="table-row">
			<label>Product Tags:</label>
			<ul id="ptags">
				<?php
				//Get the posted or database product_tags
				$ptags = set_value('product_tags', $item->product_tags);
				
				if (!empty($ptags)) {
					
					//Convert the product tags into an array
					$tags = explode(",", $ptags);
					
					//Loop through the $tags array and create an li
					foreach($tags as $tag) {
				?>
				<li><?=trim($tag);?><a href="#" class="ptag-remove"></a></li>
				<?php
					}
				}
				?>
				<li id="ptags-new"><input type="text" name="ptag_new" value="" placeholder="enter new tag" size="15" title="Press enter or type comma to add tag" /></li>
			</ul>
			<input type="hidden" name="product_tags" id="product_tags" value="<?=set_value('product_tags',$item->product_tags);?>" />
		</div>

		<div class="table-row">
			<label>Weight (KG):</label>
			<input name="product_weight" id="product_weight" value="<?=set_value('product_weight',$item->product_weight);?>" class="textbox number" size="25"/>
			<?=form_error('product_weight');?>
		</div>

	</div>
	
	<div id="section-pricing" class="section section-closed">

		<div class="table-row">
			<h3>Pricing, Stock &amp; Sales Channels</h3>
		</div>

		<div class="table-row">
			<label>Cost Price (<?=$this->config->item('currency');?>):</label>
			<input name="product_costprice" id="product_costprice" value="<?=set_value('product_costprice',$item->product_costprice);?>" class="textbox number" size="25"/> <span class="smallprint">(Exclude VAT)</span>
			<?=form_error('product_costprice');?>
		</div>

		<div class="table-row">
			<p align="right" class="smallprint">(Exclude VAT on all prices)</p>
		</div>
		
		<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 10px 10px 10px;">
			<head>
				<tr>
					<th width="30%">Sales Channel</th>
					<th width="5%"><center>Sell On</center></th>
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
					<td><label for="<?=$channel_field;?>"><?=$location->name;?></label></td>
					<td align="center">
						<?php
						if ($location->id == 1 && count($locations) == 1) {
						?>
						<input type="checkbox" name="fake_checkbox" checked="checked" disabled="disabled" />
						<input type="hidden" name="<?=$channel_field;?>" id="<?=$channel_field;?>" value="1" readonly="readonly" />
						<?php
						} else {
						?>
						<input type="checkbox" name="<?=$channel_field;?>" id="<?=$channel_field;?>" value="1" <?=is_checked('1', set_value($channel_field, $channel_field_value));?> />
						<?php } ?>
					</td>
					<td><span class="smallprint"><?=$location->note;?></span></td>
					<?php
					// Set the 'required' class by default on the product_price field. 
					$product_price_field_class = 'required';
					?>
					<td align="center">
						<input name="<?=$product_price_field;?>" id="<?=$product_price_field;?>" value="<?=set_value($product_price_field, $item->$product_price_field);?>" class="textbox number <?=$product_price_field_class;?> centered" size="8" />
						<?=form_error($product_price_field);?>
					</td>
					<td align="center">
						<input name="<?=$product_saleprice_field;?>" id="<?=$product_saleprice_field;?>" value="<?=set_value($product_saleprice_field, $item->$product_saleprice_field);?>" class="textbox number centered" size="8" />
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

	<?php
	// This feature doesn't work on IE9 or less, so hide it for these!
	$user_agent = $this->shopit->user_browser();

	// Only show this section if we're editing an existing item
	if ($this->uri->segment(2) == "edit" && ($user_agent->browser != 'explorer' && $user_agent->version >= 10)) {
	?>
	<div id="section-gallery" class="section section-closed">
	
		<div class="table-row">
			<h3>Photo Gallery &mdash; Upload Photo</h3>
			<p class="smallprint">For best results upload an image of at <strong>least 1000 pixels</strong> width &mdash; Shopit will resize accordingly.</p>
		</div>

		<div class="table-row">
			<input type="file" name="product_image[]" id="photo-select" />
			<?php
			$upload_url = ($this->uri->segment(3) != '') ? 'inventory/uploadimages/'.$this->uri->segment(3) : 'inventory/uploadimages';
			?>
			<input type="button" class="button" id="btn-photo-upload" value="Upload photo" rel="<?=site_url($upload_url);?>" />
			<span class="smallprint redtext">(Only jpg, png or gif filetypes are permitted)</span>
		</div>

		<div class="table-row">
			<h3>Current Photos</h3>
			<p class="smallprint">Drag images to preferred order. <strong class="redtext">Remember to click save after you have uploaded or made changes to your photos.</strong></p>
		</div>

		<div id="gallery" style="padding:6px 10px;position:relative;">
			
			<input type="hidden" name="gallery_product_id" value="<?=$item->product_id;?>" />

			<ul id="sortable-gallery">
				<?php
				$i = 0;
				$item_image = explode(';', $item->product_image);
				
				foreach ($item_image as $image) {
		
					if ($image != ''):
					
						$i++;
				?>
				<li class="sortable-image">
					<div class="gallerythumb" style="background-image:url('<?=site_url('image/resize/'.$image.'/100/100?'.date('YmdHis'));?>');" data-src="<?=site_url('image/resize/'.$image.'/100/100');?>">
						<img style="display:none;" src="<?=site_root("uploads/$image");?>" />
						<?php
						//Check if this is a child item so we can append to the url below
						$is_variant = ($this->uri->segment(4) == "variation") ? "/variation" : null;
						?>
						<input type="hidden" name="gallery_product_image[]" value="<?=$image;?>" />
					</div>
					<div class="sortable-image-controls">
						<a href="<?=site_url('inventory/removeimage/'.$item->product_id.'/'.$image);?>" class="ajaxdelete valign"><img src="<?=template_directory('assets/images/icon-cross.png');?>" alt="Delete" title="Delete" /></a>
					</div>
				</li>
				<?php 
					endif;
				} 
				?>
			</ul>
			
			<br clear="all" />
		</div>
		
	</div>
	<?php } ?>

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

	<div id="section-filters" class="section section-closed">
		<?php $this->load->view('modules/filters/inventory_section'); ?>
	</div>

	<div id="section-categories" class="section section-closed">
		
		<div class="table-row">
			<h3>Additional Categories</h3>
			<select name="categories" multiple="multiple" class="multiple-dropdown">
				<?php foreach ($categories as $xcat): ?>
				<option value="<?=$xcat->cat_id;?>"<?=disabled($xcat->cat_id,$item->cat_id);?>><?=str_replace(' & ',' &amp; ',$xcat->cat_name);?></option>
				<?php endforeach; ?>
			</select>

			<div class="multiple-dropdown-controls">
				<input type="button" class="button" id="add_category" value="Add category &raquo;" />
				<input type="button" class="button" id="remove_category" value="&laquo; Remove category" />
				<input type="hidden" name="xcategory_change" value="false" /><!-- Identify xcat changes (as true) -->
			</div>

			<select name="x_categories[]" multiple="multiple" class="multiple-dropdown">
				<?php 
				if($extracats > 0):
				foreach ($extracats as $chosencat): ?>
				<option value="<?=$chosencat->cat_id;?>"><?=$chosencat->cat_name;?></option>
				<?php endforeach;
				endif; ?>
			</select>
			
			
			<br clear="all" />

		</div>
		
	</div>

	<div id="section-attributes" class="section section-closed">
		
		<!-- Product attributes -->
		<div class="table-row">
			<h3>Product Attributes</h3>
			<span class="darkgrey">Enter attributes for this item e.g. width, height, technical specifications, etc.</span>
		</div>
	
		<ul id="sortable-productattributes">
		<?php 
		if ($attributes > 0):
		foreach ($attributes as $attribute): 
		?>
			<li class="table-row product-attribute">
				<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
				<input name="attribute_name[]" type="text" value="<?=$attribute->attribute_name;?>" class="textbox" size="30" /> =
				<input name="attribute_value[]" type="text" value="<?=$attribute->attribute_value;?>" class="textbox" size="30" />
				<input name="attribute_id[]" type="hidden" value="<?=$attribute->id;?>" />
				<input name="attribute_delete[]" type="hidden" value="false" />
				<a href="#" class="button removeattribute">X</a>
			</li>
		<?php 
		endforeach; 
		endif; ?>
		</ul>
		
		<div class="table-row minpadding">
			<label>Enter new attribute:</label>
			<input name="attribute_name_new" type="text" value="" class="textbox" size="30" <?=tooltip("e.g. Width");?> /> =
			<input name="attribute_value_new" type="text" value="" class="textbox" size="30" <?=tooltip("e.g. 20cm");?> />
			<a href="#" class="button addattribute">Add attribute</a>
		</div>
	
		<?php
		if (empty($attributes)):
		if ($attribute_sets > 0): 
		?>
		<div class="table-row">
			<label>Or</label>
			<select name="attribute_set" class="dropdown" id="loadAttributeSet">
				<option value="">Select an attribute set</option>
				<?php foreach ($attribute_sets as $set): ?>
				<option value="<?=$set->attribute_set_id;?>"><?=$set->attribute_set_label;?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php endif; 
		endif; ?>
	
	</div>

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
				<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
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
	
	<div id="section-optimisation" class="section section-closed">

		<div class="table-row">
			<h3>Search Engine Optimisation</h3>
		</div>
	
		<div class="table-row">
			<label>Page title:</label> 
			<input name="product_meta_title" id="product_meta_title" value="<?=set_value('product_meta_title',$item->product_meta_title);?>" class="textbox google-preview" size="75" <?=tooltip("Adding a short accurate page title can help your page get listed correctly in search engines. If you do not enter one here then the product name will be used instead. This title appears in the browser's title bar.");?> />
			<?=form_error('product_meta_title');?>
		</div>

		<div class="table-row">
			<label>Custom heading:</label> 
			<input name="product_custom_heading" id="product_custom_heading" value="<?=set_value('product_custom_heading',$item->product_custom_heading);?>" class="textbox" size="75" <?=tooltip("If you would prefer to have a custom heading displayed on the product page instead of the default product name, you can enter it here. This will appear as a H1 heading.");?> />
			<?=form_error('product_custom_heading');?>
		</div>

	
		<div class="table-row">
			<label>Page description:</label> 
			<textarea name="product_meta_description" id="product_meta_description" class="textbox google-preview" rows="3" <?=tooltip("A short description of no more than 25 words will help your page in the search engines.");?> ><?=set_value('product_meta_description',$item->product_meta_description);?></textarea>
			<?=form_error('product_meta_description');?>
		</div>
	
		<div class="table-row">
			<label>Page keywords: <br/><span class="smallprint">(Separate with commas)</span></label> 
			<textarea name="product_meta_keywords" id="product_meta_keywords" class="textbox" rows="2" <?=tooltip("Add keywords to help the search engines identify the content of this page. Separate each keyword with a comma, e.g. petware, pet bowl...");?> ><?=set_value('product_meta_keywords',$item->product_meta_keywords);?></textarea>
			<?=form_error('product_meta_keywords');?>
		</div>

		<div class="table-row">
			<label>Custom tags:</label> 
			<textarea name="product_meta_custom" id="product_meta_custom" class="textbox <?=codebox();?>" rows="3" <?=tooltip("Use this field to enter any custom head tags.");?> ><?=set_value('product_meta_custom',$item->product_meta_custom);?></textarea>
			<?=form_error('product_meta_custom');?>
		</div>
		
		<div class="table-row">
			<h4 style="text-indent:180px;">Google Preview</h4>
		</div>
		
		<div class="table-row" id="google-preview"></div>

	</div>

	<input type="hidden" name="product_id" value="<?=$item->product_id;?>" />
	<input type="hidden" name="existing_url" value="<?=get_product_slug($item->product_id)?>" />

	</div>

</div>

<div id="fixed-sidebar">
<div id="sidebar">
	<h3>Edit product information</h3>
	<p>Choose the section to edit:</p>
	<ul id="sections">
		<li><a href="#section-information" class="button">Product information</a></li>
		<li><a href="#section-pricing" class="button button-off">Pricing, Stock &amp; Channels</a></li>
		<li><a href="#section-offers" class="button button-off">Coupons &amp; Special Offers</a></li>
		<?php
		// Only show this section if we're editing an existing item
		if ($this->uri->segment(2) == "edit" && ($user_agent->browser != 'explorer' && $user_agent->version >= 10)) {
		?>
		<li><a href="#section-gallery" class="button button-off">Photo Gallery</a></li>
		<?php } ?>
		<?php if ($custom_field_templates > 0) { ?>
		<li><a href="#section-customfields" class="button button-off">Custom Fields</a></li>
		<?php } ?>
		<?php if (library_exists('filters')) { ?>
		<li><a href="#section-filters" class="button button-off">Search Filters</a></li>
		<?php } ?>
		<li><a href="#section-categories" class="button button-off">Additional categories</a></li>
		<li><a href="#section-attributes" class="button button-off">Product attributes</a></li>
		<li><a href="#section-options" class="button button-off">Product options</a></li>
		<li><a href="#section-optimisation" class="button button-off">Search engine optimisation</a></li>
	</ul>
</div>
</div>