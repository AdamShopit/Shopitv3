<div id="content">

	<div class="table">
	
		<h2><?=$form_title;?> <a href="#" id="filter-groups-collapse">Collapse/uncollapse all</a> <a id="filter-groups-create" href="<?=site_url('filters/creategroup');?>" rel="/admin/views/filters/create_group.php">Add new group</a> </h2>
		
		<?php
		if (count($filter_groups) < 1) {
		?>
		<div class="table-row" id="filters-welcome">
			<p><strong>No filters have been setup for this category.</strong></p>
		</div>
		<?php
		}
		?>

		<ul id="filter-groups">
			
			<?php 
			$i = 0;
			foreach ($filter_groups as $group) {
			
				$i++;
			?>
			<li class="filter-group" data-key="<?=$i;?>">
				<a class="remove-filter-group" href="<?=site_url('filters/deletegroup');?>" data-group-id="<?=$group->group_id;?>"></a>
				<a class="toggle-filter-group" href="#">&uarr;</a>

				<div class="filter-group-label">
					<img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable draggable-parent" />
					<input type="text" name="groups[<?=$i;?>][group_label]" value="<?=$group->label;?>" placeholder="Enter group label" class="textbox valign" size="35" />
					<select name="groups[<?=$i;?>][group_type]" class="valign">
						<option value="list" <?=is_selected('list', $group->type);?>>List</option>
						<option value="swatches" <?=is_selected('swatches', $group->type);?>>Colour Swatches</option>
					</select>
					<input type="hidden" name="groups[<?=$i;?>][group_id]" value="<?=$group->group_id;?>" />
				</div>

				<ul class="filter-group-options">
					
					<?php
					$o = 0;
					$options = $this->filters_model->options($group->group_id);
					foreach($options as $option) { 
						$o++;
					?>
					<li class="filter-group-option">
						<ul>
							<li><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable draggable-child" /></li>
							<li><input type="text" name="groups[<?=$i;?>][filters][<?=$o;?>][label]" value="<?=$option->label;?>" placeholder="Enter option label" class="textbox valign" size="40" maxlength="150" /></li>
							<li><input type="color" name="groups[<?=$i;?>][filters][<?=$o;?>][colour]" value="<?=$option->colour;?>" placeholder="#FFFFFF" class="textbox valign" size="7" maxlength="7" /></li>
							<li><a class="remove-filter-option button valign" href="<?=site_url('filters/deleteoption');?>" data-filter-id="<?=$option->filter_id;?>">Remove</a></li>
						</ul>
						<input type="hidden" name="groups[<?=$i;?>][filters][<?=$o;?>][id]" value="<?=$option->filter_id;?>" />
					</li>
					<?php } ?>
										
				</ul><!-- END .filter-group-options -->
				
				<div class="filter-group-option-new">
					<input type="text" name="new_filter_label" placeholder="Add new option" class="textbox valign" size="40" maxlength="150" />
					<input type="color" name="new_filter_colour" placeholder="#FFFFFF" class="textbox valign" size="7" maxlength="7" />
					<a class="add-filter-option button valign" href="<?=site_url('filters/createoption');?>">Add Option</a>
				</div>
			
			</li><!-- END .filter-group  -->
			
			<?php } ?>
		
		</ul><!-- END #filter-group -->
		
	</div>

</div>

<div id="sidebar">
	<h3>Manage Filters</h3>
	
	<p>Filters enable products to be classified to a higher level of detail.</p>
	
	<p>Each category can have an unlimited number of filters applied to it. To add a new group click the "Add new group" button.</p>
	
	<p><strong>Applying filters to products</strong></p>
	
	<p>Filters can be applied to products through the add/edit item page in the inventory list.</p>
</div>