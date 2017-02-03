<li class="filter-group"  data-key="<?=$this->input->post('n');?>">
	<a class="remove-filter-group" href="<?=site_url('filters/deletegroup');?>" data-group-id=""></a>
	<a class="toggle-filter-group" href="#">&uarr;</a>

	<div class="filter-group-label">
		<img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable draggable-parent" />
		<input type="text" name="groups[<?=$this->input->post('n');?>][group_label]" placeholder="Enter group label" class="textbox valign" size="35" />
		<select name="groups[<?=$this->input->post('n');?>][group_type]" class="valign">
			<option value="list">List</option>
			<option value="swatches">Colour Swatches</option>
			
		</select>
		<input type="hidden" name="groups[<?=$this->input->post('n');?>][group_id]" value="" />
	</div>

	<ul class="filter-group-options">
		<!-- Filter options would go here... -->		
	</ul><!-- END .filter-group-options -->
	
	<div class="filter-group-option-new">
		<input type="text" name="new_filter_label" placeholder="Add new option" class="textbox valign" size="40" maxlength="150" />
		<input type="color" name="new_filter_colour" placeholder="#FFFFFF" class="textbox valign" size="7" maxlength="7" />
		<a class="add-filter-option button valign" href="<?=site_url('filters/createoption');?>">Add Option</a>
	</div>

</li><!-- END .filter-group  -->