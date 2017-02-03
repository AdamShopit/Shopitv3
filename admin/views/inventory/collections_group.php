<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="4"><h2><?=$form_title;?></h2></td>
				</tr>
				<tr>
					<th width="5%"><center>ID</center></th>
					<th width="65%">Group Label</th>
					<th width="30%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($groups > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($groups as $item): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td align="center"><?=$item->id;?></td>
					<td><?=$item->group_label;?></td>
					<td align="center">
						<a href="<?=site_url('collections/groups/' . $item->id);?>" class="button">Edit</a>
						<a href="<?=site_url('collections/deletegroup/' . $item->id);?>" class="button">Delete</a>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="3" align="center">You have not created groups yet.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3>Edit group label</h3>
			<?php } else { ?>
			<h3>Create group label</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Label:</label>
			<input type="text" name="group_label" value="<?=set_value('group_label', $edit->group_label);?>" class="textbox" size="75" maxlength="128" />
			<?=form_error('group_label');?>
		</div>

		<input type="hidden" name="group_id" value="<?=set_value('group_id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Collection Groups</h3>
	<p>Groups are for admin purposes only and help to organise collections in to related groups.</p>
</div>