<div id="content">

<table cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<td colspan="6"><h2>Manage User Groups <a href="<?=site_url('users/groups/create');?>">Create group</a> <a href="<?=site_url('users');?>">Users List</a></h2></td>
		</tr>
		<tr>
			<th width="20%">Title (Type)</th>
			<th width="60%">Description</th>
			<th width="10%"><center>Members</center></th>
			<th width="10%">&nbsp;</th>
		</tr>
	</thead>

	<tbody>
	<?php
	$i = 0; //(A) used for background colour
	
	foreach($user_groups as $group) {
		$i++; //(A)
		$post = ($i&1) ? 'odd' : 'even';
	?>
	<tr class="table-row <?=$post;?>" id="<?=$user->uid;?>">
		<td>
			<span class="valign"><?=$group->group_title;?></span>
			<?php if ($group->is_locked == 1) { ?>
			<img src="<?=template_directory('assets/images/icon-archived.png');?>" alt="Locked" title="Locked" class="valign" />
			<?php } ?>
		</td>
		<td class="smallprint valign"><?=$group->group_description;?></td>
		<td align="center" class="valign"><?=$group->members;?></td>
		<td align="center" class="nowrap">
			<?php
			if ($group->is_locked == 0) {
			?>
			<a href="<?=site_url('users/groups/edit/'.base64_encode($this->encrypt->encode($group->group_id)));?>" class="button">Permissions</a>
			<?php } else { ?>
			&nbsp;
			<?php } ?>
		</td>
	</tr>
	<?php } ?>
	
	</tbody>
</table>

</div>

<div id="sidebar">
	<h3>Manage User Groups</h3>
	<p>Set up user groups here.</p>
</div>