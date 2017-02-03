<div id="content">
	<form method="post" action="<?=site_url('category/sortable');?>">
	<table cellpadding="0" cellspacing="0" border="0" id="sortable-categories">
		<thead>
			<tr>
				<td colspan="7"><h2>Categories</h2></td>
			</tr>
			<tr>
				<th width="8%"><center>ID</center></th>
				<th width="72%">Category Title</th>
				<th width="5%"><center>Sub-categories</center></th>
				<th width="5%"><center>Products</center></th>
				<th width="5%"><center>Disabled products</center></th>
				<th width="5%"><center>Status</center></th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 0; //(A) used for background colour
			
			if ($categories > 0) {
			
			foreach($categories as $category):
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
				
				if ($category->cat_hide == 1) {
					$cat_hide = 'lightgrey';
				} else {
					$cat_hide = null;
				}
		?>
		<tr class="<?=$post;?>">
			<td align="center"><?=$category->cat_id;?></td>
			<td>
				<input type="hidden" name="cat_id[]" value="<?=$category->cat_id;?>" />
				<i class="valign draggable fa fa-bars"></i>
				<a href="<?=site_url('category/sub/'.$category->cat_id);?>"><?=$category->cat_name;?></a>
			</td>
			<td align="center">
				<?=$this->category_model->subcat_count($category->cat_id)-1;?>
			</td>
			<td align="center">
				<?=$this->category_model->all_product_count($category->cat_id);?>
			</td>
			<td align="center">
				<?=$this->category_model->live_product_count($category->cat_id, 1);?>
			</td>
			<td align="center"><?=status($category->cat_hide);?></td>
			<td>
				<ul class="actions">
					<li><a href="#" class="btn-action"><i class="fa fa-angle-down"></i></a>
						<ul>
							<li><a href="<?=site_url('category/edit/'.$category->cat_id.$redirect);?>">Edit category</a></li>
							<li><a href="<?=site_url('category/sub/'.$category->cat_id);?>">Manage category</a></li>
							<li><a href="<?=site_url('category/delete/'.$category->cat_id);?>">Delete category</a></li>
							<li><a href="<?=site_url('inventory/index/0/filter=true&s_category=category-'.$category->cat_id);?>">View products</a></li>
							<li class="nav-separator lineonly">&nbsp;</li>
							<li><a href="<?=site_url('filters/manage/'.$category->cat_id);?>">Manage filters</a></li>
						</ul>
					</li>
				</ul>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php } else { ?>
		<tr>
			<td colspan="7">You have no categories setup yet.</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	</form>
</div>

<div id="sidebar">
	<h3>Sorting categories</h3>
	<p>From this screen you can also sort categories into your preferred order simply by dragging each one to its new position.</p>

	<form method="post" action="<?=site_url('category/export');?>">
		<h3>Export CSV file</h3>
		<p>If you would like to export the full list if categories, click the "Export CSV" button below.</p>
		<p align="right">
			<input type="submit" name="export" value="Export CSV" class="button" />
		</p>	
	</form>	
</div>