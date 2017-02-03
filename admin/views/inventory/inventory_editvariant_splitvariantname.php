<?php
// Loop through the variant attributes and create the array values to
// pass into the form values below
$var_attr_img = 20;
if (is_array($variant_attributes)) {
	foreach ($variant_attributes as $var_name => $var_value) {
		$i++;
		$var_attr[$i]['name']  = $var_name;
		$var_attr[$i]['value'] = $var_value['value'];
		$var_attr[$i]['image'] = $var_value['image'];
	}
}
?>
<div class="table-row">
	<label>Product Attributes: <span class="red">*</span></label>
	<p><a id="btn-joinvariantname" href="#"><img src="<?=template_directory('assets/images/icon-join.png');?>" alt="Join attributes to name" title="Join attributes to name" class="valign" /></a></p>
</div>

<ul style="list-style:none;" id="sortable-productattributes">
	<?php
	for ($v = 1; $v <= 5; $v++) {
		$var_attr_reqd = ($v == 1) ? 'required' : '';
	?>
	<li class="table-row product-attribute">
		<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
		<input name="variant_attr[<?=$v;?>][name]" type="text" value="<?=$var_attr[$v]['name'];?>" class="textbox <?=$var_attr_reqd;?>" size="35" placeholder="Enter attribute label" maxlength="35" /> = 
		<input name="variant_attr[<?=$v;?>][value]" type="text" value="<?=$var_attr[$v]['value'];?>" class="textbox <?=$var_attr_reqd;?> variant-attr-value" size="35" placeholder="Enter attribute value" maxlength="35" />
		<a id="variant_attr_<?=$v;?>_image" href="<?=site_url("filebrowser/index/image?elementid=variant_attr[$v][image]&size=$var_attr_img");?>" class="fancybox-popup">
			<?php
			if ($var_attr[$v]['image'] != '') {
				echo sprintf('<img src="%s" height="%dpx" class="valign" />', $var_attr[$v]['image'], $var_attr_img);
			} else {
			?>
			<img src="<?=template_directory('assets/scripts/markitup/sets/html/images/picture.png');?>" alt="Attach image" title="Attach image" class="valign" />
			<?php } ?>
		</a>
		<?php
		if ($var_attr[$v]['image'] != '') {
			echo sprintf('&nbsp;<a href="#" data-imginput="variant_attr[%s][image]" class="variant_attr_image_remove smallprint valign" title="Remove image">&#10005;</a>', $v);
		}
		?>
		<input name="variant_attr[<?=$v;?>][image]" type="hidden" value="<?=$var_attr[$v]['image'];?>"/>
	</li>
	<?php } ?>
</ul>

<!-- We post the following field to bypass form validation on the variant's product name field -->
<input type="hidden" name="product_name" value="AUTO" autocomplete="off" />
