<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="5">
					<h2>User Access Log</h2>
				</td>
			</tr>
			<tr>
				<th>Timestamp</th>
				<th><center>UID</center></th>
				<th>User</th>
				<th>IP Address</th>
				<th>Referer URL/User Agent</th>
			</tr>
		</thead>
	
		<tbody>
			<?php
			$i = 0; //(A) used for background colour
			
			foreach($logs as $log) {
				$i++;			
				$post = ($i&1) ? 'odd' : 'even';
			?>
			<tr>
				<td rowspan="2" class="nowrap" valign="top"><?=nice_date($log->timestamp);?></td>
				<td rowspan="2" align="center" valign="top"><?=$log->uid;?></td>
				<td rowspan="2" valign="top"><span class="badge badge-mgrey"><?=$log->username;?></span></td>
				<td rowspan="2" valign="top"><?=$log->ip_address;?></td>
				<td class="wrap"><code><?=$log->referer;?></code></td>
			</tr>
			<tr>
				<td class="collapse-border smallprint"><?=$log->user_agent;?></td>    			
			</tr>
			<?php } ?>
		</tbody>
	
	</table>

</div>

<div id="sidebar">
	<h3>User Access Log</h3>
	<p>A record of everytime an admin user logs in to the Shopit! system.</p>
	<p style="padding-bottom:16px;">If you would like a full export of the log file, please contact us.</p>
	<p align="right"><a href="<?=site_url('users');?>" class="button">Back to Users List</a></p>
</div>