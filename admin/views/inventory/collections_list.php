<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="4"><h2>Manage Collections</h2></td>
			</tr>
			<tr>
				<th width="30%">Collection</th>
				<th width="60%">URL</th>
				<th width="10%"><center>Products</center></th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>

		<?php
		foreach ($collection_groups as $group) {
			$group_title = ($group->collection_group == 0) ? "Ungrouped" : $group->group_label;
		?>

		<tr class="table-row">
			<td colspan="4"><h3><?=$group_title;?></h3></td>
		</tr>
			
		<tbody class="sortable-collections">
			<?php
			
			$i = 0; //(A) used for background colour
			$the_collections = $this->collections_model->getCollectionsList($group->collection_group);
			
			foreach($the_collections as $collection):
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
			<tr class="table-row <?=$post;?>" id="<?=$collection->collection_id;?>">
				<td>
					<input type="hidden" name="collection_id[]" value="<?=$collection->collection_id;?>" />
					<i class="fa fa-bars draggable"></i>
					<a href="<?=site_url('/collections/manage/' . $collection->collection_id);?>"><?=($collection->collection_name);?></a>
				</td>
				<td><code><?=site_root('collections/'.$collection->collection_slug);?> <a href="<?=site_root('collections/'.$collection->collection_slug);?>">&rarr;</a></code></td>
				<td align="center"><?=$this->collections_model->product_count($collection->collection_id);?></td>
				<td>
					<ul class="actions">
						<li><a href="#" class="btn-action"><i class="fa fa-angle-down"></i></a>
							<ul>
								<li><a href="<?=site_url('collections/edit/'.$collection->collection_id);?>">Edit</a></li>
								<li><a href="<?=site_url('collections/manage/'.$collection->collection_id);?>">Manage</a></li>
								<?php if ($collection->collection_lock == '0'): ?>
								<li><a href="<?=site_url('collections/delete/'.$collection->collection_id);?>" class="ajaxdelete">Delete</a></li>
								<?php endif;?>
							</ul>
						</li>
					</ul>
				</td>
			</tr>
			<?php endforeach; ?>
			
		</tbody>
		<?php
		}
		?>	
	
	</table>

</div>

<div id="sidebar">
	<h3>What are collections?</h3>
	
	<p>Collections are very similar to categories with the main difference being that you can organise products into sets and organise them in the order you want customers to see them.</p>
	
	<p>Examples could be the perfect BBQ, Mother's Day gifts, Father's Day gifts, Winter specials, etc.</p>
	
	<p>Collections can be turned off and on whenever you like.</p>
</div>