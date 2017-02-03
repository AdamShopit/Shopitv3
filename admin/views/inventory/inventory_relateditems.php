<?php 
//This view is used via ajax.
if ($inventory > 0): 
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">

	<thead>
		<tr>
			<th width="5%">Type</th>
			<th width="50%">Product name</th>
			<th width="5%"><center>Image</center></th>
			<th width="10%">Code</th>
			<th width="20%"><center>Add as</center></th>
		</tr>
	</thead>

<?php
	$i = 0; //(A) used for background colour 
	
	foreach($inventory as $item): 

		$i++; //(A)
		
		if ($i&1) { $post = 'odd'; } 
		else { $post = 'even'; } //(A);
		
		$product_name = ($item->product_type == 'variant') ? sprintf('%s <small class="smallprint">&mdash; Variation of %s</small>', unserialize_variant($item->product_name), $item->parent_name) : $item->product_name;
		$product_name_raw = ($item->product_type == 'variant') ? sprintf('%s %s', unserialize_variant($item->product_name), $item->parent_name): $item->product_name;
?>
	
	<tr class="tbody <?=$post;?>" id="<?=$item->product_id;?>">
		<td><?=$item->product_type;?></td>
		<td>
			<?=status($item->product_disabled);?>&nbsp;
			<span id="<?=$item->product_id;?>"><?=$product_name;?></span>
		</td>
		<td align="center">
			<?php 
				if (!empty($item->product_image)): 
				$image = explode(';',$item->product_image);
			?>
			<img src="<?=site_url("image/resize/$image[0]/35/35");?>" class="thumbnail" alt=""/>
			<?php else: ?>
			<img src="<?=base_url('assets/images/nophoto_50x50.gif');?>" class="thumbnail" width="35" height="35" alt="" />
			<?php endif; ?>
		</td>
		<td><?=$item->product_no;?></td>
		<td align="center">
			<div class="relateditemselect">
				<input type="hidden" name="related_product_id" id="related_product_id" value="<?=$item->product_id;?>" />
				<input type="hidden" name="related_product_name" value="<?=$product_name_raw;?>" />
				<input type="hidden" name="related_product_no" value="<?=$item->product_no;?>" />
				<input type="button" class="button addrelateditem" value="Related" data-type="R" data-label="Related" />
				<?php
				foreach($groups as $g) {
				echo sprintf('<input type="button" class="button addrelateditem" value="%s" data-type="%s" data-label="%s" />', $g->label, $g->type, $g->label);
				} 
				?>

			</div>
		</td>
	</tr>

<?php 
	endforeach; 
?>

</table>
<?php endif; ?>