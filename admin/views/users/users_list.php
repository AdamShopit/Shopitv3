<div id="content">

<table cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<td colspan="6"><h2>Manage Users <a href="<?=site_url('users/create');?>">Create user</a> <a href="<?=site_url('users/groups');?>">Manage User Groups</a></h2></td>
		</tr>
		<tr>
			<th width="15%">First name</th>
			<th width="15%">Surname</th>
			<th width="15%">Username</th>
			<th width="25%">Email</th>
			<th width="15%">Group</th>
			<th width="15%">&nbsp;</th>
		</tr>
	</thead>

	<tbody>
<?php
	$i = 0; //(A) used for background colour
	
	foreach($users as $user):

		$i++; //(A)
		
		if ($i&1) { $post = 'odd'; } 
		else { $post = 'even'; } //(A);
?>

	<tr class="table-row <?=$post;?>" id="<?=$user->uid;?>">
		<td><?=$user->firstname;?></td>
		<td><?=$user->surname;?></td>
		<td><span class="badge badge-mgrey" title="Usernames are case-insensitive &mdash; <?=$user->username;?>"><?=$user->username;?></span></td>
		<td><?=$user->email;?></td>
		<td>
		<?php if ($user->is_locked == 0){ ?>
			<a href="<?=site_url("users/groups/edit/".base64_encode( $this->encrypt->encode($user->group_id) ));?>"><?=ucwords($user->group_title);?></a>
		<?php } else {
			echo ucwords($user->group_title);
		} ?>
		</td>
		<td align="center" class="nowrap">
			<?php if ($user->uid > 1): ?>
			<a href="<?=site_url('users/update/'.base64_encode( $this->encrypt->encode($user->uid) ));?>" class="button">Update</a>
			<?php if ($this->session->userdata('uid') != $user->uid) { ?>
			<a href="<?=site_url('users/delete/'.base64_encode( $this->encrypt->encode($user->uid) ));?>" class="button ajaxdelete">Delete</a>
			<?php } ?>
			<?php endif; ?>
		</td>
	</tr>

<?php endforeach; ?>
	
	</tbody>
</table>

</div>

<div id="sidebar">
	<h3>Manage Users</h3>
	<p style="padding-bottom:16px;">Setup admin users, groups and permissions here.</p>
	<p align="right"><a href="<?=site_url('users/log');?>" class="button">View Access Log</a></p>
</div>