<div id="content">
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="6"><h2>Customers</h2></td>
			</tr>
			<tr>
				<th width="20%">Name</th>
				<th width="20%">City</th>
				<th width="20%">Email</th>
				<th width="10%"><center>Orders</center></th>
				<th width="25%">Last Order</th>
				<th width="5%">&nbsp;</th>
			</tr>
		</thead>
	
		<tbody>
	<?php
		$i = 0; //(A) used for background colour 
		
		if ($customers > 0) {
		
		foreach($customers as $item): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
	
	?>
		
		<tr class="table-row <?=$post;?><?=$class;?>">
			<td><a href="<?=site_url("customers/view/$item->order_id/$redirect");?>"><?=$item->billing_title;?> <?=$item->billing_firstname;?> <?=$item->billing_surname;?></a></td>
			<td><?=$item->billing_city;?></td>
			<td><?=$item->customer_email;?></td>
			<td align="center"><?=$item->orders;?></td>
			<td><?=nice_date($item->last_order);?></td>
			<td>
				<a href="<?=site_url("customers/view/$item->order_id/$redirect");?>" class="button">View</a>
			</td>
		</tr>
	
	<?php 
	
		endforeach;
		} else {
	?>
		<tr>
			<td colspan="6" align="center">No customers could be found.</td>
		</tr>
	<?php } ?>
		</tbody>
	
		<tfoot>
			<tr>
				<td colspan="6"><?=$this->pagination->create_links();?></td>
			</tr>
		</tfoot>
	
	</table>
</div>

<div id="sidebar">
	<form method="post" action="<?=site_url('customers/index');?>">
	<input type="hidden" name="filter" value="true" />
	<h3>Filter customers</h3>
	<p>Show me only those customers that match the following name or email:</p>
	<p><input name="s_customername" value="<?=$s_customername;?>" maxlength="55" class="textbox" /></p>
	<p align="right">
		<input type="submit" name="submit" value="Filter Customers" class="button" />
	</p>
	</form>

	<form method="post" action="<?=site_url('customers/export');?>">
		<h3>Export CSV file</h3>
		<p>If you would like to export the full list if customers, click the "Export CSV" button below.</p>
		<p align="right">
			<input type="submit" name="export" value="Export CSV" class="button" />
		</p>	
	</form>	
	
</div>