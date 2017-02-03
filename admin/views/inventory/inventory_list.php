<div id="content">
	<form name="formCheck" id="formCheck" method="post" action="<?=site_url('inventory/process');?>">

	<table cellpadding="0" cellspacing="0" border="0" class="sticky">
		<thead>
			<tr>
				<td colspan="10"><h2>Inventory</h2></td>
			</tr>
			<tr>
				<th width="5%"><center>ID</center></th>
				<th width="35%">Product name <?=table_sort('/inventory/index','product_name');?></th>
				<th width="20%">Category <?=table_sort('/inventory/index','category', true);?></th>
				<th width="50"><center>Image</center></th>
				<th width="10%"><center>Qty</center></th>
				<th width="10%">Price</th>
				<th width="1">&nbsp;</th>
				<th width="1"><center><input type="checkbox" name="checkall" value="yes" /></th>
			</tr>
		</thead>
	
		<tbody>
		<?php
		$i = 0; //(A) used for background colour 
		
		if ($inventory > 0) {
		
		foreach($inventory as $item): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
			
			$archived_class = ($item->archived == 1) ? " archived" : "";
			
			//Is this a 'variation' or 'single' product type?
			$is_variation = ($item->product_type == 'variation') ? TRUE : FALSE;
			
			$variation_class = ($is_variation) ? ' even ' : '';
		?>
		
		<tr class="<?=$variation_class.$archived_class;?>" id="<?=$item->product_id;?>">
			<td align="center"><?=$item->product_id;?></td>
			<td>
				<?=status($item->product_disabled);?>&nbsp;
				<span class="valign">
				<?php if ($item->archived == 0) { ?>
				<a href="<?=site_url("inventory/edit/$item->product_id$redirect");?>"><?=$item->product_name;?></a>
				<?php } else { 
				print $item->product_name;
				}
				?>
				</span>
			</td>
			<td><?=sprintf('<code>%s <a href="%s">%s</a></code>', $item->category, site_url("inventory/index/0/filter=true&s_category=category-$item->cat_id"), '&rarr;');?></td>
			<td align="center">
				<?php if ($item->archived == 0) { ?>
				<a href="<?=site_url("inventory/gallery/$item->product_id$redirect");?>">
				<?php 
					if (!empty($item->product_image)): 
					$image = explode(';',$item->product_image);
				?>
				<img src="<?=site_url('image/resize/'.$image[0].'/30/30');?>" class="thumbnail" alt=""/>
				<?php else: ?>
				<img src="<?=template_directory("assets/images/nophoto_50x50.gif");?>" class="thumbnail" width="30" height="30" alt="" />
				<?php endif; ?>
				</a>
				<?php } else { ?>
				<img src="<?=template_directory('assets/images/icon-archived.png');?>" alt="Archived" title="Archived" />
				<?php } ?>
			</td>
			<td>
				<center>
				<?php
				if (!$is_variation) {
					if ($item->archived == 0) {
				?>
				<span id="<?=$item->product_id;?>" class="edit editsmall editproductqty"><?=$item->location_1;?></span>
				<?php } else {
					print "~";
					}
				} else {
					echo "&nbsp;";
				}
				?>
				</center>
			</td>
			<td>
				<?php
				// If this is NOT a variation, display the price
				if (!$is_variation) {
					// If not archived
					if ($item->archived == 0) { 
						if($item->product_saleprice > 0) { 
							echo sprintf('<span class="redtext">%s</span>', money($item->product_saleprice));
						} else {
							echo money($item->product_price);
						}
					}
				} else {
					// Don't display anything if this is a variation
					echo "&nbsp;";
				}
				?>
			</td>
			<td>
				<?php if ($item->archived == 0) { ?>
				<ul class="actions">
					<li><a href="#" class="btn-action"><i class="fa fa-angle-down"></i></a>
						<ul>
							<li><a href="<?=site_url("inventory/edit/$item->product_id$redirect");?>">Edit item</a></li>
							<?php 
							if($item->product_disabled == 0) {
								$status_tick = '';
							} else {
								$status_tick = 'hide-tick';
							}
							?>
							<li><a href="<?=site_url('inventory/itemstatus/'.$item->product_id);?>" class="itemstatus itemstatus-<?=$item->product_id;?>"><img src="<?=template_directory('assets/images/tick-black.png');?>" class="<?=$status_tick;?>" />Item enabled</a></li>
							<li><a href="<?=site_url("inventory/gallery/$item->product_id$redirect");?>">Photo gallery</a></li>
							<li><a href="<?=site_url("inventory/related/$item->product_id$redirect");?>">Manage Cross-sells</a></li>
							<li class="nav-separator lineonly"></li>
							<li><a href="<?=site_url("inventory/delete/$item->product_id");?>" class="ajaxdelete" rel="Are you sure you want to delete this item and all related variations?" data-groupid="child-group-<?=$item->product_id;?>">Delete item</a></li>
							<li><a href="<?=site_url("inventory/duplicate/$item->product_id$redirect");?>">Duplicate item</a></li>
							<li><a href="<?=site_url("inventory/archive/$item->product_id$redirect");?>" class="" rel="Are you sure you want to archive this item?">Archive item</a></li>
							<?php
							if ($item->product_type == 'single') {
							?>
							<li><a href="<?=site_url("inventory/convert/variation/$item->product_id$redirect");?>">Convert to parent variation</a></li>
							<?php } ?>
							<li class="nav-separator lineonly"></li>
							<li><a href="<?=site_url("inventory/report/$item->product_id$redirect");?>">Product Report</a></li>
							<li class="nav-separator">Add to collection</li>
							<?php
							// Get an array of collection id's this item belongs to
							$this_items_collections = $this->collections_model->getCollectionsForItem($item->product_id);
							
							// Loop through the collection groups
							foreach ($collection_groups as $collection_group_label => $the_collections) {
								// Output the name of the collection group
								echo sprintf('<li class="nav-collection-group">-- %s --</li>', $collection_group_label);
								
								foreach ($the_collections as $collection_id => $collection_name) {
									
									// Check the tick status for this item
									$collection_tick = (in_array($collection_id, $this_items_collections)) ? '' : 'hide-tick';
									
									// Output the collection list item
									echo sprintf('<li><a href="%s" class="addtocollection collection-%s"><img src="%s" class="%s" />%s</a></li>', site_url("ajax/addtocollection/$item->product_id/$collection_id"), $collection_id, template_directory('assets/images/tick-black.png'), $collection_tick, $collection_name);
								}
							}
							?>
						</ul>
					</li>
				</ul>
				<?php } else { ?>
				<a class="button" href="<?=site_url("inventory/unarchive/$item->product_id$redirect");?>">Unarchive</a>
				<?php } ?>
			</td>
			<td align="center"><input type="checkbox" name="product_id[]" class="checkall" value="<?=$item->product_id;?>" /></td>
		</tr>

		<?php
		//!Variations
		if ($is_variation && $item->archived == 0) {
		?>
		<tr class="child-group-<?=$item->product_id;?> subheadings">
			<td>&nbsp;</td>
			<td>Variation</td>
			<td>Product No</td>
			<td>&nbsp;</td>
			<td><center>Qty</center></td>
			<td colspan="2">Price</td>
			<td>&nbsp;</td>
		</tr>
		<tbody class="child-group-<?=$item->product_id;?> child-item-group" data-parent_id="<?=$item->product_id;?>">
		<?php
		$variations = $this->inventory_model->getVariations($item->product_id);
		if (!empty($variations) && $item->archived == 0):
		foreach ($variations as $child) {
		?>
		<tr class="child-item" id="<?=$child->product_id;?>" rel="<?=$item->product_id;?>">
			<td align="center">&nbsp;</td>
			<td>
				<input type="hidden" name="child_id[]" value="<?=$child->product_id;?>" />
				<?php 
				if ($item->product_disabled == 1) {
					$child_status = status($item->product_disabled);
				} else {
					$child_status = status($child->product_disabled);
				}
				echo $child_status;
				?>&nbsp;
				<span class="valign"><a href="<?=site_url("inventory/editvariation/$child->product_id$redirect");?>"><?=unserialize_variant($child->product_name);?></a></span>
			</td>
			<td><?=$child->product_no;?></td>
			<td align="center">
				<a href="<?=site_url("inventory/gallery/$child->product_id/variation$redirect");?>">
				<?php 
				if (!empty($child->product_image)) {
					$image = explode(';',$child->product_image);
				?>
				<img src="<?=site_url('image/resize/'.$image[0].'/20/20');?>" class="thumbnail" alt=""/>
				<?php } else { ?>
				<img src="<?=template_directory("assets/images/nophoto_50x50.gif");?>" class="thumbnail" width="20" height="20" alt="" />
				<?php } ?>
				</a>
			</td>
			<td><center><span id="<?=$child->product_id;?>" class="edit editsmall editproductqty"><?=$child->location_1;?></span></center></td>
			<td>
				<?php 
				if($child->product_saleprice > 0) {
					echo sprintf('<span class="redtext">%s</span>', money($child->product_saleprice));
				} else {
					echo money($child->product_price);
				}
				?>
			</td>
			<td>
				<ul class="actions">
					<li><a href="#" class="btn-action"><img src="<?=template_directory('assets/images/btn-action-arrow-down.png');?>" alt=""/></a>
						<ul>
							<li><a href="<?=site_url("inventory/editvariation/$child->product_id$redirect");?>">Edit variation</a></li>
							<?php 
							if($child->product_disabled == 0) {
								$status_tick = '';
							} else {
								$status_tick = 'hide-tick';
							}
							?>
							<li><a href="<?=site_url('inventory/itemstatus/'.$child->product_id);?>" class="itemstatus itemstatus-<?=$child->product_id;?>"><img src="<?=template_directory('assets/images/tick-black.png');?>" class="<?=$status_tick;?>" />Variation enabled</a></li>
							<li><a href="<?=site_url("inventory/gallery/$child->product_id/variation$redirect");?>">Photo gallery</a></li>
							<li><a href="<?=site_url('inventory/deletevariation/'.$child->product_id);?>" class="ajaxdelete" rel="Are you sure you want to delete this variation?">Delete variation</a></li>
							<li><a href="<?=site_url("inventory/addvariation/$item->product_id/$child->product_id$redirect");?>">Duplicate variation</a></li>
							<li><a href="<?=site_url("inventory/convert/single/$child->product_id$redirect");?>">Convert to single item</a></li>
						</ul>
					</li>
				</ul>
			</td>
			<td align="center"><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="Re-order variation" title="Re-order variation" class="valign draggable" style="margin-left:4px;" /></td>
		</tr>
		<?php
		}
		else:
			echo '<tr class="child-group-placeholder"><td colspan="9" align="center"><span class="badge badge-grey">Drag &amp; drop a variation here</span></td></tr>';
		endif;
		?>
		</tbody>
		<tr class="child-group-<?=$item->product_id;?> last-subrow">
			<td>&nbsp;</td>
			<td colspan="9"><a class="button" href="<?=site_url("inventory/addvariation/$item->product_id$redirect");?>">Add variation</a></td>
		</tr>	
		<?php
		} 
		endforeach; 
		} else { 
		?>
		<tr>
			<td colspan="10" align="center">No products could be found.</td>
		</tr>
		<?php } ?>
		
		</tbody>
	
		<tfoot>
			<tr>
				<td colspan="10" align="right">
					<select name="action">
						<option value="">What would you like to do?</option>
						<option value="" disabled="disabled">-----------------------------------</option>
						
						<optgroup label="Change status of selected items to">
							<option value="enable">Enabled</option>
							<option value="disable">Disabled</option>
							<option value="archive">Archived</option>
						</optgroup>
						
						<optgroup label="Delete selected items">
							<option value="delete">Permanently delete selected items</option>
						</optgroup>
						
						<?php
						// List locations
						if (count($locations) > 1):
						?>
						<optgroup label="Add selected items to location">
							<?php foreach($locations as $channel) { ?>
							<option value="add_channel_<?=$channel->id;?>"><?=$channel->name;?></option>
							<?php } ?>
						</optgroup>
						
						<optgroup label="Remove selected items from location">
							<?php foreach($locations as $channel) { ?>
							<option value="remove_channel_<?=$channel->id;?>"><?=$channel->name;?></option>
							<?php } ?>
						</optgroup>
						<?php endif; ?>
						
						<?php if ($collections > 0): ?>
						<optgroup label="Add selected items to a collection">
							<?php foreach($collections as $collection) { ?>
							<option value="collection-<?=$collection->collection_id;?>"><?=$collection->collection_name;?></option>
							<?php } ?>
						</optgroup>
						<?php endif; ?>
						
						<?php
						// Coupons
						if (count($coupons) > 0):
						?>
						<optgroup label="Apply coupon to selected items">
							<?php foreach($coupons as $coupon) { ?>
							<option value="add_coupon_<?=$coupon->id;?>"><?=$coupon->label;?> (<?=uppercase($coupon->code);?>)</option>
							<?php } ?>
						</optgroup>
						
						<optgroup label="Remove coupon from selected items">
							<?php foreach($coupons as $coupon) { ?>
							<option value="remove_coupon_<?=$coupon->id;?>"><?=$coupon->label;?> (<?=uppercase($coupon->code);?>)</option>
							<?php } ?>
						</optgroup>
						<?php endif; ?>
						
						<?php
						// List categories 
						if ($categories > 0): 
						?>
						<optgroup label="Move selected items to another category">
							<?php foreach($categories as $category) {?>
							<option value="category-<?=$category->cat_id;?>"><?=str_replace(' & ', ' &amp; ', $category->cat_name);?></option>
							<?php } ?>
						</optgroup>
						<?php endif; ?>
					</select>
					<input type="submit" name="submitcheck" value="Submit" class="button" disabled="disabled" />
					<input type="hidden" name="redirect" value="<?=current_url();?>" />
				</td>
			</tr>
			<tr>
				<td colspan="10"><?=$this->pagination->create_links();?></td>
			</tr>
		</tfoot>
	
	</table>
	
	</form>
</div>

<div id="sidebar">
	<form method="post" action="<?=site_url('/inventory/index/');?>">
	<input type="hidden" name="filter" value="true" />
	<h3>Filter inventory</h3>
	<p>Show me only those products that match the following criteria:</p>
	<ul>
		<li><label for="s_sale" ><input type="checkbox" name="s_sale" id="s_sale" value="true"<?=is_checked('true',$s_sale);?>/>On sale</label></li>
		<li><label for="s_disabled"><input type="checkbox" name="s_disabled" id="s_disabled" value="true"<?=is_checked('true',$s_disabled);?>/>Are disabled or hidden</label></li>
		<li><label for="s_archived"><input type="checkbox" name="s_archived" id="s_archived" value="true"<?=is_checked('true',$s_archived);?>/>Include archived</label></li>
		<li><label for="s_nophotos"><input type="checkbox" name="s_nophotos" id="s_nophotos" value="true"<?=is_checked('true',$s_nophotos);?>/>Have no photos</label></li>
		<li><label for="s_stocklevel"><input type="checkbox" name="s_stocklevel" id="s_stocklevel" value="true"<?=is_checked('true',$s_stocklevel);?>/>Have a stock level less than 10</label></li>
	</ul>
	<p>Are in the following category/collection:</p>
	<p>
	<select name="s_category" class="dropdown">
		<option value="">All categories</option>
		<?php if ($collections > 0): ?>
		<optgroup label="Collections">
			<?php foreach($collections as $collection) { ?>
			<option value="collection-<?=$collection->collection_id;?>"<?=is_selected('collection-'.$collection->collection_id,$s_category);?>><?=$collection->collection_name;?></option>
			<?php } ?>
		</optgroup>
		<?php endif; ?> 
		<?php if ($categories > 0): ?>
		<optgroup label="Categories">
			<?php foreach($categories as $category) {?>
			<option value="category-<?=$category->cat_id;?>"<?=is_selected('category-'.$category->cat_id,$s_category);?>><?=str_replace(' & ', ' &amp; ',$category->cat_name);?></option>
			<?php } ?>
		</optgroup>
		<?php endif; ?>
	</select>
	</p>
	<p style="margin-top:5px;">Product name/number contains:</p>
	<p><input name="s_productno" value="<?=$s_productno;?>" maxlength="55" class="textbox" /></p>
	<p><small class="smallprint">You can also enter multiple product numbers e.g. code1, code2, code3, etc or a product's database ID e.g. id:99</small></p>

	<?php
	if (count($locations) > 1):
	?>
	<p><strong>Channels:</strong></p>
	
	<ul>
		<?php
		foreach($locations as $channel) {
			//Set the channel field name
			$channel_field = "s_channel_$channel->id";
			
			//Get the dynamic variable (which is in the format $s_channel_n, where n is the channel id
			$dynamic_channel_var = ${'s_channel_'.$channel->id};
			
			//If inventory is not filtered, then tick all the channel checkboxes by default (we are showing all products here)
			$auto_check_channel_field = ($filter != 'true') ? 'checked="checked"' : is_checked('true', $dynamic_channel_var);
		?>
		<li>
			<label>
				<input type="checkbox" name="<?=$channel_field;?>" id="<?=$channel_field;?>" value="true" <?=$auto_check_channel_field;?> />
				<?=$channel->name;?>
			</label>
		</li>
		<?php } ?>
	</ul>
	<?php endif; ?>

	<p align="right">
		<input type="submit" name="submit" value="Filter Inventory" class="button" />
	</p>
	</form>
	
	<form method="post" action="<?=site_url('inventory/export');?>">
		<h3>Export CSV file</h3>
		<p>If you would like to export the filtered inventory results you see on the left, click the "Export CSV" button below.</p>
		
		<p align="right">
			<input type="hidden" name="export_filter" value="<?=$s_filter;?>" />
			<input type="submit" name="export" value="Export CSV" class="button" />
		</p>	
	</form>
</div>
