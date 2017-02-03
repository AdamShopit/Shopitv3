<div id="content">

	<div class="table">
	
		<form id="formUploadImage" action="<?=site_url("inventory/addimage$redirect_query_string");?>" method="post" enctype="multipart/form-data">
		
		<h2>Manage Gallery for &ldquo;<?=$product_name;?>&rdquo; <a href="<?=$redirect_link;?>">Back</a></h2>
		
		<div class="table-row">
			<h3>Upload image</h3>
		</div>
		
		<div class="table-row">
			<label>Select image to upload:</label>
			<input type="file" name="product_image" id="product_image" class="uploadbox" />
			<input type="hidden" name="product_name" value="<?=$product_name;?>" />
			<input type="hidden" name="product_id" value="<?=$product_id;?>" />
			<input type="hidden" name="parent_id" value="<?=$parent_id;?>" />
			<input type="submit" name="Submit" value="Upload this image" class="button" />
			<span id="feedback"></span>
		</div>
		
		</form>
	</div>

	<div class="table">
			
		<div class="table-row">
			<h3>Current images</h3>
			<p>To reorder images simply drag them to your preferred position.</p>
		</div>

		<div id="gallery" class="table-row">
		<?php if ($images): ?>
		
		<form action="#" method="post" id="formGallery">
		
			<input type="hidden" name="gallery_product_id" value="<?=$product_id;?>" />
		
			<ul id="sortable-gallery">
			
					<?php
					$i = 0;
					$item_image = explode(';',$images);
					
					foreach ($item_image as $image) 
					{
	
						if ($image != ''):
							
							$i++;
					?>
					<li class="sortable-image">
						<div class="gallerythumb" style="background-image:url('<?=site_url('image/resize/'.$image.'/100/100?'.date('YmdHis'));?>');" data-src="<?=site_url('image/resize/'.$image.'/100/100');?>">
							<img style="display:none;" src="<?=site_root("uploads/$image");?>" />
							<?php
							//Check if this is a child item so we can append to the url below
							$is_variant = ($this->uri->segment(4) == "variation") ? "/variation" : null;
							?>
							<input type="hidden" name="gallery_product_image[]" value="<?=$image;?>" />
						</div>
						<div class="sortable-image-controls">
							<a href="<?=site_url('inventory/removeimage/'.$product_id.'/'.$image.$is_variant.$redirect_query_string);?>" class="deleteImage valign"><img src="<?=template_directory('assets/images/icon-cross.png');?>" alt="Delete" title="Delete" /></a>
						</div>
					</li>
					<?php 
						endif;
					} ?>
			</ul>
			
		</form>
		<?php
			else:
		?>
				<strong class="darkgrey">You have not uploaded any images for this product yet.</strong>
			
		<?php 
			endif;
		?>	
		
			<br clear="all"/>	
		</div>
	
	</div>

</div>

<div id="sidebar">
	<h3>Uploading images</h3>
	<p><strong class="redtext">Only jpg, gif or png images are permitted</strong>.<br/>For best results upload a large image of at least 1000 pixels width and the system will resize accordingly.</p>
	
	<h3>Sorting images</h3>
	<p>From this screen you can sort items into your preferred order simply by dragging each to its new position.</p>
</div>