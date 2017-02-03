<?php
/* ! Shouldn't need to change this file too much! */
?>
<section id="BillingDetails">

	<h3>Billing Address</h3>

	<div>
		<label>Title:</label>
	<?php
	$BillingTitle = array(
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
	
	echo form_dropdown('BillingTitle', $BillingTitle, $myacc_title, 'id="BillingTitle" class="cart-dropdown"');
	?>
	</div>

	<div>
		<label>First name:</label>
		<input type="text" name="BillingFirstname" id="BillingFirstname" class="required  <?=form_error('BillingFirstname'); ?>" maxlength="20" size="75" value="<?=set_value('BillingFirstname',$myacc_firstname);?>" />
	</div>

	<div>
		<label>Surname:</label>
		<input type="text" name="BillingSurname" id="BillingSurname" class="required  <?=form_error('BillingSurname'); ?>" maxlength="20" size="75" value="<?=set_value('BillingSurname',$myacc_surname);?>" />
	</div>

	<div>
		<label>Company:</label>
		<input type="text" name="BillingCompany" id="BillingCompany" class=" <?=form_error('BillingCompany'); ?>" maxlength="45" size="75" value="<?=set_value('BillingCompany',$myacc_company);?>" />
	</div>

	<div>
		<label>Address:</label>
		<input type="text" name="BillingAddress1" id="BillingAddress1" class="required  <?=form_error('BillingAddress1'); ?>" maxlength="65" size="75" value="<?=set_value('BillingAddress1',$myacc_address1);?>" />
	</div>
	
	<div>
		<label>&nbsp;</label>
		<input type="text" name="BillingAddress2" id="BillingAddress2" class="" maxlength="65" size="75" value="<?=set_value('BillingAddress2',$myacc_address2);?>" />
	</div>

	<div>
		<label>Town/City:</label>
		<input type="text" name="BillingCity" id="BillingCity" class="required  <?=form_error('BillingCity'); ?>" maxlength="35" size="75" value="<?=set_value('BillingCity',$myacc_city);?>" />
	</div>

	<div>
		<label>Postal code:</label>
		<input type="text" name="BillingPostcode" id="BillingPostcode" class="required  <?=form_error('BillingPostcode'); ?>" maxlength="10" size="75" value="<?=set_value('BillingPostcode',$myacc_postcode);?>" />
	</div>

	<div>
		<label>Country:</label>
		<?php
		//Start: myaccounts billing country..
		if (library_exists('myaccount')){?>
		<select name="BillingCountry" id="BillingCountry" class="cart-dropdown">
			<?php
			foreach ($countries as $billingcountry){	

				//user logged in
				if ($this->myaccount->user_logged_in()){
					if ( $billingcountry['country_name'] == $this->myaccount->get_info('country') ){
						$this_billing_country = ' selected="selected"';
					} else {
						$this_billing_country = '';
					};
				//user not logged in, use default
				} else {
					$this_billing_country = $billingcountry['is_default'];
				}
				
			?>
			<option value="<?=$billingcountry['country_name'];?>"<?=$this_billing_country;?>><?=$billingcountry['country_name'];?></option>
			<?php }; ?>
		</select>
		<?php } else { ?>
		<select name="BillingCountry" id="BillingCountry" class="cart-dropdown">
			{countries}
			<option value="{country_name}"{is_default}>{country_name}</option>
			{/countries}
		</select>
		<?php 
		};
		//End: myaccounts billing country.
		?>
	</div>

	<div>
		<label>Email:</label>
		<input type="text" name="Email" class="required email  <?=form_error('Email'); ?>" maxlength="45" size="75" value="<?=set_value('Email',$myacc_user);?>" />
	</div>

	<span id="carttable-email"></span>

	<div>
		<label>Telephone:</label>
		<input type="text" name="Phone" class="required  <?=form_error('Phone'); ?>" maxlength="20" size="75" value="<?=set_value('Phone',$myacc_phone);?>" />
	</div>

	<?php if (library_exists('myaccount')):?>
	<?php if ($this->myaccount->user_logged_in()!=TRUE): ?>
	<div>
		<label>Same for delivery?</label>
		<input type="checkbox" name="SameforDelivery" id="SameforDelivery" value="Yes" />
	</div>
	<?php else:endif; ?>
	<?php else: ?>
	<div>
		<label>Same for delivery?</label>
		<input type="checkbox" name="SameforDelivery" id="SameforDelivery" value="Yes" />
	</div>
	<?php endif; ?>

	<?php if (library_exists('myaccount') && $this->myaccount->user_logged_in()!=TRUE): ?>
	<h3>Optional: Create Account</h3>
	
	<div>
		<label>Enter a password:</label>
		<input type="password" name="Password" id="Password" class=" <?=form_error('Password'); ?>" maxlength="35" size="35" value=""/>
	</div>

	<div>
		<label>Confirm password:</label>
		<input type="password" name="cPassword" class=" <?=form_error('cPassword'); ?>" maxlength="35" size="35" value=""/>
	</div>
	<?php endif; ?>
	
</section>

<section id="DeliveryDetails">

	<h3>Delivery Address</h3>

	<div>
		<label>Title:</label>
	<?php
	$DeliveryTitle = array(
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
	
	echo form_dropdown('DeliveryTitle', $DeliveryTitle, $myacc_title, 'id="DeliveryTitle" class="cart-dropdown"');
	?>

	</div>

	<div>
		<label>First name:</label>
		<input type="text" name="DeliveryFirstname" id="DeliveryFirstname" class="required  <?=form_error('DeliveryFirstname'); ?>" maxlength="20" size="75" value="<?=set_value('DeliveryFirstname',$myacc_firstname);?>" />
	</div>

	<div>
		<label>Surname:</label>
		<input type="text" name="DeliverySurname" id="DeliverySurname" class="required  <?=form_error('DeliverySurname'); ?>" maxlength="20" size="75" value="<?=set_value('DeliverySurname',$myacc_surname);?>" />
	</div>

	<div>
		<label>Company:</label>
		<input type="text" name="DeliveryCompany" id="DeliveryCompany" class=" <?=form_error('DeliveryCompany'); ?>" maxlength="45" size="75" value="<?=set_value('DeliveryCompany',$myacc_company);?>" />
	</div>

	<div>
		<label>Address:</label>
		<input type="text" name="DeliveryAddress1" id="DeliveryAddress1" class="required  <?=form_error('DeliveryAddress1'); ?>" maxlength="65" size="75" value="<?=set_value('DeliveryAddress1',$myacc_address1);?>" />
	</div>
	
	<div>
		<label>&nbsp;</label>
		<input type="text" name="DeliveryAddress2" id="DeliveryAddress2" class="" maxlength="65" size="75" value="<?=set_value('DeliveryAddress2',$myacc_address2);?>" />
	</div>

	<div>
		<label>Town/City:</label>
		<input type="text" name="DeliveryCity" id="DeliveryCity" class="required  <?=form_error('DeliveryCity'); ?>" maxlength="35" size="75" value="<?=set_value('DeliveryCity',$myacc_city);?>" />
	</div>

	<div>
		<label>Postal code:</label>
		<input type="text" name="DeliveryPostcode" id="DeliveryPostcode" class="required  <?=form_error('DeliveryPostcode'); ?>" maxlength="10" size="75" value="<?=set_value('DeliveryPostcode',$myacc_postcode);?>" />
	</div>


	<div>
		<label>Country:</label>
		<?php
		//Start: myaccounts shipping country..
		if (library_exists('myaccount')):?>
		<select name="DeliveryCountry" id="DeliveryCountry" class="cart-dropdown">
			<?php
			foreach ($shipping_countries as $shippingcountry): 			
				//user logged in
				if ($this->myaccount->user_logged_in()):
					if ( $shippingcountry['shipping_country_name'] == $this->myaccount->get_info('country') ):
						$this_shipping_country = ' selected="selected"';
					else:
						$this_shipping_country = '';
					endif;
				//user not logged in, use default
				else:
					$this_shipping_country = $shippingcountry['is_shipping_default'];
				endif;
			?>
			<option value="<?=$shippingcountry['shipping_country_name'];?>"<?=$this_shipping_country;?>><?=$shippingcountry['shipping_country_name'];?></option>
			<?php endforeach; ?>
		</select>
		<?php else: ?>
		<select name="DeliveryCountry" id="DeliveryCountry" class="cart-dropdown">
			{shipping_countries}
			<option value="{shipping_country_name}"{is_shipping_default}>{shipping_country_name}</option>
			{/shipping_countries}
		</select>
		<?php 
		endif; 
		//End: myaccounts shipping country.
		?>
		<span class="feedback"></span>
	</div>

</section>

<section id="AdditionalInstructions">

	<h3>Additional Instructions</h3>
	
	<p>Please enter any special instructions relating to your order below. If the item is being delivered to a different address please let us know if you would prefer for the receipt not to be included in the delivery.</p>
	
	<div>
		<textarea name="Instructions" cols="100" rows="5"><?=set_value('Instructions');?></textarea>
	</div>

</section>

<section>	
	<?php if (library_exists('myaccount') && $this->myaccount->user_logged_in()!=TRUE) { ?>
	<h3>Newsletter Preferences</h3>
	<p><input type="checkbox" name="pref_newsletter" id="pref_newsletter" value="1" <?=is_checked('1', set_value('pref_newsletter', ''));?> /> <label for="pref_newsletter">Please keep me updated with special offers and new products via email.</label></p>
	<?php } elseif (!library_exists('myaccount')) { ?>
	<h3>Newsletter Preferences</h3>
	<p><input type="checkbox" name="pref_newsletter" id="pref_newsletter" value="1" <?=is_checked('1', set_value('pref_newsletter', ''));?> /> <label for="pref_newsletter">Please keep me updated with special offers and new products via email.</label></p>
	<?php } ?>

</section>
