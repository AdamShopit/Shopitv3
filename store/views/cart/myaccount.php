<?php
//If user is logged in, display 
if ($this->myaccount->user_logged_in()):
?>
<h2>Hello <?=$this->myaccount->get_info('firstname');?>! 
	<a href="<?=site_url('store/myaccount/edit');?>" class="btnAccountEdit">Edit your details</a>
	<a href="<?=site_url('store/myaccount/logout');?>" class="btnAccountEdit">Logout</a>
</h2>

<h3>Your Recent Orders (last 90 days)</h3>
<?php 
if (!empty($orders)):
foreach($orders as $order): 

$date = new DateTime($order->order_date);
$order_date = $date->format('j F Y');

?>
<table cellpadding="0" cellspacing="0" width="100%" border="0" summary="Basket Summary" class="baskettable ordertable">
	<tbody>
	<tr class="basket-item">
		<td width="300" class="ordertable-details">
			<small>Order placed:</small><br/>
			<span class="ordertable-date"><?=$order_date;?></span><br/>
			<a href="<?=site_url('store/myaccount/vieworder/' . $order->order_id);?>">View order details</a><br/><br/>
			
			Order Number: <a href="<?=site_url('store/myaccount/vieworder/' . $order->order_id);?>"><?=$order->order_ref;?></a><br/>
			Order Total: <span class="ordertable-total"><?=money($order->transaction_total, true, true, false);?></span>
		</td>
		<td>
			<a href="<?=site_url('store/myaccount/vieworder/' . $order->order_id);?>" class="btnAccount" style="float:right; margin-top:-5px;">View details</a>
			<?php
			if ($order->order_status == 'Completed' || $order->order_status == 'Dispatched'):
				$status_class = 'ordertable-status-success'; 
			else:	
				$status_class = 'ordertable-status-failed'; 
			endif;

			if (!empty($order->order_status)):
				$order_status = $order->order_status;
			else:
				$order_status = 'Cancelled checkout';
			endif;
			?>
			<h3 class="ordertable-status <?=$status_class;?>"><?=$order_status;?></h3>
		</td>
	</tr>
	</tbody>
</table>
	<?php 
	endforeach;
	else:
	?>
	<p class="baskettable ordertable">You have not made any orders within the last 90 days.</p>
	<?php endif; ?>
<?php 
else: 
	echo $this->myaccount->display_login_box();
endif;?>