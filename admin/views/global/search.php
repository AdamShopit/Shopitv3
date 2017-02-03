<div id="content">

	<?php 
	// !Customer Results
	if (!empty($customers)) {
	$results = true; // Flag to indicate something's been found.
	?>
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="6"><h2>Customers matching "<?=$keyword;?>"</h2></td>
			</tr>
			<tr>
				<th width="20%">Name</th>
				<th width="35%">Billing Address</th>
				<th width="15%">City</th>
				<th width="25%">Email</th>
				<th width="5%">&nbsp;</th>
			</tr>
		</thead>
	
		<tbody>
		<?php
		$i = 0; //(A) used for background colour 
		
		foreach($customers as $item): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
		?>
		<tr class="table-row <?=$post;?>">
			<td><a href="<?=site_url("customers/view/$item->order_id/$redirect");?>"><?=$item->billing_title;?> <?=$item->billing_firstname;?> <?=$item->billing_surname;?></a></td>
			<td><?=$item->billing_address1;?></td>
			<td><?=$item->billing_city;?></td>
			<td><?=$item->customer_email;?></td>
			<td>
				<a href="<?=site_url("customers/view/$item->order_id/$redirect");?>" class="button">View</a>
			</td>
		</tr>
	
		<?php 
		endforeach;
		?>
		</tbody>
		
	</table>
	<?php } ?>

	<?php
	// !Order Results	
	if (!empty($orders)) {
	$results = true; // Flag to indicate something's been found.
	?>
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="7"><h2>Orders matching "<?=$keyword;?>"</h2></td>
			</tr>
			<tr>
				<th width="20%">Order No</th>
				<th width="20">Site</th>
				<th width="15%"><center>Status</center></th>
				<th width="10%"><center>Date</center></th>
				<th width="40%">Customer</th>
				<th width="15%">Total</th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>
		<?php
		$i = 0; //(A) used for background colour 
		
		foreach($orders as $item): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);

			$is_today = is_today($item->order_date);
			if ($is_today == true) {
				$highlight_today_class = 'highlight-today';
			} else {
				$highlight_today_class = '';
			}

			$order_date = nice_date($item->order_date, 'time');
			
			//List refunds
			$refunds = $this->orders_model->getRefunds($item->order_ref);
			$count_refunds = count($refunds);
			
			//Create a class to highlight the refund's parent order
			$refunds_class = ($count_refunds > 0) ? "even" : "";
			
			//Order Reference
			$order_ref = ($item->order_ref == '' || $item->order_status == '') ? "" : $item->order_ref;
			
			//Unprocessed CSS class
			if ($item->order_ref == '' || $item->order_status == '') {
				$unprocessed_class = ' unprocessed';
				$highlight_today_class = '';
			} else {
				$unprocessed_class = '';
			}

			// Check the date of this order with the previous. If it's not the same
			// display a sub-heading
			if (date('Y-m-d', strtotime($item->order_date)) != $this_date) {
				echo '<tr class="subheadings">';
				if ($is_today) {
					$date_heading = 'Today &mdash; ';
				} else {
					$date_heading = '';
				}
				echo sprintf('<td colspan="8">%s%s</td>', $date_heading, date('l, j F Y', strtotime($item->order_date)));
				echo '</tr>';
			}
		?>
		<tr class="<?=$refunds_class;?> <?=$highlight_today_class;?><?=$unprocessed_class;?>" id="<?=$item->order_id;?>">
			<td>
				<a href="<?=site_url('orders/view/'.$item->order_id . $redirect);?>"><?=$order_ref;?></a>
			</td>
			<td align="center"><?=site_icon($item->site);?></td>
			<td align="center"><?=order_status($item->order_status);?></td>
			<td align="center"><?=$order_date;?></td>
			<td>
				<?=$item->billing_title;?> <?=capitalise($item->billing_firstname);?> <?=capitalise($item->billing_surname);?>
				<?php if ($item->orders > 1){ echo '<span class="flag" title="Repeat customer">R</span>'; } ?>
			</td>
			<td><?=$this->config->item('currency');?><?=number_format($item->total,2);?></td>
			<td>
				<ul class="actions">
					<li><a href="#" class="btn-action"><img src="<?=template_directory("assets/images/btn-action-arrow-down.png");?>" alt=""/></a>
						<ul>
							<li><a href="<?=site_url('orders/view/'.$item->order_id . $redirect);?>">View order</a></li>
							<li><a href="<?=site_url('orders/edit/'.$item->order_id . $redirect);?>">Edit order</a></li>
							<?php if ($item->order_status != 'Dispatched' && empty($item->dispatch_date)) {?>
							<li class="markasdispatched"><a href="<?=site_url("orders/markasdispatched/$item->order_id");?>">Mark as dispatched</a></li>
							<?php } ?>
							<?php
							echo '<li class="nav-separator">Print</li>';
							// Default the site to 'website', if this is 'mobile' (as it won't have it's own channel)
							$site = ($item->site == 'mobile') ? 'website' : $item->site;
							// Get list of available templates
							if (!empty($templates[$site])) {
								foreach($templates[$site] as $template) {
									$template = (object)$template;
									echo sprintf('<li><a href="%s/%d%s">%s</a></li>', site_url("orders/printnote/$item->order_id"), $template->id, $redirect, $template->title);
								}
							}
							echo sprintf('<li><a href="%s">Create new template</a></li>', site_url('orders/templates'));
							?>
						</ul>
					</li>
				</ul>
			</td>
		</tr>
		<?php
		$r = 0;
		if ($refunds):
			//!Display the refunds
			foreach($refunds as $refund) {
			
				$r++;
				$array_product_name = array();
				
				//Add a "last" class
				$last_subrow_class = ($count_refunds == $r) ? 'last-subrow' : "";
	
				// Organise the product names so they display better
				$pieces = explode(';', trim($refund->inventory));
	
				foreach ($pieces as $refund_item) {
	
					//Create an array of products if product_name exists
					if (!empty($refund_item)) {
						$array_product_name[] = "&mdash; $refund_item";
					}
				
				}
	
				//Create a list of products e.g. item 1, item 2, item 3, etc.
				$product_names = implode('<br/>', $array_product_name);
			?>
			<tr class="highlight-refund" id="<?=$refund->refund_id;?>">
				<td valign="top" colspan="2">
					<img src="<?=template_directory('assets/images/refund-marker2.png');?>" alt="" />
					<a href="<?=site_url("orders/editrefund/$refund->refund_id$redirect");?>" class="valign"><?=nice_date($refund->refund_date, 'date');?></a>
				</td>
				<td valign="top" align="center"><?=order_status($refund->order_status);?></td>
				<td valign="top" colspan="2" class="redtext"><?=$product_names;?></td>
				<td valign="top" class="redtext"><?=money($refund->total);?></td>
				<td valign="top" align="center"><a href="<?=site_url("orders/deleterefund/$refund->refund_id");?>" class="ajaxdelete valign" rel="Are you sure you want to delete this refund?"><img src="<?=template_directory('assets/images/icon-cross.png');?>" alt="Delete" title="Delete" /></a></td>
			</tr>
			<?php
			}
			endif;
			?>
			<?php
				 
				// Record the date of this order so we can compare 
				// with the next in loop
				$this_date = date('Y-m-d', strtotime($item->order_date));
			
			endforeach;
			?> 
		</tbody>
		
	</table>
	<?php
		}
	?>

	<?php 
	// !Inventory Results
	if (!empty($inventory)) {
	$results = true; // Flag to indicate something's been found.
	?>
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="9"><h2>Inventory items matching "<?=$keyword;?>"</h2></td>
			</tr>
			<tr>
				<th width="5%"><center>ID</center></th>
				<th width="35%">Product name</th>
				<th width="20%">Category</th>
				<th width="50"><center>Image</center></th>
				<th width="10%"><center>Qty</center></th>
				<th width="10%">Price</th>
				<th width="1">&nbsp;</th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>
	
		<tbody>
		<?php
		$i = 0; //(A) used for background colour 
		
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
					<li><a href="#" class="btn-action"><img src="<?=template_directory('assets/images/btn-action-arrow-down.png');?>" alt=""/></a>
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
							<li><a href="<?=site_url("inventory/delete/$item->product_id");?>" class="ajaxdelete" rel="Are you sure you want to delete this item and all related variations?" data-groupid="child-group-<?=$item->product_id;?>">Delete item</a></li>
							<li><a href="<?=site_url("inventory/duplicate/$item->product_id$redirect");?>">Duplicate item</a></li>
							<li><a href="<?=site_url("inventory/archive/$item->product_id$redirect");?>" class="" rel="Are you sure you want to archive this item?">Archive item</a></li>
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
			<td>&nbsp;</td>
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
			<td>&nbsp</td>
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
		?>
		
		</tbody>
	
	</table>
	<?php } ?>

	<?php
	// Nothing found so display a standard message.
	if ($results != true) {
	?>
	<div class="table">
		<h2>No Results Found for "<?=$keyword;?>"</h2>
		<div class="table-row">
			<p style="padding-top:15px;" align="center">We couldn't find any records matching "<strong><?=$keyword;?></strong>". Try a search using a different keyword.</p>
		</div>
	</div>
	<?php } ?>
</div>

<div id="sidebar">
	<h3>Super Search</h3>
	<p>Our "super search" searches all orders, inventory items including variations and customer details.</p>
</div>