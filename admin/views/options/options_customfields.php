<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="4"><h2><?=$form_title;?> Custom fields</h2></td>
				</tr>
				<tr>
					<th width="35%">Title</th>
					<th width="35%">Template Tag</th>
					<th width="20%">Type</th>
					<th width="10%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($custom_fields > 0) {
			$i = 0; //(A) used for background colour 
			
			foreach($custom_fields as $item): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
				
				//Create the variable/tag
				$custom_field_label = str_replace('custom_', 'custom:', $item->custom_field_label);
				$custom_field_label = str_replace('-', '_', $custom_field_label);

			?>
				<tr class="<?=$post;?>">
					<td>
						<?=capitalise($item->custom_field_title);?>
						<?php
						if ($item->variants == 1) {
							echo '<span class="badge badge-grey">Variants</span>';
						}
						?>
					</td>
					<td>
						<?php
						if ($item->template_tag == 1) {
							$variant_snippet_prefix = ($item->variants == 1) ? 'variant_' : '';
							echo sprintf('<code class="snippet select-on-click">{%s%s}</code>', $variant_snippet_prefix, $custom_field_label);
						} else {
							echo "&mdash;";
						}
						?>
					</td>
					<td><?=capitalise($item->custom_field_type);?></td>
					<td align="center"><a href="<?=site_url($this->uri->segment(1). '/custom/' . $item->custom_field_id);?>#edit" class="button">Edit</a></td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="4" align="center">There are no custom fields setup.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3 id="edit">Edit custom field</h3>
			<?php } else { ?>
			<h3 id="edit">Create custom field</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Title:</label>
			<input type="text" name="custom_field_title" value="<?=set_value('custom_field_title',ucwords($edit->custom_field_title));?>" class="textbox match-title" size="75" autocomplete="off" maxlength="250" <?=tooltip('Enter a short title for this custom field so you can identify it, e.g. Warehouse Location Code.');?> />
			<?=form_error('custom_field_title');?>
		</div>

		<?php if (empty($edit)) { ?>
		<div class="table-row">
			<label>Tag:</label>
			<input type="text" name="custom_field_label" value="<?=set_value('custom_field_label');?>" class="textbox match-tag" size="75" autocomplete="off" maxlength="25" <?=tooltip('Enter a short unique tag for the custom field here. This will be used as an identifier within the database, e.g. location_code. Tags are automatically prepended with the word &quot;custom:&quot;.');?> />
			<?=form_error('custom_field_label');?>
		</div>
		<?php } ?>

		<div class="table-row">
			<label>Type:</label>
			<select name="custom_field_type" class="dropdown">
				<option value="single"<?=is_selected('single', $edit->custom_field_type);?>>Single line</option>
				<option value="multi"<?=is_selected('multi', $edit->custom_field_type);?>>Multi-line</option>
				<?php if ($this->uri->segment(1) == "inventory"): ?>
				<option value="editor"<?=is_selected('editor', $edit->custom_field_type);?>>Multi-line field with editor</option>
				<option value="image"<?=is_selected('image', $edit->custom_field_type);?>>Image</option>
				<?php endif; ?>
				<option value="date"<?=is_selected('date', $edit->custom_field_type);?>>Date</option>
				<option value="yes/no"<?=is_selected('yes/no', $edit->custom_field_type);?>>Yes/No</option>
				<option value="option"<?=is_selected('option', $edit->custom_field_type);?>>Option</option>
			</select>
			<input type="hidden" name="custom_field_id" value="<?=set_value('custom_field_id', $edit->custom_field_id);?>" />
		</div>

		<div class="table-row">
			<label>Options:</label>
			<textarea name="custom_field_default" class="textbox" rows="6" <?=tooltip('If you have selected the <em>option</em> field type enter each option on a new line.');?>><?=set_value('custom_field_default', $edit->custom_field_default);?></textarea>
			<?=form_error('custom_field_default');?>
		</div>
		
		<div class="table-row">
			<label>Template Tag:</label>
			<label class="reset" for="template_tag"><input type="checkbox" name="template_tag" id="template_tag" value="1" <?=is_checked(1, set_value('template_tag', $edit->template_tag));?> /> <span class="smallprint">Tick to enable this field as a template tag</span></label>
		</div>
		
		<?php
		if ($this->uri->segment(1) === 'inventory') {
		?>
		<div class="table-row">
			<label>Apply to variants?</label>
			<label class="reset" for="variants"><input type="checkbox" name="variants" id="variants" value="1" <?=is_checked(1, set_value('variants', $edit->variants));?> /> <span class="smallprint">Tick to apply this field to product variants only</span></label>
		</div>
		<?php
		} else {
		?>
		<input type="hidden" name="variants" value="0" />
		<?php } ?>
	</div>

</div>

<div id="sidebar">
	<h3>What are custom fields?</h3>
	<p>Custom fields are additional pieces of information you may wish to store alongside your product or order information.</p>
</div>