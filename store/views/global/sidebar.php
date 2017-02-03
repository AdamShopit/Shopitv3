<nav>
	<h3>Shop by category</h3>
	<ul>
		{shopit:categories}
	</ul>
</nav>

<nav>
	<h3>Collections List</h3>
	<p>This list is generated using the <code>$this->core->collections_list()</code> core library function. Returns <code>NULL</code> if no collections exist.</p>
	<?php
	$collection_group_id = 0;
	echo $this->core->collections_list($collection_group_id);
	?>
</nav>

{shopit:mybasket}

<h3>Alternative Basket Summary</h3>

<p>An alternative basket is available using the <code>$this->core->mybasket()</code> core library function. This is useful for basket popup menus, etc. Returns an unordered list or <code>NULL</code> if nothing's in the basket.</p>

<p>If there's anything in the basket, it'll be displayed below as an unordered list.</p>

<?php
$thumbnail_size = 75;
$include_vat = true;
echo  $this->core->mybasket($thumbnail_size, $include_vat);
?>