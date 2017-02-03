<div id="content">

	<table cellpadding="0" cellspacing="0" border="0" id="relateditems">
		<thead>
			<tr>
				<td colspan="4"><h2>Similar &amp; Related items for <?=$item->product_name;?></h2></td>
			</tr>
			<tr>
				<th width="50%">Product name</th>
				<th width="15%">Code</th>
				<th width="25%">Type</th>
				<th width="10%">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php			
		if (!empty($relateditems)) {
		foreach ($relateditems as $relateditem) :

			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
			
			switch ($relateditem->type) {
				case 'R':
					$type = 'Related';
					break;
				default:
					$type = $relateditem->label;
					break;
			}
			
			$product_name = ($relateditem->product_type == 'variant') ? sprintf('%s <small class="smallprint">&mdash; Variation of %s</small>', unserialize_variant($relateditem->product_name), $relateditem->parent_name) : $relateditem->product_name;
				
		?>
			<tr class="tbody <?=$post;?>">
				<td>
					<?=status($relateditem->product_disabled);?>&nbsp;
					<?=$product_name;?>
				</td>
				<td><?=$relateditem->product_no;?></td>
				<td><?=$type;?></td>
				<td align="center">
					<input type="hidden" name="related_items_id[]" value="<?=$relateditem->xitem_id;?>" />
					<input type="hidden" name="related_items_delete[]" value="false" />
					<input type="hidden" name="related_items_type[]" value="<?=$relateditem->type;?>" />
					<a class="button removerelateditem">Remove</a>
				</td>
			</tr>
		<?php
		endforeach;
		}
		?>			
		</tbody>
	</table>

	<div class="table">
		
		<div class="table-row">
			<h3>Add Items</h3>
			<span class="darkgrey">Use this section to link items to this product.</span>
		</div>
		
		<div class="table-row">
			<label>Type product name or code:</label>
			<input name="related_items" id="related_items" size="75" class="textbox" />
		</div>
		
		<div class="inner-table-row" id="showrelatedresults"></div>
	
		<input type="hidden" name="product_id" value="<?=$item->product_id;?>" />

	</div>
</div>

<div id="sidebar">

</div>