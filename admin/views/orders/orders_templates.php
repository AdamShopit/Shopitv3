<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="4"><h2><?=$form_title;?> <a href="<?=site_url('orders/templates#create');?>">New Template</a></h2></td>
				</tr>
				<tr>
					<th width="35%">Title</th>
					<th width="20%">Type</th>
					<th width="20%">Channel</th>
					<th width="25%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($templates > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($templates as $template): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td><?=$template->title;?></td>
					<td><?=capfirst($template->type);?></td>
					<td>
						<?php
						$location = $this->inventory_model->getLocationByShortname($template->site);
						echo $location->name;
						?>
					</td>
					<td align="center">
						<a href="<?=site_url("orders/templates/$template->id#edit");?>" class="button">Edit</a>
						<a href="<?=site_url('orders/previewtemplate/' . $template->id);?>" class="button">Preview</a>
						<a href="<?=site_url('orders/deletetemplate/' . base64_encode($template->id));?>" class="button ajaxdelete" rel="Are you sure you want to delete this template?">Delete</a>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="4" align="center">You have not set up any order note templates yet.</td>
				</td>
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3 id="edit">Edit template</h3>
			<?php } else { ?>
			<h3 id="create">Create template</h3>
			<?php } ?>
		</div>

		<div class="table-row">
			<label>Channel:</label>
			<select name="site" class="dropdown">
			<?php foreach($channels as $channel) { ?>
				<option value="<?=$channel->shortname;?>" <?=is_selected($channel->shortname, set_value('site', $edit->site));?>><?=$channel->name;?></option>
			<?php } ?>
			</select>
		</div>

		<div class="table-row">
			<label>Note Type:</label>
			<select name="type" class="dropdown">
				<option value="dispatch" <?=is_selected('dispatch', set_value('dispatch', $edit->type));?>>Dispatch</option>
				<option value="invoice" <?=is_selected('invoice', set_value('invoice', $edit->type));?>>Invoice</option>
				<option value="other" <?=is_selected('other', set_value('other', $edit->type));?>>Other</option>
				<option value="packing" <?=is_selected('packing', set_value('packing', $edit->type));?>>Packing</option>
				<option value="promotional" <?=is_selected('promotional', set_value('promotional', $edit->type));?>>Promotional</option>
			</select>
		</div>
				
		<div class="table-row">
			<label>Title:</label>
			<input type="text" autocomplete="off" name="title" value="<?=set_value('title', $edit->title);?>" class="textbox" size="75" maxlength="250" placeholder="Add a title for this template..." />
			<?=form_error('title');?>
		</div>
		
		<div class="table-row">
			<label>Content:</label>
			<div class="editor">
				<textarea name="content" id="template_content" class="textbox <?=codebox();?>" rows="50" placeholder="Add the content of your template here as HTML..."><?=set_value('content', $edit->content);?></textarea>
			</div>
			<?=form_error('content');?>
		</div>

		<input type="hidden" name="id" value="<?=set_value('id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Packing Note Templates</h3>
	
	<p>Use these tags to insert data into your templates:</p>
	
	<p><strong>Order</strong></p>
	
	<ul class="info" style="font-size:12px;">
		<li title="The order reference">{order_ref}</li>
		<li title="Date of the order">{order_date}</li>
		<li title="Salutation of the billing contact e.g. Mr, Mrs, etc">{billing_title}</li>
		<li title="First name of the billing contact">{billing_firstname}</li>
		<li title="Last name of the billing contact">{billing_surname}</li>
		<li title="Billing contact's company">{billing_company}</li>
		<li title="Billing contact's address line 1">{billing_address1}</li>
		<li title="Billing contact's address line 2">{billing_address2}</li>
		<li title="Billing contact's city or town">{billing_city}</li>
		<li title="Billing contact's postal code">{billing_postcode}</li>
		<li title="Billing contact's country">{billing_country}</li>
		<li title="Salutation of the delivery contact e.g. Mr, Mrs, etc">{delivery_title}</li>
		<li title="First name of the delivery contact">{delivery_firstname}</li>
		<li title="Last name of the delivery contact">{delivery_surname}</li>
		<li title="Delivery contact's company">{delivery_company}</li>
		<li title="Delivery contact's address line 1">{delivery_address1}</li>
		<li title="Delivery contact's address line 2">{delivery_address2}</li>
		<li title="Delivery contact's city or town">{delivery_city}</li>
		<li title="Delivery contact's postal code">{delivery_postcode}</li>
		<li title="Delivery contact's country">{delivery_country}</li>
		<li title="Customer's email address">{customer_email}</li>
		<li title="Customer's phone number">{customer_phone}</li>
		<li title="Total of the line items">{order_total}</li>
		<li title="Total VAT of the order">{order_vat}</li>
		<li title="Shipping cost">{order_shipping}</li>
		<li title="Total of the whole order">{total}</li>
		<li title="Order discount">{order_discount}</li>
		<li title="Method of shipping">{shipping_method}</li>
		<li title="The current status of the order">{order_status}</li>
		<li title="The date of dispatch">{dispatch_date}</li>
		<li title="Any instructions the customer entered during checkout">{instructions}</li>
	</ul>
	
	<p><strong>Order Items</strong></p>
	
	<p>List of order items must be wrapped within the tags <em>{items}{/items}</em>.</p>
	
	<ul class="info">
		<li title="Starting tag">{items}</li>
		<li title="Product's code or sku">{product_no}</li>
		<li title="Name of the product purchased">{product_name}</li>
		<li title="Quantity of product purchased">{product_qty}</li>
		<li title="Price of one item">{product_price}</li>
		<li title="The total of the line item">{linetotal}</li>
		<li title="Product options (if any)">{product_options}</li>
		<li title="Closing tag">{/items}</li>
	</ul>
	
	<p><strong>Company/Settings</strong></p>
	
	<ul class="info">
		<li title="The stores currency">{currency}</li>
		<li title="Name of the store">{store_name}</li>
		<li title="The email address you receive store alerts on">{store_email}</li>
		<li title="The company name">{company_name}</li>
		<li title="Company address">{company_address}</li>
		<li title="Phone number of the company">{company_tel}</li>
		<li title="Fax number of the company">{company_fax}</li>
		<li title="Email address that all customer correspondence is made">{company_email}</li>
		<li title="The company registration details">{company_reg}</li>
	</ul>
	
	<p><strong>Snippets</strong></p>
	
	<p>Snippets can be included within notification templates by simply entering the appropriate tag as listed <a href="<?=site_url('pages/snippets');?>">here</a>.</p>
	
</div>