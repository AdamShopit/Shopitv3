<div id="content">

	<div class="table">
		<h2><?=$form_title;?></h2>
	
		<?php if (validation_errors()) { ?>
		<p class="error_notice">Sorry, we found some errors with the account information. Please check below.</p>
		<?php } ?>

		<div class="table-row">
			<h3>Address Details</h3>
		</div>
	
		<div class="table-row">
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
			
			echo form_dropdown('account_title', $billing_title, set_value('account_title', $customer->account_title), 'class="dropdown" tabindex="1"');
			?>
		</div>
	
		<div class="table-row">
			<label>First Name: <span class="red">*</span></label> <input name="account_firstname" id="account_firstname" value="<?=set_value('account_firstname', $customer->account_firstname);?>" class="textbox required" size="75" autocomplete="off" />
			<?=form_error('account_firstname');?>
		</div>

		<div class="table-row">
			<label>Surname: <span class="red">*</span></label> <input name="account_surname" id="account_surname" value="<?=set_value('account_surname', $customer->account_surname);?>" class="textbox required" size="75" autocomplete="off" />
			<?=form_error('account_surname');?>
		</div>

		<div class="table-row">
			<label>Company:</label> <input name="account_company" id="account_company" value="<?=set_value('account_company', $customer->account_company);?>" class="textbox" size="75" autocomplete="off" />
			<?=form_error('account_company');?>
		</div>

		<div class="table-row">
			<label>Address: <span class="red">*</span></label> <input name="account_address1" id="account_address1" value="<?=set_value('account_address1', $customer->account_address1);?>" class="textbox required" size="75" autocomplete="off" />
			<?=form_error('account_address1');?>
		</div>

		<div class="table-row">
			<label>&nbsp;</label> <input name="account_address2" id="account_address2" value="<?=set_value('account_address2', $customer->account_address2);?>" class="textbox" size="75" autocomplete="off" />
			<?=form_error('account_address2');?>
		</div>

		<div class="table-row">
			<label>City: <span class="red">*</span></label> <input name="account_city" id="account_city" value="<?=set_value('account_city', $customer->account_city);?>" class="textbox required" size="75" autocomplete="off" />
			<?=form_error('account_city');?>
		</div>

		<div class="table-row">
			<label>Postal Code: <span class="red">*</span></label> <input name="account_postcode" id="account_postcode" value="<?=set_value('account_postcode', $customer->account_postcode);?>" class="textbox required" size="75" autocomplete="off" />
			<?=form_error('account_postcode');?>
		</div>

		<div class="table-row">
			<label>Country: <span class="red">*</span></label>
			<select name="account_country" class="dropdown" tabindex="16">
			<?php 
			foreach ($countries as $country) { 
				if ($customer->account_country == "") {
					$delivery_country = default_country();
				} else {
					$delivery_country = $customer->account_country;
				}
			?>
				<option value="<?=$country->country_name;?>" <?=is_selected($country->country_name, set_value('account_country', $delivery_country));?>><?=$country->country_name;?></option>
			<?php } ?>
			</select>
			<?=form_error('account_country');?>
		</div>

		<div class="table-row">
			<label>Phone:</label> <input name="account_phone" id="account_phone" value="<?=set_value('account_phone', $customer->account_phone);?>" class="textbox required" size="55" autocomplete="off" />
			<?=form_error('account_phone');?>
		</div>

		<div class="table-row">
			<h3>Login Details</h3>
		</div>

		<div class="table-row">
			<label>Email: <span class="red">*</span></label> <input name="account_user" id="account_user" value="<?=set_value('account_user', $customer->account_user);?>" class="textbox required" size="55" autocomplete="off" /> <span class="smallprint">(This is the customer's username to login)</span>
			<?=form_error('account_user');?>
			<div id="account_checkuser"></div>
		</div>

		<div class="table-row">
			<label>Password:<?php if ($this->uri->segment(2) == "create") { ?> <span class="red">*</span><?php } ?></label> <input name="account_pass" id="account_pass" value="" class="textbox" size="55" maxlength="35" autocomplete="off" /> 
			<?php if ($this->uri->segment(2) == "edit") { ?>
			<span class="smallprint">(Leave blank to retain existing password)</span>
			<?php } ?>
			<?=form_error('account_user');?>
		</div>

		<?php if ($this->uri->segment(2) == "create") { ?>
		<div class="table-row">
			<label>&nbsp;</label>
			<input type="checkbox" name="notifyuser" value="yes" <?=is_checked('yes', set_value('notifyuser', 'yes'));?> /> Email user with their new account details?
		</div>
		<?php } ?>

		<div class="table-row">
			<h3>Marketing Preferences</h3>
		</div>
		
		<div class="table-row">
			<input type="checkbox" name="pref_newsletter" id="pref_newsletter" value="1" <?=is_checked(1, set_value('pref_newsletter', $customer->pref_newsletter));?> /> Please keep me updated with special offers and new products via email.
		</div>

		<?php 
		if ($this->uri->segment(2) == "create") { 
			$default_sync_option = 1;
		} else {
			$default_sync_option = 0;
		}
		?>
		
		<div class="table-row">
			<h3>Sync Orders</h3>
			<p class="smallprint">Syncing orders will attach any previous orders matching this email address to this account.</p>
		</div>

		<div class="table-row">
			<input type="checkbox" name="sync_orders" id="sync_orders" value="1" <?=is_checked(1, set_value('sync_orders', $default_sync_option));?> /> 
			Yes, synchronise orders for this customer.
		</div>
			
		<input type="hidden" name="account_id" value="<?=$customer->account_id;?>" />
	</div>

</div>

<div id="sidebar">
	<h3>Edit Customer Account</h3>
	<p>From here you can make changes to a customer's account including changing their password.</p>
</div>