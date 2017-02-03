<h1>Register Account</h1>

<form name="formRegister" action="<?=site_url('store/myaccount/register');?>" method="post" id="formCheckout">

<?=$this->session->flashdata('notice');?>

<div>

	<h3>My Details</h3>

	<p>Complete your details below. Field's marked with * are required.</p>

	<div>
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

	<div>
		<label>First name: *</label>
		<input type="text" name="BillingFirstname" id="BillingFirstname" class="required cart-textbox" maxlength="20" value="<?=set_value('BillingFirstname',$this->myaccount->get_info('firstname'));?>" />
		<?=form_error('BillingFirstname'); ?>
	</div>

	<div>
		<label>Surname: *</label>
		<input type="text" name="BillingSurname" id="BillingSurname" class="required cart-textbox" maxlength="20" value="<?=set_value('BillingSurname',$this->myaccount->get_info('surname'));?>" />
		<?=form_error('BillingSurname'); ?>
	</div>

	<div>
		<label>Company: </label>
		<input type="text" name="BillingCompany" id="BillingCompany" class="cart-textbox" maxlength="45" value="<?=set_value('BillingCompany',$this->myaccount->get_info('company'));?>" />
		<?=form_error('BillingCompany'); ?>
	</div>

	<div>
		<label>Address: *</label>
		<input type="text" name="BillingAddress1" id="BillingAddress1" class="required cart-textbox" maxlength="65" value="<?=set_value('BillingAddress1',$this->myaccount->get_info('address1'));?>" />
		<?=form_error('BillingAddress1'); ?>
	</div>
	
	<div>
		<label>&nbsp;</label>
		<input type="text" name="BillingAddress2" id="BillingAddress2" class="cart-textbox" maxlength="65" value="<?=set_value('BillingAddress2',$this->myaccount->get_info('address2'));?>" />
	</div>

	<div>
		<label>Town/City: *</label>
		<input type="text" name="BillingCity" id="BillingCity" class="required cart-textbox" maxlength="35" value="<?=set_value('BillingCity',$this->myaccount->get_info('city'));?>" />
		<?=form_error('BillingCity'); ?>
	</div>

	<div>
		<label>Postal code: *</label>
		<input type="text" name="BillingPostcode" id="BillingPostcode" class="required cart-textbox" maxlength="10" value="<?=set_value('BillingPostcode',$this->myaccount->get_info('postcode'));?>" />
		<?=form_error('BillingPostcode'); ?>
	</div>

	<div>
		<label>Country:</label>
		<input name="Country" id="Country" class="cart-textbox" value="United Kingdom" disabled="disabled" />
		<input type="hidden" name="BillingCountry" value="United Kingdom" />
	</div>

	<div>
		<label>&nbsp;</label><small>The country can not be changed.</small>
	</div>

	<div>
		<label>Email: *</label>
		<input type="text" name="Email" class="required email cart-textbox" value="<?=set_value('Email',$this->myaccount->get_info('user'));?>" />
		<?=form_error('Email'); ?>
	</div>
	
	<div>
		<label>&nbsp;</label><small>This is your account username.</small>
	</div>

	<div>
		<label>Telephone: *</label>
		<input type="text" name="Phone" class="required cart-textbox" maxlength="20" value="<?=set_value('Phone',$this->myaccount->get_info('phone'));?>" />
		<?=form_error('Phone'); ?>
	</div>

	<h3>My Preferences</h3>
	
	<input type="checkbox" name="pref_newsletter" id="pref_newsletter" value="1" <?=is_checked( 1, set_value('pref_newsletter', $this->myaccount->get_info('pref_newsletter')) ); ?> /> <label for="pref_newsletter">Please keep me updated with special offers and new products via email.</label>

	<h3>Create Password</h3>
	
	<div>
		<label>Enter a password:</label>
		<input type="password" name="Password" id="Password" class="cart-textbox required" maxlength="35" value=""/>
		<?=form_error('Password'); ?>
	</div>

	<div>
		<label>Confirm password:</label>
		<input type="password" name="cPassword" id="cPassword" class="cart-textbox required" maxlength="35" value=""/>
		<?=form_error('cPassword'); ?>
	</div>


	<div>
		<label>&nbsp;</label>
		<input type="submit" name="submit" value="Complete Registration" />
	</div>

</div>
</form>
