<?php
#------------------------------------------------------
# This is the SIMILAR html as used in the inventory
# item add/edit page
#------------------------------------------------------
?>
<div id="content">
	<div class="table">
		<h2>Manage Attributes for <?=$attribute_set->attribute_set_label;?></h2>
		
		<!-- Product attributes -->
		<div class="table-row">
			<h3>Product Attributes</h3>
			<span class="darkgrey">Enter attributes for this attribute set e.g. width, height, depth, etc.</span>
		</div>
	
		<ul id="sortable-productattributes">
		<?php 
		if ($attributes > 0):
		foreach ($attributes as $attribute): 
		?>
	
		<li class="table-row product-attribute">
			<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
			<input name="attribute_name[]" type="text" value="<?=$attribute->attribute_name;?>" class="textbox" size="30" /> =
			<input name="attribute_value[]" type="text" value="<?=$attribute->attribute_value;?>" class="textbox" size="30" />
			<input name="attribute_id[]" type="hidden" value="<?=$attribute->id;?>" />
			<input name="attribute_delete[]" type="hidden" value="false" />
			<a href="#" class="button removeattribute">X</a>
		</li>
	
		<?php 
		endforeach; 
		endif;?>
		</ul>
		
		<div class="table-row minpadding">
			<label>Enter attribute:</label>
			<input name="attribute_name_new" type="text" value="" class="textbox" size="30" <?=tooltip("E.g. Width");?> placeholder="E.g. Width" /> =
			<input name="attribute_value_new" type="text" value="" class="textbox" size="30" <?=tooltip("Enter default value (Optional)");?> placeholder="Enter default value (Optional)" />
			<a href="#" class="button addattribute">Add attribute</a>
		</div>
	
	</div>

</div>

<div id="sidebar">
	<h3>Attributes</h3>
	<p>Attribute sets are groups of product specifications. Setting up sets makes it quicker and easier for you to apply them to products without having to type the information in time and again.</p>
	<p>Enter your attributes here. You can drag them to your preferred order.</p>
</div>