<nav>
	<h3>Pages</h3>

	<ul>
		{shopit:pages}
	</ul>

</nav>

<nav>

	<?php
	$brands = $this->core->get_brands();
	if (!empty($brands)): 
	?>
	<h3>Shop by brand</h3>
	
	<ul>
	<?php 
	foreach ($brands as $brand): 
		$brand = (object) $brand;
		if ($brand->product_brand != ''):	
		?>
		<li><a href="<?=site_url('brand/' . slug($brand->product_brand));?>"><?=$brand->product_brand;?></a></li>
		<?php 
		endif;
	endforeach;	
	?>
	</ul>
	<?php endif; ?>

</nav>

<?php cookie_monster(); ?>

</body>
</html>