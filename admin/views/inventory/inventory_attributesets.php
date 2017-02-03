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
			if ($attribute_sets > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($attribute_sets as $item): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td><?=$item->attribute_set_label;?></td>
					<td><?=$item->attribute_set_desc;?></td>
					<td>
						<ul class="actions">
						<li><a href="#" class="btn-action"><img src="<?=template_directory('assets/images/btn-action-arrow-down.png');?>" alt=""/></a>
							<ul>
								<li><a href="<?=site_url('inventory/attributesets/' . $item->attribute_set_id);?>">Edit set</a></li>
								<li><a href="<?=site_url('inventory/attributes/' . $item->attribute_set_id);?>">Manage attributes</a></li>
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
					<td colspan="3" align="center">There are no attribute sets available.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3>Edit attribute set</h3>
			<?php } else { ?>
			<h3>Create attribute set</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Label:</label>
			<input type="text" name="attribute_set_label" value="<?=set_value('attribute_set_label', $edit->attribute_set_label);?>" class="textbox" size="35" maxlength="25" <?=tooltip('Enter a short unique label for the custom field here. This will be used as an identifier within the database, e.g. location code.');?> />
			<?=form_error('attribute_set_label');?>
		</div>

		<div class="table-row">
			<label>Description:</label>
			<textarea name="attribute_set_desc" <?=tooltip('Enter a short title for this custom field so you can identify it, e.g. Warehouse Location Code.');?> class="textbox" rows="3"><?=set_value('attribute_set_desc', $edit->attribute_set_desc);?></textarea>
			<?=form_error('attribute_set_desc');?>
			<input type="hidden" name="attribute_set_id" value="<?=set_value('attribute_set_id', $edit->attribute_set_id);?>" />
		</div>

	</div>

</div>

<div id="sidebar">
	<h3>What are attribute sets?</h3>
	<p>Attribute sets are groups of product specifications. Setting up sets makes it quicker and easier for you to apply them to products without having to type the information in time and again. You can select the set when adding or editing an item in your inventory.</p>
</div>