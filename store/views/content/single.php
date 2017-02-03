{shopit:breadcrumb}
	
	<h2>{product_name}</h2>
	
	<p class="productImage">{product_image}</p>
	<p class="productGallery">
		{product_gallery}
		<br clear="all"/>
	</p>

	<p><em>{product_tags}</em></p>
	
	{product_excerpt}
	{product_desc}
	{product_attributes}
	
	<p>
		Condition: {product_condition}<br/>
		{product_stock_level}
	</p>

	<p>{product_file}</p>
		

	<p>
		<strong>Price: {product_price} {product_delivery}</strong><br/>
		<strong>Price exc VAT: {product_price_exvat} ex VAT</strong>
	</p>
	
	<form method="post" action="<?=site_url('basket/additem');?>">
		{product_options}
		<fieldset class="product_option_group">
			<legend>{option_group}</legend>
			{option}
			<label>
				<input type="radio" name="product_option[{option_number}]" value="{option_id}" {option_checked} />
				{option_label} (+{option_price}, {option_price_exvat} ex VAT)
			</label><br/>
			{/option}
		</fieldset>
		{/product_options}
		<p>
			{qty_select}
			{product_buybtn}
		</p>
	</form>
		
	{shopit:specialoffers}
	
	<p>Product Code: {product_code}</p>
	
{shopit:related_items}

<br clear="all"/>
