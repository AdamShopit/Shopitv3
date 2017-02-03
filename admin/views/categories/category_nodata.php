<div id="content">
		
	<div class="table">
		
		<?php if(!empty($parent_cat)): ?>
		<h2>Manage Sub Categories for <?=$parent_cat;?> <a href="<?=site_url("category/add/$parent_id");?>">Add new sub-category</a>
			<?php if ($is_third_tier == true): ?>
			<a href="<?=site_url("category/sub/$parent_father_id");?>">View parent categories</a>
			<?php else: ?>
			<a href="<?=site_url('category');?>">View parent categories</a>
			<?php endif; ?>
		</h2>
		<?php else: ?>
		<h2>Manage Sub Categories</h2>
		<?php endif;?>
		
		<div class="table-row">
			<p align="center">No categories have been created.</p>
		</div>
	
	</div>

</div>

<div id="sidebar">
	<h3>Sorting categories</h3>
	<p>From this screen you can also sort categories into your preferred order simply by dragging each one to its new position.</p>
	
	<form method="post" target="_blank" action="<?=site_url('category/export');?>">
		<h3>Export CSV file</h3>
		<p>If you would like to export the full list if categories, click the "Export CSV" button below.</p>
		<p align="right">
			<input type="submit" name="export" value="Export CSV" class="button" />
		</p>	
	</form>
</div>