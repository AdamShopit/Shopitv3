<div class="table-row">
	<h3>Search Filters</h3>
	<p>Select the filters that apply to this product. Select as many as is required.</p>
</div>
	
<div class="table-row" id="shopit-ajax-filter-section" data-link="<?=site_url('filters/load');?>">
<?php 
if ($filter_groups) {
	foreach($filter_groups as $group) {
?>
	<fieldset id="filtergroup-<?=$group->group_id;?>" class="fullwidth multiple">
		<legend><?=$group->label;?></legend>
		<div>
		<?php 
		$options = $this->filters_model->options($group->group_id);
		foreach($options as $option) { 
			
			// Set the field name
			$filter_field_name = "filter_$option->filter_id";
		?>
		<label for="<?=$filter_field_name;?>"><input type="checkbox" name="<?=$filter_field_name;?>" id="<?=$filter_field_name;?>" value="1" <?=is_checked('1', set_value($filter_field_name, $item->$filter_field_name));?> /> <?=$option->label;?></label>
		<?php } ?>
		</div>
		
		<label style="margin-top:-4px;">
			<input type="text" name="new_filter" data-link="<?=site_url('filters/addoption');?>" data-group-id="<?=$group->group_id;?>" placeholder="Enter label &crarr;" class="textbox" size="15" maxlength="150" autocomplete="off" <?=tooltip('Enter label and hit enter to apply');?> />
		</label>
	</fieldset>
<?php 
	} 
} else {
?>
	<p><strong>There are no filters setup for the selected category.</strong></p>
<?php } ?>
</div>