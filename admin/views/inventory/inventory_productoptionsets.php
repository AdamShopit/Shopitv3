<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="3"><h2><?=$form_title;?></h2></td>
				</tr>
				<tr>
					<th width="30%">Set</th>
					<th width="70%">Description</th>
					<th width="1">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($productoption_sets > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($productoption_sets as $item): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td><?=$item->option_set_label;?></td>
					<td><?=$item->option_set_desc;?></td>
					<td>
						<ul class="actions">
						<li><a href="#" class="btn-action"><img src="<?=template_directory('assets/images/btn-action-arrow-down.png');?>" alt=""/></a>
							<ul>
								<li><a href="<?=site_url('inventory/productoptionsets/' . $item->option_set_id);?>">Edit set</a></li>
								<li><a href="<?=site_url('inventory/productoptions/' . $item->option_set_id);?>">Manage option</a></li>
							</ul>
						</li>
						</ul>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="3" align="center">There are no product options sets available.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3>Edit product option set</h3>
			<?php } else { ?>
			<h3>Create product option set</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Label:</label>
			<input type="text" name="option_set_label" value="<?=set_value('option_set_label', $edit->option_set_label);?>" class="textbox" size="35" maxlength="25" <?=tooltip('Enter a short unique label for the custom field here. This will be used as an identifier within the database, e.g. location code.');?> />
			<?=form_error('option_set_label');?>
		</div>

		<div class="table-row">
			<label>Description:</label>
			<textarea name="option_set_desc" <?=tooltip('Enter a short title for this custom field so you can identify it, e.g. Warehouse Location Code.');?> class="textbox" rows="3"><?=set_value('option_set_desc', $edit->option_set_desc);?></textarea>
			<?=form_error('option_set_desc');?>
			<input type="hidden" name="option_set_id" value="<?=set_value('option_set_id', $edit->option_set_id);?>" />
		</div>

	</div>

</div>

<div id="sidebar">
	<h3>What are product option sets?</h3>
	<p>These are sets used to list product options, e.g. clothing sizes, shoe sizes, etc. These sets will be made available when you add or edit an item in your inventory to save you typing this information in every time.</p>
</div>