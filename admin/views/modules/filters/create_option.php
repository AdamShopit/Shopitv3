<li class="filter-group-option">
	<ul>
		<li><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable draggable-child" /></li>
		<li><input type="text" name="groups[<?=$this->input->post('key');?>][filters][<?=$this->input->post('n');?>][label]" value="<?=$this->input->post('filter_label');?>" placeholder="Enter option label" class="textbox valign" size="40" maxlength="150" /></li>
		<li><input type="color" name="groups[<?=$this->input->post('key');?>][filters][<?=$this->input->post('n');?>][colour]" value="<?=$this->input->post('filter_colour');?>" placeholder="#FFFFFF" class="textbox valign" size="7" maxlength="7" /></li>
		<li><a class="remove-filter-option button valign" href="#">Remove</a></li>
		<input type="hidden" name="groups[<?=$this->input->post('key');?>][filters][<?=$this->input->post('n');?>][id]" value="" />
	</ul>
</li>