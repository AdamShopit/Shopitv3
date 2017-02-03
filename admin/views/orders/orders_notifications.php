<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="5"><h2><?=$form_title;?></h2></td>
				</tr>
				<tr>
					<th width="15%">Status</th>
					<th width="45%">Subject <em><small>- note</small></em></th>
					<th width="15%">Recipients</th>
					<th width="10%"><center>Enabled</center></th>
					<th width="15%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($notifications > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($notifications as $email): 
		
				$recipients = array();
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td>
						<span class="light valign" style="background-color:<?=$email->color;?>;"></span>
						<span class="valign"><?=$email->label;?></span>
					</td>
					<td>
						<?=$email->subject;?>
						<?php if ($email->note != "") { ?>
						<span class="smallprint">&mdash; <?=$email->note;?></span>
						<?php } ?>
					</td>
					<td>
						<?php 
						if ($email->customer > 0) {
							$recipients[] = '<span class="badge badge-mgrey">Customer</span>';
						}
						
						if ($email->admin > 0) {
							$recipients[] = '<span class="badge badge-mgrey" title="'.$email->email.'">'.$email->firstname.'</span>';
						}
						
						$sendto = implode(" ", $recipients);
						echo $sendto;
						?>
					</td>
					<td align="center"><?=status($email->enabled, 1);?></td>
					<td align="center">
						<a href="<?=site_url("orders/notifications/$email->id#edit");?>" class="button">Edit</a>
						<a href="<?=site_url('orders/deletenotification/' . $email->id);?>" class="button ajaxdelete" rel="Are you sure you want to delete this notification?">Delete</a>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="4" align="center">You have not set up any status update notifications yet.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3 id="edit">Edit notification</h3>
			<?php } else { ?>
			<h3>Create notification</h3>
			<?php } ?>
		</div>

		<div class="table-row">
			<label>Enable notification:</label>
			<select name="enabled" class="dropdown">
				<option value="1" <?=is_selected('1', $edit->enabled);?>>Yes</option>
				<option value="0" <?=is_selected('0', $edit->enabled);?>>No</option>
			</select>
		</div>
		
		<div class="table-row">
			<label>Applicable status:</label>
			<select name="status_id" class="dropdown">
			<?php foreach($statuses as $status) { ?>
				<option value="<?=$status->id;?>" <?=is_selected($status->id, set_value('status_id', $edit->status_id));?>><?=$status->label;?></option>
			<?php } ?>
			</select>
		</div>
		
		<div class="table-row">
			<?php
			//Default as ticked, if this is an "Create notification" form
			$customer_field_value = ($edit->customer == NULL) ? 1 : $edit->customer;
			?>
			<label>Send to:</label>
			<label class="reset" for="customer"><input type="checkbox" name="customer" id="customer" value="1" <?=is_checked(1, set_value('customer', $customer_field_value));?> /> Customer</label>
		</div>

		<div class="table-row">
			<label>&nbsp;</label>
			<select name="admin" class="dropdown">
				<option value="0">Do not send to Admin User</option>
				<?php foreach($users as $user) { ?>
				<option value="<?=$user->uid;?>" <?=is_selected($user->uid, set_value('admin', $edit->admin));?>><?=$user->firstname;?> (<?=$user->email;?>)</option>
				<?php } ?>
			</select>
		</div>
		
		<div class="table-row">
			<label>Email subject:</label>
			<input type="text" autocomplete="off" name="subject" value="<?=set_value('subject', $edit->subject);?>" class="textbox" size="75" maxlength="250" placeholder="Add a subject line for your email..." />
			<?=form_error('subject');?>
		</div>
		
		<div class="table-row">
			<label>Email body:</label>
			<div class="editor">
				<textarea name="body" id="body" class="textbox <?=codebox();?>" rows="50" placeholder="Add the content of your email here as HTML..."><?=set_value('body', $edit->body);?></textarea>
			</div>
			<?=form_error('body');?>
		</div>

		<div class="table-row">
			<label>Note:</label>
			<input type="text" autocomplete="off" name="note" value="<?=set_value('note', $edit->note);?>" class="textbox" size="75" maxlength="250" placeholder="Add a note for your reference..." />
			<?=form_error('note');?>
		</div>

		<input type="hidden" name="id" value="<?=set_value('id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Order Notifications</h3>
	
	<p>Use these tags to insert data into your email templates:</p>
	
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