<div id="content">
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="7"><h2>Customer Accounts</h2></td>
			</tr>
			<tr>
				<th width="10%">ID</th>
				<th width="15%">Surname</th>
				<th width="15%">First Name</th>
				<th width="30%">Email</th>
				<th width="5%"><center>Marketing</center></th>
				<th width="20%">Last Login</th>
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
	
			$pref_newsletter = ($item->pref_newsletter == 1) ? "Yes" : "No";
	?>
		<tr class="table-row <?=$post;?><?=$class;?>">
			<td><?=$item->account_id;?>
			<td><strong><?=$item->account_surname;?></strong></td>
			<td><?=$item->account_firstname;?></td>
			<td><?=$item->account_user;?></td>
			<td align="center"><?=$pref_newsletter;?></td>
			<td><?=nice_date($item->last_login, 'date');?></td>
			<td>
				<a href="<?=site_url('customers/edit/'.$item->account_id.$redirect);?>" class="button">edit</a>
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
	<form method="post" action="<?=site_url('customers/accounts');?>">
	<input type="hidden" name="filter" value="true" />
	<h3>Filter accounts</h3>
	<p>Show me only those accounts that match the following name or email address:</p>
	<p><input name="s_customername" value="<?=$s_customername;?>" maxlength="55" class="textbox" /></p>
	<p align="right">
		<input type="submit" name="submit" value="Filter Customers" class="button" />
	</p>
	</form>

	<form method="post" action="<?=site_url('customers/exportaccounts');?>">
		<h3>Export CSV file</h3>
		<p>If you would like to export the full list if customer accounts, click the "Export CSV" button below.</p>
		<p align="right">
			<input type="submit" name="export" value="Export CSV" class="button" />
		</p>	
	</form>	
	
</div>