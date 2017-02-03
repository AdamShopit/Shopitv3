<div id="content">

		<table id="sortable-crosssellgroups" cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="4"><h2><?=$form_title;?></h2></td>
				</tr>
				<tr>
					<th width="30%">Group Label</th>
					<th width="60%">Type</th>
					<th width="10%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if (count($groups) > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($groups as $item) { 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td>
						<input type="hidden" name="id[]" value="<?=$item->id;?>" />
						<img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" />
						<?=$item->label;?>
					</td>
					<td><?=$item->type;?></td>
					<td align="center">
						<a href="<?=site_url("inventory/crosssellgroups/$item->id");?>" class="button">Edit</a>
					</td>
				</tr>
			<?php 
			} 
			} else {
			?>
				<tr>
					<td colspan="3" align="center">You have not set any groups yet.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3>Edit cross-sell group</h3>
			<?php } else { ?>
			<h3>Create cross-sell group</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Label:</label>
			<input type="text" name="label" value="<?=set_value('label', $edit->label);?>" class="textbox" size="75" maxlength="128" />
			<?=form_error('label');?>
		</div>

		<input type="hidden" name="group_id" value="<?=set_value('group_id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Cross-Sell Groups</h3>
	<p>Groups help to organise cross-sells into related sections on the product pages.</p>
	<p>In order for groups to be displayed within the store-front, the designer/developer would need to have set up the feature.</p>
</div>