<?php
if ( $this->config->item('can_access_dashwidgets_inventory') ) {
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="widget">

<?php 
$i = 0;
if ($lowStock[1] != ''):?>
<thead>
	<tr>
		<td colspan="4"><h2>Stock Alerts</h2></td>
	</tr>
	<tr>
		<th width="50%">Item</th>
		<th>Product Code</th>
		<th>Qty</th>
		<th>&nbsp;</th>
	</tr>
</thead>
<tbody>
<?php
foreach($lowStock as $stock): 

	$i++; //(A)
	
	if ($i&1) { $post = 'odd'; } 
	else { $post = 'even'; } //(A);

?>
	
	<tr class="<?=$post;?>">
		<td><?=$stock->product_name;?></td>
		<td><?=$stock->product_no;?></td>
		<td align="center"><?=$stock->product_qty;?></td>
		<td><a href="<?=site_url('inventory/edit/' . $stock->product_id);?>" class="button">update</a></td>
	</tr>

<?php endforeach;?>
</tbody>
<?php else: ?>
<thead>
	<tr>
		<td colspan="4"><h2>Stock Alerts <span class="notification"><?=$countLowStock;?></span></h2></td>
	</tr>
	<tr>
		<td colspan="4"><p>All stock items are up to date.</p></th>
	</tr>
</thead>
<?php
endif;
?>
</table>
<?php } ?>