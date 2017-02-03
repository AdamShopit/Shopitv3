<?php if (!empty($relateditems)) { ?>
<h3>You may also be interested in...</h3>

{relateditems}
<div class="relateditem {css_classes}">
	
	<a href="{item_url}">{item_image}</a>
	<p><a href="{item_url}">{item_name}</a><br/>
	<span>{item_price}</span></p>
	
</div>
{/relateditems}
<?php } else { ?>

<?php } ?>