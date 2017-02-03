<form name="basket" id="basket" action="<?=site_url('basket/update');?>" method="post">

<h2>My Basket</h2>

<p><input type="submit" name="submit" value="Update basket" /> <a href="{site_url}" class="button">Continue shopping</a></p>

<table cellpadding="0" cellspacing="0" width="100%" border="0" summary="Basket Summary">
	<thead>
		<tr>
			<th width="20">&nbsp;</th>
			<th width="25">&nbsp;</th>
			<th width="35%">Item</th>
			<th width="5%">Qty</th>
			<th width="85">Item Price <small>(Ex VAT)</small></th>
			<th width="230">Total Price</th>
		</tr>
	</thead>
	<tbody>

		{basket}
		<tr id="prod-{product_id}">
			<td align="center">
				<a href="{product_remove_link}" class="removeitem"><img src="/core/images/btn-delete.gif" width="15" height="15" alt="Remove item" title="Remove item" border="0"/></a>
			</td>
			<td align="center">
				<a href="{product_url}">{product_image}</a>
			</td>
			<td>
				<a href="{product_url}">{product_name}</a><br/>
				<span class="basket-product-option">Product Code: {product_no} 
					{product_options}
						{option_no}. {option_label}: {option_value}{option_delimiter}
					{/product_options}
				</span> 
				<small>{product_offer}</small>
				<br /><small>In stock: {product_stock_level}</small>
			</td>
			<td>{product_qty_select}</td>
			<td>{product_price_exvat}</td>
			<td>
				{product_linetotal}
				<input type="hidden" name="product_id[]" value="{product_id}" />
				<input type="hidden" name="basket_id[]" value="{basket_id}" />
			</td>
		</tr>
		{/basket}
		
		<?php if ($discount != "") { ?>
		<tr class="basket-item basket-discount">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>Discount:</td>
			<td>{discount}</td>
		</tr>
		<?php } ?>

		<tr class="basket-shipping">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>Shipping:</td>
			<td>
				<select name="ShippingCalc" id="ShippingCalc" class="basket-dropdown">
					{shipping_calc}
					<option value="{shipping_country_name}"{is_shipping_default}>{shipping_country_name}</option>
					{/shipping_calc}
				</select>
			</td>
		</tr>

		<tr class="basket-footer">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td id="myShipping">
				<select name="shipping_select" id="shipping_select" class="basket-dropdown">
				{shipping}
				</select>
			</td>
		</tr>

		<tr class="basket-footer">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>VAT:</td>
			<td>{tax}</td>
		</tr>

		<tr class="basket-total">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td width="35%">&nbsp;</td>
			<td width="15%">&nbsp;</td>
			<td><strong>Total:</strong></td>
			<td><strong id="myTotal">{total}</strong> <span class="feedback"></span></td>
		</tr>

	</tbody>
</table>

</form>

{shopit:coupons}

{shopit:myaccount}

{shopit:myaccount:login}

<form name="checkout" id="formCheckout" action="<?=site_url('checkout');?>" method="post">

	{shopit:customer_details}
	
	<section id="cartProceed">
		<p id="TCAgreement" class="valign">I have read, agree and understood the <a href="{link_to_terms}" target="_blank">Terms &amp; Conditions</a><input type="hidden" name="AgreeTC" value="Y" /></p>	
		
		<h4 id="youPay">{to_pay}</h4>

		<p id="paymentMethod">
			<strong style="padding-right:20px;">Payment method:</strong> 
			{payment_options}
			<input type="hidden" name="shipping" id="shipping" value="{shipping_encrypted}" />
			<input type="hidden" name="shipping_method" id="shipping_method" value="{shipping_method}" />
			<input type="hidden" name="vat" id="vat" value="{vat_encrypted}" />
			<input type="hidden" name="amount" id="amount" value="{total_encrypted}" />
			<input type="hidden" name="weight" id="weight" value="{weight_encrypted}" />
			<input type="hidden" name="discount" id="discount" value="{discount_encrypted}" />
			<input type="hidden" name="cat_ids" id="cat_ids" value="{cat_ids}" />
		</p>
	
	</section>

</form>