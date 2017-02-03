<h2>Hello <?=$this->myaccount->get_info('firstname');?>! <a href="<?=site_url('store/myaccount');?>" class="btnAccountEdit">Back to My Account</a></h2>

<form name="formEditAccount" action="<?=site_url('store/myaccount/save');?>" method="post" id="formCheckout">

<?=$this->session->flashdata('notice');?>

<div class="carttable accountedit-table">

	<h3>Edit Account Details</h3>

	<p>Update your details below. Field's marked with * are required.</p>

	<div class="carttable-row">
		<label>Title:</label>
	<?php
	$BillingTitle = array(
	                  ''  	 => 'Please select',
	                  'Mr'   => 'Mr',
	                  'Ms'   => 'Ms',
	                  'Mrs'  => 'Mrs',
	                  'Miss' => 'Miss',
	                );
	
	echo form_dropdown('BillingTitle', $BillingTitle, $this->myaccount->get_info('title'), 'id="BillingTitle" class="cart-dropdown"');
	?>
	</div>

	<div class="carttable-row">
		<label>First name: *</label>
		<input type="text" name="BillingFirstname" id="BillingFirstname" class="required cart-textbox" maxlength="20" value="<?=set_value('BillingFirstname',$this->myaccount->get_info('firstname'));?>" />
		<?=form_error('BillingFirstname'); ?>
	</div>

	<div class="carttable-row">
		<label>Surname: *</label>
		<input type="text" name="BillingSurname" id="BillingSurname" class="required cart-textbox" maxlength="20" value="<?=set_value('BillingSurname',$this->myaccount->get_info('surname'));?>" />
		<?=form_error('BillingSurname'); ?>
	</div>

	<div class="carttable-row">
		<label>Company: </label>
		<input type="text" name="BillingCompany" id="BillingCompany" class="cart-textbox" maxlength="45" value="<?=set_value('BillingCompany',$this->myaccount->get_info('company'));?>" />
		<?=form_error('BillingCompany'); ?>
	</div>

	<div class="carttable-row">
		<label>Address: *</label>
		<input type="text" name="BillingAddress1" id="BillingAddress1" class="required cart-textbox" maxlength="65" value="<?=set_value('BillingAddress1',$this->myaccount->get_info('address1'));?>" />
		<?=form_error('BillingAddress1'); ?>
	</div>
	
	<div class="carttable-row">
		<label>&nbsp;</label>
		<input type="text" name="BillingAddress2" id="BillingAddress2" class="cart-textbox" maxlength="65" value="<?=set_value('BillingAddress2',$this->myaccount->get_info('address2'));?>" />
	</div>

	<div class="carttable-row">
		<label>Town/City: *</label>
		<input type="text" name="BillingCity" id="BillingCity" class="required cart-textbox" maxlength="35" value="<?=set_value('BillingCity',$this->myaccount->get_info('city'));?>" />
		<?=form_error('BillingCity'); ?>
	</div>

	<div class="carttable-row">
		<label>Postal code: *</label>
		<input type="text" name="BillingPostcode" id="BillingPostcode" class="required cart-textbox" maxlength="10" value="<?=set_value('BillingPostcode',$this->myaccount->get_info('postcode'));?>" />
		<?=form_error('BillingPostcode'); ?>
	</div>

	<div class="carttable-row">
		<label>Country:</label>
		<?php echo form_dropdown('BillingCountry', $countries, $countryCurrent, 'id="BillingCountry" class="cart-dropdown"'); ?>

	</div>

	<div class="carttable-row">
		<label>Email: *</label>
		<input type="text" name="Email" class="required email cart-textbox" value="<?=set_value('Email',$this->myaccount->get_info('user'));?>" />
		<?=form_error('Email'); ?>
	</div>
	
	<div class="carttable-row">
		<label>&nbsp;</label><small>Your email will also be your account username.</small>
	</div>

	<div class="carttable-row">
		<label>Telephone: *</label>
		<input type="text" name="Phone" class="required cart-textbox" maxlength="20" value="<?=set_value('Phone',$this->myaccount->get_info('phone'));?>" />
		<?=form_error('Phone'); ?>
	</div>

	<h3>Preferences</h3>
	
	<input type="checkbox" name="pref_newsletter" id="pref_newsletter" value="1" <?=is_checked( 1, set_value('pref_newsletter', $this->myaccount->get_info('pref_newsletter')) ); ?> /> <label for="pref_newsletter">Please keep me updated with special offers and new products via email.</label>

	<h3>Enter New Password</h3>
	
	<p><small>(Leave it blank to keep your existing one.)</small></p>
	
	<div class="carttable-row">
		<label>Enter a password:</label>
		<input type="password" name="Password" id="Password" class="cart-textbox" maxlength="35" value=""/>
		<?=form_error('Password'); ?>
	</div>

	<div class="carttable-row">
		<label>Confirm password:</label>
		<input type="password" name="cPassword" id="cPassword" class="cart-textbox" maxlength="35" value=""/>
		<?=form_error('cPassword'); ?>
	</div>


	<div class="carttable-row">
		<label>&nbsp;</label>
		<input type="submit" name="submit" value="Save" class="btnAccount" />
	</div>

</div>
</form>
