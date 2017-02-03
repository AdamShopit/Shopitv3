{shopit:breadcrumb}

<h2>{cat_name}</h2>

{cat_desc}

{cat_excerpt}

{shopit:layers}

<?php if ($item > 0): ?>
<p>Showing {showing} of {total_products} items</p>

{shopit:sort_options}

<article>

	<ul>
		{item}
		<li class="product {css_classes}">
			<p><a href="{url}">{product_image}</a></p>
			<h4><a href="{url}">{product_name}</a></h4>
			<div>{product_price}</div>
			{shopit:specialoffers}
		</li>
		{/item}
	</ul>

</article>

{if {pagination} != ""}
<nav>
	<h3>Pagination</h3>
	{pagination}
</nav>
{/if}

<?php else:?>

<p><strong>{message}</strong></p>

{shopit:sort_options}

<?php endif;?>

