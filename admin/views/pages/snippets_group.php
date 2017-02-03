<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="4"><h2><?=$form_title;?></h2></td>
				</tr>
				<tr>
					<th width="70%">Group Label</th>
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
					<td><?=$item->label;?></td>
					<td align="center">
						<a href="<?=site_url('pages/snippetgroups/' . $item->group_id);?>" class="button">Edit</a>
						<a href="<?=site_url('pages/deletesnippetgroup/' . $item->group_id);?>" class="button">Delete</a>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="3" align="center">You have not created any snippet groups yet.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3>Edit snippet group</h3>
			<?php } else { ?>
			<h3>Create snippet group</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Label:</label>
			<input type="text" name="label" value="<?=set_value('label', $edit->label);?>" class="textbox" size="75" maxlength="128" />
			<?=form_error('label');?>
		</div>

		<input type="hidden" name="group_id" value="<?=set_value('group_id', $edit->group_id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Snippet Groups</h3>
	<p>Groups are for admin purposes only and help to organise snippets into related groups.</p>
</div>