<div id="content">
	<div class="table">
		<h2><?=$form_title;?></h2>
		
		<div class="table-row">
			<h3>Stock Locations</h3>
		</div>
	
		<ul id="sortable-productattributes">
		<?php 
		$i = 0; //(A) used for background colour
		$a = 0;
		if ($locations > 0):
		foreach ($locations as $location): 

			$i++; //(A)
			$a++;
			$post = ($i&1) ? "odd" : "even";
		?>
	
		<li class="table-row <?=$post;?>">
			<input name="location_name[<?=$a;?>]" type="text" value="<?=$location->name;?>" class="textbox" size="30" maxlength="75" placeholder="Location name" />
			<input name="location_note[<?=$a;?>]" type="text" value="<?=$location->note;?>" class="textbox" size="90" maxlength="150" placeholder="Enter a note if required (for your reference) or the domain name if this is a website" />
			<input name="location_id[<?=$a;?>]" type="hidden" value="<?=$location->id;?>" />
			<?php if ($location->locked == 0) { ?>
			<a href="<?=site_url("inventory/deletelocation/$location->id");?>" class="button ajaxdelete">X</a>
			<?php } ?>
			<br /><br />
			<?php
			//If not locked
			if ($location->id > 1) {
			?>
			<span class="smallprint">Location Type</span>
			<select name="location_type[<?=$a;?>]">
				<option value="" disabled="disabled">Location Type</option>
				<option value="other" <?=is_selected($location->type, 'other');?>>Other</option>
				<option value="amazon" <?=is_selected($location->type, 'amazon');?>>Amazon</option>
				<option value="ebay" <?=is_selected($location->type, 'ebay');?>>eBay</option>
				<option value="shop" <?=is_selected($location->type, 'shop');?>>Retail Outlet</option>
				<option value="website" <?=is_selected($location->type, 'website');?>>Website</option>
			</select>
			<label class="smallprint" style="float:none;margin-left:40px;" for="location_useglobal[<?=$a;?>]"><input type="checkbox" name="location_useglobal[<?=$a;?>]" id="location_useglobal[<?=$a;?>]" value="1" <?=is_checked($location->use_global_stock, 1);?> /> Use default location's stock level</label>
			<?php } else { ?>
			<input type="hidden" name="location_type[<?=$a;?>]" value="default" />
			<?php } ?>
		</li>
	
		<?php 
		endforeach; 
		endif;?>
		</ul>
		
		<div class="table-row">&nbsp;</div>
		
		<div class="table-row">
			<h4>Add a new location</h4>
		</div>
		
		<div class="table-row">
			<input name="location_name_new" type="text" value="" class="textbox" size="30" maxlength="75" placeholder="Location name" <?=tooltip("Enter the name of the location");?>  />
			<input name="location_note_new" type="text" value="" class="textbox" size="90" maxlength="150" placeholder="Enter a note if required (for your reference) or the domain name if this is a website" <?=tooltip("Enter a note for this location or the domain name without the http:// if this is a website.");?>  />
		</div>
		
		<div class="table-row">
			<span class="smallprint">Location Type</span>
			<select name="location_type_new">
				<option value="" disabled="disabled">Location Type</option>
				<option value="other">Other</option>
				<option value="amazon">Amazon</option>
				<option value="ebay">eBay</option>
				<option value="shop">Retail Outlet</option>
				<option value="website">Website</option>
			</select>
			<label class="smallprint" style="float:none;margin-left:40px;" for="location_useglobal_new"><input type="checkbox" name="location_useglobal_new" id="location_useglobal_new" value="1" /> Use default location's stock level</label>
		</div>
	
	</div>

</div>

<div id="sidebar">
	<h3>Stock Locations</h3>
	<p>Enter stock locations here. If you want the quantities to be consumed from the default location (website) when a successful purchase is made, tick the "Use Default Location's Stock Level" option.</p>
</div>