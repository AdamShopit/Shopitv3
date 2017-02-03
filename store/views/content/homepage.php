<section>
	<h2>{doc_title}</h2>
	{doc_content}
</section>

{shopit:search}

<article>

	<h3>Latest Products</h3>
	
	<ul>
		{item}
		<li class="{css_classes}">
			<p><a href="{url}">{product_image}</a></p>
			<h4><a href="{url}">{product_name}</a></h4>
			<p>{product_price}</p>
			{shopit:specialoffers}
		</li>
		{/item}
	</ul>

</article>
