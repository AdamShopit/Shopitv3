<form action="{paypal_url}" method="post" name="frmProcess">	

<input type="hidden" name="cmd" value="_cart" />
<input type="hidden" name="upload" value="1" />
<input type="hidden" name="quantity" value="1" />
<input type="hidden" name="invoice" value="{orderid}" />

{items}
<input type="hidden" name="{label_item_name}" value="{item_name}" />
<input type="hidden" name="{label_item_number}" value="{item_number}" />
<input type="hidden" name="{label_qty}" value="{item_qty}" />
<input type="hidden" name="{label_amount}" value="{item_amount}" />
{/items}

<input type="hidden" name="shipping_1" value="{shipping}" />
<input type="hidden" name="tax_cart" value="{tax}" />

<input type="hidden" name="business" value="{business}" />
<input type="hidden" name="lc" value="GB" />

<input type="hidden" name="currency_code" value="{currency_code}" />
<input type="hidden" name="notify_url" value="{notify_url}" />
<input type="hidden" name="return" value="{return_url}" />
<input type="submit" name="submit" value="Click here if you're not automatically redirected... " />

</form>