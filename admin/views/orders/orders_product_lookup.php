<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shopit - Inventory Lookup</title>

<link href="<?=template_directory('assets/styles/shopit.css');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<link href="<?=template_directory('assets/styles/shopit_styles.css');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>

<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery-ui.min.js?v=1.10.3');?>"></script>
<script type="text/javascript">
$(document).ready(function() {

	//Add border to last column header
	$('table').each(function(){
		$(this).find('th:last').addClass('last-column');
	});

	//Calculate prices
	$('.product_option').change(function(){
		var this_id = $(this).attr('rel');
		var base_price = $(this).closest('tr').find('input[name="base_price"]').val();
		var price = '';
		$('.product_option_' + this_id).each(function(i){
			price = (price*1) + ($(this).val()*1); //multiply by 1 to convert to int
		});
		
		price = (base_price*1) + (price*1);
		
		$(this).closest('tr').find('.product_price span.price').html('<?=$this->config->item('currency');?>' + price.toFixed(2));
		$(this).closest('tr').find('input[name="lookup_product_price"]').val(price.toFixed(2));
	});
	
	//Copy item to parent screen
	$('body').on('click', '.copyitem', function(){
		var this_id = $(this).attr('rel');
		var options = new Array();
		$('.product_option_' + this_id).each(function(i){
			options.push($('option:selected',this).html());
		});
		
		var opts = options.join(', ');
		
		if (opts.length > 0) {
			opts = ' (' + opts + ')';
		}

    	var product_options = '';
    	var product_id = $(this).closest('tr').find('input[name="lookup_product_id"]').val();
    	var cat_id = $(this).closest('tr').find('input[name="lookup_cat_id"]').val();
    	var product_no = $(this).closest('tr').find('input[name="lookup_product_no"]').val();
    	var product_name = $(this).closest('tr').find('input[name="lookup_product_name"]').val() + opts;
    	var product_price = $(this).closest('tr').find('input[name="lookup_product_price"]').val();
    	var product_weight = $(this).closest('tr').find('input[name="lookup_product_weight"]').val();
    	var qty = $(this).closest('tr').find('input[name="qty"]').val();
    	
		$('input[name="new_product_id"]', window.parent.document).val(product_id);
		$('input[name="new_cat_id"]', window.parent.document).val(cat_id);
		$('input[name="new_product_no"]', window.parent.document).val(product_no);
		$('input[name="new_product_name"]', window.parent.document).val(product_name);
		$('input[name="new_product_qty"]', window.parent.document).val(qty).attr('data-product_id', product_id);
		$('input[name="new_product_price"]', window.parent.document).val(product_price);
		$('input[name="new_product_weight"]', window.parent.document).val(product_weight);
		parent.$.fancybox.close();	
	});

	// Product previews
	$('.product_name').click(function(){
		//Get the id's
		var id = $(this).attr('id');
		var target = '#i-'+id;

		//hide the last descriptions, but not this one
		$('.product_preview').not(target).hide();

		//Show the preview
		if ($(target).is(':visible')) {
			$(target).hide();
		} else {
			$(target).slideDown();
		}
	});
	
});
</script>
<style>
body {
	margin: 20px auto 0px auto;
}
table label {
	width: 75px;
	margin: 2px 0;
}

table select {
	width: 225px;
	margin: 2px 0;
}

.product_name {
	border-bottom: 1px dotted #ccc;
	cursor: help;
}

.product_name:hover {
	border-bottom-color: #8194A3;
}

.product_preview {
	display: none;
}

.product_preview td {
	background-color: #292b36;
	color: #f7f7f7;
	font-family: "Lucida Console", Monaco, courier, monospace;
	font-size: 11px;
	line-height: 18px;
}

.product_preview h1,
.product_preview h2,
.product_preview h3,
.product_preview h4,
.product_preview h5,
.product_preview h6 {
	color: #f7f7f7;
	font-weight: 500;
	padding-bottom: 10px;
	text-transform: uppercase;
}

.product_preview p {
	padding-bottom: 16px;
}

.product_preview p:last-child {
	padding-bottom: 0px;
}
</style>
</head>

<body class="<?=body_class();?>">

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	
		<thead>
			<tr>
				<th width="30%">Product name</th>
				<th width="5%"><center>Image</center></th>
				<th width="10%">Code</th>
				<th width="5%"><center>Status</center></th>
				<th width="5%"><center>Stock</center></th>
				<th width="30%">Options</th>
				<th width="10%">Price (exc. VAT)</th>
				<th width="5%">&nbsp;</th>
			</tr>
		</thead>
		<tbody>

	<?php if ($inventory > 0): 
		$i = 0; //(A) used for background colour 
		
		foreach($inventory as $item): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
			
			// Adjust the product name
			if ($item->variant_name != '') {
				$product_name = sprintf('%s - %s', $item->parent_name, unserialize_variant($item->variant_name));
			} else {
				$product_name = $item->parent_name;
			}
			
	?>
		
		<tr class="<?=$post;?>" id="<?=$item->product_id;?>">
			<td><span id="<?=$item->product_id;?>" class="product_name" title="<?=$product_name;?>"><?=$product_name;?></span></td>
			<td align="center">
				<?php 
				if (!empty($item->product_image)): 
					$image = explode(';',$item->product_image);
				?>
				<img src="<?=site_url();?>/image/resize/<?=$image[0];?>/50/50" class="thumbnail" alt=""/>
				<?php else: ?>
				<img src="<?=base_url();?>assets/images/nophoto_50x50.gif" class="thumbnail" width="50" height="50" alt="" />
				<?php endif; ?>
			</td>
			<td><?=$item->product_no;?></td>
			<td align="center"><?=status($item->product_disabled);?></td>
			<td align="center"><?php if ($item->product_qty > 0) { echo $item->product_qty; } else { echo '<span class="redtext">' . $item->product_qty . '</span>'; } ?></td>
			<td>
				<input type="hidden" name="qty" value="1" />
				<?php
				$option_groups = $this->inventory_model->getProductOptionGroups($item->product_id);
					
				if ($option_groups > 0):
				foreach ($option_groups as $group) {
				?>
				<select name="product_option[]" rel="<?=$item->product_id;?>" class="product_option product_option_<?=$item->product_id;?>">
				<?php	
					$options = $this->inventory_model->getProductOptions($item->product_id, $group->option_label);	
				
					foreach ($options as $option) {
					
					//Option price
					$option_price = ($option->option_price > 0) ? '(+'.money($option->option_price).')' :  '';
				?>
					<option value="<?=$option->option_price;?>"><?=$group->option_label;?>-<?=$option->option_criteria;?> <?=$option_price;?></option>
				<?php } ?>
				</select><br/>
				<?php }
				endif;
				?>
			</td>
			<td class="product_price" align="center">
				<?php 
				if($item->product_saleprice > '0.00') { 
					echo sprintf('<span class="redtext">%s</span>', money($item->product_saleprice));
				} else {
					print money($item->price);
				}
				?>
			</td>
			<td align="center">
				<?php
				if ($item->price > 0) {
				?>
				<input type="hidden" subject="This field is used for product option calculations" name="base_price" value="<?=$item->price;?>" />
				<input type="hidden" name="lookup_product_id" id="lookup_product_id" value="<?=$item->product_id;?>" />
				<input type="hidden" name="lookup_cat_id" value="<?=$item->cat_id;?>" />
				<input type="hidden" name="lookup_product_no" value="<?=$item->product_no;?>" />
				<input type="hidden" name="lookup_product_name" value="<?=str_replace('"', '', $product_name);?>" />
				<input type="hidden" name="lookup_product_price" value="<?=$item->price;?>" />
				<input type="hidden" name="lookup_product_weight" value="<?=$this->inventory_model->getWeight($item->product_id);?>" />
				<input type="button" class="button copyitem" value="Copy to Order" rel="<?=$item->product_id;?>" />
				<?php } ?>
			</td>
		</tr>
		<?php
		if (trim($item->product_description) != "") {
		?>
		<tr id="i-<?=$item->product_id;?>" class="product_preview">
			<td colspan="8">
			<h4>Product Description</h4>
			<?php
			echo sprintf('<p>%s</p>', nl2br(strip_tags($item->product_description)));
			?>
			</td>
		</tr>
		<?php } ?>
	<?php 
		endforeach; 
	else: 
	?> 
		<tr>
			<td colspan="5">No products found.</td>
		</tr>
	<?php endif; ?>
	</tbody>
	</table>

</body>
</html>