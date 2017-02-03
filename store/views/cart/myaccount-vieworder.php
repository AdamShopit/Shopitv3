<?php
//If user is logged in, display 
if ($this->myaccount->user_logged_in()):
?>
<h2>Hello <?=$this->myaccount->get_info('firstname');?>! 
	<a href="<?=site_url('store/myaccount/edit');?>" class="btnAccountEdit">Edit your details</a>
	<a href="<?=site_url('store/myaccount/logout');?>" class="btnAccountEdit">Logout</a>
</h2>

<h3>Your Order Details <a href="<?=site_url('store/myaccount');?>" class="btnAccount">Back to recent orders</a></h3>

<?php 
if (!empty($orders)):
foreach($orders as $order): 

$date = new DateTime($order->order_date);
$order_date = $date->format('j F Y');

if (!empty($order->order_status)):
	$order_status = $order->order_status;
else:
	$order_status = 'Cancelled checkout';
endif;
?>
<div class="carttable ordertable">
	<strong>Order Number:</strong> <?=$order->order_ref;?><br/>
	<strong>Order Placed:</strong> <?=$order_date;?><br/>
	<strong>Order Status:</strong> <?=$order_status;?><br/>
</div>

<table width="100%" cellpadding="0" cellspacing="0" border="0" class="carttable ordertable">
	<thead>
		<tr>
			<td width="50%"><h3>Billing Address</h3></td>
			<td width="50%"><h3>Delivery Address</h3></td>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td valign="top">
				<?=$order->billing_title;?> <?=$order->billing_firstname;?> <?=$order->billing_surname;?><br/>
				<?=$order->billing_address1;?><br/>
				<?=$order->billing_address2;?><br/>
				<?=$order->billing_city;?><br/>
				<?=$order->billing_postcode;?><br/>
				<?=$order->billing_country;?><br/><br/>
				<strong>Email:</strong> <?=$order->customer_email;?><br/>
				<strong>Phone:</strong> <?=$order->customer_phone;?><br/>
			</td>
			<td valign="top">
				<?=$order->delivery_title;?> <?=$order->delivery_firstname;?> <?=$order->delivery_surname;?><br/>
				<?=$order->delivery_address1;?><br/>
				<?=$order->delivery_address2;?><br/>
				<?=$order->delivery_city;?><br/>
				<?=$order->delivery_postcode;?><br/>
				<?=$order->delivery_country;?><br/>
			</td>
		</tr>
	</tbody>
</table>


<table width="100%" cellpadding="0" cellspacing="0" border="0" class="baskettable ordertable">
	<thead>
		<tr>
			<td colspan="5"><h3>Order Inventory</h3></td>
		</tr>
	</thead>
	
	<tbody>
		<tr>
			<th width="15%">Product No</th>
			<th width="40%">Product</th>
			<th width="15%">Qty</th>
			<th width="15%">Price</th>
			<th width="15%">Total</th>
		</tr>
		<?php
		$i = 0; //(A) used for background colour 
		
		if ($inventory) {
		
			foreach ($inventory as $item) {

				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			
				if (!empty($item->product_name)) {

				$product_options = str_replace('@', ': ', $item->product_options);
				$product_options = str_replace('{', '',$product_options);
				$product_options = str_replace('}', ' | ',$product_options);
				$product_options = preg_replace('@ \|\ $@','',$product_options); //Remove the last pipeline symbol
				$product_options = trim($product_options);
			
		?>
			
		<tr class="<?=$post;?> ordertable-row">
			<td><?=$item->product_no;?></td>
			<td><?=$item->product_name;?><br/><small class="blue"><?=$product_options;?></small></td>
			<td>
				<?php
				if ($item->free_qty > 0) {
					print $item->product_qty + $item->free_qty . ' ('.$item->free_qty.' FREE)';
				} else {
					print $item->product_qty;
				}
				?>
			</td>
			<td><?=money($item->product_price, true, true, false);?></td>
			<td><?=money($item->linetotal, true, true, false);?></td>
		</tr>
				
		<?php
				}
			}
			
		}			

		if (!empty($order->shipping_method)) :
			
			$shipping_method = '<br/><small>(' . trim($order->shipping_method) . ')</small>';
			
		endif;
		?>

		<?php if ($order->order_discount > 0) { ?>
		<tr class="ordertable-row ordertable-row-discount">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>Discount:</td>
			<td>(<?=money($order->order_discount, true, true, false);?>)</td>
		</tr>
		<?php } ?>		

		<tr class="ordertable-row">
			<td class="ordertable-rule">&nbsp;</td>
			<td class="ordertable-rule">&nbsp;</td>
			<td class="ordertable-rule">&nbsp;</td>
			<td class="ordertable-rule" valign="top">Shipping: <?=$shipping_method;?></td>
			<td class="ordertable-rule" valign="top"><?=money($order->order_shipping, true, true, false);?></td>
		</tr>
		
		<?php
		if ($order->order_vat > 0):
		?>
		<tr class="ordertable-row">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>VAT:</td>
			<td><?=money($order->order_vat, true, true, false); ?>
			</td>
		</tr>
		<?php
		endif;
		?>
		<tr class="ordertable-row">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td class="ordertable-total"><strong>Total:</strong></td>
			<td class="ordertable-total"><strong><?=money($order->order_total + $order->order_shipping + $order->order_vat, true, true, false);?></strong></td>
		</tr>
			
	</tbody>
	
</table>
<?php endforeach;
else:
?>
<p class="baskettable ordertable ordertable-status-failed">Sorry, no order details were found.</p>
<?php 
endif; else: 
	$this->myaccount->display_login_box();
endif;?>