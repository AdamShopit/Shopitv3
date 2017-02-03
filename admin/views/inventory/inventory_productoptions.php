<?php
#------------------------------------------------------
# This is the SIMILAR html as used in the inventory
# item add/edit page
#------------------------------------------------------
?>
<div id="content">
	<div class="table">
		<h2>Manage Product Options for <?=$productoption_set->option_set_label;?></h2>
		
		<!-- Product options -->
		<div class="table-row">
			<h3>Product Options</h3>
			<p>Enter product options below. E.g. Available sizes, colours, etc. You can <strong>drag</strong> each option to your preferred order.</p>
		</div>
	
		<ul id="sortable-productoptions">
		<?php 
		if ($productoptions > 0):
		foreach ($productoptions as $option):
		?>
		<li class="table-row product-option">
			<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
			<input name="option_label[]" type="text" value="<?=$option->option_label;?>" class="textbox" size="20" />
			<input name="option_criteria[]" type="text" value="<?=$option->option_criteria;?>" class="textbox" size="20" />
			<input name="option_price[]" type="text" value="<?=$option->option_price;?>" class="textbox number" size="20" />
			<input name="option_id[]" value="<?=$option->id;?>" type="hidden" />
			<input name="option_delete[]" type="hidden" value="false" />
			<a href="#" class="button removeproductoption">X</a>
		</li>
		<?php 
		endforeach; 
		endif;
		?>
		</ul>
	
		<div class="table-row minpadding">
			<label>&nbsp;</label>
			<label for="option_label" style="width:165px;"><strong>Group Label</strong></label>
			<label for="option_criteria" style="width:165px;"><strong>Option</strong></label>
			<label for="option_price"><strong>Price</strong></label>
			<br clear="all" />
		</div>
	
		<div class="table-row minpadding">
			<label>Enter Option:</label>
			<input name="option_label_new" type="text" value="" class="textbox" size="20" <?=tooltip("Enter a label e.g. Size");?> />
			<input name="option_criteria_new" type="text" value="" class="textbox" size="20" <?=tooltip("Enter an option e.g. Small");?> />
			<input name="option_price_new" type="text" value="" class="textbox number" size="20" <?=tooltip("Enter this option's price difference e.g. 2.00");?> />
			<a href="#" class="button addproductoption">Add option</a>
		</div>
	
	</div>

</div>

<div id="sidebar">
	<h3>Product Options</h3>
	<p>These are sets used to list product options, e.g. clothing sizes, shoe sizes, etc. Setting up sets makes it quicker and easier for you to apply them to products without having to type the information in time and again.</p>
	<p>Enter your product options here. You can drag them to your preferred order.</p>
</div>