{shopit:breadcrumb}

<h2>{product_name}</h2>

<p><em>{product_tags}</em></p>

{product_excerpt}
{product_desc}
{product_attributes}

<p>Condition: {product_condition}</p>

<p>{product_file}</p>
		
<p>{product_stock_level}</p>

<p>From {product_price} {product_delivery}</p>
	
{shopit:specialoffers}
	
<p class="productImage">{product_image}</p>
<div class="productGallery">
	{product_gallery}
	<br clear="all"/>
</div>

{variation_selector}

<table cellpadding="4" cellspacing="2" width="100%" border="1">
	<thead>
		<tr>
			<th>Product Id</th>
			<th>Image</th>
			<th>Name</th>
			<th>Code</th>
			<th>EAN</th>
			<th>MPN</th>
			<th>UPC</th>
			<th>RRP</th>
			<th>RRP ex VAT</th>
			<th>Price</th>
			<th>Price ex VAT</th>
			<th>Sale Price</th>
			<th>Sale Price ex VAT</th>
			<th>Options</th>
			<th>Stock</th>
			<th>Buy</th>
		</tr>
	</thead>
	<tbody>
		{variations}
		<tr class="{variant_css_classes}">
			<form method="post" action="<?=site_url('basket/additem');?>">
			<td>{variant_id}</td>
			<td align="center">{variant_product_image}</td>
			<td>{variant_product_name} {shopit:specialoffers}</td>
			<td>{variant_product_code}</td>
			<td>{variant_product_ean}</td>
			<td>{variant_product_mpn}</td>
			<td>{variant_product_upc}</td>
			<td>{variant_product_rrp}</td>
			<td>{variant_product_rrp_exvat}</td>
			<td>{variant_product_price}</td>
			<td>{variant_product_price_exvat}</td>
			<td>{variant_product_saleprice}</td>
			<td>{variant_product_saleprice_exvat}</td>
			<td>
				{variant_product_options}
					<label>{option_group}</label>
					<select name="product_option[{option_number}]">
						{option}
						<option value="{option_id}">{option_label} (+{option_price}, {option_price_exvat} ex VAT)</option>}
						{/option}
					</select>
				{/variant_product_options}
			</td>
			<td>{variant_stock_level}</td>
			<td>
				{variant_qty_select}
				{variant_buybtn}
			</td>
			</form>
		</tr>
		{/variations}
	</tbody>
</table>

{shopit:related_items}
