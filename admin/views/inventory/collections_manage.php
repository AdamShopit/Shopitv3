<div id="content">

	<form action="<?=site_url('collections/sortable');?>" method="post" id="formCollections">
	
	<input type="hidden" name="collection_id" value="<?=$collection_id;?>" />
	
	<div class="table">
		<h2>Manage Collection <noscript><input type="submit" name="submit" value="Update Order" class="button" style="cursor:pointer;" /></noscript> <a href="<?=$redirect;?>">Back</a></h2>
	
		<div class="table-row"><h3><?=$collection_name;?></h3></div>
	
		<?php if (!empty($products)) { ?>
		
		<ul id="sortable-collection">
		
			<?php 
			foreach ($products as $item): ?>
				
				<li class="sortable-box">
					<div class="grid-product">
						
						<div class="grid-productimage">
							<a href="<?=site_url('inventory/gallery/'.$item->product_id);?>">
							<?php 
								if (!empty($item->product_image)): 
								$image = explode(';',$item->product_image);
							?>
							<img src="<?=site_url('image/resize/'.$image[0].'/100/100');?>" class="" alt=""/>
							<?php else: ?>
							<img src="/admin/assets/images/nophoto_50x50.gif" class="thumbnail" width="100" height="100" alt="" />
							<?php endif; ?>
							</a>
						</div>
						
						<div><?=status($item->product_disabled);?> <?=$item->product_no;?></div>
						
						<div class="grid-productoptions">
							<a href="<?=site_url('inventory/edit/'.$item->product_id);?>" class="button">Edit</a>
							<a href="<?=site_url('collections/removeitem/'.$collection_id.'/'.$item->product_id);?>" class="ajaxdeletecollectionitem button">Remove</a>
						</div>
						
						<input type="hidden" name="product_id[]" value="<?=$item->product_id;?>" />
						
					</div>
					<br class="clearall"/>
				</li>
		
			<?php endforeach; ?>
		
		</ul>
	
		<?php } else { ?>
		<div class="table-row">
			<p>You have no items in this collection.</p>
		</div>
		<?php } ?>
	
		<br class="clearall"/>
	</div>
	
	</form>

</div>

<div id="sidebar">
	<h3>Sort collections</h3>
	<p>From this screen you can sort items into your preferred order simply by dragging each item to its new position.</p>
	<h3>Need to add a new item?</h3>
	<p>Adding a new item to this collection can be done via the <a href="<?=site_url('inventory');?>" style="margin:0;">inventory</a>.</p>
</div>